<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Helpers\Cfg;

/**
 * SaludSistemaController — dashboard de calidad de datos.
 *
 * Consolida en un solo endpoint las métricas que indican qué partes del
 * sistema necesitan atención. Es un complemento agregado al panel principal
 * pensado para gerencia y mantenimiento operativo.
 *
 * GET /api/salud-sistema
 */
class SaludSistemaController extends ResourceController
{
    use \App\Traits\JwtUserAware;

    protected $format = 'json';

    public function index()
    {
        $db = \Config\Database::connect();

        // ── 1. Cobertura de proveedores (% MPs con item_proveedor) ─────────
        $mpsEnFormulas = (int) $db->query("
            SELECT COUNT(DISTINCT igf.item_general_id) AS n
            FROM item_general_formulaciones igf
            INNER JOIN formulaciones f ON f.id_formulaciones = igf.formulaciones_id AND f.estado = 1
            INNER JOIN item_general ig ON ig.id_item_general = igf.item_general_id AND ig.deleted_at IS NULL
        ")->getRow()->n;

        $mpsCubiertas = (int) $db->query("
            SELECT COUNT(DISTINCT igf.item_general_id) AS n
            FROM item_general_formulaciones igf
            INNER JOIN formulaciones f ON f.id_formulaciones = igf.formulaciones_id AND f.estado = 1
            INNER JOIN item_general ig ON ig.id_item_general = igf.item_general_id AND ig.deleted_at IS NULL
            INNER JOIN item_proveedor ip ON ip.item_general_id = igf.item_general_id
              AND ip.disponible = 1 AND ip.deleted_at IS NULL
        ")->getRow()->n;

        $cobertura = [
            'mps_totales'   => $mpsEnFormulas,
            'mps_cubiertas' => $mpsCubiertas,
            'pct'           => $mpsEnFormulas > 0 ? round(($mpsCubiertas / $mpsEnFormulas) * 100, 1) : 0,
        ];

        // ── 2. MPs sin movimiento >90 días (consumo en producción) ─────────
        $mpsSinMovimiento = $db->query("
            SELECT ig.id_item_general, ig.nombre, ig.codigo,
                   COALESCE(SUM(ic.cantidad_disponible), 0) AS stock_kg
            FROM item_general ig
            LEFT JOIN inventario_capas ic
                ON ic.item_general_id = ig.id_item_general AND ic.estado = 1
            WHERE ig.tipo = 1
              AND ig.deleted_at IS NULL
              AND NOT EXISTS (
                SELECT 1 FROM movimiento_inventario mi
                WHERE mi.item_general_id = ig.id_item_general
                  AND mi.fecha_movimiento >= DATE_SUB(NOW(), INTERVAL 90 DAY)
              )
            GROUP BY ig.id_item_general, ig.nombre, ig.codigo
            HAVING stock_kg > 0
            ORDER BY stock_kg DESC
            LIMIT 50
        ")->getResultArray();

        // ── 3. Productos sin fórmula activa ────────────────────────────────
        $productosSinFormula = $db->query("
            SELECT ig.id_item_general, ig.nombre, ig.codigo
            FROM item_general ig
            LEFT JOIN formulaciones f
                ON f.item_general_id = ig.id_item_general AND f.estado = 1
            WHERE ig.tipo = 0
              AND ig.deleted_at IS NULL
              AND f.id_formulaciones IS NULL
            ORDER BY ig.nombre
            LIMIT 50
        ")->getResultArray();

        // ── 4. OCs Enviadas hace >14 días sin recibir ──────────────────────
        $ocsRetrasadas = $db->query("
            SELECT oc.id_orden, oc.numero, oc.fecha, oc.fecha_esperada, oc.total,
                   p.nombre_empresa,
                   DATEDIFF(NOW(), oc.fecha) AS dias_pendiente
            FROM ordenes_compra oc
            LEFT JOIN proveedor p ON p.id_proveedor = oc.proveedor_id
            WHERE oc.estado = 'Enviada'
              AND oc.deleted_at IS NULL
              AND oc.fecha < DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            ORDER BY oc.fecha ASC
            LIMIT 30
        ")->getResultArray();

        // ── 5. Facturas en mora >X días (umbral configurable) ──────────────
        $moraCriticaDias = (int) Cfg::n('mora_critica_dias', 60);
        $facturasEnMora = $db->query("
            SELECT f.id_facturas, f.numero, f.fecha_emision, f.total, f.saldo_pendiente,
                   c.nombre_empresa, c.nombre_encargado,
                   DATEDIFF(NOW(), f.fecha_emision) AS dias_mora
            FROM facturas f
            LEFT JOIN clientes c ON c.id_clientes = f.cliente_id
            WHERE f.deleted_at IS NULL
              AND f.estado IN ('Pendiente', 'Parcial', 'Vencida')
              AND f.saldo_pendiente > 0
              AND f.fecha_emision < DATE_SUB(CURDATE(), INTERVAL ? DAY)
            ORDER BY f.fecha_emision ASC
            LIMIT 30
        ", [$moraCriticaDias])->getResultArray();

        // ── 6. Items archivados con stock activo (anomalía) ────────────────
        $archivadosConStock = $db->query("
            SELECT ig.id_item_general, ig.nombre, ig.codigo,
                   SUM(ic.cantidad_disponible) AS stock_kg
            FROM item_general ig
            INNER JOIN inventario_capas ic
                ON ic.item_general_id = ig.id_item_general AND ic.estado = 1
            WHERE ig.deleted_at IS NOT NULL
              AND ic.cantidad_disponible > 0
            GROUP BY ig.id_item_general, ig.nombre, ig.codigo
            ORDER BY stock_kg DESC
            LIMIT 20
        ")->getResultArray();

        // ── Cálculo de "score" global (0-100) ──────────────────────────────
        // Suma penalidades por cada categoría con problemas.
        $issues = [
            'cobertura_baja'        => $cobertura['pct'] < 80 ? 1 : 0,
            'mps_sin_movimiento'    => count($mpsSinMovimiento) > 0 ? 1 : 0,
            'productos_sin_formula' => count($productosSinFormula) > 0 ? 1 : 0,
            'ocs_retrasadas'        => count($ocsRetrasadas) > 0 ? 1 : 0,
            'facturas_en_mora'      => count($facturasEnMora) > 0 ? 1 : 0,
            'archivados_con_stock'  => count($archivadosConStock) > 0 ? 1 : 0,
        ];
        $totalChecks  = count($issues);
        $issuesActivos = array_sum($issues);
        $score        = $totalChecks > 0
            ? round((($totalChecks - $issuesActivos) / $totalChecks) * 100)
            : 100;

        return $this->respond([
            'score'                  => $score,
            'issues_activos'         => $issuesActivos,
            'total_checks'           => $totalChecks,
            'cobertura'              => $cobertura,
            'mps_sin_movimiento_90d' => array_map(fn($r) => [
                'id'       => (int) $r['id_item_general'],
                'nombre'   => $r['nombre'],
                'codigo'   => $r['codigo'],
                'stock_kg' => (float) $r['stock_kg'],
            ], $mpsSinMovimiento),
            'productos_sin_formula'  => array_map(fn($r) => [
                'id'     => (int) $r['id_item_general'],
                'nombre' => $r['nombre'],
                'codigo' => $r['codigo'],
            ], $productosSinFormula),
            'ocs_retrasadas'         => array_map(fn($r) => [
                'id'              => (int) $r['id_orden'],
                'numero'          => $r['numero'],
                'fecha'           => $r['fecha'],
                'fecha_esperada'  => $r['fecha_esperada'],
                'total'           => (float) $r['total'],
                'proveedor'       => $r['nombre_empresa'],
                'dias_pendiente'  => (int) $r['dias_pendiente'],
            ], $ocsRetrasadas),
            'facturas_en_mora'       => array_map(fn($r) => [
                'id'              => (int) $r['id_facturas'],
                'numero'          => $r['numero'],
                'fecha_emision'   => $r['fecha_emision'],
                'total'           => (float) $r['total'],
                'saldo_pendiente' => (float) $r['saldo_pendiente'],
                'cliente'         => $r['nombre_empresa'] ?: ($r['nombre_encargado'] ?: '—'),
                'dias_mora'       => (int) $r['dias_mora'],
            ], $facturasEnMora),
            'archivados_con_stock'   => array_map(fn($r) => [
                'id'       => (int) $r['id_item_general'],
                'nombre'   => $r['nombre'],
                'codigo'   => $r['codigo'],
                'stock_kg' => (float) $r['stock_kg'],
            ], $archivadosConStock),
            'umbral_mora_dias'       => $moraCriticaDias,
        ]);
    }
}
