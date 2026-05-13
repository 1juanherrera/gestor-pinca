<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Audit log de inventario.
 *
 * Cada cambio de stock (recepción de OC, venta, despacho, traspaso, ajuste,
 * consumo de producción, anulaciones reversas) se registra aquí con:
 *  - tipo_movimiento (ENTRADA | SALIDA | TRASPASO | AJUSTE)
 *  - saldos antes/después
 *  - costo unitario al momento del movimiento
 *  - referencia al documento origen (tipo + id)
 *  - responsable
 *  - metadata JSON con detalle completo del evento
 */
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
        'responsable',
        'metadata',
        'created_at',
    ];

    /**
     * Tipos canónicos de movimiento.
     */
    public const TIPO_ENTRADA  = 'ENTRADA';
    public const TIPO_SALIDA   = 'SALIDA';
    public const TIPO_TRASPASO = 'TRASPASO';
    public const TIPO_AJUSTE   = 'AJUSTE';

    /**
     * Referencias canónicas (origen del movimiento).
     */
    public const REF_OC          = 'ORDEN_COMPRA';
    public const REF_FACTURA     = 'FACTURA_VENTA';
    public const REF_REMISION    = 'REMISION';
    public const REF_PRODUCCION  = 'ORDEN_PRODUCCION';
    public const REF_TRASPASO    = 'TRASPASO_BODEGA';
    public const REF_AJUSTE      = 'AJUSTE_MANUAL';
    public const REF_ANULACION   = 'ANULACION';

    /**
     * Calcula el saldo TOTAL actual del item (suma de capas activas).
     * Si se pasa bodega_id, lo limita a esa bodega.
     */
    public function saldoActual(int $itemId, ?int $bodegaId = null): float
    {
        $q = $this->db->table('inventario_capas')
            ->selectSum('cantidad_disponible', 'total')
            ->where('item_general_id', $itemId)
            ->where('estado', 1)
            ->where('cantidad_disponible >', 0);
        if ($bodegaId !== null) {
            $q->where('bodegas_id', $bodegaId);
        }
        $row = $q->get()->getRowArray();
        return (float) ($row['total'] ?? 0);
    }

    /**
     * Helper centralizado de registro. SIEMPRE usar este método para que el
     * audit log sea consistente.
     *
     * Si saldo_anterior/saldo_nuevo NO se proveen, los calcula a partir del
     * stock actual (útil cuando el movimiento ya se aplicó en BD).
     *
     * @param array $data {
     *     tipo:           ENTRADA | SALIDA | TRASPASO | AJUSTE  (REQUIRED)
     *     item_general_id (REQUIRED)
     *     cantidad        (REQUIRED, siempre POSITIVA — el signo lo da `tipo`)
     *     bodega_id
     *     referencia_tipo (REQUIRED — usar constantes REF_*)
     *     referencia_id
     *     descripcion
     *     costo_unitario
     *     saldo_anterior  (opcional — autocalcula si falta)
     *     saldo_nuevo     (opcional — autocalcula si falta)
     *     responsable
     *     metadata        (array, se serializa a JSON)
     * }
     * @return int|false  ID del movimiento o false si falla
     */
    public function registrar(array $data)
    {
        if (empty($data['tipo']) || empty($data['item_general_id']) || !isset($data['cantidad'])) {
            log_message('warning', '[MovimientoInventario] registrar() llamado sin datos mínimos');
            return false;
        }

        $itemId   = (int) $data['item_general_id'];
        $bodegaId = isset($data['bodega_id']) ? (int) $data['bodega_id'] : null;

        // Auto-calcular saldos si no vienen
        $saldoNuevo     = $data['saldo_nuevo']     ?? $this->saldoActual($itemId, $bodegaId);
        $cantidad       = abs((float) $data['cantidad']);

        if (!isset($data['saldo_anterior'])) {
            // Inferir el saldo anterior según el tipo
            $saldoAnterior = match ($data['tipo']) {
                self::TIPO_ENTRADA  => $saldoNuevo - $cantidad,
                self::TIPO_SALIDA   => $saldoNuevo + $cantidad,
                default             => $saldoNuevo,
            };
        } else {
            $saldoAnterior = $data['saldo_anterior'];
        }

        $row = [
            'tipo_movimiento'  => $data['tipo'],
            'cantidad'         => $cantidad,
            'fecha_movimiento' => $data['fecha'] ?? date('Y-m-d H:i:s'),
            'descripcion'      => isset($data['descripcion']) ? mb_substr((string) $data['descripcion'], 0, 255) : null,
            'referencia_tipo'  => $data['referencia_tipo'] ?? null,
            'referencia_id'    => isset($data['referencia_id']) ? (int) $data['referencia_id'] : null,
            'item_general_id'  => $itemId,
            'bodega_id'        => $bodegaId,
            'costo_unitario'   => isset($data['costo_unitario']) ? (float) $data['costo_unitario'] : null,
            'saldo_anterior'   => round((float) $saldoAnterior, 4),
            'saldo_nuevo'      => round((float) $saldoNuevo, 4),
            'responsable'      => $data['responsable'] ?? 'sistema',
            'metadata'         => isset($data['metadata']) && is_array($data['metadata'])
                                    ? json_encode($data['metadata'], JSON_UNESCAPED_UNICODE)
                                    : null,
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        $ok = $this->insert($row);
        if (!$ok) {
            log_message('error', '[MovimientoInventario] insert falló: ' . json_encode($this->errors()));
            return false;
        }
        return $this->getInsertID();
    }

    /**
     * Atajo para registrar el reverso de un movimiento previo (anulaciones).
     */
    public function registrarReverso(array $original, string $motivo, ?string $responsable = null)
    {
        $tipoReverso = $original['tipo'] === self::TIPO_ENTRADA
            ? self::TIPO_SALIDA
            : self::TIPO_ENTRADA;

        return $this->registrar([
            'tipo'             => $tipoReverso,
            'item_general_id'  => $original['item_general_id'],
            'bodega_id'        => $original['bodega_id'] ?? null,
            'cantidad'         => $original['cantidad'],
            'referencia_tipo'  => self::REF_ANULACION,
            'referencia_id'    => $original['referencia_id'] ?? null,
            'descripcion'      => "Reverso: {$motivo}",
            'costo_unitario'   => $original['costo_unitario'] ?? null,
            'responsable'      => $responsable,
            'metadata'         => array_merge($original['metadata'] ?? [], ['motivo_reverso' => $motivo]),
        ]);
    }

    /**
     * @deprecated Usar registrar() en su lugar.
     */
    public function registrarMovimiento(array $data)
    {
        return $this->insert($data);
    }

    /**
     * Lista paginada con filtros y joins necesarios.
     */
    public function get_movimientos(array $filtros = [], int $page = 1, int $limit = 20)
    {
        $builder = $this->builder();
        $builder->select('movimiento_inventario.*,
                          item_general.nombre AS item_nombre,
                          item_general.codigo AS item_codigo,
                          bodegas.nombre      AS bodega_nombre');
        $builder->join('item_general', 'item_general.id_item_general = movimiento_inventario.item_general_id', 'left');
        $builder->join('bodegas',      'bodegas.id_bodegas           = movimiento_inventario.bodega_id', 'left');

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
            $builder->where('DATE(movimiento_inventario.fecha_movimiento) >=', $filtros['fecha_inicio']);
        }
        if (!empty($filtros['fecha_fin'])) {
            $builder->where('DATE(movimiento_inventario.fecha_movimiento) <=', $filtros['fecha_fin']);
        }
        if (!empty($filtros['search'])) {
            $search = $filtros['search'];
            $builder->groupStart();
            $builder->like('item_general.nombre', $search);
            $builder->orLike('item_general.codigo', $search);
            $builder->orLike('movimiento_inventario.descripcion', $search);
            $builder->orLike('movimiento_inventario.responsable', $search);
            $builder->groupEnd();
        }

        $builder->orderBy('movimiento_inventario.id_movimiento_inventario', 'DESC');

        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();

        $offset = ($page - 1) * $limit;
        $builder->limit($limit, $offset);
        $data = $builder->get()->getResult();

        // Decodificar metadata JSON y castear numéricos
        $dataFormatted = array_map(function ($row) {
            $row->cantidad       = (float) $row->cantidad;
            $row->costo_unitario = (float) $row->costo_unitario;
            $row->saldo_anterior = (float) $row->saldo_anterior;
            $row->saldo_nuevo    = (float) $row->saldo_nuevo;
            if (!empty($row->metadata)) {
                $decoded = json_decode($row->metadata, true);
                $row->metadata = is_array($decoded) ? $decoded : null;
            } else {
                $row->metadata = null;
            }
            return $row;
        }, $data);

        return [
            'data' => $dataFormatted,
            'meta' => [
                'total' => $total,
                'page'  => $page,
                'limit' => $limit,
                'pages' => (int) ceil($total / $limit),
            ],
        ];
    }
}
