<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InstalacionesModel;

class InstalacionesController extends ResourceController 
{
    protected $modelName = InstalacionesModel::class;

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

    public function create(){
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        $insert_id = $this->model->create_table($data, 'instalaciones');
        if ($insert_id) {
            return $this->respondCreated([
                'mensaje' => 'Instalación creada correctamente',
                'id'      => $insert_id,
            ]);
        }
        return $this->fail('Error al crear la instalación');
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
        if (!$this->model->get($id, 'instalaciones')) {
            return $this->failNotFound("Instalación con ID $id no encontrada.");
        }
        // Intentar actualizar
        $updated = $this->model->update_table($id, $data, 'instalaciones');

        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->fail('No se pudo actualizar la instalación.');
        }
        return $this->respond([
            'mensaje' => "Instalación con ID $id actualizada correctamente",
            'data'    => $data
        ]);
    }

    public function delete($id = null)
    {
        // Validar que se envió un ID
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        // Verificar que la instalación exista
        if (!$this->model->get($id, 'instalaciones')) {
            return $this->failNotFound("Instalación con ID $id no encontrada.");
        }
        // Intentar eliminar usando BaseModel
        $deleted = $this->model->delete_table($id, 'instalaciones');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar la instalación con ID $id.");
        }
        return $this->respondDeleted([
            'mensaje' => "Instalación con ID $id eliminada correctamente"
        ]);
    }
}