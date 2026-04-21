<?php

namespace App\Controllers;

use App\Models\RequisicionesCompraModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class RequisicionesCompraController extends BaseController
{
    private RequisicionesCompraModel $model;

    public function __construct()
    {
        $this->model = new RequisicionesCompraModel();
    }

    /**
     * GET /api/preparaciones/verificar-disponibilidad
     * Query params: item_general_id, cantidad, unidad_id
     */
    public function verificarDisponibilidad(): ResponseInterface
    {
        $itemId   = (int)   $this->request->getGet('item_general_id');
        $cantidad = (float) $this->request->getGet('cantidad');
        $unidadId = (int)   $this->request->getGet('unidad_id');

        if (!$itemId || $cantidad <= 0 || !$unidadId) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Parámetros requeridos: item_general_id, cantidad (> 0), unidad_id.',
            ]);
        }

        try {
            $result = $this->model->verificarDisponibilidad($itemId, $cantidad, $unidadId);
            return $this->response->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * GET /api/requisiciones
     * Query param opcional: estado=PENDIENTE|APROBADA|CONVERTIDA|CANCELADA
     */
    public function index(): ResponseInterface
    {
        $estado = $this->request->getGet('estado');
        try {
            $data = $this->model->listar($estado ?: null);
            return $this->response->setJSON(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * GET /api/requisiciones/preparacion/:id
     */
    public function porPreparacion(int $prepId): ResponseInterface
    {
        try {
            $data = $this->model->listarPorPreparacion($prepId);
            return $this->response->setJSON(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /api/requisiciones
     * Body: array de requisiciones, cada una con:
     *   preparacion_id, item_general_id, item_proveedor_id, proveedor_id,
     *   cantidad_necesaria, cantidad_disponible, cantidad_solicitada,
     *   precio_unitario?, observaciones?
     */
    public function create(): ResponseInterface
    {
        $body = $this->request->getJSON(true) ?? [];

        // Acepta tanto un array directo como un objeto con clave 'items'
        $items = isset($body['items']) ? $body['items'] : $body;

        if (!is_array($items) || empty($items)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'El cuerpo debe ser un array de requisiciones.',
            ]);
        }

        try {
            $created = $this->model->crearRequisiciones($items);
            return $this->response->setStatusCode(201)->setJSON([
                'success' => true,
                'data'    => $created,
            ]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * PATCH /api/requisiciones/:id/estado
     * Body: { "estado": "APROBADA" | "PENDIENTE" | "CANCELADA" }
     */
    public function actualizarEstado(int $id): ResponseInterface
    {
        $body   = $this->request->getJSON(true) ?? [];
        $estado = trim($body['estado'] ?? '');

        if (!$estado) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'El campo estado es requerido.',
            ]);
        }

        try {
            $updated = $this->model->actualizarEstado($id, strtoupper($estado));
            return $this->response->setJSON(['success' => true, 'data' => $updated]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /api/requisiciones/convertir-oc
     * Body: { "ids": [1,2,3], "bodegas_id": 1, "observaciones": "..." }
     * Convierte las requisiciones APROBADAS en OC(s), agrupadas por proveedor.
     */
    public function convertirAOC(): ResponseInterface
    {
        $body     = $this->request->getJSON(true) ?? [];
        $ids      = $body['ids']        ?? [];
        $bodegaId = (int) ($body['bodegas_id'] ?? 0);
        $obs      = $body['observaciones'] ?? null;

        if (empty($ids) || !$bodegaId) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Se requieren ids (array) y bodegas_id.',
            ]);
        }

        try {
            $ocIds = $this->model->convertirAOC(
                array_map('intval', $ids),
                $bodegaId,
                $obs
            );
            return $this->response->setStatusCode(201)->setJSON([
                'success'          => true,
                'ordenes_compra_ids' => $ocIds,
                'message'          => count($ocIds) . ' orden(es) de compra generada(s).',
            ]);
        } catch (Exception $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
