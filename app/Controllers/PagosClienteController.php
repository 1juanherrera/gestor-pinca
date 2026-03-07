<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PagosClienteModel;
use App\Models\FacturasModel;

class PagosClienteController extends ResourceController
{
    protected $modelName = PagosClienteModel::class;
    protected $request;

    // ── GET /pagos-cliente  (?cliente_id=X | ?factura_id=X) ──────────────
    public function index()
    {
        $db        = \Config\Database::connect();
        $clienteId = $this->request->getGet('cliente_id');
        $facturaId = $this->request->getGet('factura_id');

        $query = $db->table('pagos_cliente p')
            ->select('p.*, c.nombre_empresa, c.nombre_encargado, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = p.clientes_id', 'left')
            ->join('facturas f', 'f.id_facturas = p.facturas_id', 'left')
            ->orderBy('p.fecha_pago', 'DESC');

        if ($clienteId) $query->where('p.clientes_id', $clienteId);
        if ($facturaId) $query->where('p.facturas_id', $facturaId);

        return $this->respond($query->get()->getResultArray());
    }

    // ── GET /pagos-cliente/:id ────────────────────────────────────────────
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $pago = $this->model->find($id);

        if (!$pago) return $this->failNotFound("Pago con ID $id no encontrado.");

        return $this->respond($pago);
    }

    // ── POST /pagos-cliente ───────────────────────────────────────────────
    // Body: { fecha_pago, monto, metodo_pago, tipo, clientes_id, facturas_id?, ... }
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        foreach (['fecha_pago', 'monto', 'clientes_id'] as $campo) {
            if (empty($data[$campo])) {
                return $this->fail("El campo '$campo' es requerido", 400);
            }
        }

        try {
            $monto     = (float) $data['monto'];
            $facturaId = $data['facturas_id'] ?? null;

            // Validar que el monto no supere el saldo pendiente de la factura
            if ($facturaId) {
                $facturaModel = new FacturasModel();
                $factura      = $facturaModel->find($facturaId);

                if (!$factura) throw new \Exception('La factura indicada no existe');

                $saldo = (float) $factura['saldo_pendiente'];
                if ($monto > $saldo) {
                    throw new \Exception("El monto ($monto) supera el saldo pendiente de la factura ($saldo)");
                }
            }

            $id = $this->model->create_table($data, 'pagos_cliente');
            if (!$id) throw new \Exception(implode(', ', $this->model->errors()));

            // Recalcular saldo de la factura automáticamente
            if ($facturaId) $this->recalcularSaldo($facturaId);

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Pago registrado exitosamente',
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PUT /pagos-cliente/:id ────────────────────────────────────────────
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!$id)   return $this->fail('ID no proporcionado', 400);
        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Pago con ID $id no encontrado.");

        try {
            $this->model->update_table($id, $data, 'pagos_cliente');

            // Recalcular saldo de la factura afectada
            $facturaId = $data['facturas_id'] ?? $existing['facturas_id'] ?? null;
            if ($facturaId) $this->recalcularSaldo($facturaId);

            return $this->respond([
                'status'  => 200,
                'message' => "Pago $id actualizado correctamente",
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── DELETE /pagos-cliente/:id ─────────────────────────────────────────
    public function delete($id = null)
    {
        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Pago con ID $id no encontrado.");

        $facturaId = $existing['facturas_id'] ?? null;

        $this->model->delete_table($id, 'pagos_cliente');

        // Recalcular saldo después de eliminar el pago
        if ($facturaId) $this->recalcularSaldo($facturaId);

        return $this->respondDeleted(['message' => "Pago $id eliminado"]);
    }

    // ── PRIVADO: recalcula saldo_pendiente y estado de la factura ─────────
    private function recalcularSaldo(int $facturaId): void
    {
        $facturaModel = new FacturasModel();
        $factura      = $facturaModel->find($facturaId);
        if (!$factura) return;

        $db = \Config\Database::connect();

        $totalPagado = (float) ($db->table('pagos_cliente')
            ->selectSum('monto')
            ->where('facturas_id', $facturaId)
            ->get()
            ->getRowArray()['monto'] ?? 0);

        $total          = (float) $factura['total'];
        $saldoPendiente = max(0, $total - $totalPagado);

        $nuevoEstado = match (true) {
            $saldoPendiente <= 0 => 'Pagada',
            $totalPagado > 0     => 'Parcial',
            default              => 'Pendiente',
        };

        $facturaModel->update_table($facturaId, [
            'saldo_pendiente' => $saldoPendiente,
            'estado'          => $nuevoEstado,
        ], 'facturas');
    }
}