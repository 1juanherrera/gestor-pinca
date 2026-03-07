<?php

namespace App\Models;

class CotizacionesModel extends BaseModel
{
    protected $table      = 'cotizaciones';
    protected $primaryKey = 'id_cotizaciones';

    protected $allowedFields = [
        'numero',
        'cliente_id',
        'fecha_cotizacion',
        'fecha_vencimiento',
        'subtotal',
        'descuento',
        'impuestos',
        'retencion',
        'total',
        'estado',
        'observaciones',
        'facturas_id',
    ];

    public function __construct()
    {
        parent::__construct();
    }
}