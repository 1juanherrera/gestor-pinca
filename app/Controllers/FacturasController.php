<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FacturasModel;

class FacturasController extends ResourceController
{
    protected $modelName = FacturasModel::class;
    protected $request;

    // ── GET /facturas ─────────────────────────────────────────────────────
    public function index()
    {
        $db = \Config\Database::connect();

        $facturas = $db->table('facturas f')
            ->select('f.*, c.nombre_empresa, c.nombre_encargado')
            ->join('clientes c', 'c.id_clientes = f.cliente_id', 'left')
            ->orderBy('f.id_facturas', 'DESC')
            ->get()
            ->getResultArray();

        return $this->respond($facturas);
    }

    // ── GET /facturas/:id ─────────────────────────────────────────────────
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $db = \Config\Database::connect();

        $factura = $db->table('facturas f')
            ->select('f.*, c.nombre_empresa, c.nombre_encargado, c.numero_documento, c.email, c.telefono, c.direccion')
            ->join('clientes c', 'c.id_clientes = f.cliente_id', 'left')
            ->where('f.id_facturas', $id)
            ->get()
            ->getRowArray();

        if (!$factura) return $this->failNotFound("Factura con ID $id no encontrada.");

        return $this->respond($factura);
    }

    // ── GET /facturas/:id/detalle ─────────────────────────────────────────
    public function detalle($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $items = $this->model->get_all('facturas_detalle', ['facturas_id' => $id]);

        return $this->respond($items);
    }

    // ── GET /facturas/:id/abonos ──────────────────────────────────────────
    public function abonos($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $abonos = $this->model->get_all('pagos_cliente', ['facturas_id' => $id]);

        return $this->respond($abonos);
    }

    // ── GET /facturas/:id/remision ────────────────────────────────────────
    public function remision($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $db       = \Config\Database::connect();
        $remision = $db->table('remisiones')
            ->where('facturas_id', $id)
            ->get()
            ->getRowArray();

        return $this->respond($remision);
    }

    // ── POST /facturas ────────────────────────────────────────────────────
    // Body: { ...campos, items?: [{descripcion, cantidad, precio_unit, descuento_pct, subtotal}] }
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        try {
            $items = $data['items'] ?? [];
            unset($data['items']);

            // El saldo inicial es igual al total
            $data['saldo_pendiente'] = $data['total'] ?? 0;

            $id = $this->model->create_table($data, 'facturas');
            if (!$id) throw new \Exception(implode(', ', $this->model->errors()));

            if (!empty($items)) {
                foreach ($items as &$item) {
                    $item['facturas_id'] = $id;
                }
                $this->model->create_table($items, 'facturas_detalle');
            }

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Factura creada exitosamente',
                'data'    => $this->model->get($id, 'facturas'),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PUT /facturas/:id ─────────────────────────────────────────────────
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!$id)   return $this->fail('ID no proporcionado', 400);
        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);
        if (!$this->model->find($id)) return $this->failNotFound("Factura con ID $id no encontrada.");

        try {
            $this->model->update_table($id, $data, 'facturas');

            return $this->respond([
                'status'  => 200,
                'message' => "Factura $id actualizada correctamente",
                'data'    => $this->model->get($id, 'facturas'),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PATCH /facturas/:id/estado ────────────────────────────────────────
    // Body: { "estado": "Pagada" | "Pendiente" | "Parcial" | "Vencida" | "Anulada" }
    public function cambiarEstado($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $data       = $this->request->getJSON(true);
        $estado     = $data['estado'] ?? null;
        $permitidos = ['Pendiente', 'Pagada', 'Parcial', 'Vencida', 'Anulada'];

        if (!$estado || !in_array($estado, $permitidos)) {
            return $this->fail('Estado no válido. Permitidos: ' . implode(', ', $permitidos), 400);
        }

        if (!$this->model->find($id)) return $this->failNotFound("Factura con ID $id no encontrada.");

        try {
            $update = ['estado' => $estado];
            if ($estado === 'Pagada') $update['saldo_pendiente'] = 0;

            $this->model->update_table($id, $update, 'facturas');

            return $this->respond([
                'status'  => 200,
                'message' => "Factura marcada como $estado",
                'data'    => $this->model->get($id, 'facturas'),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── DELETE /facturas/:id ──────────────────────────────────────────────
    public function delete($id = null)
    {
        if (!$this->model->find($id)) return $this->failNotFound("Factura con ID $id no encontrada.");

        $this->model->delete_table($id, 'facturas');

        return $this->respondDeleted(['message' => "Factura $id eliminada"]);
    }
}