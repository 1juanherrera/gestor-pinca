<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FormulacionesModel;

class FormulacionesController extends ResourceController 
{
    protected $modelName = FormulacionesModel::class;

    public function formulaciones()
    {
        $items = $this->model->get_items_formulaciones();
        return $this->respond($items);
    }

    public function calcular_costos_nuevo_volumen($itemId, $newVolume = null)
    {
        try {
            $costs = $this->model->calculate_costs_new_volume($itemId, $newVolume);
            return $this->respond($costs);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }
}