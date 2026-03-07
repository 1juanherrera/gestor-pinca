<?php

namespace App\Controllers;

use App\Models\PreparacionesModel;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class PreparacionesController extends BaseController
{
    private PreparacionesModel $model;

    public function __construct()
    {
        $this->model = new PreparacionesModel();
    }

    /**
     * GET /preparaciones
     * Lista todas las preparaciones (paginado opcional con ?page=1&limit=20)
     */
    public function index(): ResponseInterface
    {
        try {
            $page  = (int) ($this->request->getGet('page')  ?? 1);
            $limit = (int) ($this->request->getGet('limit') ?? 20);
            $result = $this->model->get_all_preparaciones($page, $limit);
            return $this->response->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * POST /preparaciones
     * Crea una nueva orden de preparación.
     */
    public function create(): ResponseInterface
    {
        $body = $this->request->getJSON(true) ?? $this->request->getPost();

        try {
            $result = $this->model->create_preparacion($body);
            return $this->response
                ->setStatusCode(201)
                ->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /preparaciones/(:num)
     * Detalle de una preparación con su desglose de materias primas.
     */
    public function show(int $id): ResponseInterface
    {
        try {
            $result = $this->model->get_preparacion_by_id($id);
            return $this->response->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * GET /preparaciones/item/(:num)
     * Lista todas las preparaciones de un item específico.
     */
    public function byItem(int $itemId): ResponseInterface
    {
        try {
            $result = $this->model->get_preparaciones_by_item($itemId);
            return $this->response->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * PUT /preparaciones/(:num)
     * Actualiza estado u observaciones de una preparación.
     */
    public function update(int $id): ResponseInterface
    {
        $body = $this->request->getJSON(true) ?? $this->request->getRawInput();

        try {
            $result = $this->model->update_preparacion($id, $body);
            return $this->response->setJSON(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}