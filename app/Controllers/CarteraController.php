<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CarteraModel;

class CarteraController extends ResourceController
{
    protected $modelName = CarteraModel::class;

    // ── GET /cartera/resumen ──────────────────────────────────
    // KPIs del dashboard: total cartera, vencida, recaudo del mes,
    // clientes en mora y la factura más antigua sin pagar.
    public function resumen()
    {
        return $this->respond($this->model->resumen());
    }

    // ── GET /cartera/aging ────────────────────────────────────
    // Facturas agrupadas por rango de vencimiento.
    // Respuesta: { grupos: { corriente, dias_1_30, dias_31_60, dias_60_mas }, total_mora }
    public function aging()
    {
        return $this->respond($this->model->aging());
    }

    // ── GET /cartera/estado_cuenta/:id ────────────────────────
    // Estado de cuenta completo de un cliente:
    // cliente + facturas con sus pagos + totales.
    public function estadoCuenta($clienteId = null)
    {
        if (!$clienteId) return $this->fail('ID de cliente no proporcionado', 400);

        $data = $this->model->estadoCuenta((int) $clienteId);

        if (!$data) return $this->failNotFound("Cliente con ID $clienteId no encontrado.");

        return $this->respond($data);
    }
}