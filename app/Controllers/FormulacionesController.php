<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FormulacionesModel;
use Exception;

class FormulacionesController extends ResourceController 
{
    protected $modelName = FormulacionesModel::class;

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
}