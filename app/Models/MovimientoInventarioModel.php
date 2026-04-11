<?php

namespace App\Models;

use CodeIgniter\Model;

class MovimientoInventarioModel extends Model
{
    protected $table      = 'movimiento_inventario';
    protected $primaryKey = 'id_movimiento_inventario';

    protected $allowedFields = [
        'tipo_movimiento',
        'cantidad',
        'fecha_movimiento',
        'descripcion',
        'referencia_tipo',
        'item_general_id',
        'bodega_id',
        'referencia_id',
        'costo_unitario',
        'saldo_anterior',
        'saldo_nuevo',
        'responsable'
    ];

    /**
     * Registra un nuevo movimiento de inventario, actualiza saldos si es necesario.
     * Esta función debe ser llamada DENTRO de una transacción en los modelos que la usen
     * (e.g. Compras, Producción, Remisiones).
     */
    public function registrarMovimiento(array $data)
    {
        return $this->insert($data);
    }

    /**
     * Obtiene el listado de movimientos con paginación, filtros y joins necesarios.
     */
    public function get_movimientos(array $filtros = [], int $page = 1, int $limit = 20)
    {
        $builder = $this->builder();
        $builder->select('movimiento_inventario.*, item_general.nombre as item_nombre, item_general.codigo as item_codigo, bodegas.nombre as bodega_nombre');
        $builder->join('item_general', 'item_general.id_item_general = movimiento_inventario.item_general_id', 'left');
        $builder->join('bodegas', 'bodegas.id_bodegas = movimiento_inventario.bodega_id', 'left');

        // Filtros
        if (!empty($filtros['item_general_id'])) {
            $builder->where('movimiento_inventario.item_general_id', $filtros['item_general_id']);
        }
        if (!empty($filtros['bodega_id'])) {
            $builder->where('movimiento_inventario.bodega_id', $filtros['bodega_id']);
        }
        if (!empty($filtros['tipo_movimiento'])) {
            $builder->where('movimiento_inventario.tipo_movimiento', $filtros['tipo_movimiento']);
        }
        if (!empty($filtros['referencia_tipo'])) {
            $builder->where('movimiento_inventario.referencia_tipo', $filtros['referencia_tipo']);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $builder->where('movimiento_inventario.fecha_movimiento >=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $builder->where('movimiento_inventario.fecha_movimiento <=', $filtros['fecha_fin']);
        }
        if (!empty($filtros['search'])) {
            $search = $filtros['search'];
            $builder->groupStart();
            $builder->like('item_general.nombre', $search);
            $builder->orLike('item_general.codigo', $search);
            $builder->orLike('movimiento_inventario.descripcion', $search);
            $builder->groupEnd();
        }

        $builder->orderBy('movimiento_inventario.id_movimiento_inventario', 'DESC');

        // Total registros (clonando builder para no perder joins/where)
        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();

        // Paginación
        $offset = ($page - 1) * $limit;
        $builder->limit($limit, $offset);
        $data = $builder->get()->getResult();

        // Formateo para frontend
        $dataFormatted = array_map(function($row) {
            $row->cantidad = (float) $row->cantidad;
            $row->costo_unitario = (float) $row->costo_unitario;
            $row->saldo_anterior = (float) $row->saldo_anterior;
            $row->saldo_nuevo = (float) $row->saldo_nuevo;
            return $row;
        }, $data);

        return [
            'data' => $dataFormatted,
            'meta' => [
                'total' => $total,
                'page'  => $page,
                'limit' => $limit,
                'pages' => (int) ceil($total / $limit)
            ]
        ];
    }
}
