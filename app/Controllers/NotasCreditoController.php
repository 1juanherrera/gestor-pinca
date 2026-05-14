<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\NotasCreditoModel;
use App\Models\FacturasModel;
use App\Models\NumeracionModel;

class NotasCreditoController extends ResourceController
{
    protected $modelName = NotasCreditoModel::class;
    protected $request;

    // ── GET /notas_credito (?cliente_id=X | ?factura_id=X) ───
    public function index()
    {
        $db        = \Config\Database::connect();
        $clienteId = $this->request->getGet('cliente_id');
        $facturaId = $this->request->getGet('factura_id');

        $query = $db->table('notas_credito nc')
            ->select('nc.*, c.nombre_empresa, c.nombre_encargado, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = nc.clientes_id', 'left')
            ->join('facturas f',  'f.id_facturas  = nc.facturas_id',  'left')
            ->orderBy('nc.creado_en', 'DESC');

        if ($clienteId) $query->where('nc.clientes_id', $clienteId);
        if ($facturaId) $query->where('nc.facturas_id', $facturaId);

        return $this->respond($query->get()->getResultArray());
    }

    // ── GET /notas_credito/:id ────────────────────────────────
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $db   = \Config\Database::connect();
        $nota = $db->table('notas_credito nc')
            ->select('nc.*, c.nombre_empresa, c.nombre_encargado, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = nc.clientes_id', 'left')
            ->join('facturas f',  'f.id_facturas  = nc.facturas_id',  'left')
            ->where('nc.id_nota_credito', $id)
            ->get()->getRowArray();

        if (!$nota) return $this->failNotFound("Nota crédito con ID $id no encontrada.");

        return $this->respond($nota);
    }

    // ── POST /notas_credito ───────────────────────────────────
    // Body: { facturas_id, clientes_id, fecha, monto, motivo? }
    // El número se genera automáticamente.
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        foreach (['facturas_id', 'clientes_id', 'fecha', 'monto'] as $campo) {
            if (empty($data[$campo])) {
                return $this->fail("El campo '$campo' es requerido", 400);
            }
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $monto     = (float) $data['monto'];
            $facturaId = (int)   $data['facturas_id'];

            $facturaModel = new FacturasModel();
            $factura      = $facturaModel->find($facturaId);

            if (!$factura)
                throw new \Exception('La factura indicada no existe');

            if ((int) $factura['cliente_id'] !== (int) $data['clientes_id'])
                throw new \Exception('La factura no pertenece al cliente indicado');

            if ($factura['estado'] === 'Pagada')
                throw new \Exception('No se puede crear una nota crédito sobre una factura ya pagada');

            if ($monto > (float) $factura['saldo_pendiente'])
                throw new \Exception("El monto ($monto) supera el saldo pendiente ({$factura['saldo_pendiente']})");

            // Número automático (centralizado en NumeracionModel)
            $data['numero'] = (new NumeracionModel())->reservar('nota_credito');
            $data['estado'] = 'Activa';

            $id = $this->model->create_table($data, 'notas_credito');
            if (!$id) throw new \Exception(implode(', ', $this->model->errors()));

            // Recalcula saldo de la factura incluyendo esta nota
            $facturaModel->recalcularSaldo($facturaId);

            $db->transComplete();
            if (!$db->transStatus()) throw new \Exception('Error al confirmar la transacción');

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Nota crédito creada exitosamente',
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PATCH /notas_credito/:id/anular ──────────────────────
    // Anula una nota crédito y revierte su efecto en la factura.
    // Las notas crédito nunca se eliminan, solo se anulan (auditoría).
    public function anular($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $nota = $this->model->find($id);
        if (!$nota) return $this->failNotFound("Nota crédito con ID $id no encontrada.");

        if ($nota['estado'] === 'Anulada')
            return $this->fail('La nota crédito ya está anulada', 400);

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $this->model->update((int) $id, ['estado' => 'Anulada']);

            // Recalcula saldo de la factura sin contar esta nota
            $facturaModel = new FacturasModel();
            $facturaModel->recalcularSaldo((int) $nota['facturas_id']);

            $db->transComplete();
            if (!$db->transStatus()) throw new \Exception('Error al confirmar la transacción');

            return $this->respond([
                'status'  => 200,
                'message' => "Nota crédito {$nota['numero']} anulada correctamente",
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage(), 400);
        }
    }
}