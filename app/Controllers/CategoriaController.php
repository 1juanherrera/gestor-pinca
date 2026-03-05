<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CategoriaModel;

class CategoriaController extends ResourceController 
{
    protected $modelName = CategoriaModel::class;

    public function categorias()
    {
        $categorias = $this->model->get_all('categoria');
        return $this->respond($categorias);
    }

    public function get_item_categorias($id = null)
    {
        $data = $this->model->get_item_categorias($id);

        if ($id !== null && !$data) {
            return $this->failNotFound("categoria con ID $id no encontrada.");
        }

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $categoria = $this->model->get($id, 'categoria');
        if (!$categoria) {
            return $this->failNotFound("categoria con ID $id no encontrada.");
        }
        return $this->respond($categoria);
    }

    public function create()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        $insert_id = $this->model->create_table($data, 'categorias');
        if ($insert_id) {
            return $this->respondCreated([
                'mensaje' => 'categoria creada correctamente',
                'id'      => $insert_id,
            ]);
        }
        return $this->fail('Error al crear la categoria.');
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
        if (!$this->model->get($id, 'categoria')) {
            return $this->failNotFound("categoria con ID $id no encontrada.");
        }
        // Intentar actualizar
        $updated = $this->model->update_table($id, $data, 'categorias');
        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->fail('No se pudo actualizar la categoria.');
        }
        return $this->respond([
            'mensaje' => "categoria con ID $id actualizada correctamente",
            'data'    => $data
        ]);
    }

    public function delete($id = null)
    {
        // Validar que se envió un ID
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        // Verificar que la categoria exista
        if (!$this->model->get($id, 'categoria')) {
            return $this->failNotFound("categoria con ID $id no encontrada.");
        }
        // Intentar eliminar usando BaseModel
        $deleted = $this->model->delete_table($id, 'categorias');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar la categoria con ID $id.");
        }
        return $this->respondDeleted([
            'mensaje' => "categoria con ID $id eliminada correctamente"
        ]);
    }
}