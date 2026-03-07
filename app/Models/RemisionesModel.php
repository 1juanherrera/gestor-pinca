<?php

namespace App\Models;

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
}