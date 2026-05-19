<?php

namespace App\Controllers;

use App\Models\CarteraModel;
use App\Models\SincronizacionModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Panel Principal — agregador de KPIs del negocio.
 *
 * Endpoint único `/api/dashboard` que consolida ~10 KPIs leyendo de los modelos
 * existentes (CarteraModel, SincronizacionModel, etc.) + 4 queries propias para
 * lo que no estaba calculado: ventas/cotizaciones/OCs/producción agregadas.
 *
 * Diseñado para llamarse cada 60s desde el frontend — todas las queries son
 * O(1) o usan índices existentes. No queries N+1.
 */
class DashboardController extends BaseController
{
    use \App\Traits\JwtUserAware;

    public function index(): ResponseInterface
    {
        try {
            $cartera   = (new CarteraModel())->resumen();
            $aging     = (new CarteraModel())->aging();
            // Skip detección de duplicados (Levenshtein O(n²)) — la vista de
            // Sincronización lo calcula bajo demanda. El dashboard debe ser rápido (<500ms).
            $sincStats = (new SincronizacionModel())->stats(false);

            return $this->response->setJSON([
                'success' => true,
                'data'    => [
                    'cartera'           => $cartera,
                    'aging_resumen'     => [
                        'total_mora' => $aging['total_mora'],
                        'corriente'  => $aging['grupos']['corriente']['monto'],
                        'd_1_30'     => $aging['grupos']['dias_1_30']['monto'],
                        'd_31_60'    => $aging['grupos']['dias_31_60']['monto'],
                        'd_60_mas'   => $aging['grupos']['dias_60_mas']['monto'],
                    ],
                    'top_deudores'      => $this->topDeudores(5),
                    'sincronizacion'    => $sincStats,
                    'ventas_mes'        => $this->ventasMes(),
                    'cotizaciones'      => $this->cotizacionesPendientes(),
                    'ocs_pendientes'    => $this->ocsPendientes(),
                    'mp_criticas'       => $this->mpCriticas(),
                    'produccion_curso'  => $this->produccionEnCurso(),
                    'movimientos_hoy'   => $this->movimientosHoy(),
                    'top_descripciones' => $this->topDescripcionesMes(5),
                    'rentabilidad'      => $this->rentabilidadMes(),
                    'generated_at'      => date('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[DashboardController] ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al consolidar el dashboard.',
            ]);
        }
    }

    // ─── Top 5 deudores agrupando aging por cliente ────────────────────────
    private function topDeudores(int $limit = 5): array
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT
                f.cliente_id,
                c.nombre_empresa,
                c.nombre_encargado,
                SUM(f.saldo_pendiente) AS total_deuda,
                COUNT(f.id_facturas)   AS facturas_count,
                MAX(DATEDIFF(CURDATE(), f.fecha_vencimiento)) AS max_dias_mora
            FROM facturas f
            LEFT JOIN clientes c ON c.id_clientes = f.cliente_id
            WHERE f.estado IN ('Pendiente', 'Parcial', 'Vencida')
              AND f.saldo_pendiente > 0
              AND f.deleted_at IS NULL
            GROUP BY f.cliente_id, c.nombre_empresa, c.nombre_encargado
            ORDER BY total_deuda DESC
            LIMIT ?
        ", [$limit])->getResultArray();
    }

    // ─── Ventas del mes en curso (facturado vs cobrado) ────────────────────
    private function ventasMes(): array
    {
        $db        = \Config\Database::connect();
        $inicioMes = date('Y-m-01');
        $finMes    = date('Y-m-t');

        $facturado = $db->query("
            SELECT
                COUNT(*) AS facturas_count,
                COALESCE(SUM(total), 0)              AS total_facturado,
                COALESCE(SUM(saldo_pendiente), 0)    AS saldo_pendiente_mes,
                SUM(CASE WHEN estado = 'Pagada'   THEN 1 ELSE 0 END) AS pagadas,
                SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN estado = 'Anulada'  THEN 1 ELSE 0 END) AS anuladas
            FROM facturas
            WHERE DATE(fecha_emision) BETWEEN ? AND ?
              AND deleted_at IS NULL
        ", [$inicioMes, $finMes])->getRowArray() ?: [];

        return [
            'facturas_count'  => (int)   ($facturado['facturas_count']      ?? 0),
            'total_facturado' => (float) ($facturado['total_facturado']     ?? 0),
            'saldo_pendiente' => (float) ($facturado['saldo_pendiente_mes'] ?? 0),
            'pagadas'         => (int)   ($facturado['pagadas']             ?? 0),
            'pendientes'      => (int)   ($facturado['pendientes']          ?? 0),
            'anuladas'        => (int)   ($facturado['anuladas']            ?? 0),
        ];
    }

    // ─── Cotizaciones en estado abierto ────────────────────────────────────
    private function cotizacionesPendientes(): array
    {
        $db = \Config\Database::connect();
        $row = $db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN estado = 'Borrador' THEN 1 ELSE 0 END) AS borradores,
                SUM(CASE WHEN estado = 'Enviada'  THEN 1 ELSE 0 END) AS enviadas,
                COALESCE(SUM(CASE WHEN estado IN ('Borrador','Enviada') THEN total ELSE 0 END), 0) AS valor_total
            FROM cotizaciones
            WHERE estado IN ('Borrador', 'Enviada')
              AND deleted_at IS NULL
        ")->getRowArray() ?: [];

        return [
            'total'       => (int)   ($row['total']       ?? 0),
            'borradores'  => (int)   ($row['borradores']  ?? 0),
            'enviadas'    => (int)   ($row['enviadas']    ?? 0),
            'valor_total' => (float) ($row['valor_total'] ?? 0),
        ];
    }

    // ─── OCs pendientes de recibir (estado='Enviada' con líneas faltantes) ─
    private function ocsPendientes(): array
    {
        $db = \Config\Database::connect();
        $row = $db->query("
            SELECT
                COUNT(DISTINCT oc.id_orden) AS total,
                COALESCE(SUM(oc.total), 0)   AS valor_total,
                COUNT(DISTINCT CASE WHEN oc.fecha_esperada < CURDATE() THEN oc.id_orden END) AS retrasadas
            FROM ordenes_compra oc
            WHERE oc.estado = 'Enviada'
              AND oc.deleted_at IS NULL
        ")->getRowArray() ?: [];

        return [
            'total'       => (int)   ($row['total']       ?? 0),
            'valor_total' => (float) ($row['valor_total'] ?? 0),
            'retrasadas'  => (int)   ($row['retrasadas']  ?? 0),
        ];
    }

    // ─── MP en stock crítico (días restantes < 7) ──────────────────────────
    // Reproduce la lógica de InventarioController::global pero filtrada
    private function mpCriticas(int $umbralDias = 7, int $limit = 10): array
    {
        $db = \Config\Database::connect();

        $items = $db->query("
            SELECT
                ig.id_item_general,
                ig.nombre,
                ig.codigo,
                COALESCE(SUM(ic.cantidad_disponible), 0) AS stock_total
            FROM item_general ig
            LEFT JOIN inventario_capas ic
                   ON ic.item_general_id = ig.id_item_general AND ic.estado = 1
            WHERE ig.tipo = 1
            GROUP BY ig.id_item_general, ig.nombre, ig.codigo
        ")->getResultArray();

        $consumo = $db->query("
            SELECT pid.item_general_id, SUM(pid.cantidad) AS consumo_30d
            FROM produccion_insumos_detalle pid
            JOIN preparaciones p ON p.id_preparaciones = pid.preparacion_id
            WHERE p.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              AND p.estado != 0
            GROUP BY pid.item_general_id
        ")->getResultArray();

        $consumoMap = [];
        foreach ($consumo as $c) {
            $consumoMap[(int) $c['item_general_id']] = (float) $c['consumo_30d'];
        }

        $criticas = [];
        foreach ($items as $it) {
            $stock = (float) $it['stock_total'];
            $consumo30 = $consumoMap[(int) $it['id_item_general']] ?? 0;
            $diario = $consumo30 > 0 ? $consumo30 / 30 : 0;
            $dias = $diario > 0 ? (int) round($stock / $diario) : null;

            if ($dias !== null && $dias < $umbralDias) {
                $criticas[] = [
                    'id_item_general' => (int) $it['id_item_general'],
                    'nombre'          => $it['nombre'],
                    'codigo'          => $it['codigo'],
                    'stock_total'     => $stock,
                    'consumo_diario'  => round($diario, 4),
                    'dias_restantes'  => $dias,
                ];
            }
        }

        usort($criticas, fn($a, $b) => $a['dias_restantes'] <=> $b['dias_restantes']);

        return [
            'total'    => count($criticas),
            'top'      => array_slice($criticas, 0, $limit),
        ];
    }

    // ─── Producción en curso ───────────────────────────────────────────────
    // estado=1 → en proceso (según mapeo en PreparacionesModel)
    private function produccionEnCurso(): array
    {
        $db = \Config\Database::connect();
        $row = $db->query("
            SELECT
                COUNT(*) AS total,
                COALESCE(SUM(cantidad), 0) AS volumen_kg
            FROM preparaciones
            WHERE estado = 1
        ")->getRowArray() ?: [];

        return [
            'total'      => (int)   ($row['total']      ?? 0),
            'volumen_kg' => (float) ($row['volumen_kg'] ?? 0),
        ];
    }

    // ─── Movimientos de hoy ────────────────────────────────────────────────
    private function movimientosHoy(): array
    {
        $db  = \Config\Database::connect();
        $hoy = date('Y-m-d');
        $row = $db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN tipo_movimiento = 'ENTRADA'  THEN 1 ELSE 0 END) AS entradas,
                SUM(CASE WHEN tipo_movimiento = 'SALIDA'   THEN 1 ELSE 0 END) AS salidas,
                SUM(CASE WHEN tipo_movimiento = 'TRASPASO' THEN 1 ELSE 0 END) AS traspasos,
                SUM(CASE WHEN tipo_movimiento = 'AJUSTE'   THEN 1 ELSE 0 END) AS ajustes
            FROM movimiento_inventario
            WHERE DATE(fecha_movimiento) = ?
        ", [$hoy])->getRowArray() ?: [];

        return [
            'total'     => (int) ($row['total']     ?? 0),
            'entradas'  => (int) ($row['entradas']  ?? 0),
            'salidas'   => (int) ($row['salidas']   ?? 0),
            'traspasos' => (int) ($row['traspasos'] ?? 0),
            'ajustes'   => (int) ($row['ajustes']   ?? 0),
        ];
    }

    // ─── Top descripciones facturadas del mes ──────────────────────────────
    // NOTA: facturas_detalle no tiene FK a item_general, agrupamos por descripción
    // textual. Para alta precisión haría falta normalizar el detalle a items.
    private function topDescripcionesMes(int $limit = 5): array
    {
        $db        = \Config\Database::connect();
        $inicioMes = date('Y-m-01');
        $finMes    = date('Y-m-t');

        return $db->query("
            SELECT
                fd.descripcion,
                SUM(fd.cantidad)  AS unidades,
                SUM(fd.subtotal)  AS monto_total
            FROM facturas_detalle fd
            JOIN facturas f ON f.id_facturas = fd.facturas_id
            WHERE DATE(f.fecha_emision) BETWEEN ? AND ?
              AND f.estado != 'Anulada'
              AND f.deleted_at IS NULL
            GROUP BY fd.descripcion
            ORDER BY monto_total DESC
            LIMIT ?
        ", [$inicioMes, $finMes, $limit])->getResultArray();
    }

    // ─── Rentabilidad estimada del mes ─────────────────────────────────────
    // Ingresos = facturas no anuladas del mes (subtotal sin IVA)
    // Costos   = produccion_insumos_detalle.subtotal de preparaciones del mes
    // Margen   = (ingresos - costos) / ingresos
    private function rentabilidadMes(): array
    {
        $db        = \Config\Database::connect();
        $inicioMes = date('Y-m-01');
        $finMes    = date('Y-m-t');

        $ingresos = (float) ($db->query("
            SELECT COALESCE(SUM(subtotal), 0) AS total
            FROM facturas
            WHERE DATE(fecha_emision) BETWEEN ? AND ?
              AND estado != 'Anulada'
              AND deleted_at IS NULL
        ", [$inicioMes, $finMes])->getRowArray()['total'] ?? 0);

        $costos = (float) ($db->query("
            SELECT COALESCE(SUM(pid.subtotal), 0) AS total
            FROM produccion_insumos_detalle pid
            JOIN preparaciones p ON p.id_preparaciones = pid.preparacion_id
            WHERE DATE(p.fecha_creacion) BETWEEN ? AND ?
              AND p.estado != 0
        ", [$inicioMes, $finMes])->getRowArray()['total'] ?? 0);

        $utilidad = $ingresos - $costos;
        $margenPct = $ingresos > 0 ? round(($utilidad / $ingresos) * 100, 2) : 0;

        return [
            'ingresos'  => $ingresos,
            'costos'    => $costos,
            'utilidad'  => $utilidad,
            'margen_pct' => $margenPct,
        ];
    }
}
