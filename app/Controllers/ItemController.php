<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ItemModel;

class ItemController extends ResourceController 
{
    protected $modelName = ItemModel::class;
    protected $request;

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
        if (!$id) return $this->fail("ID no proporcionado", 400);

        $item = $this->model->get_full_item_details($id);

        if (!$item) {
            return $this->failNotFound("El item no existe.");
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
        // 1. Obtener datos del cuerpo de la petición (soporta JSON de React)
        $data = $this->request->getJSON(true);

        if (!$id) {
            return $this->fail("ID no proporcionado", 400);
        }

        try {
            // 2. Llamamos a nuestra función personalizada que actualiza
            // item_general, costos_item y las formulaciones en una sola transacción
            $this->model->update_full_item($id, $data);

            return $this->respond([
                'status'  => 200,
                'message' => "Item $id y sus dependencias actualizados correctamente"
            ]);

        } catch (\Exception $e) {
            // 3. Si algo falla (el item no existe o error SQL), devolvemos el error real
            return $this->fail($e->getMessage(), 400);
        }
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
