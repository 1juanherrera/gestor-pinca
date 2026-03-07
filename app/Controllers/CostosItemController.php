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

        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }

        if (!$this->model->get($id, 'costos_item')) {
            return $this->failNotFound("costos_item con ID $id no encontrada.");
        }

        // Filtrar solo campos permitidos
        $allowedFields = ['envase', 'etiqueta', 'bandeja', 'plastico', 'costo_mod', 'porcentaje_utilidad'];
        $dataToSave = array_intersect_key($data, array_flip($allowedFields));

        if (empty($dataToSave)) {
            return $this->failValidationErrors('No hay campos válidos para actualizar.');
        }

        $updated = $this->model->update_costos_item($id, $dataToSave, 'costos_item');

        if ($updated === false) {
            return $this->fail('No se pudo actualizar el costos_item.');
        }

        return $this->respond([
            'success' => true,
            'mensaje' => "costos_item con ID $id actualizada correctamente",
            'data'    => $dataToSave
        ]);
    }
}