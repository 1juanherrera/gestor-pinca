<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ItemModel;

class ItemController extends ResourceController 
{
    protected $modelName = ItemModel::class;

    public function item_general()
    {
        $items = $this->model->get_all('item_general');
        return $this->respond($items);
    }

    public function get_items_all(){
        $items = $this->model->get_items_all();
        return $this->respond($items);
    }

    public function show($id = null)
    {
        $item = $this->model->get($id, 'item_general');
        if (!$item) {
            return $this->failNotFound("Item con ID $id no encontrado.");
        }
        return $this->respond($item);
    }

    public function create()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);

        if (!$data) return $this->fail('JSON inválido', 400);

        // Llamamos al modelo
        $result = $this->model->create_full_item($data);

        // Verificamos si es un ID (éxito) o un array de error
        if (is_numeric($result)) {
            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Ítem creado exitosamente',
                'id'      => $result
            ]);
        } else {
            // Si falló, mostramos el mensaje exacto que nos dio el modelo
            $mensajeError = is_array($result) && isset($result['error']) 
                            ? $result['error'] 
                            : 'Error desconocido al guardar';
                            
            return $this->failServerError($mensajeError);
        }
    }

    public function update($id = null)
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        if (!$this->model->find($id)) {
            return $this->failNotFound("Item con ID $id no encontrado.");
        }
        $this->model->update($id, $data);
        return $this->respond(['mensaje' => "Item $id actualizado"]);
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound("Item con ID $id no encontrado.");
        }
        $this->model->delete($id);
        return $this->respondDeleted(['mensaje' => "Item $id eliminado"]);
    }
}
