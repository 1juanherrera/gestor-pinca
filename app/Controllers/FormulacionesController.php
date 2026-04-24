<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FormulacionesModel;
use Exception;

class FormulacionesController extends ResourceController 
{
    protected $modelName = FormulacionesModel::class;

    protected $request;

    public function showItem($itemId = null)
    {
        try {
            if (empty($itemId)) {
                return $this->fail('El parámetro itemId es requerido.', 400);
            }

            $data = $this->model->getFormulacionConMateriasPrimas($itemId);

            return $this->respond([
                'status'  => 'success',
                'data'    => $data
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 404);
        }
    }

    public function formulaciones()
    {
        $items = $this->model->get_items_formulaciones();
        return $this->respond($items);
    }

    public function show($id = null)
    {
        try {
            $model = new FormulacionesModel();
            $data = $model->get_item_formulacion_by_id($id);

            return $this->response->setJSON([
                'status'  => 200,
                'success' => true,
                'data'    => $data
            ]);

        } catch (Exception $e) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function calcular_costos_volumen($itemId, $newVolume = null)
    {
        try {
            $costs = $this->model->calculate_costs($itemId, $newVolume);
            return $this->respond($costs);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function recalcular_costos_por_volumen($itemId, $newVolume)
    {
        try {
            $costs = $this->model->recalculate_costs_with_new_volume($itemId, (float) $newVolume);
            return $this->respond($costs);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // POST /api/formulaciones
    public function create()
    {
        try {
            $data = $this->request->getJSON(true);

            if (empty($data)) {
                return $this->failValidationErrors('No se recibieron datos válidos.');
            }

            $result = $this->model->crearFormulacion($data);

            return $this->respondCreated([
                'status'  => 'success',
                'message' => $result['message'],
                'id'      => $result['formulacion_id'],
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function proveedores_formulacion($itemId = null)
    {
        try {
            if (empty($itemId)) {
                return $this->fail('El parámetro itemId es requerido.', 400);
            }
            $data = $this->model->get_proveedores_formulacion((int) $itemId);
            return $this->respond($data);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function calcular_costos_por_proveedor($itemId = null, $proveedorId = null)
    {
        try {
            if (empty($itemId) || empty($proveedorId)) {
                return $this->fail('Los parámetros itemId y proveedorId son requeridos.', 400);
            }
            $data = $this->model->calculate_costs_by_proveedor((int) $itemId, (int) $proveedorId);
            return $this->respond($data);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

// PUT /api/formulaciones/:id
    public function update($id = null)
    {
        try {
            if (empty($id)) {
                return $this->failValidationErrors('El ID es obligatorio.');
            }

            $data = $this->request->getJSON(true);

            if (empty($data)) {
                return $this->failValidationErrors('No se recibieron datos válidos.');
            }

            $result = $this->model->actualizarFormulacion((int) $id, $data);

            return $this->respond([
                'status'  => 'success',
                'message' => $result['message'],
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }
}