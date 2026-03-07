<?php

namespace App\Models;

class FacturasModel extends BaseModel
{
    protected $table      = 'facturas';
    protected $primaryKey = 'id_facturas';

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
}