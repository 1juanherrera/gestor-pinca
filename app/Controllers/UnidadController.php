<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UnidadModel;

class UnidadController extends ResourceController
{
    use \App\Traits\JwtUserAware;
    use \App\Traits\ApiResponse;

    protected $modelName = UnidadModel::class;

    public function unidades()
    {
        $unidades = $this->model->get_all('unidad');
        return $this->respond($unidades);
    }

    public function get_item_unidades($id = null)
    {
        $data = $this->model->get_item_unidades($id);

        if ($id !== null && !$data) {
            return $this->failNotFound("unidad con ID $id no encontrada.");
        }

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $unidad = $this->model->get($id, 'unidad');
        if (!$unidad) {
            return $this->failNotFound("unidad con ID $id no encontrada.");
        }
        return $this->respond($unidad);
    }

    public function create()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        // Validación de input. Columnas reales del modelo: numero, nombre,
        // descripcion, estados, escala. NO existe abreviatura/simbolo en el schema.
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nombre' => 'required|max_length[50]',
        ]);
        if (!$validation->run($data)) {
            return $this->apiValidationError($validation->getErrors());
        }
        $insert_id = $this->model->create_table($data, 'unidad');
        if ($insert_id) {
            return $this->respondCreated([
                'mensaje' => 'unidad creada correctamente',
                'id'      => $insert_id,
            ]);
        }
        return $this->fail('Error al crear la unidad');
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
        if (!$this->model->get($id, 'unidad')) {
            return $this->failNotFound("unidad con ID $id no encontrada.");
        }
        // Intentar actualizar
        $updated = $this->model->update_table($id, $data, 'unidad');
        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->fail('No se pudo actualizar la unidad.');
        }
        return $this->respond([
            'mensaje' => "unidad con ID $id actualizada correctamente",
            'data'    => $data
        ]);
    }

    public function delete($id = null)
    {
        // Validar que se envió un ID
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        // Verificar que la unidad exista
        if (!$this->model->get($id, 'unidad')) {
            return $this->failNotFound("unidad con ID $id no encontrada.");
        }
        // Intentar eliminar usando BaseModel
        $deleted = $this->model->delete_table($id, 'unidad');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar la unidad con ID $id.");
        }
        log_message('info', "[DELETE_UNIDAD] usuario={$this->getUsername()} id={$id}");
        return $this->respondDeleted([
            'mensaje' => "unidad con ID $id eliminada correctamente"
        ]);
    }
}