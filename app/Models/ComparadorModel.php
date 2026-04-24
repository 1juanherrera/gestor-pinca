<?php

namespace App\Models;

class ComparadorModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    // ── Vista 1: mismo producto ofrecido por distintos proveedores ────────
    // Agrupa por nombre normalizado y devuelve todos los proveedores
    // que lo ofrecen con su precio actual.
    public function por_item(): array
    {
        $sql = '
            SELECT
                ip.nombre                               AS nombre,
                ip.tipo                                 AS tipo,
                uc.nombre                               AS unidad_empaque,
                ip.id_item_proveedor                    AS id_item_proveedor,
                ip.codigo                               AS codigo,
                ip.precio_unitario                      AS precio_unitario,
                ip.precio_con_iva                       AS precio_con_iva,
                ip.disponible                           AS disponible,
                ip.item_general_id                      AS item_general_id,
                ig.nombre                               AS item_general_nombre,
                p.id_proveedor                          AS id_proveedor,
                p.nombre_empresa                        AS nombre_empresa,
                p.nombre_encargado                      AS nombre_encargado
            FROM item_proveedor ip
            LEFT JOIN proveedor    p  ON p.id_proveedor     = ip.proveedor_id
            LEFT JOIN item_general ig ON ig.id_item_general = ip.item_general_id
            LEFT JOIN unidad       uc ON uc.id_unidad       = ip.unidad_compra_id
            ORDER BY ip.nombre ASC, ip.precio_unitario ASC
        ';

        $rows = $this->db->query($sql)->getResult();

        // Agrupar por nombre del producto para facilitar la comparación
        $grouped = [];
        foreach ($rows as $row) {
            $key = strtolower(trim($row->nombre));

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'nombre'               => $row->nombre,
                    'tipo'                 => $row->tipo,
                    'unidad_empaque'       => $row->unidad_empaque,
                    'item_general_id'      => $row->item_general_id,
                    'item_general_nombre'  => $row->item_general_nombre,
                    'precio_min'           => (float) $row->precio_unitario,
                    'precio_max'           => (float) $row->precio_unitario,
                    'proveedores'          => [],
                ];
            }

            $precio = (float) $row->precio_unitario;
            if ($precio < $grouped[$key]['precio_min']) $grouped[$key]['precio_min'] = $precio;
            if ($precio > $grouped[$key]['precio_max']) $grouped[$key]['precio_max'] = $precio;

            $grouped[$key]['proveedores'][] = [
                'id_item_proveedor' => $row->id_item_proveedor,
                'codigo'            => $row->codigo,
                'precio_unitario'   => (float) $row->precio_unitario,
                'precio_con_iva'    => (float) $row->precio_con_iva,
                'disponible'        => $row->disponible,
                'id_proveedor'      => $row->id_proveedor,
                'nombre_empresa'    => $row->nombre_empresa,
                'nombre_encargado'  => $row->nombre_encargado,
            ];
        }

        return array_values($grouped);
    }

    // ── Vista 2: todos los productos de un proveedor ordenados por precio ─
    public function por_proveedor(int $proveedorId): array
    {
        $sql = '
            SELECT
                ip.*,
                p.nombre_empresa,
                p.nombre_encargado,
                ig.nombre  AS item_general_nombre,
                ig.codigo  AS item_general_codigo
            FROM item_proveedor ip
            LEFT JOIN proveedor    p  ON p.id_proveedor     = ip.proveedor_id
            LEFT JOIN item_general ig ON ig.id_item_general = ip.item_general_id
            WHERE ip.proveedor_id = ?
            ORDER BY ip.precio_unitario ASC
        ';

        $rows = $this->db->query($sql, [$proveedorId])->getResult();

        return array_map(function ($row) {
            $row = (array) $row;
            $row['precio_unitario'] = (float) $row['precio_unitario'];
            $row['precio_con_iva']  = (float) $row['precio_con_iva'];
            return $row;
        }, $rows);
    }

    // ── Vista 3: historial de precios de un item_proveedor ────────────────
    public function historial(int $itemProveedorId): array
    {
        $sql = '
            SELECT
                hp.id_historial,
                hp.precio_unitario,
                hp.precio_con_iva,
                hp.fecha,
                hp.observacion,
                hp.creado_en,
                ip.nombre           AS nombre_producto,
                ip.codigo           AS codigo_producto,
                p.nombre_empresa    AS nombre_empresa
            FROM historial_precios hp
            JOIN item_proveedor ip ON ip.id_item_proveedor = hp.item_proveedor_id
            JOIN proveedor      p  ON p.id_proveedor       = ip.proveedor_id
            WHERE hp.item_proveedor_id = ?
            ORDER BY hp.fecha ASC
        ';

        $rows = $this->db->query($sql, [$itemProveedorId])->getResult();

        return array_map(function ($row) {
            $row = (array) $row;
            $row['precio_unitario'] = (float) $row['precio_unitario'];
            $row['precio_con_iva']  = (float) $row['precio_con_iva'];
            return $row;
        }, $rows);
    }
}