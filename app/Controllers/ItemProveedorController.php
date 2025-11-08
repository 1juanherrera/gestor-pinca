<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ItemProveedorModel;

class ItemProveedorController extends ResourceController 
{
    protected $modelName = ItemProveedorModel::class;

    public function item_proveedores()
    {
        $proveedores = $this->model->get_all('item_proveedor');
        return $this->respond($proveedores);
    }

    public function get_item_proveedores()
    {
        $data = $this->model->get_item_proveedores();
        if (!$data) {
            return $this->failNotFound("No se encontraron ítems de proveedores.");
        }
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $item_proveedor = $this->model->get($id, 'item_proveedor');
        if (!$item_proveedor) {
            return $this->failNotFound("Item Proveedor con ID $id no encontrado.");
        }
        return $this->respond($item_proveedor);
    }

    public function create()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        $insert_id = $this->model->create_table($data, 'item_proveedor');
        if ($insert_id) {
            return $this->respondCreated([
                'mensaje' => 'Item Proveedor creado correctamente',
                'id'      => $insert_id,
            ]);
        }
        return $this->fail('Error al crear el Item Proveedor');
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
        if (!$this->model->get($id, 'item_proveedor')) {
            return $this->failNotFound("Item Proveedor con ID $id no encontrado.");
        }
        // Intentar actualizar
        $updated = $this->model->update_table($id, $data, 'item_proveedor');

        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->fail('No se pudo actualizar el Item Proveedor.');
        }
        return $this->respond([
            'mensaje' => "Item Proveedor con ID $id actualizada correctamente",
            'data'    => $data
        ]);
    }

    public function delete($id = null)
    {
        // Validar que se envió un ID
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        // Verificar que el Item Proveedor exista
        if (!$this->model->get($id, 'item_proveedor')) {
            return $this->failNotFound("Item Proveedor con ID $id no encontrado.");
        }
        // Intentar eliminar usando BaseModel
        $deleted = $this->model->delete_table($id, 'item_proveedor');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar el Item Proveedor con ID $id.");
        }
        return $this->respondDeleted([
            'mensaje' => "Item Proveedor con ID $id eliminada correctamente"
        ]);
    }
}