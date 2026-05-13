<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CostosIndirectosModel;

class CostosIndirectosController extends ResourceController
{
    protected $modelName = CostosIndirectosModel::class;

    // GET /costos_indirectos
    public function index()
    {
        return $this->respond($this->model->listar());
    }

    // GET /costos_indirectos/resumen
    public function resumen()
    {
        return $this->respond($this->model->resumen());
    }

    // GET /costos_indirectos/:id
    public function show($id = null)
    {
        $row = $this->model->find($id);
        if (!$row) return $this->failNotFound("Costo indirecto #$id no encontrado.");
        return $this->respond($row);
    }

    // POST /costos_indirectos
    public function create()
    {
        $data = $this->request->getJSON(true);
        if (empty($data['nombre']) || empty($data['categoria'])) {
            return $this->failValidationErrors('nombre y categoria son obligatorios.');
        }
        $data['fecha_actualizacion'] = date('Y-m-d');
        $data['activo'] = 1;
        $id = $this->model->insert($data);
        return $this->respondCreated(['mensaje' => 'Costo indirecto creado', 'id' => $id]);
    }

    // PUT /costos_indirectos/:id
    public function update($id = null)
    {
        $row = $this->model->find($id);
        if (!$row) return $this->failNotFound("Costo indirecto #$id no encontrado.");

        $data = $this->request->getJSON(true);
        $data['fecha_actualizacion'] = date('Y-m-d');
        $this->model->update($id, $data);
        return $this->respond(['mensaje' => "Costo indirecto #$id actualizado"]);
    }

    // DELETE /costos_indirectos/:id
    public function delete($id = null)
    {
        $row = $this->model->find($id);
        if (!$row) return $this->failNotFound("Costo indirecto #$id no encontrado.");
        $this->model->delete($id);
        return $this->respondDeleted(['mensaje' => "Costo indirecto #$id eliminado"]);
    }

    // GET /costos_indirectos/item/:item_id
    public function costosItem($itemId = null)
    {
        if (!$itemId) return $this->fail('item_id requerido', 400);
        $costos = $this->model->costosDeItem((int)$itemId);
        $total  = $this->model->totalAsignadoItem((int)$itemId);
        return $this->respond(['costos' => $costos, 'total_asignado' => $total]);
    }

    // POST /costos_indirectos/item/:item_id
    // Body: { costos_indirectos_id, valor_asignado }
    public function asignarItem($itemId = null)
    {
        if (!$itemId) return $this->fail('item_id requerido', 400);
        $data = $this->request->getJSON(true);
        if (empty($data['costos_indirectos_id'])) {
            return $this->failValidationErrors('costos_indirectos_id es obligatorio.');
        }
        $this->model->asignarAItem(
            (int)$itemId,
            (int)$data['costos_indirectos_id'],
            (float)($data['valor_asignado'] ?? 0)
        );
        return $this->respond(['mensaje' => 'Costo asignado correctamente']);
    }
}
