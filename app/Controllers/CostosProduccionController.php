<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FormulacionesModel;

/**
 * CostosProduccionController — panel de costos finales por producto.
 *
 * Endpoint principal: GET /api/costos-produccion (listado batch)
 * Endpoint detalle:   GET /api/costos-produccion/:id (con breakdown por ingrediente)
 *
 * Diferencia vs Formulaciones:
 *  - Aquí se calcula el costo HOY usando el proveedor más barato por ingrediente.
 *  - Marca explícitamente productos con MPs sin proveedor como "incompletos".
 *  - Vista agregada para análisis y pricing — no para edición.
 */
class CostosProduccionController extends ResourceController
{
    use \App\Traits\ApiResponse;

    use \App\Traits\JwtUserAware;

    protected $modelName = FormulacionesModel::class;
    protected $format    = 'json';

    /** GET /api/costos-produccion */
    public function index()
    {
        try {
            $data = $this->model->get_costos_produccion_batch();
            return $this->respond($data);
        } catch (\Throwable $e) {
            return $this->apiFail($e->getMessage(), 500);
        }
    }

    /** GET /api/costos-produccion/:id */
    public function show($id = null)
    {
        if (!$id || !is_numeric($id)) {
            return $this->apiFail('ID inválido.', 422);
        }
        try {
            $data = $this->model->get_costo_produccion_detalle((int) $id);
            if (!$data) return $this->apiNotFound("Producto #{$id} no encontrado o sin fórmula activa.");
            return $this->respond($data);
        } catch (\Throwable $e) {
            return $this->apiFail($e->getMessage(), 500);
        }
    }

    /** GET /api/costos-produccion/:id/historia — snapshots del costo */
    public function historia($id = null)
    {
        if (!$id || !is_numeric($id)) {
            return $this->apiFail('ID inválido.', 422);
        }
        $db = \Config\Database::connect();
        $rows = $db->table('costos_snapshot')
            ->select('fecha, estado, costo_mp_por_unidad, costo_empaque_mod, costo_total, precio_venta_calc, mps_total, mps_cubiertas')
            ->where('item_general_id', (int) $id)
            ->orderBy('fecha', 'ASC')
            ->limit(36) // hasta 3 años de data mensual
            ->get()->getResultArray();

        return $this->respond([
            'item_general_id' => (int) $id,
            'snapshots'       => array_map(fn($r) => [
                'fecha'              => $r['fecha'],
                'estado'             => $r['estado'],
                'costo_mp_por_unidad'=> $r['costo_mp_por_unidad'] !== null ? (float) $r['costo_mp_por_unidad'] : null,
                'costo_empaque_mod'  => (float) $r['costo_empaque_mod'],
                'costo_total'        => $r['costo_total'] !== null ? (float) $r['costo_total'] : null,
                'precio_venta_calc'  => $r['precio_venta_calc'] !== null ? (float) $r['precio_venta_calc'] : null,
                'mps_total'          => (int) $r['mps_total'],
                'mps_cubiertas'      => (int) $r['mps_cubiertas'],
            ], $rows),
        ]);
    }
}
