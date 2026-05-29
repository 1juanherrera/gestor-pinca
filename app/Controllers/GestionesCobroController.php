<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\GestionesCobroModel;

class GestionesCobroController extends ResourceController
{
    use \App\Traits\ApiResponse;

    protected $modelName = GestionesCobroModel::class;

    protected $request;

    // ── GET /gestiones_cobro (?cliente_id=X | ?factura_id=X) ─
    public function index()
    {
        $db        = \Config\Database::connect();
        $clienteId = $this->request->getGet('cliente_id');
        $facturaId = $this->request->getGet('factura_id');

        $query = $db->table('gestiones_cobro g')
            ->select('g.*, c.nombre_empresa, c.nombre_encargado, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = g.clientes_id', 'left')
            ->join('facturas f',  'f.id_facturas  = g.facturas_id',  'left')
            ->orderBy('g.creado_en', 'DESC');

        if ($clienteId) $query->where('g.clientes_id', $clienteId);
        if ($facturaId) $query->where('g.facturas_id', $facturaId);

        return $this->respond($query->get()->getResultArray());
    }

    // ── GET /gestiones_cobro/:id ──────────────────────────────
    public function show($id = null)
    {
        if (!$id) return $this->apiFail('ID no proporcionado', 400);

        $db      = \Config\Database::connect();
        $gestion = $db->table('gestiones_cobro g')
            ->select('g.*, c.nombre_empresa, c.nombre_encargado, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = g.clientes_id', 'left')
            ->join('facturas f',  'f.id_facturas  = g.facturas_id',  'left')
            ->where('g.id_gestion', $id)
            ->get()->getRowArray();

        if (!$gestion) return $this->apiNotFound("Gestión con ID $id no encontrada.");

        return $this->respond($gestion);
    }

    // ── POST /gestiones_cobro ─────────────────────────────────
    // Body: { facturas_id, clientes_id, tipo, resultado?, proxima_gestion? }
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$data) return $this->apiFail('No se recibieron datos o el JSON es inválido', 400);

        foreach (['facturas_id', 'clientes_id', 'tipo'] as $campo) {
            if (empty($data[$campo])) {
                return $this->apiFail("El campo '$campo' es requerido", 400);
            }
        }

        $tiposValidos = ['llamada', 'email', 'visita', 'whatsapp'];
        if (!in_array($data['tipo'], $tiposValidos)) {
            return $this->apiFail("El tipo debe ser uno de: " . implode(', ', $tiposValidos), 400);
        }

        try {
            $id = $this->model->create_table($data, 'gestiones_cobro');
            if (!$id) throw new \Exception(implode(', ', $this->model->errors()));

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Gestión de cobro registrada exitosamente',
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // ── PUT /gestiones_cobro/:id ──────────────────────────────
    public function update($id = null)
    {
        if (!$id) return $this->apiFail('ID no proporcionado', 400);

        $data = $this->request->getJSON(true);
        if (!$data) return $this->apiFail('No se recibieron datos o el JSON es inválido', 400);

        $existing = $this->model->find($id);
        if (!$existing) return $this->apiNotFound("Gestión con ID $id no encontrada.");

        if (isset($data['tipo'])) {
            $tiposValidos = ['llamada', 'email', 'visita', 'whatsapp'];
            if (!in_array($data['tipo'], $tiposValidos)) {
                return $this->apiFail("El tipo debe ser uno de: " . implode(', ', $tiposValidos), 400);
            }
        }

        try {
            $this->model->update((int) $id, $data);

            return $this->respond([
                'status'  => 200,
                'message' => "Gestión $id actualizada correctamente",
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // ── DELETE /gestiones_cobro/:id ───────────────────────────
    public function delete($id = null)
    {
        if (!$id) return $this->apiFail('ID no proporcionado', 400);

        $existing = $this->model->find($id);
        if (!$existing) return $this->apiNotFound("Gestión con ID $id no encontrada.");

        $this->model->delete((int) $id);

        return $this->respondDeleted(['message' => "Gestión $id eliminada"]);
    }
}