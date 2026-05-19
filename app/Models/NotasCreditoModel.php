<?php

namespace App\Models;

class NotasCreditoModel extends BaseModel
{
    protected $table      = 'notas_credito';
    protected $primaryKey = 'id_nota_credito';

    protected $allowedFields = [
        'numero',
        'facturas_id',
        'clientes_id',
        'fecha',
        'monto',
        'motivo',
        'estado',   // 'Activa' | 'Anulada'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // generarNumero ELIMINADO — reemplazado por
    // (new NumeracionModel())->reservar('nota_credito') que usa SELECT … FOR UPDATE.
}