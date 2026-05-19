<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Búsqueda global unificada para Cmd+K (palette).
 *
 *   GET /api/search?q=texto&limit=5
 *
 * Devuelve un array plano de resultados normalizados:
 *   [{ tipo, id, label, sublabel, path }]
 *
 * Tipos soportados: item, cliente, proveedor, factura, cotizacion,
 * remision, orden_compra, nota_credito.
 */
class SearchController extends ResourceController
{
    use \App\Traits\JwtUserAware;

    public function search(): ResponseInterface
    {
        $q = trim((string) $this->request->getGet('q'));
        if ($q === '' || mb_strlen($q) < 2) {
            return $this->respond([]);
        }

        $limit = (int) ($this->request->getGet('limit') ?? 5);
        $limit = max(1, min(10, $limit));

        $db   = \Config\Database::connect();
        $like = "%{$q}%";
        $out  = [];

        // ── ITEMS (catálogo) ──────────────────────────────────────────────
        $items = $db->query("
            SELECT ig.id_item_general, ig.nombre, ig.codigo, ig.tipo
            FROM item_general ig
            WHERE (ig.nombre LIKE ? OR ig.codigo LIKE ?)
              AND (ig.deleted_at IS NULL)
            ORDER BY (ig.nombre = ?) DESC, LENGTH(ig.nombre) ASC
            LIMIT {$limit}
        ", [$like, $like, $q])->getResultArray();

        $tipoLabel = [1 => 'Materia prima', 2 => 'Insumo', 0 => 'Producto'];
        foreach ($items as $r) {
            $out[] = [
                'tipo'     => 'item',
                'id'       => (int) $r['id_item_general'],
                'label'    => $r['nombre'],
                'sublabel' => trim(($r['codigo'] ?? '') . ' · ' . ($tipoLabel[(int) $r['tipo']] ?? '—'), ' ·'),
                'path'     => '/catalogo?q=' . rawurlencode($r['nombre']),
            ];
        }

        // ── CLIENTES ──────────────────────────────────────────────────────
        $clientes = $db->query("
            SELECT id_clientes, nombre_empresa, nombre_encargado, numero_documento, ciudad
            FROM clientes
            WHERE (nombre_empresa LIKE ? OR nombre_encargado LIKE ? OR numero_documento LIKE ?)
              AND deleted_at IS NULL
            ORDER BY (nombre_empresa = ?) DESC, LENGTH(nombre_empresa) ASC
            LIMIT {$limit}
        ", [$like, $like, $like, $q])->getResultArray();

        foreach ($clientes as $r) {
            $sub = [];
            if (!empty($r['nombre_encargado'])) $sub[] = $r['nombre_encargado'];
            if (!empty($r['numero_documento'])) $sub[] = 'NIT ' . $r['numero_documento'];
            if (!empty($r['ciudad']))           $sub[] = $r['ciudad'];
            $out[] = [
                'tipo'     => 'cliente',
                'id'       => (int) $r['id_clientes'],
                'label'    => $r['nombre_empresa'] ?? '—',
                'sublabel' => implode(' · ', $sub),
                'path'     => '/clientes?q=' . rawurlencode($r['nombre_empresa'] ?? ''),
            ];
        }

        // ── PROVEEDORES ───────────────────────────────────────────────────
        $proveedores = $db->query("
            SELECT id_proveedor, nombre_empresa, nombre_encargado, numero_documento
            FROM proveedor
            WHERE (nombre_empresa LIKE ? OR nombre_encargado LIKE ? OR numero_documento LIKE ?)
              AND deleted_at IS NULL
            ORDER BY (nombre_empresa = ?) DESC, LENGTH(nombre_empresa) ASC
            LIMIT {$limit}
        ", [$like, $like, $like, $q])->getResultArray();

        foreach ($proveedores as $r) {
            $sub = [];
            if (!empty($r['nombre_encargado'])) $sub[] = $r['nombre_encargado'];
            if (!empty($r['numero_documento'])) $sub[] = 'NIT ' . $r['numero_documento'];
            $out[] = [
                'tipo'     => 'proveedor',
                'id'       => (int) $r['id_proveedor'],
                'label'    => $r['nombre_empresa'] ?? '—',
                'sublabel' => implode(' · ', $sub),
                'path'     => '/proveedores?q=' . rawurlencode($r['nombre_empresa'] ?? ''),
            ];
        }

        // ── FACTURAS ──────────────────────────────────────────────────────
        $facturas = $db->query("
            SELECT f.id_facturas, f.numero, f.estado, f.total, f.fecha_emision,
                   c.nombre_empresa AS cliente_nombre
            FROM facturas f
            LEFT JOIN clientes c ON c.id_clientes = f.cliente_id
            WHERE f.numero LIKE ?
              AND f.deleted_at IS NULL
            ORDER BY f.fecha_emision DESC
            LIMIT {$limit}
        ", [$like])->getResultArray();

        foreach ($facturas as $r) {
            $sub = [];
            if (!empty($r['cliente_nombre'])) $sub[] = $r['cliente_nombre'];
            $sub[] = '$' . number_format((float) $r['total'], 0, ',', '.');
            if (!empty($r['estado']))         $sub[] = $r['estado'];
            $out[] = [
                'tipo'     => 'factura',
                'id'       => (int) $r['id_facturas'],
                'label'    => $r['numero'],
                'sublabel' => implode(' · ', $sub),
                'path'     => '/comercial?tab=facturas&q=' . rawurlencode($r['numero']),
            ];
        }

        // ── COTIZACIONES ──────────────────────────────────────────────────
        $cotiz = $db->query("
            SELECT co.id_cotizaciones, co.numero, co.estado, co.total, co.fecha_cotizacion,
                   c.nombre_empresa AS cliente_nombre
            FROM cotizaciones co
            LEFT JOIN clientes c ON c.id_clientes = co.cliente_id
            WHERE co.numero LIKE ?
              AND co.deleted_at IS NULL
            ORDER BY co.fecha_cotizacion DESC
            LIMIT {$limit}
        ", [$like])->getResultArray();

        foreach ($cotiz as $r) {
            $sub = [];
            if (!empty($r['cliente_nombre'])) $sub[] = $r['cliente_nombre'];
            $sub[] = '$' . number_format((float) $r['total'], 0, ',', '.');
            if (!empty($r['estado']))         $sub[] = $r['estado'];
            $out[] = [
                'tipo'     => 'cotizacion',
                'id'       => (int) $r['id_cotizaciones'],
                'label'    => $r['numero'],
                'sublabel' => implode(' · ', $sub),
                'path'     => '/comercial?tab=cotizaciones&q=' . rawurlencode($r['numero']),
            ];
        }

        // ── REMISIONES ────────────────────────────────────────────────────
        $remi = $db->query("
            SELECT r.id_remisiones, r.numero, r.estado, r.fecha_remision,
                   c.nombre_empresa AS cliente_nombre
            FROM remisiones r
            LEFT JOIN clientes c ON c.id_clientes = r.cliente_id
            WHERE r.numero LIKE ?
              AND r.deleted_at IS NULL
            ORDER BY r.fecha_remision DESC
            LIMIT {$limit}
        ", [$like])->getResultArray();

        foreach ($remi as $r) {
            $sub = [];
            if (!empty($r['cliente_nombre'])) $sub[] = $r['cliente_nombre'];
            if (!empty($r['estado']))         $sub[] = $r['estado'];
            $out[] = [
                'tipo'     => 'remision',
                'id'       => (int) $r['id_remisiones'],
                'label'    => $r['numero'],
                'sublabel' => implode(' · ', $sub),
                'path'     => '/comercial?tab=remisiones&q=' . rawurlencode($r['numero']),
            ];
        }

        // ── ÓRDENES DE COMPRA ─────────────────────────────────────────────
        $ocs = $db->query("
            SELECT oc.id_orden, oc.numero, oc.estado, oc.total, oc.fecha,
                   p.nombre_empresa AS proveedor_nombre
            FROM ordenes_compra oc
            LEFT JOIN proveedor p ON p.id_proveedor = oc.proveedor_id
            WHERE oc.numero LIKE ?
              AND oc.deleted_at IS NULL
            ORDER BY oc.fecha DESC
            LIMIT {$limit}
        ", [$like])->getResultArray();

        foreach ($ocs as $r) {
            $sub = [];
            if (!empty($r['proveedor_nombre'])) $sub[] = $r['proveedor_nombre'];
            $sub[] = '$' . number_format((float) $r['total'], 0, ',', '.');
            if (!empty($r['estado']))           $sub[] = $r['estado'];
            $out[] = [
                'tipo'     => 'orden_compra',
                'id'       => (int) $r['id_orden'],
                'label'    => $r['numero'],
                'sublabel' => implode(' · ', $sub),
                'path'     => '/compras?q=' . rawurlencode($r['numero']),
            ];
        }

        // ── NOTAS DE CRÉDITO ──────────────────────────────────────────────
        $ncs = $db->query("
            SELECT nc.id_nota_credito, nc.numero, nc.estado, nc.monto, nc.fecha,
                   c.nombre_empresa AS cliente_nombre
            FROM notas_credito nc
            LEFT JOIN clientes c ON c.id_clientes = nc.clientes_id
            WHERE nc.numero LIKE ?
            ORDER BY nc.fecha DESC
            LIMIT {$limit}
        ", [$like])->getResultArray();

        foreach ($ncs as $r) {
            $sub = [];
            if (!empty($r['cliente_nombre'])) $sub[] = $r['cliente_nombre'];
            $sub[] = '$' . number_format((float) $r['monto'], 0, ',', '.');
            if (!empty($r['estado']))         $sub[] = $r['estado'];
            $out[] = [
                'tipo'     => 'nota_credito',
                'id'       => (int) $r['id_nota_credito'],
                'label'    => $r['numero'],
                'sublabel' => implode(' · ', $sub),
                'path'     => '/cartera?q=' . rawurlencode($r['numero']),
            ];
        }

        return $this->respond($out);
    }
}
