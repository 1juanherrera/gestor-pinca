<?php

namespace App\Models;

use Exception;

class RemisionesModel extends BaseModel
{
    protected $table      = 'remisiones';
    protected $primaryKey = 'id_remisiones';

    protected $allowedFields = [
        'numero',
        'cliente_id',
        'fecha_remision',
        'estado',
        'direccion_entrega',
        'observaciones',
        'facturas_id',
        'movimiento_inventario_id',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // ─── Lista todas las remisiones con datos del cliente ─────────────────────
    // Renombrado a get_remisiones() para no colisionar con BaseModel::get_all()
    public function get_remisiones(?int $clienteId = null, ?int $facturaId = null): array
    {
        $query = $this->db->table('remisiones r')
            ->select('r.*, c.nombre_empresa, c.nombre_encargado, c.numero_documento AS nit_cliente, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = r.cliente_id', 'left')
            ->join('facturas f', 'f.id_facturas  = r.facturas_id', 'left')
            ->orderBy('r.id_remisiones', 'DESC');

        if ($clienteId) $query->where('r.cliente_id', $clienteId);
        if ($facturaId) $query->where('r.facturas_id', $facturaId);

        return array_map(
            fn($r) => $this->_format((object) $r),
            $query->get()->getResultArray()
        );
    }

    // ─── Una remisión con su detalle ──────────────────────────────────────────
    public function get_remision_by_id(int $id): array
    {
        $r = $this->db->table('remisiones r')
            ->select('r.*, c.nombre_empresa, c.nombre_encargado, c.numero_documento AS nit_cliente, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = r.cliente_id', 'left')
            ->join('facturas f', 'f.id_facturas  = r.facturas_id', 'left')
            ->where('r.id_remisiones', $id)
            ->get()
            ->getRow();

        if (!$r) throw new Exception("Remisión #{$id} no encontrada.");

        $detalle  = $this->get_detalle($id);
        $subtotal = array_sum(array_column($detalle, 'subtotal'));

        return array_merge($this->_format($r), [
            'items'    => $detalle,
            'subtotal' => $subtotal,
        ]);
    }

    // ─── Ítems de una remisión ────────────────────────────────────────────────
    public function get_detalle(int $remisionId): array
    {
        $rows = $this->db->query(
            "SELECT id_detalle, remisiones_id, descripcion, cantidad, precio_unit, subtotal
             FROM remisiones_detalle
             WHERE remisiones_id = ?
             ORDER BY id_detalle ASC",
            [$remisionId]
        )->getResult();

        return array_map(fn($d) => [
            'id_detalle'    => $d->id_detalle,
            'remisiones_id' => $d->remisiones_id,
            'descripcion'   => $d->descripcion,
            'cantidad'      => (float) $d->cantidad,
            'precio_unit'   => (float) $d->precio_unit,
            'subtotal'      => (float) $d->subtotal,
        ], $rows);
    }

    // ─── Formato estándar de una fila ─────────────────────────────────────────
    private function _format(object $r): array
    {
        return [
            'id_remisiones'            => $r->id_remisiones,
            'numero'                   => $r->numero,
            'cliente_id'               => $r->cliente_id,
            'nombre_empresa'           => $r->nombre_empresa           ?? null,
            'nombre_encargado'         => $r->nombre_encargado         ?? null,
            'nit_cliente'              => $r->nit_cliente              ?? null,
            'fecha_remision'           => $r->fecha_remision,
            'estado'                   => $r->estado,
            'direccion_entrega'        => $r->direccion_entrega        ?? null,
            'observaciones'            => $r->observaciones            ?? null,
            'facturas_id'              => $r->facturas_id              ?? null,
            'numero_factura'           => $r->numero_factura           ?? null,
            'movimiento_inventario_id' => $r->movimiento_inventario_id ?? null,
            'creado_en'                => $r->creado_en,
        ];
    }
}