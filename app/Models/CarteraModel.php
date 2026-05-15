<?php

namespace App\Models;

class CarteraModel extends BaseModel
{
    protected $table      = 'facturas';
    protected $primaryKey = 'id_facturas';

    protected $allowedFields = [];

    public function __construct()
    {
        parent::__construct();
    }

    // ── Resumen general de cartera ────────────────────────────
    // Totales para el dashboard: cartera total, vencida, recaudo
    // del mes en curso y número de clientes en mora.
    public function resumen(): array
    {
        $db        = \Config\Database::connect();
        $hoy       = date('Y-m-d');
        $inicioMes = date('Y-m-01');
        $finMes    = date('Y-m-t');

        $totalCartera = (float) ($db->table('facturas')
            ->selectSum('saldo_pendiente')
            ->whereIn('estado', ['Pendiente', 'Parcial', 'Vencida'])
            ->where('deleted_at', null)
            ->get()->getRowArray()['saldo_pendiente'] ?? 0);

        $carteraVencida = (float) ($db->table('facturas')
            ->selectSum('saldo_pendiente')
            ->where('fecha_vencimiento <', $hoy)
            ->whereIn('estado', ['Pendiente', 'Parcial', 'Vencida'])
            ->where('deleted_at', null)
            ->get()->getRowArray()['saldo_pendiente'] ?? 0);

        $recaudoMes = (float) ($db->table('pagos_cliente')
            ->selectSum('monto')
            ->where('fecha_pago >=', $inicioMes)
            ->where('fecha_pago <=', $finMes)
            ->get()->getRowArray()['monto'] ?? 0);

        $clientesEnMora = (int) ($db->table('facturas')
            ->select('COUNT(DISTINCT cliente_id) as total')
            ->where('fecha_vencimiento <', $hoy)
            ->whereIn('estado', ['Pendiente', 'Parcial', 'Vencida'])
            ->where('deleted_at', null)
            ->get()->getRowArray()['total'] ?? 0);

        $facturaVieja = $db->table('facturas f')
            ->select('f.numero, f.fecha_vencimiento, DATEDIFF(CURDATE(), f.fecha_vencimiento) AS dias_mora')
            ->whereIn('f.estado', ['Pendiente', 'Parcial', 'Vencida'])
            ->where('f.fecha_vencimiento <', $hoy)
            ->where('f.deleted_at', null)
            ->orderBy('f.fecha_vencimiento', 'ASC')
            ->limit(1)
            ->get()->getRowArray();

        return [
            'total_cartera'     => $totalCartera,
            'cartera_vencida'   => $carteraVencida,
            'recaudo_mes'       => $recaudoMes,
            'clientes_en_mora'  => $clientesEnMora,
            'factura_mas_vieja' => $facturaVieja ?: null,
        ];
    }

    // ── Aging de cartera ──────────────────────────────────────
    // Agrupa facturas con saldo pendiente por rango de vencimiento.
    public function aging(): array
    {
        $db = \Config\Database::connect();

        $facturas = $db->table('facturas f')
            ->select('f.id_facturas, f.numero, f.cliente_id, f.saldo_pendiente,
                      f.fecha_vencimiento, f.estado,
                      c.nombre_empresa, c.nombre_encargado, c.ciudad,
                      c.plazo_pago, c.tipo AS cliente_tipo,
                      DATEDIFF(CURDATE(), f.fecha_vencimiento) AS dias_mora')
            ->join('clientes c', 'c.id_clientes = f.cliente_id', 'left')
            ->whereIn('f.estado', ['Pendiente', 'Parcial', 'Vencida'])
            ->where('f.saldo_pendiente >', 0)
            ->where('f.deleted_at', null)
            ->orderBy('dias_mora', 'DESC')
            ->get()->getResultArray();

        $grupos = [
            'corriente'   => ['label' => 'Corriente',    'monto' => 0, 'facturas' => []],
            'dias_1_30'   => ['label' => '1 – 30 días',  'monto' => 0, 'facturas' => []],
            'dias_31_60'  => ['label' => '31 – 60 días', 'monto' => 0, 'facturas' => []],
            'dias_60_mas' => ['label' => 'Más de 60',    'monto' => 0, 'facturas' => []],
        ];

        foreach ($facturas as $f) {
            $dias  = (int)   $f['dias_mora'];
            $saldo = (float) $f['saldo_pendiente'];

            $key = match (true) {
                $dias <= 0  => 'corriente',
                $dias <= 30 => 'dias_1_30',
                $dias <= 60 => 'dias_31_60',
                default     => 'dias_60_mas',
            };

            $grupos[$key]['monto']      += $saldo;
            $grupos[$key]['facturas'][]  = $f;
        }

        $totalMora = $grupos['dias_1_30']['monto']
                   + $grupos['dias_31_60']['monto']
                   + $grupos['dias_60_mas']['monto'];

        return [
            'grupos'     => $grupos,
            'total_mora' => $totalMora,
        ];
    }

    // ── Estado de cuenta de un cliente ───────────────────────
    // Todas las facturas del cliente con sus pagos y saldo total.
    public function estadoCuenta(int $clienteId): ?array
    {
        $db = \Config\Database::connect();

        $cliente = $db->table('clientes')
            ->where('id_clientes', $clienteId)
            ->where('deleted_at', null)
            ->get()->getRowArray();

        if (!$cliente) return null;

        $facturas = $db->table('facturas')
            ->where('cliente_id', $clienteId)
            ->where('deleted_at', null)
            ->orderBy('fecha_emision', 'DESC')
            ->get()->getResultArray();

        foreach ($facturas as &$factura) {
            $factura['pagos'] = $db->table('pagos_cliente')
                ->where('facturas_id', $factura['id_facturas'])
                ->orderBy('fecha_pago', 'DESC')
                ->get()->getResultArray();
        }
        unset($factura);

        $totalDeuda = array_sum(array_column(
            array_filter($facturas, fn($f) => $f['estado'] !== 'Pagada'),
            'saldo_pendiente'
        ));

        $totalPagado = (float) ($db->table('pagos_cliente')
            ->selectSum('monto')
            ->where('clientes_id', $clienteId)
            ->get()->getRowArray()['monto'] ?? 0);

        return [
            'cliente'      => $cliente,
            'facturas'     => $facturas,
            'total_deuda'  => (float) $totalDeuda,
            'total_pagado' => $totalPagado,
            'saldo_total'  => (float) $totalDeuda,
        ];
    }
}