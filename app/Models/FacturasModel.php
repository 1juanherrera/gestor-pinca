<?php

namespace App\Models;

class FacturasModel extends BaseModel
{
    protected $table      = 'facturas';
    protected $primaryKey = 'id_facturas';
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    // allowedFields se llena dinámicamente en BaseModel,
    // pero lo declaramos para claridad y validación manual
    protected $allowedFields = [
        'numero',
        'cliente_id',
        'fecha_emision',
        'fecha_vencimiento',  // NUEVO
        'subtotal',
        'descuento',          // NUEVO
        'impuestos',
        'retencion',
        'total',
        'saldo_pendiente',    // NUEVO — se recalcula en el controlador
        'estado',
        'observaciones',      // NUEVO
        'movimiento_inventario_id',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Recalcula saldo_pendiente y estado de una factura a partir de
     * pagos_cliente y notas_credito activas.
     *
     * Reglas de estado:
     *  - Anulada / soft-deleted: no se toca.
     *  - saldo ≤ 0.01: Pagada.
     *  - Vencida con abono parcial: sigue Vencida (vencimiento prevalece).
     *  - Otro caso con pagos o NC: Parcial.
     *  - Sin movimientos: vuelve a Pendiente si estaba en Pagada/Parcial.
     */
    public function recalcularSaldo(int $id): void
    {
        $factura = $this->db->table('facturas')
            ->where('id_facturas', $id)
            ->where('deleted_at', null)
            ->get()->getRowArray();

        if (!$factura) return;
        if ($factura['estado'] === 'Anulada') return;

        $total = (float) ($factura['total'] ?? 0);

        $pagos = (float) ($this->db->table('pagos_cliente')
            ->selectSum('monto', 't')
            ->where('facturas_id', $id)
            ->get()->getRow()->t ?? 0);

        $nc = (float) ($this->db->table('notas_credito')
            ->selectSum('monto', 't')
            ->where('facturas_id', $id)
            ->where('estado', 'Activa')
            ->get()->getRow()->t ?? 0);

        $saldo  = max(0, $total - $pagos - $nc);
        $estado = $factura['estado'];

        if ($saldo <= 0.01) {
            $nuevoEstado = 'Pagada';
        } elseif (($pagos + $nc) > 0) {
            // Vencida con abono parcial conserva su estado de vencimiento.
            $nuevoEstado = ($estado === 'Vencida') ? 'Vencida' : 'Parcial';
        } else {
            $nuevoEstado = in_array($estado, ['Pagada', 'Parcial'], true) ? 'Pendiente' : $estado;
        }

        $this->db->table('facturas')
            ->where('id_facturas', $id)
            ->update([
                'saldo_pendiente' => round($saldo, 2),
                'estado'          => $nuevoEstado,
            ]);
    }
}