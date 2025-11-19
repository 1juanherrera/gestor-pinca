<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FacturasModel;

class FacturasController extends ResourceController 
{
    protected $modelName = FacturasModel::class;

    public function facturas()
    {
        $facturas = $this->model->get_all('facturas');
        return $this->respond($facturas);
    }

    public function get_item_facturas($id = null)
    {
        $data = $this->model->get_item_facturas($id);

        if ($id !== null && !$data) {
            return $this->failNotFound("factura con ID $id no encontrado.");
        }

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $factura = $this->model->get($id, 'facturas');
        if (!$factura) {
            return $this->failNotFound("factura con ID $id no encontrado.");
        }
        return $this->respond($factura);
    }

    public function create()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        $insert_id = $this->model->create_table($data, 'facturas');
        if ($insert_id) {
            return $this->respondCreated([
                'mensaje' => 'factura creada correctamente',
                'id'      => $insert_id,
            ]);
        }
        return $this->fail('Error al crear la factura');
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
        if (!$this->model->get($id, 'facturas')) {
            return $this->failNotFound("factura con ID $id no encontrada.");
        }
        // Intentar actualizar
        $updated = $this->model->update_table($id, $data, 'facturas');
        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->fail('No se pudo actualizar la factura.');
        }
        return $this->respond([
            'mensaje' => "factura con ID $id actualizada correctamente",
            'data'    => $data
        ]);
    }

    public function delete($id = null)
    {
        // Validar que se envió un ID
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        // Verificar que la factura exista
        if (!$this->model->get($id, 'facturas')) {
            return $this->failNotFound("factura con ID $id no encontrada.");
        }
        // Intentar eliminar usando BaseModel
        $deleted = $this->model->delete_table($id, 'facturas');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar la factura con ID $id.");
        }
        return $this->respondDeleted([
            'mensaje' => "factura con ID $id eliminada correctamente"
        ]);
    }
}