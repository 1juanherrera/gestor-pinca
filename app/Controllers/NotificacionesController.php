<?php

namespace App\Controllers;

use App\Models\NotificacionModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class NotificacionesController extends ResourceController
{
    use \App\Traits\JwtUserAware;

    protected $modelName = NotificacionModel::class;

    /**
     * GET /api/notificaciones?solo_no_leidas=1&limit=30&offset=0
     */
    public function index(): ResponseInterface
    {
        $userId = $this->getUserId();
        $rol    = $this->getUserRol();
        if (!$userId) return $this->fail('No autenticado.', 401);

        $opts = [
            'solo_no_leidas' => $this->request->getGet('solo_no_leidas'),
            'limit'          => $this->request->getGet('limit'),
            'offset'         => $this->request->getGet('offset'),
        ];

        $items   = $this->model->listarPara($userId, $rol, $opts);
        $noLeida = $this->model->contarNoLeidas($userId, $rol);

        return $this->respond([
            'data'      => $items,
            'no_leidas' => $noLeida,
        ]);
    }

    /**
     * GET /api/notificaciones/no-leidas
     * Solo el contador, para el badge del Bell.
     */
    public function noLeidas(): ResponseInterface
    {
        $userId = $this->getUserId();
        $rol    = $this->getUserRol();
        if (!$userId) return $this->fail('No autenticado.', 401);

        return $this->respond([
            'no_leidas' => $this->model->contarNoLeidas($userId, $rol),
        ]);
    }

    /**
     * PATCH /api/notificaciones/:id/leer
     */
    public function marcarLeida($id = null): ResponseInterface
    {
        $userId = $this->getUserId();
        $rol    = $this->getUserRol();
        if (!$userId) return $this->fail('No autenticado.', 401);
        if (!$id)     return $this->fail('ID requerido.', 400);

        $ok = $this->model->marcarLeida((int) $id, $userId, $rol);
        if (!$ok) return $this->failNotFound("Notificación #{$id} no encontrada.");

        return $this->respond(['ok' => true]);
    }

    /**
     * POST /api/notificaciones/leer-todas
     */
    public function marcarTodasLeidas(): ResponseInterface
    {
        $userId = $this->getUserId();
        $rol    = $this->getUserRol();
        if (!$userId) return $this->fail('No autenticado.', 401);

        $count = $this->model->marcarTodasLeidas($userId, $rol);
        return $this->respond(['marcadas' => $count]);
    }
}
