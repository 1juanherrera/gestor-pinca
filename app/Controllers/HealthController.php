<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

/**
 * GET /api/health — endpoint público para load balancers / monitoring.
 *
 * Devuelve `{ok, status, db, timestamp, version}`. El `db` indica si la
 * conexión a MySQL responde a `SELECT 1`. NO requiere JWT (excluido en
 * `app/Config/Filters.php`).
 */
class HealthController extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $db = false;
        try {
            $result = \Config\Database::connect()->query('SELECT 1');
            $db = $result !== false;
        } catch (\Throwable $e) {
            log_message('warning', '[HEALTH] DB check failed: ' . $e->getMessage());
            $db = false;
        }

        $status     = $db ? 'ok' : 'degraded';
        $httpStatus = $db ? 200 : 503;

        return $this->response
            ->setStatusCode($httpStatus)
            ->setJSON([
                'ok'        => $db,
                'status'    => $status,
                'db'        => $db,
                'timestamp' => time(),
                'version'   => '1.0',
            ]);
    }
}
