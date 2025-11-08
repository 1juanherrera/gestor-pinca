<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ProveedorModel;

class ProveedorController extends ResourceController 
{
    protected $modelName = ProveedorModel::class;

    public function proveedores()
    {
        $proveedores = $this->model->get_all('proveedor');
        return $this->respond($proveedores);
    }

    public function get_item_proveedores($id = null)
    {
        $data = $this->model->get_item_proveedores($id);

        if ($id !== null && !$data) {
            return $this->failNotFound("Proveedor con ID $id no encontrado.");
        }

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $proveedor = $this->model->get($id, 'proveedor');
        if (!$proveedor) {
            return $this->failNotFound("Proveedor con ID $id no encontrado.");
        }
        return $this->respond($proveedor);
    }

    public function create()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        $insert_id = $this->model->create_table($data, 'proveedor');
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
        if (!$this->model->get($id, 'proveedor')) {
            return $this->failNotFound("Instalación con ID $id no encontrada.");
        }
        // Intentar actualizar
        $updated = $this->model->update_table($id, $data, 'proveedor');

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
        if (!$this->model->get($id, 'proveedor')) {
            return $this->failNotFound("Instalación con ID $id no encontrada.");
        }
        // Intentar eliminar usando BaseModel
        $deleted = $this->model->delete_table($id, 'proveedor');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar la instalación con ID $id.");
        }
        return $this->respondDeleted([
            'mensaje' => "Instalación con ID $id eliminada correctamente"
        ]);
    }
}