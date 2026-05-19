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

        // Regenerar notificaciones automáticas on-demand (lazy cron)
        // Solo lo hace una vez por hora gracias al dedup_key con timestamp.
        try {
            $this->generarAutomaticas();
        } catch (\Throwable $e) {
            log_message('warning', '[Notif auto] ' . $e->getMessage());
        }

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
     * Genera notificaciones automáticas escaneando el estado actual del sistema.
     * - Stock crítico de MP (días_restantes < umbral configurable)
     * - OCs Enviadas hace > 14 días sin recibir
     * - Facturas en mora > umbral configurable
     *
     * Cada tipo usa dedup_key con fecha-hora-bucket (HH dividido por 6) →
     * máximo 4 inserts por día por entidad → no spam.
     */
    private function generarAutomaticas(): void
    {
        $db = \Config\Database::connect();
        $hoyHora = date('Y-m-d-H'); // bucket por hora

        // Solo regeneramos cada hora (dedup_key cambia 1 vez por hora)
        // El modelo dedup en 24h, así que esto solo trabaja si pasó >24h
        // o si el bucket cambió y no hay dedup match.

        // ── 1. Stock crítico ────────────────────────────────────────────
        $criticoDias = (int) \App\Helpers\Cfg::n('stock_critico_dias', 7);
        $criticas = $db->query("
            SELECT ig.id_item_general, ig.nombre,
                   COALESCE(SUM(ic.cantidad_disponible), 0) AS stock,
                   (SELECT SUM(pid.cantidad)
                      FROM produccion_insumos_detalle pid
                      JOIN preparaciones p ON p.id_preparaciones = pid.preparacion_id
                      WHERE pid.item_general_id = ig.id_item_general
                        AND p.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                        AND p.estado != 3) AS consumo_30d
            FROM item_general ig
            LEFT JOIN inventario_capas ic
                   ON ic.item_general_id = ig.id_item_general AND ic.estado = 1
            WHERE ig.deleted_at IS NULL AND ig.tipo = 1
            GROUP BY ig.id_item_general, ig.nombre
            HAVING consumo_30d > 0
        ")->getResultArray();

        foreach ($criticas as $mp) {
            $stock = (float) $mp['stock'];
            $cons30 = (float) $mp['consumo_30d'];
            $diario = $cons30 > 0 ? $cons30 / 30 : 0;
            $diasRest = $diario > 0 ? (int) round($stock / $diario) : null;
            if ($diasRest !== null && $diasRest < $criticoDias) {
                $this->model->crear([
                    'tipo'       => \App\Models\NotificacionModel::TIPO_MP_CRITICA,
                    'titulo'     => "Stock crítico: {$mp['nombre']}",
                    'mensaje'    => "Quedan ~{$diasRest} días al ritmo actual ({$cons30} kg consumidos en 30d).",
                    'rol_target' => 'admin',
                    'link'       => '/inventario-global',
                    'metadata'   => ['item_general_id' => (int) $mp['id_item_general'], 'dias_restantes' => $diasRest],
                    'dedup_key'  => 'mp-critica-' . $mp['id_item_general'] . '-' . date('Y-m-d'),
                ]);
            }
        }

        // ── 2. OCs Enviadas sin recibir hace >14 días ─────────────────
        $ocs = $db->query("
            SELECT oc.id_orden, oc.numero, oc.fecha,
                   p.nombre_empresa AS proveedor_nombre,
                   DATEDIFF(NOW(), oc.fecha) AS dias
            FROM ordenes_compra oc
            LEFT JOIN proveedor p ON p.id_proveedor = oc.proveedor_id
            WHERE oc.estado = 'Enviada'
              AND oc.deleted_at IS NULL
              AND oc.fecha < DATE_SUB(NOW(), INTERVAL 14 DAY)
            ORDER BY oc.fecha ASC
            LIMIT 20
        ")->getResultArray();

        foreach ($ocs as $oc) {
            $this->model->crear([
                'tipo'       => \App\Models\NotificacionModel::TIPO_OC_RETRASADA,
                'titulo'     => "OC {$oc['numero']} sin recibir",
                'mensaje'    => "Enviada hace {$oc['dias']} días" .
                                ($oc['proveedor_nombre'] ? " a {$oc['proveedor_nombre']}" : '') . '.',
                'rol_target' => 'admin',
                'link'       => '/compras',
                'metadata'   => ['id_orden' => (int) $oc['id_orden'], 'dias' => (int) $oc['dias']],
                'dedup_key'  => 'oc-retrasada-' . $oc['id_orden'] . '-' . date('Y-m-d'),
            ]);
        }

        // ── 3. Facturas en mora ────────────────────────────────────────
        $moraCritica = (int) \App\Helpers\Cfg::n('mora_critica_dias', 60);
        $facts = $db->query("
            SELECT f.id_facturas, f.numero, f.saldo_pendiente,
                   c.nombre_empresa AS cliente_nombre,
                   DATEDIFF(NOW(), f.fecha_vencimiento) AS dias_mora
            FROM facturas f
            LEFT JOIN clientes c ON c.id_clientes = f.cliente_id
            WHERE f.estado IN ('Pendiente', 'Parcial', 'Vencida')
              AND f.deleted_at IS NULL
              AND f.saldo_pendiente > 0
              AND f.fecha_vencimiento < DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY f.fecha_vencimiento ASC
            LIMIT 20
        ", [$moraCritica])->getResultArray();

        foreach ($facts as $f) {
            $monto = number_format((float) $f['saldo_pendiente'], 0, ',', '.');
            $this->model->crear([
                'tipo'       => \App\Models\NotificacionModel::TIPO_FACTURA_VENCIMIENTO,
                'titulo'     => "Factura {$f['numero']} en mora",
                'mensaje'    => ($f['cliente_nombre'] ? "{$f['cliente_nombre']} · " : '') .
                                "{$f['dias_mora']} días vencida · saldo \${$monto}",
                'rol_target' => 'admin',
                'link'       => '/cartera',
                'metadata'   => [
                    'id_facturas' => (int) $f['id_facturas'],
                    'dias_mora'   => (int) $f['dias_mora'],
                    'saldo'       => (float) $f['saldo_pendiente'],
                ],
                'dedup_key'  => 'factura-mora-' . $f['id_facturas'] . '-' . date('Y-m-d'),
            ]);
        }
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
