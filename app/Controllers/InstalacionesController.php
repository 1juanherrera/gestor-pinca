<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InstalacionesModel;

class InstalacionesController extends ResourceController 
{
    public function __construct(){

        $this->model = new InstalacionesModel();
    }

    public function instalaciones()
    {
        $instalaciones = $this->model->get_all('instalaciones');
        return $this->respond($instalaciones);
    }

    public function instalaciones_with_bodegas() 
    {
        $data = $this->model->instalaciones_with_bodegas();
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $instalacion = $this->model->get($id, 'instalaciones');
        if (!$instalacion) {
            return $this->failNotFound("Instalación con ID $id no encontrada.");
        }
        return $this->respond($instalacion);
    }

    public function create()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        if ($this->model->create_instalacion($data, 'instalaciones')) {
            return $this->respondCreated([
                'mensaje' => 'instalacion creado',
                'id' => $this->model->insertID(),
            ]);
        }
        return $this->failValidationErrors($this->model->errors());
    }
}