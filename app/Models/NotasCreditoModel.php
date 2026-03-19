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

    // ── Genera el siguiente número de nota crédito ────────────
    // Formato: NC-001, NC-002, NC-003 ...
    public function generarNumero(): string
    {
        $db = \Config\Database::connect();

        $ultimo = $db->table('notas_credito')
            ->select('numero')
            ->orderBy('id_nota_credito', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$ultimo) return 'NC-001';

        // Extrae el número del formato NC-XXX y suma 1
        $partes    = explode('-', $ultimo['numero']);
        $siguiente = (int) end($partes) + 1;

        return 'NC-' . str_pad($siguiente, 3, '0', STR_PAD_LEFT);
    }
}