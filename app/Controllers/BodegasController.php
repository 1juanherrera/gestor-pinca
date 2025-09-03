<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BodegasModel;

class BodegasController extends ResourceController
{
    protected $modelName = BodegasModel::class;


    public function bodegas()
    {
        $bodegas = $this->model->get_bodegas_all();
        return $this->respond($bodegas);
    }

    public function show($id = null)
    {
        $bodega = $this->model->get($id, 'bodega');
        if (!$bodega) {
            return $this->failNotFound("Bodega con ID $id no encontrada.");
        }
        return $this->respond($bodega);
    }

    public function create()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        if ($this->model->create_bodega($data, 'bodega')) {
            return $this->respondCreated([
                'mensaje' => 'Bodega creada',
                'id' => $this->model->insertID(),
            ]);
        }
        return $this->failValidationErrors($this->model->errors());
    }

    public function update($id = null)
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        if (!$this->model->find($id)) {
            return $this->failNotFound("Bodega con ID $id no encontrada.");
        }
        $this->model->update($id, $data);
        return $this->respond(['mensaje' => "Bodega $id actualizada"]);
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound("Bodega con ID $id no encontrada.");
        }
        $this->model->delete($id);
        return $this->respondDeleted(['mensaje' => "Bodega $id eliminada"]);
    }
}
