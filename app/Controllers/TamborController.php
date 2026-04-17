<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\IncomingRequest;
use App\Models\TamborModel;
use Exception;

class TamborController extends ResourceController
{
    protected $modelName = TamborModel::class;
    protected IncomingRequest $req;

    public function __construct()
    {
        $this->req = service('request');
    }

    public function index()
    {
        try {
            $filters = [
                'item_general_id' => $this->req->getGet('item_general_id'),
                'bodegas_id'      => $this->req->getGet('bodegas_id'),
                'estado'          => $this->req->getGet('estado'),
                'search'          => $this->req->getGet('search'),
            ];
            $data = $this->model->get_tambores($filters);
            return $this->respond(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function show($id = null)
    {
        try {
            $tambor = $this->model->get_tambor_detalle((int) $id);
            if (!$tambor) {
                return $this->failNotFound("Tambor con ID $id no encontrado.");
            }
            return $this->respond(['status' => 'success', 'data' => $tambor]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function create()
    {
        try {
            $data = $this->req->getJSON(true);

            if (empty($data['numeros'])) {
                return $this->failValidationErrors('El campo "numeros" es obligatorio (ej: "223,224,225").');
            }
            if (empty($data['item_general_id'])) {
                return $this->failValidationErrors('El campo "item_general_id" es obligatorio.');
            }
            if (empty($data['bodegas_id'])) {
                return $this->failValidationErrors('El campo "bodegas_id" es obligatorio.');
            }
            if (!isset($data['cantidad_inicial']) || $data['cantidad_inicial'] <= 0) {
                return $this->failValidationErrors('El campo "cantidad_inicial" debe ser mayor a 0.');
            }

            $ids = $this->model->crear_tambores($data);
            return $this->respondCreated([
                'status'  => 'success',
                'message' => count($ids) . ' tambor(es) creado(s).',
                'ids'     => $ids,
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function update($id = null)
    {
        try {
            $data    = $this->req->getJSON(true);
            $tambor  = $this->model->find((int) $id);
            if (!$tambor) {
                return $this->failNotFound("Tambor con ID $id no encontrado.");
            }

            $allowed = ['bodegas_id', 'estado', 'numero_tambor'];
            $update  = array_intersect_key($data, array_flip($allowed));

            if (empty($update)) {
                return $this->failValidationErrors('No se enviaron campos válidos para actualizar.');
            }

            $this->model->update((int) $id, $update);
            return $this->respond([
                'status' => 'success',
                'data'   => $this->model->get_tambor_detalle((int) $id),
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function consumir($id = null)
    {
        try {
            $data = $this->req->getJSON(true);

            if (empty($data['cantidad']) || $data['cantidad'] <= 0) {
                return $this->failValidationErrors('El campo "cantidad" debe ser mayor a 0.');
            }

            $referencia_tipo = $data['referencia_tipo'] ?? 'AJUSTE';
            $referencia_id   = $data['referencia_id']   ?? null;

            $tambor = $this->model->consumir_tambor((int) $id, (float) $data['cantidad'], $referencia_tipo, $referencia_id);
            return $this->respond(['status' => 'success', 'data' => $tambor]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function disponibles()
    {
        try {
            $item_general_id = $this->req->getGet('item_general_id');
            $bodegas_id      = $this->req->getGet('bodegas_id');

            if (!$item_general_id) {
                return $this->failValidationErrors('El parámetro "item_general_id" es obligatorio.');
            }

            $data = $this->model->get_tambores_disponibles((int) $item_general_id, $bodegas_id ? (int) $bodegas_id : null);
            return $this->respond(['status' => 'success', 'data' => $data]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }
}
