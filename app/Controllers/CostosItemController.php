<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CostosItemModel;

class CostosItemController extends ResourceController 
{
    protected $modelName = CostosItemModel::class;

    public function update($id = null)
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        // Verificar que el registro exista antes de actualizar
        if (!$this->model->get($id, 'costos_item')) {
            return $this->failNotFound("costos_item con ID $id no encontrada.");
        }
        // Intentar actualizar
        $updated = $this->model->update_costos_item($id, $data, 'costos_item');
        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->fail('No se pudo actualizar el costos_item.');
        }
        return $this->respond([
            'mensaje' => "costos_item con ID $id actualizada correctamente",
            'data'    => $data
        ]);
    }
}