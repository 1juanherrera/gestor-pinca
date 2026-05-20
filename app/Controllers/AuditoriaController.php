<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Traits\JwtUserAware;
use App\Helpers\Cfg;

/**
 * AuditoriaController — solo lectura sobre tablas existentes.
 *
 * Acceso restringido a `rol=admin` (validado en cada método).
 * Pagina + filtra para que la UI pueda hacer scroll/búsqueda eficientes.
 */
class AuditoriaController extends ResourceController
{
    use JwtUserAware;

    protected $format = 'json';

    /**
     * GET /api/auditoria/login-attempts?ip=&usuario=&desde=&hasta=&page=1&per_page=50
     */
    public function loginAttempts()
    {
        if (!$this->userHasAdminAccess()) {
            return $this->failForbidden('Solo administradores pueden ver el log de auditoría.');
        }

        $db    = \Config\Database::connect();
        $req   = $this->request;
        $page  = max(1, (int) $req->getGet('page'));
        $maxPer = Cfg::n('max_per_page', 200);
        $defPer = Cfg::n('page_size_default', 50);
        $per    = min($maxPer, max(10, (int) ($req->getGet('per_page') ?: $defPer)));
        $offset = ($page - 1) * $per;

        $b = $db->table('login_attempts')->orderBy('created_at', 'DESC');

        if ($ip = trim((string) $req->getGet('ip')))           $b->like('ip_address', $ip);
        if ($usr = trim((string) $req->getGet('usuario')))     $b->like('username_attempt', $usr);
        if ($desde = trim((string) $req->getGet('desde')))     $b->where('created_at >=', $desde . ' 00:00:00');
        if ($hasta = trim((string) $req->getGet('hasta')))     $b->where('created_at <=', $hasta . ' 23:59:59');

        $total = (clone $b)->countAllResults(false);
        $rows  = $b->limit($per, $offset)->get()->getResultArray();

        return $this->respond([
            'data'  => $rows,
            'meta'  => [
                'page' => $page, 'per_page' => $per, 'total' => $total,
                'pages' => (int) ceil($total / $per),
            ],
        ]);
    }

    /**
     * GET /api/auditoria/movimientos?tipo=&item=&responsable=&desde=&hasta=&page=1&per_page=50
     */
    public function movimientos()
    {
        if (!$this->userHasAdminAccess()) {
            return $this->failForbidden('Solo administradores pueden ver el log de auditoría.');
        }

        $db    = \Config\Database::connect();
        $req   = $this->request;
        $page  = max(1, (int) $req->getGet('page'));
        $maxPer = Cfg::n('max_per_page', 200);
        $defPer = Cfg::n('page_size_default', 50);
        $per    = min($maxPer, max(10, (int) ($req->getGet('per_page') ?: $defPer)));
        $offset = ($page - 1) * $per;

        $b = $db->table('movimiento_inventario mi')
                ->select('mi.*, ig.nombre AS item_nombre, ig.codigo AS item_codigo, b.nombre AS bodega_nombre')
                ->join('item_general ig', 'ig.id_item_general = mi.item_general_id', 'left')
                ->join('bodegas b',       'b.id_bodegas       = mi.bodega_id',       'left')
                ->orderBy('mi.fecha_movimiento', 'DESC');

        if ($tipo = trim((string) $req->getGet('tipo')))               $b->where('mi.tipo_movimiento', $tipo);
        if ($ref  = trim((string) $req->getGet('referencia_tipo')))    $b->where('mi.referencia_tipo', $ref);
        if ($item = trim((string) $req->getGet('item'))) {
            $b->groupStart()
              ->like('ig.nombre', $item)
              ->orLike('ig.codigo', $item)
              ->groupEnd();
        }
        if ($resp  = trim((string) $req->getGet('responsable')))  $b->like('mi.responsable', $resp);
        if ($desde = trim((string) $req->getGet('desde')))        $b->where('mi.fecha_movimiento >=', $desde . ' 00:00:00');
        if ($hasta = trim((string) $req->getGet('hasta')))        $b->where('mi.fecha_movimiento <=', $hasta . ' 23:59:59');

        $total = (clone $b)->countAllResults(false);
        $rows  = $b->limit($per, $offset)->get()->getResultArray();

        // Decodear metadata JSON para la UI
        foreach ($rows as &$r) {
            if (!empty($r['metadata']) && is_string($r['metadata'])) {
                $decoded = json_decode($r['metadata'], true);
                $r['metadata'] = $decoded ?? null;
            }
        }

        return $this->respond([
            'data'  => $rows,
            'meta'  => [
                'page' => $page, 'per_page' => $per, 'total' => $total,
                'pages' => (int) ceil($total / $per),
            ],
        ]);
    }
}
