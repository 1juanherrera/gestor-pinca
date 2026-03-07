<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RemisionesModel;
use App\Models\FacturasModel;

class RemisionesController extends ResourceController
{
    protected $modelName = RemisionesModel::class;
    protected $request;

    // ── GET /remisiones  (?cliente_id=X | ?factura_id=X) ─────────────────
    public function index()
    {
        $db        = \Config\Database::connect();
        $clienteId = $this->request->getGet('cliente_id');
        $facturaId = $this->request->getGet('factura_id');

        $query = $db->table('remisiones r')
            ->select('r.*, c.nombre_empresa, c.nombre_encargado, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = r.cliente_id', 'left')
            ->join('facturas f', 'f.id_facturas = r.facturas_id', 'left')
            ->orderBy('r.id_remisiones', 'DESC');

        if ($clienteId) $query->where('r.cliente_id', $clienteId);
        if ($facturaId) $query->where('r.facturas_id', $facturaId);

        return $this->respond($query->get()->getResultArray());
    }

    // ── GET /remisiones/:id ───────────────────────────────────────────────
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $db = \Config\Database::connect();

        $remision = $db->table('remisiones r')
            ->select('r.*, c.nombre_empresa, c.nombre_encargado, c.direccion, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = r.cliente_id', 'left')
            ->join('facturas f', 'f.id_facturas = r.facturas_id', 'left')
            ->where('r.id_remisiones', $id)
            ->get()
            ->getRowArray();

        if (!$remision) return $this->failNotFound("Remisión con ID $id no encontrada.");

        return $this->respond($remision);
    }

    // ── GET /remisiones/:id/detalle ───────────────────────────────────────
    public function detalle($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $items = $this->model->get_all('remisiones_detalle', ['remisiones_id' => $id]);

        return $this->respond($items);
    }

    // ── POST /remisiones ──────────────────────────────────────────────────
    // Body: { ...campos, items?: [{descripcion, cantidad, precio_unit, subtotal}] }
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        try {
            $items = $data['items'] ?? [];
            unset($data['items']);

            if (empty($data['numero'])) $data['numero'] = $this->generarNumero();
            if (empty($data['estado'])) $data['estado'] = 'Pendiente';

            $id = $this->model->create_table($data, 'remisiones');
            if (!$id) throw new \Exception(implode(', ', $this->model->errors()));

            if (!empty($items)) {
                foreach ($items as &$item) {
                    $item['remisiones_id'] = $id;
                }
                $this->model->create_table($items, 'remisiones_detalle');
            }

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Remisión creada exitosamente',
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PUT /remisiones/:id ───────────────────────────────────────────────
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!$id)   return $this->fail('ID no proporcionado', 400);
        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Remisión con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Anulada') {
                throw new \Exception('No se puede editar una remisión anulada');
            }

            $this->model->update_table($id, $data, 'remisiones');

            return $this->respond([
                'status'  => 200,
                'message' => "Remisión $id actualizada correctamente",
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PATCH /remisiones/:id/estado ──────────────────────────────────────
    // Body: { "estado": "Facturada" | "Anulada" }
    public function cambiarEstado($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $data       = $this->request->getJSON(true);
        $estado     = $data['estado'] ?? null;
        $permitidos = ['Pendiente', 'Facturada', 'Anulada'];

        if (!$estado || !in_array($estado, $permitidos)) {
            return $this->fail('Estado no válido. Permitidos: ' . implode(', ', $permitidos), 400);
        }

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Remisión con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Anulada') {
                throw new \Exception('No se puede cambiar el estado de una remisión anulada');
            }

            $this->model->update_table($id, ['estado' => $estado], 'remisiones');

            return $this->respond([
                'status'  => 200,
                'message' => "Remisión marcada como $estado",
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── POST /remisiones/:id/convertir ────────────────────────────────────
    // Genera una Factura a partir de la remisión copiando sus ítems
    public function convertir($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $remision = $this->model->find($id);
        if (!$remision) return $this->failNotFound("Remisión con ID $id no encontrada.");

        try {
            if ($remision['estado'] === 'Facturada') {
                throw new \Exception('Esta remisión ya fue convertida a factura');
            }
            if ($remision['estado'] === 'Anulada') {
                throw new \Exception('No se puede convertir una remisión anulada');
            }

            $items     = $this->model->get_all('remisiones_detalle', ['remisiones_id' => $id]);
            $subtotal  = array_sum(array_column($items, 'subtotal'));
            $iva       = round($subtotal * 0.19, 2); // 19% IVA — ajusta si es necesario
            $total     = $subtotal + $iva;

            $facturaModel = new FacturasModel();
            $numeroFac    = $this->generarNumeroFactura();

            $facturaData = [
                'numero'            => $numeroFac,
                'cliente_id'        => $remision['cliente_id'],
                'fecha_emision'     => date('Y-m-d'),
                'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
                'subtotal'          => $subtotal,
                'descuento'         => 0,
                'impuestos'         => $iva,
                'retencion'         => 0,
                'total'             => $total,
                'saldo_pendiente'   => $total,
                'estado'            => 'Pendiente',
                'observaciones'     => "Generada desde remisión {$remision['numero']}",
            ];

            $facturaId = $facturaModel->create_table($facturaData, 'facturas');
            if (!$facturaId) throw new \Exception('Error al generar la factura');

            // Copiar ítems: remisiones_detalle → facturas_detalle
            if (!empty($items)) {
                $itemsFactura = array_map(fn($item) => [
                    'facturas_id'   => $facturaId,
                    'descripcion'   => $item['descripcion'],
                    'cantidad'      => $item['cantidad'],
                    'precio_unit'   => $item['precio_unit'],
                    'descuento_pct' => 0,
                    'subtotal'      => $item['subtotal'],
                ], $items);
                $this->model->create_table($itemsFactura, 'facturas_detalle');
            }

            // Vincular factura y marcar como Facturada
            $this->model->update_table($id, [
                'estado'      => 'Facturada',
                'facturas_id' => $facturaId,
            ], 'remisiones');

            return $this->respondCreated([
                'status'  => 201,
                'message' => "Remisión convertida. Factura $numeroFac creada.",
                'data'    => $facturaModel->find($facturaId),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── DELETE /remisiones/:id ────────────────────────────────────────────
    public function delete($id = null)
    {
        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Remisión con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Facturada') {
                throw new \Exception('No se puede eliminar una remisión que ya tiene factura generada');
            }

            $this->model->delete_table($id, 'remisiones');

            return $this->respondDeleted(['message' => "Remisión $id eliminada"]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PRIVADOS: generadores de número correlativo ───────────────────────
    private function generarNumero(): string
    {
        return $this->correlativo('remisiones', 'REM');
    }

    private function generarNumeroFactura(): string
    {
        return $this->correlativo('facturas', 'FAC');
    }

    private function correlativo(string $tabla, string $prefijo): string
    {
        $db   = \Config\Database::connect();
        $year = date('Y');

        $last = $db->table($tabla)
            ->like('numero', "{$prefijo}-{$year}-", 'after')
            ->orderBy("id_$tabla", 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $seq = $last ? ((int) end(explode('-', $last['numero'])) + 1) : 1;

        return "{$prefijo}-{$year}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}