<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Helpers\Cfg;

/**
 * Sistema centralizado de notificaciones in-app.
 *
 * Patrón estándar para crear:
 *
 *   $model = new NotificacionModel();
 *   $model->crear([
 *     'tipo'       => NotificacionModel::TIPO_FACTURA_VENCIMIENTO,
 *     'titulo'     => 'Factura próxima a vencer',
 *     'mensaje'    => 'La factura FAC-2026-0042 vence en 3 días',
 *     'rol_target' => 'admin',  // o user_id directo
 *     'link'       => '/cartera?factura=42',
 *     'metadata'   => ['factura_id' => 42, 'monto' => 1500000],
 *   ]);
 *
 * Soporta deduplicación opcional via `dedup_key` (ver crear()).
 */
class NotificacionModel extends Model
{
    protected $table      = 'notificaciones';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id', 'rol_target', 'tipo', 'titulo', 'mensaje',
        'link', 'leida', 'leida_at', 'metadata', 'created_at',
    ];

    // Tipos canónicos (para filtrado y agrupación frontend)
    public const TIPO_FACTURA_VENCIMIENTO = 'factura_vencimiento';
    public const TIPO_OC_RETRASADA        = 'oc_retrasada';
    public const TIPO_MP_CRITICA          = 'mp_critica';
    public const TIPO_REQUISICION_NUEVA   = 'requisicion_nueva';
    public const TIPO_ITEM_HUERFANO       = 'item_huerfano';
    public const TIPO_INFO                = 'info';

    /**
     * Crea una notificación. Si `dedup_key` se provee como metadata, evita
     * duplicar notificaciones del mismo evento dentro de las últimas 24h.
     *
     * @param array $data {
     *   tipo (REQUIRED), titulo (REQUIRED), mensaje, link,
     *   user_id, rol_target,                         // al menos uno; si ambos null = global admin
     *   metadata: array,                             // opcional, libre
     *   dedup_key: string                            // opcional, evita duplicados en 24h
     * }
     * @return int|false  ID o false
     */
    public function crear(array $data)
    {
        if (empty($data['tipo']) || empty($data['titulo'])) {
            log_message('warning', '[Notificacion] crear() sin tipo o titulo');
            return false;
        }

        // Deduplicación opcional
        $dedupKey = $data['dedup_key'] ?? null;
        if ($dedupKey) {
            $existe = $this->db->table($this->table)
                ->where('tipo', $data['tipo'])
                ->where("JSON_EXTRACT(metadata, '$.dedup_key')", $dedupKey)
                ->where("created_at >=", date('Y-m-d H:i:s', strtotime('-24 hours')))
                ->countAllResults();
            if ($existe > 0) return false;
        }

        $metadata = $data['metadata'] ?? [];
        if ($dedupKey) $metadata['dedup_key'] = $dedupKey;

        $row = [
            'user_id'    => $data['user_id']    ?? null,
            'rol_target' => $data['rol_target'] ?? null,
            'tipo'       => $data['tipo'],
            'titulo'     => mb_substr($data['titulo'], 0, 150),
            'mensaje'    => $data['mensaje'] ?? null,
            'link'       => $data['link']    ?? null,
            'leida'      => 0,
            'metadata'   => !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $this->insert($row);
        return $ok ? (int) $this->getInsertID() : false;
    }

    /**
     * Lista notificaciones para un usuario (incluye las dirigidas a su rol).
     */
    public function listarPara(int $userId, string $rol, array $opts = []): array
    {
        $soloNoLeidas = !empty($opts['solo_no_leidas']);
        $defLim       = Cfg::n('limit_default', 30);
        $maxLim       = Cfg::n('limit_maximo',  100);
        $limit        = min((int) ($opts['limit'] ?? $defLim), $maxLim);
        $offset       = max(0, (int) ($opts['offset'] ?? 0));

        $builder = $this->builder()
            ->groupStart()
                ->where('user_id', $userId)
                ->orWhere('rol_target', $rol)
                ->orWhere('rol_target', null, false) // null = global, todos lo ven
            ->groupEnd()
            ->orderBy('id', 'DESC')
            ->limit($limit, $offset);

        if ($soloNoLeidas) {
            $builder->where('leida', 0);
        }

        $rows = $builder->get()->getResultArray();

        return array_map(function ($r) {
            $r['id']      = (int) $r['id'];
            $r['leida']   = (int) $r['leida'];
            $r['user_id'] = $r['user_id'] !== null ? (int) $r['user_id'] : null;
            if (!empty($r['metadata'])) {
                $decoded = json_decode($r['metadata'], true);
                $r['metadata'] = is_array($decoded) ? $decoded : null;
            } else {
                $r['metadata'] = null;
            }
            return $r;
        }, $rows);
    }

    public function contarNoLeidas(int $userId, string $rol): int
    {
        return $this->builder()
            ->groupStart()
                ->where('user_id', $userId)
                ->orWhere('rol_target', $rol)
                ->orWhere('rol_target', null, false)
            ->groupEnd()
            ->where('leida', 0)
            ->countAllResults();
    }

    public function marcarLeida(int $id, int $userId, string $rol): bool
    {
        // Solo permite marcar leídas las que le pertenecen
        $affected = $this->builder()
            ->where('id', $id)
            ->groupStart()
                ->where('user_id', $userId)
                ->orWhere('rol_target', $rol)
                ->orWhere('rol_target', null, false)
            ->groupEnd()
            ->update(['leida' => 1, 'leida_at' => date('Y-m-d H:i:s')]);
        return $affected > 0;
    }

    public function marcarTodasLeidas(int $userId, string $rol): int
    {
        $this->builder()
            ->where('leida', 0)
            ->groupStart()
                ->where('user_id', $userId)
                ->orWhere('rol_target', $rol)
                ->orWhere('rol_target', null, false)
            ->groupEnd()
            ->update(['leida' => 1, 'leida_at' => date('Y-m-d H:i:s')]);

        return $this->db->affectedRows();
    }
}
