<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SincronizacionModel;

class SincronizacionController extends ResourceController
{
    use \App\Traits\JwtUserAware;

    protected $modelName = SincronizacionModel::class;

    public function stats()
    {
        try {
            return $this->respond($this->model->stats());
        } catch (\Throwable $e) {
            log_message('error', '[SincronizacionController::stats] ' . $e->getMessage());
            return $this->failServerError('Error al obtener estadísticas de sincronización.');
        }
    }

    public function maestro()
    {
        $search    = $this->request->getGet('search');
        $cobertura = $this->request->getGet('cobertura');
        $tipo      = $this->request->getGet('tipo');

        try {
            $data = $this->model->maestro(
                $search ?: null,
                $cobertura ?: null,
                $tipo !== null && $tipo !== '' ? (int) $tipo : null
            );
            return $this->respond($data);
        } catch (\Throwable $e) {
            log_message('error', '[SincronizacionController::maestro] ' . $e->getMessage());
            return $this->failServerError('Error al obtener el maestro de sincronización.');
        }
    }

    public function pendientes()
    {
        try {
            return $this->respond($this->model->pendientes());
        } catch (\Throwable $e) {
            log_message('error', '[SincronizacionController::pendientes] ' . $e->getMessage());
            return $this->failServerError('Error al obtener pendientes de sincronización.');
        }
    }

    public function duplicados()
    {
        $threshold = (int) ($this->request->getGet('threshold') ?? 70);
        $threshold = max(50, min($threshold, 100));

        try {
            return $this->respond($this->model->duplicados($threshold));
        } catch (\Throwable $e) {
            log_message('error', '[SincronizacionController::duplicados] ' . $e->getMessage());
            return $this->failServerError('Error al detectar duplicados.');
        }
    }

    public function huerfanos()
    {
        try {
            return $this->respond($this->model->huerfanos());
        } catch (\Throwable $e) {
            log_message('error', '[SincronizacionController::huerfanos] ' . $e->getMessage());
            return $this->failServerError('Error al obtener huérfanos.');
        }
    }

    public function merge()
    {
        // Solo admin u operador pueden mergear items (afecta integridad histórica)
        if (!$this->userHasRole(['admin', 'operador'])) {
            return $this->failForbidden('Solo administradores u operadores pueden unificar items.');
        }

        $data = $this->request->getJSON(true) ?? [];

        $keepId   = isset($data['keep_id'])   ? (int) $data['keep_id']   : 0;
        $removeId = isset($data['remove_id']) ? (int) $data['remove_id'] : 0;

        if ($keepId <= 0 || $removeId <= 0) {
            return $this->fail('keep_id y remove_id son requeridos.', 400);
        }

        try {
            $result = $this->model->merge($keepId, $removeId);
            log_message('info', sprintf(
                '[MERGE_ITEMS] Usuario "%s" (rol: %s) unificó item #%d ← #%d',
                $this->getUsername(), $this->getUserRol(), $keepId, $removeId
            ));

            // Notif a admin si el merge dejó al keep sin proveedores activos
            $db = \Config\Database::connect();
            $totalProvKeep = $db->table('item_proveedor')
                ->where('item_general_id', $keepId)
                ->where('disponible', 1)
                ->countAllResults();

            if ($totalProvKeep === 0) {
                $notif = new \App\Models\NotificacionModel();
                $notif->crear([
                    'tipo'       => \App\Models\NotificacionModel::TIPO_ITEM_HUERFANO,
                    'titulo'     => 'Item huérfano tras unificación',
                    'mensaje'    => "El item #{$keepId} «{$result['nombre_keep']}» quedó sin proveedores activos.",
                    'rol_target' => 'admin',
                    'link'       => '/sincronizacion',
                    'metadata'   => [
                        'keep_id'   => $keepId,
                        'remove_id' => $removeId,
                        'origen'    => 'merge',
                    ],
                    'dedup_key'  => "huerfano-merge-{$keepId}",
                ]);
            }

            return $this->respond([
                'message' => 'Merge realizado correctamente.',
                'detalle' => $result,
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[SincronizacionController::merge] ' . $e->getMessage());
            return $this->fail($e->getMessage(), 400);
        }
    }
}
