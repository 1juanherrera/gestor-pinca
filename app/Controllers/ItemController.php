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
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->fail('No se recibieron datos o el JSON es inválido', 400);
        }

        // Validación mínima de seguridad
        if (empty($data['nombre']) || empty($data['categoria_id'])) {
            return $this->failValidationErrors('El nombre y la categoría son obligatorios.');
        }

        // Llamamos al modelo que ya tiene todos los campos de Costos, Inventario y Propiedades
        $result = $this->model->create_full_item($data);

        // Si el modelo devuelve un error (llave foránea, columna inexistente, etc.)
        if (is_array($result) && isset($result['error'])) {
            return $this->fail($result['error'], 400);
        }

        // ÉXITO: Retornamos el ID y un mensaje claro para el frontend
        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Ítem completo creado con éxito',
            'id'      => $result
        ]);
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
