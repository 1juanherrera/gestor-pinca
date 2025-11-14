<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ClientesModel;

class ClientesController extends ResourceController 
{
    protected $modelName = ClientesModel::class;

    public function clientes()
    {
        $clientes = $this->model->get_all('clientes');
        return $this->respond($clientes);
    }

    public function get_item_clientes($id = null)
    {
        $data = $this->model->get_item_clientes($id);

        if ($id !== null && !$data) {
            return $this->failNotFound("Cliente con ID $id no encontrado.");
        }

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $cliente = $this->model->get($id, 'clientes');
        if (!$cliente) {
            return $this->failNotFound("Cliente con ID $id no encontrado.");
        }
        return $this->respond($cliente);
    }

    public function create()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        $insert_id = $this->model->create_table($data, 'clientes');
        if ($insert_id) {
            return $this->respondCreated([
                'mensaje' => 'cliente creada correctamente',
                'id'      => $insert_id,
            ]);
        }
        return $this->fail('Error al crear la cliente');
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
        if (!$this->model->get($id, 'clientes')) {
            return $this->failNotFound("cliente con ID $id no encontrada.");
        }
        // Intentar actualizar
        $updated = $this->model->update_table($id, $data, 'clientes');
        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->fail('No se pudo actualizar la cliente.');
        }
        return $this->respond([
            'mensaje' => "cliente con ID $id actualizada correctamente",
            'data'    => $data
        ]);
    }

    public function delete($id = null)
    {
        // Validar que se envió un ID
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        // Verificar que la cliente exista
        if (!$this->model->get($id, 'clientes')) {
            return $this->failNotFound("cliente con ID $id no encontrada.");
        }
        // Intentar eliminar usando BaseModel
        $deleted = $this->model->delete_table($id, 'clientes');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar la cliente con ID $id.");
        }
        return $this->respondDeleted([
            'mensaje' => "cliente con ID $id eliminada correctamente"
        ]);
    }
}