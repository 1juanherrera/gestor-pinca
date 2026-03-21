<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CotizacionesModel;
use App\Models\FacturasModel;

class CotizacionesController extends ResourceController
{
    protected $modelName = CotizacionesModel::class;
    protected $request;

    // ── GET /cotizaciones  (?cliente_id=X) ────────────────────────────────
    public function index()
    {
        $db        = \Config\Database::connect();
        $clienteId = $this->request->getGet('cliente_id');

        $query = $db->table('cotizaciones co')
            ->select('co.*, c.nombre_empresa, c.nombre_encargado, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = co.cliente_id', 'left')
            ->join('facturas f', 'f.id_facturas = co.facturas_id', 'left')
            ->orderBy('co.id_cotizaciones', 'DESC');

        if ($clienteId) $query->where('co.cliente_id', $clienteId);

        return $this->respond($query->get()->getResultArray());
    }

    // ── GET /cotizaciones/:id ─────────────────────────────────────────────
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $db = \Config\Database::connect();

        $cotizacion = $db->table('cotizaciones co')
            ->select('co.*, c.nombre_empresa, c.nombre_encargado, c.email, c.telefono')
            ->join('clientes c', 'c.id_clientes = co.cliente_id', 'left')
            ->where('co.id_cotizaciones', $id)
            ->get()
            ->getRowArray();

        if (!$cotizacion) return $this->failNotFound("Cotización con ID $id no encontrada.");

        return $this->respond($cotizacion);
    }

    // ── GET /cotizaciones/:id/detalle ─────────────────────────────────────
    public function detalle($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $items = $this->model->get_all('cotizaciones_detalle', ['cotizaciones_id' => $id]);

        return $this->respond($items);
    }

    // ── POST /cotizaciones ────────────────────────────────────────────────
    public function create()
    {
        $data = $this->request->getJSON(true);
        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        try {
            $items = $data['items'] ?? [];
            unset($data['items'], $data['cliente_libre']);

            if (empty($data['numero'])) $data['numero'] = $this->generarNumero('COT');
            if (empty($data['estado'])) $data['estado'] = 'Borrador';

            // Limpiar campos opcionales vacíos
            $data['fecha_vencimiento'] = $data['fecha_vencimiento'] ?? null ?: null;
            $data['observaciones']     = $data['observaciones']     ?? null ?: null;
            $data['facturas_id']       = $data['facturas_id']       ?? null ?: null;

            $id = $this->model->create_table($data, 'cotizaciones');
            if (!$id) throw new \Exception(implode(', ', $this->model->errors()));

            // Insertar ítems con query directa para evitar conflictos de allowedFields
            if (!empty($items)) {
                $db = \Config\Database::connect();
                foreach ($items as $item) {
                    $db->query(
                        "INSERT INTO cotizaciones_detalle
                            (cotizaciones_id, descripcion, cantidad, precio_unit, descuento_pct, subtotal)
                         VALUES (?, ?, ?, ?, ?, ?)",
                        [
                            $id,
                            $item['descripcion']   ?? '',
                            (float) ($item['cantidad']      ?? 0),
                            (float) ($item['precio_unit']   ?? 0),
                            (float) ($item['descuento_pct'] ?? 0),
                            (float) ($item['subtotal']      ?? 0),
                        ]
                    );
                }
            }

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Cotización creada exitosamente',
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PUT /cotizaciones/:id ─────────────────────────────────────────────
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if (!$id)   return $this->fail('ID no proporcionado', 400);
        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Cotización con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Convertida') {
                throw new \Exception('No se puede editar una cotización ya convertida a factura');
            }

            unset($data['items'], $data['cliente_libre']);
            $this->model->update_table($id, $data, 'cotizaciones');

            return $this->respond([
                'status'  => 200,
                'message' => "Cotización $id actualizada correctamente",
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PATCH /cotizaciones/:id/estado ────────────────────────────────────
    public function cambiarEstado($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $data       = $this->request->getJSON(true);
        $estado     = $data['estado'] ?? null;
        $permitidos = ['Borrador', 'Enviada', 'Aceptada', 'Rechazada', 'Vencida'];

        if (!$estado || !in_array($estado, $permitidos)) {
            return $this->fail('Estado no válido. Permitidos: ' . implode(', ', $permitidos), 400);
        }

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Cotización con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Convertida') {
                throw new \Exception('No se puede cambiar el estado de una cotización convertida');
            }

            $this->model->update_table($id, ['estado' => $estado], 'cotizaciones');

            return $this->respond([
                'status'  => 200,
                'message' => "Cotización marcada como $estado",
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── POST /cotizaciones/:id/convertir ──────────────────────────────────
    public function convertir($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $cotizacion = $this->model->find($id);
        if (!$cotizacion) return $this->failNotFound("Cotización con ID $id no encontrada.");

        try {
            if ($cotizacion['estado'] === 'Convertida') {
                throw new \Exception('Esta cotización ya fue convertida a factura');
            }
            if (!in_array($cotizacion['estado'], ['Aceptada', 'Enviada'])) {
                throw new \Exception('Solo se pueden convertir cotizaciones en estado Aceptada o Enviada');
            }

            $facturaModel = new FacturasModel();
            $numeroFac    = $this->generarNumero('FAC');

            $facturaData = [
                'numero'            => $numeroFac,
                'cliente_id'        => $cotizacion['cliente_id'],
                'fecha_emision'     => date('Y-m-d'),
                'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
                'subtotal'          => $cotizacion['subtotal'],
                'descuento'         => $cotizacion['descuento'],
                'impuestos'         => $cotizacion['impuestos'],
                'retencion'         => $cotizacion['retencion'],
                'total'             => $cotizacion['total'],
                'saldo_pendiente'   => $cotizacion['total'],
                'estado'            => 'Pendiente',
                'observaciones'     => "Generada desde cotización {$cotizacion['numero']}",
            ];

            $facturaId = $facturaModel->create_table($facturaData, 'facturas');
            if (!$facturaId) throw new \Exception('Error al generar la factura');

            // Copiar ítems con query directa
            $items = $this->model->get_all('cotizaciones_detalle', ['cotizaciones_id' => $id]);
            if (!empty($items)) {
                $db = \Config\Database::connect();
                foreach ($items as $item) {
                    $db->query(
                        "INSERT INTO facturas_detalle
                            (facturas_id, descripcion, cantidad, precio_unit, descuento_pct, subtotal)
                         VALUES (?, ?, ?, ?, ?, ?)",
                        [
                            $facturaId,
                            $item['descripcion'],
                            $item['cantidad'],
                            $item['precio_unit'],
                            $item['descuento_pct'] ?? 0,
                            $item['subtotal'],
                        ]
                    );
                }
            }

            $this->model->update_table($id, [
                'estado'      => 'Convertida',
                'facturas_id' => $facturaId,
            ], 'cotizaciones');

            return $this->respondCreated([
                'status'  => 201,
                'message' => "Cotización convertida. Factura {$numeroFac} creada.",
                'data'    => $facturaModel->find($facturaId),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── DELETE /cotizaciones/:id ──────────────────────────────────────────
    public function delete($id = null)
    {
        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Cotización con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Convertida') {
                throw new \Exception('No se puede eliminar una cotización ya convertida a factura');
            }
            $this->model->delete_table($id, 'cotizaciones');
            return $this->respondDeleted(['message' => "Cotización $id eliminada"]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── Número correlativo ────────────────────────────────────────────────
    private function generarNumero(string $prefijo): string
    {
        $tabla = $prefijo === 'COT' ? 'cotizaciones' : 'facturas';
        $db    = \Config\Database::connect();
        $year  = date('Y');

        $last = $db->table($tabla)
            ->like('numero', "{$prefijo}-{$year}-", 'after')
            ->orderBy("id_{$tabla}", 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $parts = explode('-', $last['numero'] ?? '');
        $seq   = $last ? ((int) end($parts) + 1) : 1;

        return "{$prefijo}-{$year}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}