<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CostosIndirectosModel;

class CostosIndirectosController extends ResourceController
{
    use \App\Traits\ApiResponse;

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
        if (!$row) return $this->apiNotFound("Costo indirecto #$id no encontrado.");
        return $this->respond($row);
    }

    // POST /costos_indirectos
    public function create()
    {
        $data = $this->request->getJSON(true);
        if (empty($data['nombre']) || empty($data['categoria'])) {
            return $this->apiFail('nombre y categoria son obligatorios.', 422);
        }
        // valor_mensual alimenta SUM(valor_mensual) en resumen(): debe ser numérico >= 0.
        if (isset($data['valor_mensual']) && (!is_numeric($data['valor_mensual']) || (float) $data['valor_mensual'] < 0)) {
            return $this->apiFail('valor_mensual debe ser un número mayor o igual a 0.', 422);
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
        if (!$row) return $this->apiNotFound("Costo indirecto #$id no encontrado.");

        $data = $this->request->getJSON(true);
        if (isset($data['valor_mensual']) && (!is_numeric($data['valor_mensual']) || (float) $data['valor_mensual'] < 0)) {
            return $this->apiFail('valor_mensual debe ser un número mayor o igual a 0.', 422);
        }
        $data['fecha_actualizacion'] = date('Y-m-d');
        $this->model->update($id, $data);
        return $this->respond(['mensaje' => "Costo indirecto #$id actualizado"]);
    }

    // DELETE /costos_indirectos/:id
    public function delete($id = null)
    {
        $row = $this->model->find($id);
        if (!$row) return $this->apiNotFound("Costo indirecto #$id no encontrado.");
        $this->model->delete($id);
        return $this->respondDeleted(['mensaje' => "Costo indirecto #$id eliminado"]);
    }

    // GET /costos_indirectos/item/:item_id
    public function costosItem($itemId = null)
    {
        if (!$itemId) return $this->apiFail('item_id requerido', 400);
        $costos = $this->model->costosDeItem((int)$itemId);
        $total  = $this->model->totalAsignadoItem((int)$itemId);
        return $this->respond(['costos' => $costos, 'total_asignado' => $total]);
    }

    // POST /costos_indirectos/item/:item_id
    // Body: { costos_indirectos_id, valor_asignado }
    public function asignarItem($itemId = null)
    {
        if (!$itemId) return $this->apiFail('item_id requerido', 400);
        $data = $this->request->getJSON(true);
        if (empty($data['costos_indirectos_id'])) {
            return $this->apiFail('costos_indirectos_id es obligatorio.', 422);
        }
        $this->model->asignarAItem(
            (int)$itemId,
            (int)$data['costos_indirectos_id'],
            (float)($data['valor_asignado'] ?? 0)
        );
        return $this->respond(['mensaje' => 'Costo asignado correctamente']);
    }
}
