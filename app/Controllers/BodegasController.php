<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BodegasModel;

class BodegasController extends ResourceController
{
    protected $modelName = BodegasModel::class;


    public function bodegas()
    {
        // Opción 1: usando tu método genérico
        $bodegas = $this->model->get_bodegas_all();
        return $this->respond($bodegas);
    }

    public function show($id = null)
    {
        $bodega = $this->model->get($id, 'bodegas');

        if (!$bodega) {
            return $this->failNotFound("No se encontró la bodega con ID $id");
        }

        return $this->respond($bodega);
    }
}
