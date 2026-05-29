<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ComparadorModel;

class ComparadorController extends ResourceController
{
    use \App\Traits\ApiResponse;

    protected $modelName = ComparadorModel::class;

    // GET api/comparador/por_item
    public function por_item()
    {
        $data = $this->model->por_item();
        return $this->respond($data);
    }

    // GET api/comparador/por_proveedor/{id}
    public function por_proveedor($id = null)
    {
        if (!$id) {
            return $this->apiFail('Se requiere el ID del proveedor.', 422);
        }
        $data = $this->model->por_proveedor((int) $id);
        return $this->respond($data);
    }

    // GET api/comparador/historial/{item_proveedor_id}
    public function historial($id = null)
    {
        if (!$id) {
            return $this->apiFail('Se requiere el ID del item proveedor.', 422);
        }
        $data = $this->model->historial((int) $id);
        return $this->respond($data);
    }
}