<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InventarioCapasModel;

class CapasInventarioController extends ResourceController
{
    use \App\Traits\ApiResponse;

    public function capas($itemGeneralId = null)
    {
        if (!$itemGeneralId) {
            return $this->apiFail('item_general_id requerido.', 422);
        }

        $bodegaId = $this->request->getGet('bodega_id')
            ? (int) $this->request->getGet('bodega_id')
            : null;

        $model   = new InventarioCapasModel();
        $capas   = $model->obtenerCapas((int) $itemGeneralId, $bodegaId);
        $resumen = $model->resumenStock((int) $itemGeneralId);

        $db   = \Config\Database::connect();
        $item = $db->query(
            'SELECT id_item_general, nombre, codigo FROM item_general WHERE id_item_general = ?',
            [$itemGeneralId]
        )->getRow();

        $capasFormatted = array_map(function ($c) {
            return [
                'id_capa'              => (int) $c->id_capa,
                'proveedor_id'         => $c->proveedor_id ? (int) $c->proveedor_id : null,
                'proveedor_nombre'     => $c->proveedor_nombre,
                'bodega_id'            => (int) $c->bodegas_id,
                'bodega_nombre'        => $c->bodega_nombre,
                'cantidad_original'    => (float) $c->cantidad_original,
                'cantidad_disponible'  => (float) $c->cantidad_disponible,
                'costo_unitario'       => (float) $c->costo_unitario,
                'unidad_compra_nombre' => $c->unidad_compra_nombre,
                'factor_conversion'    => $c->factor_conversion ? (float) $c->factor_conversion : null,
                'precio_compra'        => $c->precio_compra ? (float) $c->precio_compra : null,
                'lote_proveedor'       => $c->lote_proveedor,
                'fecha_ingreso'        => $c->fecha_ingreso,
                'dias_en_stock'        => (int) round((time() - strtotime($c->fecha_ingreso)) / 86400),
                'orden_compra_id'      => $c->orden_compra_id ? (int) $c->orden_compra_id : null,
            ];
        }, $capas);

        return $this->respond([
            'item_general_id'          => (int) $itemGeneralId,
            'nombre'                   => $item->nombre ?? null,
            'codigo'                   => $item->codigo ?? null,
            'stock_total'              => $resumen['stock_total'],
            'costo_promedio_ponderado' => $resumen['costo_promedio_ponderado'],
            'total_capas'              => $resumen['total_capas'],
            'capas'                    => $capasFormatted,
        ]);
    }

    public function bodegasConCapas()
    {
        $db   = \Config\Database::connect();
        $rows = $db->query('
            SELECT DISTINCT b.id_bodegas, b.nombre
            FROM inventario_capas ic
            INNER JOIN bodegas b ON b.id_bodegas = ic.bodegas_id
            WHERE ic.estado = 1 AND ic.cantidad_disponible > 0
            ORDER BY b.nombre
        ')->getResult();

        return $this->respond($rows);
    }

    public function consumosPorPreparacion($preparacionId = null)
    {
        if (!$preparacionId) {
            return $this->apiFail('preparacion_id requerido.', 422);
        }

        $db = \Config\Database::connect();
        $consumos = $db->query('
            SELECT pcc.*, ic.proveedor_id, ic.lote_proveedor, ic.fecha_ingreso,
                   p.nombre_empresa AS proveedor_nombre,
                   ig.nombre AS item_nombre, ig.codigo AS item_codigo,
                   b.nombre AS bodega_nombre
            FROM preparacion_consumo_capas pcc
            INNER JOIN inventario_capas ic ON ic.id_capa = pcc.capa_id
            INNER JOIN item_general ig ON ig.id_item_general = pcc.item_general_id
            LEFT JOIN proveedor p ON p.id_proveedor = ic.proveedor_id
            LEFT JOIN bodegas b ON b.id_bodegas = ic.bodegas_id
            WHERE pcc.preparacion_id = ?
            ORDER BY pcc.item_general_id, ic.fecha_ingreso
        ', [$preparacionId])->getResult();

        return $this->respond(array_map(fn($c) => [
            'id'                  => (int) $c->id,
            'capa_id'             => (int) $c->capa_id,
            'item_general_id'     => (int) $c->item_general_id,
            'item_nombre'         => $c->item_nombre,
            'item_codigo'         => $c->item_codigo,
            'cantidad_consumida'  => (float) $c->cantidad_consumida,
            'costo_unitario'      => (float) $c->costo_unitario,
            'costo_total'         => (float) $c->costo_total,
            'proveedor_nombre'    => $c->proveedor_nombre,
            'lote_proveedor'      => $c->lote_proveedor,
            'bodega_nombre'       => $c->bodega_nombre,
            'fecha_ingreso_capa'  => $c->fecha_ingreso,
        ], $consumos));
    }
}
