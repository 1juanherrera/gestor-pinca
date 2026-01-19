<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InventarioModel;

class InventarioController extends ResourceController 
{
    protected $modelName = InventarioModel::class;

    public function traspaso()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);

        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }

        $result = $this->model->traspaso($data);
        if ($result) {
            return $this->respond([
                'mensaje' => 'Traspaso realizado correctamente',
            ]);
        }
        return $this->fail('Error al realizar el traspaso');
    }
}