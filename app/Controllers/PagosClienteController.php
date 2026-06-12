<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PagosClienteModel;
use App\Models\FacturasModel;

class PagosClienteController extends ResourceController
{
    use \App\Traits\ApiResponse;

    protected $modelName = PagosClienteModel::class;
    protected $request;

    // ── GET /pagos_cliente  (?cliente_id=X | ?factura_id=X) ──────────────
    public function index()
    {
        $db        = \Config\Database::connect();
        $clienteId = $this->request->getGet('cliente_id');
        $facturaId = $this->request->getGet('factura_id');

        $query = $db->table('pagos_cliente p')
            ->select('p.*, c.nombre_empresa, c.nombre_encargado, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = p.clientes_id', 'left')
            ->join('facturas f',  'f.id_facturas  = p.facturas_id',  'left')
            ->orderBy('p.fecha_pago', 'DESC');

        if ($clienteId) $query->where('p.clientes_id', $clienteId);
        if ($facturaId) $query->where('p.facturas_id', $facturaId);

        return $this->respond($query->get()->getResultArray());
    }

    // ── GET /pagos_cliente/:id ────────────────────────────────────────────
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $db   = \Config\Database::connect();
        $pago = $db->table('pagos_cliente p')
            ->select('p.*, c.nombre_empresa, c.nombre_encargado, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = p.clientes_id', 'left')
            ->join('facturas f',  'f.id_facturas  = p.facturas_id',  'left')
            ->where('p.id_pagos_cliente', $id)
            ->get()->getRowArray();

        if (!$pago) return $this->failNotFound("Pago con ID $id no encontrado.");

        return $this->respond($pago);
    }

    // ── POST /pagos_cliente ───────────────────────────────────────────────
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        // Validación de tipos + reglas mínimas (no rompe contrato — agrega rechazos 422 explícitos).
        $errors = [];
        $facturaId = $data['facturas_id'] ?? null;
        if ($facturaId !== null && $facturaId !== '' && (!is_numeric($facturaId) || (int) $facturaId <= 0)) {
            $errors['facturas_id'] = 'facturas_id debe ser entero > 0';
        }
        if (!isset($data['monto']) || !is_numeric($data['monto']) || (float) $data['monto'] <= 0) {
            $errors['monto'] = 'monto es requerido y debe ser numérico > 0';
        }
        if (empty($data['fecha_pago']) || strtotime((string) $data['fecha_pago']) === false) {
            $errors['fecha_pago'] = 'fecha_pago es requerida y debe ser una fecha válida';
        }
        if (empty($data['metodo_pago']) || !is_string($data['metodo_pago'])) {
            $errors['metodo_pago'] = 'metodo_pago es requerido (string)';
        }
        if (!empty($errors)) {
            return $this->apiValidationError($errors, 'Validación');
        }

        foreach (['fecha_pago', 'monto', 'clientes_id'] as $campo) {
            if (empty($data[$campo])) {
                return $this->fail("El campo '$campo' es requerido", 400);
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $monto     = (float) $data['monto'];
            $facturaId = $data['facturas_id'] ?? null;

            if ($facturaId) {
                // Lock pesimista de la fila para serializar pagos concurrentes a la misma factura
                // (sin esto, dos pagos en paralelo leen el mismo saldo y sobrepagan — race condition).
                $factura = $db->query(
                    'SELECT * FROM facturas WHERE id_facturas = ? AND deleted_at IS NULL FOR UPDATE',
                    [$facturaId]
                )->getRowArray();

                if (!$factura)
                    throw new \Exception('La factura indicada no existe');

                if ((int) $factura['cliente_id'] !== (int) $data['clientes_id'])
                    throw new \Exception('La factura no pertenece al cliente indicado');

                if ($factura['estado'] === 'Pagada')
                    throw new \Exception('La factura ya está completamente pagada');

                // Una factura anulada no debe recibir pagos: recalcularSaldo retorna temprano para
                // 'Anulada', así que el pago quedaría huérfano sin recalcular el saldo.
                if ($factura['estado'] === 'Anulada')
                    throw new \Exception('No se puede registrar un pago sobre una factura anulada');

                $saldo = (float) $factura['saldo_pendiente'];
                if ($monto > $saldo)
                    throw new \Exception("El monto ($monto) supera el saldo pendiente de la factura ($saldo)");
            }

            $id = $this->model->create_table($data, 'pagos_cliente');
            if (!$id) throw new \Exception(implode(', ', $this->model->errors()));

            if ($facturaId) (new FacturasModel())->recalcularSaldo((int) $facturaId);

            $db->transComplete();
            if (!$db->transStatus()) throw new \Exception('Error al confirmar la transacción');

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Pago registrado exitosamente',
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PUT /pagos_cliente/:id ────────────────────────────────────────────
    public function update($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $data = $this->request->getJSON(true);
        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Pago con ID $id no encontrado.");

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $facturaId       = $data['facturas_id'] ?? $existing['facturas_id'] ?? null;
            $facturaOriginal = $existing['facturas_id'] ?? null; // factura previa (antes de reasignar)
            $monto           = isset($data['monto']) ? (float) $data['monto'] : (float) $existing['monto'];

            if ($facturaId) {
                // Lock pesimista (mismo motivo que en create): evita sobrepago por pagos concurrentes.
                $factura = $db->query(
                    'SELECT * FROM facturas WHERE id_facturas = ? AND deleted_at IS NULL FOR UPDATE',
                    [$facturaId]
                )->getRowArray();

                if (!$factura)
                    throw new \Exception('La factura indicada no existe');

                $clienteId = $data['clientes_id'] ?? $existing['clientes_id'];
                if ((int) $factura['cliente_id'] !== (int) $clienteId)
                    throw new \Exception('La factura no pertenece al cliente indicado');

                $saldoDisponible = (float) $factura['saldo_pendiente'] + (float) $existing['monto'];
                if ($monto > $saldoDisponible)
                    throw new \Exception("El monto ($monto) supera el saldo disponible ($saldoDisponible)");
            }

            $this->model->update_table($id, $data, 'pagos_cliente');

            // Recalcular la factura DESTINO (la nueva o la misma).
            if ($facturaId) (new FacturasModel())->recalcularSaldo((int) $facturaId);

            // Si el pago se reasignó a otra factura (o se desvinculó), recalcular también la ORIGINAL:
            // de lo contrario quedaría con el saldo reducido por un pago que ya no le pertenece.
            if ($facturaOriginal && (int) $facturaOriginal !== (int) $facturaId) {
                (new FacturasModel())->recalcularSaldo((int) $facturaOriginal);
            }

            $db->transComplete();
            if (!$db->transStatus()) throw new \Exception('Error al confirmar la transacción');

            return $this->respond([
                'status'  => 200,
                'message' => "Pago $id actualizado correctamente",
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── DELETE /pagos_cliente/:id ─────────────────────────────────────────
    public function delete($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Pago con ID $id no encontrado.");

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $facturaId = $existing['facturas_id'] ?? null;

            $this->model->delete_table($id, 'pagos_cliente');

            if ($facturaId) (new FacturasModel())->recalcularSaldo((int) $facturaId);

            $db->transComplete();
            if (!$db->transStatus()) throw new \Exception('Error al confirmar la transacción');

            return $this->respondDeleted(['message' => "Pago $id eliminado"]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage(), 400);
        }
    }
}