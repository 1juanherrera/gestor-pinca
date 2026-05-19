<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FormulacionesModel;
use Exception;

class FormulacionesController extends ResourceController
{
    use \App\Traits\JwtUserAware;

    protected $modelName = FormulacionesModel::class;

    protected $request;

    public function showItem($itemId = null)
    {
        try {
            if (empty($itemId)) {
                return $this->fail('El parámetro itemId es requerido.', 400);
            }

            $data = $this->model->getFormulacionConMateriasPrimas($itemId);

            return $this->respond([
                'status'  => 'success',
                'data'    => $data
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 404);
        }
    }

    public function formulaciones()
    {
        $items = $this->model->get_items_formulaciones();
        return $this->respond($items);
    }

    public function show($id = null)
    {
        try {
            $model = new FormulacionesModel();
            $data = $model->get_item_formulacion_by_id($id);

            return $this->response->setJSON([
                'status'  => 200,
                'success' => true,
                'data'    => $data
            ]);

        } catch (Exception $e) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 404,
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function calcular_costos_volumen($itemId, $newVolume = null)
    {
        try {
            $costs = $this->model->calculate_costs($itemId, $newVolume);
            return $this->respond($costs);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function recalcular_costos_por_volumen($itemId, $newVolume)
    {
        try {
            $costs = $this->model->recalculate_costs_with_new_volume($itemId, (float) $newVolume);
            return $this->respond($costs);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // POST /api/formulaciones
    public function create()
    {
        try {
            $data = $this->request->getJSON(true);

            if (empty($data)) {
                return $this->failValidationErrors('No se recibieron datos válidos.');
            }

            // Validar que el item destino exista y no esté archivado.
            if (!empty($data['item_general_id'])) {
                $existe = db_connect()->table('item_general')
                    ->where('id_item_general', (int) $data['item_general_id'])
                    ->where('deleted_at', null)
                    ->countAllResults();
                if ($existe === 0) {
                    return $this->failValidationErrors("El item #{$data['item_general_id']} no existe o está archivado.");
                }
            }

            // Inyectar responsable real para el snapshot de versión inicial
            if (empty($data['responsable'])) {
                $data['responsable'] = $this->getUsername();
            }

            $result = $this->model->crearFormulacion($data);

            return $this->respondCreated([
                'status'      => 'success',
                'message'     => $result['message'],
                'id'          => $result['formulacion_id'],
                'version_id'  => $result['version_id']  ?? null,
                'version_num' => $result['version_num'] ?? null,
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    /**
     * POST /formulaciones/clonar
     * Body: { from_item_id, to_item_id, nombre? }
     * Copia la fórmula activa del producto origen al destino.
     */
    public function clonar()
    {
        try {
            $data = $this->request->getJSON(true) ?? [];
            $fromId = (int) ($data['from_item_id'] ?? 0);
            $toId   = (int) ($data['to_item_id'] ?? 0);
            $nombre = isset($data['nombre']) ? trim((string) $data['nombre']) : null;
            $force  = !empty($data['force']);

            if ($fromId <= 0 || $toId <= 0) {
                return $this->failValidationErrors('from_item_id y to_item_id son obligatorios.');
            }

            $result = $this->model->clonarFormulacion($fromId, $toId, $nombre, $this->getUsername(), $force);

            return $this->respondCreated([
                'status'      => 'success',
                'message'     => 'Fórmula clonada correctamente',
                'id'          => $result['formulacion_id'],
                'version_id'  => $result['version_id']  ?? null,
                'version_num' => $result['version_num'] ?? null,
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function proveedores_formulacion($itemId = null)
    {
        try {
            if (empty($itemId)) {
                return $this->fail('El parámetro itemId es requerido.', 400);
            }
            $data = $this->model->get_proveedores_formulacion((int) $itemId);
            return $this->respond($data);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function opciones_proveedor_ingrediente($itemId = null)
    {
        try {
            if (empty($itemId)) {
                return $this->fail('El parámetro itemId es requerido.', 400);
            }
            $data = $this->model->get_opciones_proveedor_formulacion((int) $itemId);
            return $this->respond($data);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function calcular_costos_por_proveedor($itemId = null, $proveedorId = null)
    {
        try {
            if (empty($itemId) || empty($proveedorId)) {
                return $this->fail('Los parámetros itemId y proveedorId son requeridos.', 400);
            }
            $data = $this->model->calculate_costs_by_proveedor((int) $itemId, (int) $proveedorId);
            return $this->respond($data);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

// PUT /api/formulaciones/:id
    public function update($id = null)
    {
        try {
            if (empty($id)) {
                return $this->failValidationErrors('El ID es obligatorio.');
            }

            $data = $this->request->getJSON(true);

            if (empty($data)) {
                return $this->failValidationErrors('No se recibieron datos válidos.');
            }

            // Inyectar responsable real para el snapshot de versión
            if (empty($data['responsable'])) {
                $data['responsable'] = $this->getUsername();
            }

            $result = $this->model->actualizarFormulacion((int) $id, $data);

            return $this->respond([
                'status'      => 'success',
                'message'     => $result['message'],
                'version_id'  => $result['version_id']  ?? null,
                'version_num' => $result['version_num'] ?? null,
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    /**
     * GET /api/formulaciones/:id/versiones
     * Lista las versiones (sin snapshot completo, para timeline).
     */
    public function versiones(int $id = 0)
    {
        if ($id <= 0) return $this->fail('ID de formulación requerido', 400);
        try {
            return $this->respond($this->model->listarVersiones($id));
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    /**
     * GET /api/formulaciones/versiones/:versionId
     * Detalle de una versión con su snapshot completo + diff vs anterior.
     */
    public function versionDetalle(int $versionId = 0)
    {
        if ($versionId <= 0) return $this->fail('ID de versión requerido', 400);
        try {
            $detalle = $this->model->detalleVersion($versionId);
            if (!$detalle) return $this->failNotFound("Versión #{$versionId} no encontrada");
            return $this->respond($detalle);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    /**
     * POST /api/formulaciones/versiones/:versionId/restaurar
     * Body opcional: { notas? }
     * Restaura una versión histórica como la receta activa (crea nueva versión
     * con ese snapshot — el historial queda lineal y trazable).
     */
    public function restaurarVersion($versionId = 0)
    {
        $versionId = (int) $versionId;
        if ($versionId <= 0) {
            return $this->failValidationErrors('ID de versión requerido.');
        }
        try {
            $data  = $this->request->getJSON(true) ?? [];
            $notas = isset($data['notas']) ? trim((string) $data['notas']) : null;

            $result = $this->model->restaurarVersion($versionId, $this->getUsername(), $notas ?: null);
            return $this->respond($result);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }
}