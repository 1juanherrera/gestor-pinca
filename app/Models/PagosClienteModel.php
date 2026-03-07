<?php

namespace App\Models;

class PagosClienteModel extends BaseModel
{
    protected $table      = 'pagos_cliente';
    protected $primaryKey = 'id_pagos_cliente';

    protected $allowedFields = [
        'fecha_pago',
        'monto',
        'metodo_pago',
        'tipo',               // 'pago_total' | 'abono'
        'numero_referencia',
        'observaciones',
        'clientes_id',
        'facturas_id',
    ];

    public function __construct()
    {
        parent::__construct();
    }
}