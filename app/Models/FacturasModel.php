<?php

namespace App\Models;
use App\Libraries\Formatter;

class FacturasModel extends BaseModel
{

    protected $table = 'facturas';
    protected $primaryKey = 'id_facturas';
    protected $allowedFields = [
        "numero",
        "cliente_id",
        "fecha_emision",
        "total",
        "estado",
        "subtotal",
        "impuestos",
        "retencion",
        "movimiento_inventario_id"
    ];

    public function __construct()
    {
        parent::__construct();
    }
}