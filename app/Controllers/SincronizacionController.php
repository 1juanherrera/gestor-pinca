<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SincronizacionModel;

class SincronizacionController extends ResourceController
{
    use \App\Traits\ApiResponse;

    use \App\Traits\JwtUserAware;

    protected $modelName = SincronizacionModel::class;

    public function stats()
    {
        try {
            return $this->respond($this->model->stats());
        } catch (\Throwable $e) {
            log_message('error', '[SincronizacionController::stats] ' . $e->getMessage());
            return $this->apiFail('Error al obtener estadísticas de sincronización.', 500);
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
            return $this->apiFail('Error al obtener el maestro de sincronización.', 500);
        }
    }

    public function pendientes()
    {
        try {
            return $this->respond($this->model->pendientes());
        } catch (\Throwable $e) {
            log_message('error', '[SincronizacionController::pendientes] ' . $e->getMessage());
            return $this->apiFail('Error al obtener pendientes de sincronización.', 500);
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
            return $this->apiFail('Error al detectar duplicados.', 500);
        }
    }

    public function huerfanos()
    {
        try {
            return $this->respond($this->model->huerfanos());
        } catch (\Throwable $e) {
            log_message('error', '[SincronizacionController::huerfanos] ' . $e->getMessage());
            return $this->apiFail('Error al obtener huérfanos.', 500);
        }
    }

    public function merge()
    {
        // Fusionar items reapunta FKs y soft-deletea catálogo → solo admin/superadmin.
        if (!$this->userHasAdminAccess()) {
            return $this->apiForbidden('Solo un administrador puede unificar materias primas.');
        }

        $data = $this->request->getJSON(true) ?? [];

        $keepId   = isset($data['keep_id'])   ? (int) $data['keep_id']   : 0;
        $removeId = isset($data['remove_id']) ? (int) $data['remove_id'] : 0;

        if ($keepId <= 0 || $removeId <= 0) {
            return $this->apiFail('keep_id y remove_id son requeridos.', 400);
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
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // ── Deduplicación asistida por IA ───────────────────────────────────────

    /** GET /sincronizacion/ia/clusters?estado=&confianza=&tipo= */
    public function iaClusters()
    {
        $estado    = $this->request->getGet('estado');
        $confianza = $this->request->getGet('confianza');
        $tipo      = $this->request->getGet('tipo');
        try {
            return $this->respond($this->model->listarClusters(
                $estado ?: null,
                $confianza ?: null,
                ($tipo !== null && $tipo !== '') ? (int) $tipo : null,
            ));
        } catch (\Throwable $e) {
            log_message('error', '[Sinc::iaClusters] ' . $e->getMessage());
            return $this->apiFail('Error al listar clusters.', 500);
        }
    }

    /** GET /sincronizacion/ia/clusters/:id */
    public function iaCluster($id = null)
    {
        $cluster = $this->model->detalleCluster((int) $id);
        if (!$cluster) return $this->apiNotFound('Cluster no encontrado.');
        return $this->respond($cluster);
    }

    /** PATCH /sincronizacion/ia/clusters/:id */
    public function iaActualizarCluster($id = null)
    {
        if (!$this->userHasAdminAccess()) return $this->apiForbidden('Solo un administrador puede editar sugerencias.');
        $data = $this->request->getJSON(true) ?? [];
        try {
            $this->model->actualizarCluster((int) $id, $data);
            return $this->respond(['message' => 'Cluster actualizado.', 'cluster' => $this->model->detalleCluster((int) $id)]);
        } catch (\Throwable $e) {
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    /** PATCH /sincronizacion/ia/cluster-items/:id  body {rol} */
    public function iaMoverItem($id = null)
    {
        if (!$this->userHasAdminAccess()) return $this->apiForbidden('Solo un administrador puede editar sugerencias.');
        $data = $this->request->getJSON(true) ?? [];
        $rol  = $data['rol'] ?? '';
        try {
            $this->model->moverItem((int) $id, $rol);
            return $this->respond(['message' => 'Miembro actualizado.']);
        } catch (\Throwable $e) {
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    /** POST /sincronizacion/ia/clusters/:id/fusionar */
    public function iaFusionarGrupo($id = null)
    {
        if (!$this->userHasAdminAccess()) return $this->apiForbidden('Solo un administrador puede fusionar grupos.');
        try {
            $result = $this->model->fusionarCluster((int) $id, $this->getUsername());
            log_message('info', sprintf(
                '[MERGE_CLUSTER] Usuario "%s" fusionó cluster #%d (%d ítems → keep #%d)',
                $this->getUsername(), (int) $id, $result['fusionados'], $result['keep_id']
            ));
            return $this->respond(['message' => 'Grupo fusionado correctamente.', 'detalle' => $result]);
        } catch (\Throwable $e) {
            log_message('error', '[Sinc::iaFusionarGrupo] ' . $e->getMessage());
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    /** POST /sincronizacion/ia/clusters/:id/descartar */
    public function iaDescartarCluster($id = null)
    {
        if (!$this->userHasAdminAccess()) return $this->apiForbidden('Solo un administrador puede descartar grupos.');
        $this->model->descartarCluster((int) $id);
        return $this->respond(['message' => 'Grupo descartado.']);
    }

    /** GET /sincronizacion/ia/verificar/:keepId */
    public function iaVerificar($keepId = null)
    {
        return $this->respond($this->model->verificarPostMerge((int) $keepId));
    }

    /** POST /sincronizacion/ia/auditoria/:id/revertir */
    public function iaRevertir($id = null)
    {
        if (!$this->userHasAdminAccess()) return $this->apiForbidden('Solo un administrador puede revertir fusiones.');
        try {
            $result = $this->model->revertirMerge((int) $id, $this->getUsername());
            log_message('info', sprintf('[MERGE_UNDO] Usuario "%s" revirtió auditoría #%d', $this->getUsername(), (int) $id));
            return $this->respond(['message' => 'Fusión revertida (parcial).', 'detalle' => $result]);
        } catch (\Throwable $e) {
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    /**
     * POST /sincronizacion/ia/clasificar  body {tipo?}
     * Dispara la clasificación química con IA y guarda los clusters propuestos.
     * Si no hay ANTHROPIC_API_KEY, responde 400 indicando usar el modo offline (command).
     */
    public function iaClasificar()
    {
        if (!$this->userHasAdminAccess()) return $this->apiForbidden('Solo un administrador puede ejecutar la clasificación con IA.');
        $data = $this->request->getJSON(true) ?? [];
        $tipo = isset($data['tipo']) && $data['tipo'] !== '' ? (int) $data['tipo'] : null;

        try {
            $dataset = $this->model->datasetParaClasificacion($tipo);
            if (empty($dataset)) {
                return $this->apiFail('No hay materias primas/insumos para clasificar.', 400);
            }

            $service  = new \App\Services\ClasificadorQuimicoService();
            $lote     = 'IA-' . date('YmdHis');
            $clusters = $service->clasificar($dataset);
            $res      = $this->model->guardarSugerencias($clusters, $lote, $service->modelo());

            log_message('info', sprintf('[IA_CLASIFICAR] Usuario "%s" clasificó %d ítems → %d clusters (lote %s)',
                $this->getUsername(), count($dataset), $res['clusters_creados'], $lote));

            return $this->respond(['message' => 'Clasificación completada.', 'detalle' => $res]);
        } catch (\Throwable $e) {
            log_message('error', '[Sinc::iaClasificar] ' . $e->getMessage());
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    /**
     * GET /api/sincronizacion/uso-formulas/:itemId
     * Preview: fórmulas que usan la materia (para el reemplazo manual).
     */
    public function usoEnFormulas($itemId = null)
    {
        $id = (int) $itemId;
        if ($id <= 0) return $this->apiFail('itemId inválido.', 400);
        // ?to=<id> opcional: marca qué fórmulas YA tienen el reemplazo (se consolidarán).
        $to = (int) $this->request->getGet('to');
        $db = \Config\Database::connect();
        $stock = (float) ($db->query(
            'SELECT COALESCE(SUM(cantidad_disponible),0) s FROM inventario_capas WHERE item_general_id = ? AND estado = 1 AND cantidad_disponible > 0',
            [$id]
        )->getRow()->s ?? 0);
        return $this->respond([
            'item_id'      => $id,
            'origen_stock' => $stock,
            'formulas'     => $this->model->formulasQueUsan($id, $to > 0 ? $to : null),
        ]);
    }

    /**
     * POST /api/sincronizacion/reemplazar-formula
     * Body: { from_item_id, to_item_id, formulacion_ids?: number[] }
     * Reemplaza la materia A por B en el BOM (buscar y reemplazar). Solo admin.
     */
    public function reemplazarFormula()
    {
        // Modifica el BOM de fórmulas y puede soft-deletear el catálogo → solo admin/superadmin.
        if (!$this->userHasAdminAccess()) {
            return $this->apiForbidden('Solo un administrador puede reemplazar materias en fórmulas.');
        }

        $data   = $this->request->getJSON(true) ?? [];
        $fromId = isset($data['from_item_id']) ? (int) $data['from_item_id'] : 0;
        $toId   = isset($data['to_item_id'])   ? (int) $data['to_item_id']   : 0;
        $formIds = (isset($data['formulacion_ids']) && is_array($data['formulacion_ids']))
            ? $data['formulacion_ids'] : null;

        if ($fromId <= 0 || $toId <= 0) {
            return $this->apiFail('from_item_id y to_item_id son requeridos.', 400);
        }

        try {
            $result = $this->model->reemplazarEnFormulas($fromId, $toId, $formIds, $this->getUsername());
            log_message('info', sprintf(
                '[REEMPLAZO_FORMULA] Usuario "%s" (rol: %s) reemplazó MP #%d → #%d en %d fórmula(s)%s',
                $this->getUsername(), $this->getUserRol(), $fromId, $toId,
                $result['formulas_afectadas'], $result['origen_eliminada'] ? ' (origen eliminada)' : ''
            ));
            return $this->respond($result);
        } catch (\InvalidArgumentException $e) {
            return $this->apiFail($e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', '[Sinc::reemplazarFormula] ' . $e->getMessage());
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    /** GET /api/sincronizacion/reemplazos — historial de reemplazos (para deshacer). */
    public function historialReemplazos()
    {
        return $this->respond(['reemplazos' => $this->model->historialReemplazos()]);
    }

    /** POST /api/sincronizacion/reemplazos/:id/revertir — deshace un reemplazo. Solo admin. */
    public function revertirReemplazo($id = null)
    {
        if (!$this->userHasAdminAccess()) {
            return $this->apiForbidden('Solo un administrador puede deshacer un reemplazo.');
        }
        $logId = (int) $id;
        if ($logId <= 0) return $this->apiFail('id inválido.', 400);
        try {
            $result = $this->model->revertirReemplazo($logId, $this->getUsername());
            log_message('info', sprintf('[REEMPLAZO_REVERT] Usuario "%s" deshizo el reemplazo #%d', $this->getUsername(), $logId));
            return $this->respond($result);
        } catch (\InvalidArgumentException $e) {
            return $this->apiFail($e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', '[Sinc::revertirReemplazo] ' . $e->getMessage());
            return $this->apiFail($e->getMessage(), 400);
        }
    }
}
