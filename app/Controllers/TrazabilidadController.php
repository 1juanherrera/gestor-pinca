<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Trazabilidad de lote — vínculo bidireccional entre lote del proveedor y
 * producción. Resuelve dos preguntas críticas en industria química:
 *
 *   1. Dada una preparación / tambor producido: ¿qué lotes de MP entraron?
 *      → GET /api/trazabilidad/preparacion/:id
 *
 *   2. Dado un lote del proveedor con problema: ¿qué preparaciones lo usaron?
 *      → GET /api/trazabilidad/lote/:lote
 *
 *   3. Autocomplete de lotes existentes:
 *      → GET /api/trazabilidad/lotes?q=texto
 *
 * Granularidad: usa `preparacion_consumo_capas` JOIN `inventario_capas` para
 * el detalle real (varios lotes por línea soportados). El campo `lote_proveedor`
 * en `produccion_insumos_detalle` es el snapshot rápido para queries inversas
 * típicas (cuando una preparación consumió un único lote del item).
 */
class TrazabilidadController extends ResourceController
{
    use \App\Traits\JwtUserAware;

    /**
     * GET /api/trazabilidad/preparacion/:id
     * Devuelve el árbol completo de origen: preparación → MP → capas/lotes/proveedores.
     */
    public function porPreparacion(int $id = 0): ResponseInterface
    {
        if ($id <= 0) return $this->fail('ID de preparación requerido', 400);

        $db = \Config\Database::connect();

        // Cabecera de la preparación
        $prep = $db->query("
            SELECT
                p.id_preparaciones,
                p.cantidad,
                p.fecha_creacion,
                p.fecha_inicio,
                p.fecha_fin,
                p.estado,
                p.observaciones,
                p.item_general_id,
                ig.nombre AS producto_nombre,
                ig.codigo AS producto_codigo,
                u.nombre  AS unidad_nombre
            FROM preparaciones p
            LEFT JOIN item_general ig ON ig.id_item_general = p.item_general_id
            LEFT JOIN unidad u        ON u.id_unidad        = p.unidad_id
            WHERE p.id_preparaciones = ?
        ", [$id])->getRowArray();

        if (!$prep) return $this->failNotFound("Preparación #{$id} no encontrada");

        // Detalle de capas consumidas (granularidad real por lote)
        $consumos = $db->query("
            SELECT
                pcc.item_general_id,
                ig.nombre  AS item_nombre,
                ig.codigo  AS item_codigo,
                pcc.cantidad_consumida,
                pcc.costo_unitario,
                pcc.costo_total,
                ic.id_capa,
                ic.lote_proveedor,
                ic.fecha_ingreso       AS fecha_ingreso_capa,
                ic.orden_compra_id,
                oc.numero              AS orden_compra_numero,
                ic.proveedor_id,
                p.nombre_empresa       AS proveedor_nombre,
                b.nombre               AS bodega_nombre
            FROM preparacion_consumo_capas pcc
            JOIN inventario_capas ic ON ic.id_capa     = pcc.capa_id
            JOIN item_general ig     ON ig.id_item_general = pcc.item_general_id
            LEFT JOIN proveedor p    ON p.id_proveedor = ic.proveedor_id
            LEFT JOIN bodegas b      ON b.id_bodegas   = ic.bodegas_id
            LEFT JOIN ordenes_compra oc ON oc.id_orden = ic.orden_compra_id
            WHERE pcc.preparacion_id = ?
            ORDER BY pcc.item_general_id, ic.fecha_ingreso ASC
        ", [$id])->getResultArray();

        // Agrupar por ingrediente
        $porIngrediente = [];
        foreach ($consumos as $c) {
            $key = $c['item_general_id'];
            if (!isset($porIngrediente[$key])) {
                $porIngrediente[$key] = [
                    'item_general_id' => (int) $c['item_general_id'],
                    'nombre'          => $c['item_nombre'],
                    'codigo'          => $c['item_codigo'],
                    'cantidad_total'  => 0,
                    'costo_total'     => 0,
                    'capas'           => [],
                ];
            }
            $porIngrediente[$key]['cantidad_total'] += (float) $c['cantidad_consumida'];
            $porIngrediente[$key]['costo_total']    += (float) $c['costo_total'];
            $porIngrediente[$key]['capas'][] = [
                'capa_id'             => (int) $c['id_capa'],
                'lote_proveedor'      => $c['lote_proveedor'],
                'fecha_ingreso'       => $c['fecha_ingreso_capa'],
                'orden_compra_id'     => $c['orden_compra_id'] ? (int) $c['orden_compra_id'] : null,
                'orden_compra_numero' => $c['orden_compra_numero'],
                'proveedor_id'        => $c['proveedor_id'] ? (int) $c['proveedor_id'] : null,
                'proveedor_nombre'    => $c['proveedor_nombre'],
                'bodega_nombre'       => $c['bodega_nombre'],
                'cantidad'            => (float) $c['cantidad_consumida'],
                'costo_unitario'      => (float) $c['costo_unitario'],
                'subtotal'            => (float) $c['costo_total'],
            ];
        }

        return $this->respond([
            'preparacion'   => $prep,
            'ingredientes'  => array_values($porIngrediente),
            'totales'       => [
                'ingredientes_count' => count($porIngrediente),
                'capas_count'        => count($consumos),
                'costo_total'        => array_sum(array_column($consumos, 'costo_total')),
            ],
        ]);
    }

    /**
     * GET /api/trazabilidad/lote/:lote
     * Trazabilidad inversa: qué preparaciones consumieron un lote específico.
     * Útil cuando un proveedor reporta un lote defectuoso.
     */
    public function porLote(string $lote = ''): ResponseInterface
    {
        $lote = trim(urldecode($lote));
        if ($lote === '') return $this->fail('Lote requerido', 400);

        $db = \Config\Database::connect();

        // Capas que matchean ese lote
        $capas = $db->query("
            SELECT
                ic.id_capa,
                ic.lote_proveedor,
                ic.item_general_id,
                ig.nombre        AS item_nombre,
                ig.codigo        AS item_codigo,
                ic.proveedor_id,
                p.nombre_empresa AS proveedor_nombre,
                ic.fecha_ingreso,
                ic.cantidad_original,
                ic.cantidad_disponible,
                ic.costo_unitario,
                ic.orden_compra_id,
                oc.numero        AS orden_compra_numero
            FROM inventario_capas ic
            JOIN item_general ig ON ig.id_item_general = ic.item_general_id
            LEFT JOIN proveedor p ON p.id_proveedor    = ic.proveedor_id
            LEFT JOIN ordenes_compra oc ON oc.id_orden = ic.orden_compra_id
            WHERE ic.lote_proveedor = ?
        ", [$lote])->getResultArray();

        if (empty($capas)) {
            return $this->respond([
                'lote'         => $lote,
                'capas'        => [],
                'preparaciones' => [],
                'mensaje'      => 'No se encontró ningún lote con ese código.',
            ]);
        }

        $capaIds = array_column($capas, 'id_capa');
        $placeholders = implode(',', array_fill(0, count($capaIds), '?'));

        // Preparaciones que consumieron alguna de esas capas
        $preparaciones = $db->query("
            SELECT DISTINCT
                p.id_preparaciones,
                p.fecha_creacion,
                p.fecha_inicio,
                p.fecha_fin,
                p.estado,
                p.cantidad,
                ig.nombre AS producto_nombre,
                ig.codigo AS producto_codigo,
                SUM(pcc.cantidad_consumida) AS cantidad_lote_usada,
                SUM(pcc.costo_total)        AS costo_lote_usado
            FROM preparacion_consumo_capas pcc
            JOIN preparaciones p   ON p.id_preparaciones = pcc.preparacion_id
            LEFT JOIN item_general ig ON ig.id_item_general = p.item_general_id
            WHERE pcc.capa_id IN ({$placeholders})
            GROUP BY p.id_preparaciones, p.fecha_creacion, p.fecha_inicio, p.fecha_fin,
                     p.estado, p.cantidad, ig.nombre, ig.codigo
            ORDER BY p.fecha_creacion DESC
        ", $capaIds)->getResultArray();

        return $this->respond([
            'lote'          => $lote,
            'capas'         => array_map(fn($c) => [
                'id_capa'             => (int) $c['id_capa'],
                'item_general_id'     => (int) $c['item_general_id'],
                'item_nombre'         => $c['item_nombre'],
                'item_codigo'         => $c['item_codigo'],
                'proveedor_id'        => $c['proveedor_id'] ? (int) $c['proveedor_id'] : null,
                'proveedor_nombre'    => $c['proveedor_nombre'],
                'fecha_ingreso'       => $c['fecha_ingreso'],
                'cantidad_original'   => (float) $c['cantidad_original'],
                'cantidad_disponible' => (float) $c['cantidad_disponible'],
                'costo_unitario'      => (float) $c['costo_unitario'],
                'orden_compra_id'     => $c['orden_compra_id'] ? (int) $c['orden_compra_id'] : null,
                'orden_compra_numero' => $c['orden_compra_numero'],
            ], $capas),
            'preparaciones' => array_map(fn($p) => [
                'id_preparaciones'    => (int) $p['id_preparaciones'],
                'producto_nombre'     => $p['producto_nombre'],
                'producto_codigo'     => $p['producto_codigo'],
                'cantidad'            => (float) $p['cantidad'],
                'fecha_creacion'      => $p['fecha_creacion'],
                'fecha_inicio'        => $p['fecha_inicio'],
                'fecha_fin'           => $p['fecha_fin'],
                'estado'              => (int) $p['estado'],
                'cantidad_lote_usada' => (float) $p['cantidad_lote_usada'],
                'costo_lote_usado'    => (float) $p['costo_lote_usado'],
            ], $preparaciones),
            'totales' => [
                'capas_count'         => count($capas),
                'preparaciones_count' => count($preparaciones),
            ],
        ]);
    }

    /**
     * GET /api/trazabilidad/lotes?q=texto
     * Autocomplete de lotes existentes (para el filtro del módulo Movimientos).
     */
    public function lotes(): ResponseInterface
    {
        $q = trim((string) $this->request->getGet('q'));
        $db = \Config\Database::connect();

        $builder = $db->table('inventario_capas')
            ->select('lote_proveedor, COUNT(*) as capas, MAX(fecha_ingreso) as ultima_recepcion')
            ->where('lote_proveedor IS NOT NULL')
            ->where('lote_proveedor !=', '')
            ->groupBy('lote_proveedor')
            ->orderBy('ultima_recepcion', 'DESC')
            ->limit(20);

        if ($q !== '') {
            $builder->like('lote_proveedor', $q);
        }

        $rows = $builder->get()->getResultArray();

        return $this->respond(array_map(fn($r) => [
            'lote'              => $r['lote_proveedor'],
            'capas'             => (int) $r['capas'],
            'ultima_recepcion'  => $r['ultima_recepcion'],
        ], $rows));
    }
}
