<?php

namespace App\Models;

class GestionesCobroModel extends BaseModel
{
    protected $table      = 'gestiones_cobro';
    protected $primaryKey = 'id_gestion';

    protected $allowedFields = [
        'facturas_id',
        'clientes_id',
        'tipo',             // 'llamada' | 'email' | 'visita' | 'whatsapp'
        'resultado',
        'proxima_gestion',  // DATE — cuándo se debe hacer el próximo seguimiento
    ];

    public function __construct()
    {
        parent::__construct();
    }
}