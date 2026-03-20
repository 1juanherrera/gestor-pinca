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

    // DELETE api/inventario/{item_id}/bodega/{bodega_id}
    public function removeFromBodega(int $itemId, int $bodegaId)
    {
        $result = $this->model->removeFromBodega($itemId, $bodegaId);

        if ($result) {
            return $this->respond([
                'mensaje' => 'Ítem eliminado del inventario correctamente',
            ]);
        }
        return $this->fail('No se encontró el ítem en esta bodega', 404);
    }
}