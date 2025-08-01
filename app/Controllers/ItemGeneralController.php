<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ItemGeneralModel;

class ItemGeneralController extends ResourceController 
{
    public function __construct(){

        $this->model = new ItemGeneralModel();
    }

    public function item_general()
    {
        $items = $this->model->get_all('item_general');
        return $this->respond($items);
    }

    public function get_items_all(){
        $items = $this->model->get_items_all();
        return $this->respond($items);
    }

    public function item_formulaciones()
    {
        $items = $this->model->get_items_formulaciones();
        return $this->respond($items);
    }

    public function show($id = null)
    {
        $item = $this->model->get_item($id, 'item_general');
        if (!$item) {
            return $this->failNotFound("Item con ID $id no encontrado.");
        }
        return $this->respond($item);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);
        if ($this->model->create_item($data, 'item_general')) {
            return $this->respondCreated([
                'mensaje' => 'Item creado',
                'id' => $this->model->insertID(),
            ]);
        }
        return $this->failValidationErrors($this->model->errors());
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
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
