<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BodegasModel;

class BodegasController extends ResourceController
{
    protected $modelName = BodegasModel::class;

    public function bodegas()
    {
        $bodegas = $this->model->get_all('bodegas');
        return $this->respond($bodegas);
    }

    public function bodega_inventario($id = null)
    {
        $bodega = $this->model->bodega_inventario($id);
        if (!$bodega) {
            return $this->failNotFound("Bodega con ID $id no encontrada.");
        }
        return $this->respond($bodega);
    }

    public function show($id = null)
    {
        $bodega = $this->model->get($id, 'bodegas');
        if (!$bodega) {
            return $this->failNotFound("Bodega con ID $id no encontrada.");
        }
        return $this->respond($bodega);
    }

    public function create(){
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        $insert_id = $this->model->create_table($data, 'bodegas');
        if ($insert_id) {
            return $this->respondCreated([
                'mensaje' => 'Bodega creada correctamente',
                'id'      => $insert_id,
            ]);
        }
        return $this->fail('Error al crear la bodega');
    }

    public function update($id = null)
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        // Verificar que el registro exista antes de actualizar
        if (!$this->model->get($id, 'bodegas')) {
            return $this->failNotFound("Bodega con ID $id no encontrada.");
        }
        // Intentar actualizar
        $updated = $this->model->update_table($id, $data, 'bodegas');

        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->fail('No se pudo actualizar la bodega.');
        }
        return $this->respond([
            'mensaje' => "Bodega con ID $id actualizada correctamente",
            'data'    => $data
        ]);
    }

    public function delete($id = null)
    {
        // Validar que se envió un ID
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        // Verificar que la bodega exista
        if (!$this->model->get($id, 'bodegas')) {
            return $this->failNotFound("Bodega con ID $id no encontrada.");
        }
        // Intentar eliminar usando BaseModel
        $deleted = $this->model->delete_table($id, 'bodegas');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar la bodega con ID $id.");
        }
        return $this->respondDeleted([
            'mensaje' => "Bodega con ID $id eliminada correctamente"
        ]);
    }
}
