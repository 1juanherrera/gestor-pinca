<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\InventarioModel;

class InventarioController extends ResourceController
{
    use \App\Traits\JwtUserAware;

    protected $modelName = InventarioModel::class;

    // GET /api/inventario/global?tipo=1
    public function global()
    {
        $db   = \Config\Database::connect();
        $tipo = $this->request->getGet('tipo');

        // 1. Stock consolidado por ítem
        $sql = "
            SELECT
                ig.id_item_general,
                ig.nombre,
                ig.codigo,
                ig.tipo,
                ub.nombre AS unidad_base,
                uv.nombre AS unidad_venta,
                COALESCE(SUM(ic.cantidad_disponible), 0) AS stock_total,
                COALESCE(
                    SUM(ic.cantidad_disponible * ic.costo_unitario)
                    / NULLIF(SUM(ic.cantidad_disponible), 0),
                0) AS costo_promedio,
                COALESCE(SUM(ic.cantidad_disponible * ic.costo_unitario), 0) AS valor_inventario,
                COUNT(DISTINCT CASE WHEN ic.cantidad_disponible > 0 THEN ic.bodegas_id END) AS bodegas_con_stock
            FROM item_general ig
            LEFT JOIN unidad ub ON ub.id_unidad = ig.unidad_almacenaje_id
            LEFT JOIN unidad uv ON uv.id_unidad = ig.unidad_id
            LEFT JOIN inventario_capas ic
                   ON ic.item_general_id = ig.id_item_general AND ic.estado = 1
        ";

        $params = [];
        if ($tipo !== null && $tipo !== '') {
            $sql   .= " WHERE ig.tipo = ?";
            $params[] = (int) $tipo;
        }

        $sql .= "
            GROUP BY ig.id_item_general, ig.nombre, ig.codigo, ig.tipo,
                     ub.nombre, uv.nombre
            ORDER BY ig.nombre ASC
        ";

        $items = $db->query($sql, $params)->getResultArray();

        // 2. Stock por bodega (todas las bodegas con saldo > 0)
        $bodegaRows = $db->query("
            SELECT
                ic.item_general_id,
                ic.bodegas_id,
                b.nombre                          AS bodega_nombre,
                COALESCE(ins.nombre, '')           AS instalacion_nombre,
                SUM(ic.cantidad_disponible)        AS cantidad
            FROM inventario_capas ic
            JOIN bodegas b     ON b.id_bodegas          = ic.bodegas_id
            LEFT JOIN instalaciones ins ON ins.id_instalaciones = b.instalaciones_id
            WHERE ic.estado = 1
            GROUP BY ic.item_general_id, ic.bodegas_id, b.nombre, ins.nombre
            HAVING SUM(ic.cantidad_disponible) > 0
            ORDER BY ic.item_general_id, b.nombre
        ")->getResultArray();

        $stockPorBodega = [];
        foreach ($bodegaRows as $row) {
            $stockPorBodega[$row['item_general_id']][] = [
                'bodega_id'   => (int) $row['bodegas_id'],
                'bodega'      => $row['bodega_nombre'],
                'instalacion' => $row['instalacion_nombre'],
                'cantidad'    => (float) $row['cantidad'],
            ];
        }

        // 3. Consumo últimos 30 días (solo preparaciones no canceladas)
        $consumoRows = $db->query("
            SELECT
                pid.item_general_id,
                SUM(pid.cantidad) AS consumo_30_dias
            FROM produccion_insumos_detalle pid
            JOIN preparaciones p ON p.id_preparaciones = pid.preparacion_id
            WHERE p.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              AND p.estado != 3
            GROUP BY pid.item_general_id
        ")->getResultArray();

        $consumo30 = [];
        foreach ($consumoRows as $row) {
            $consumo30[$row['item_general_id']] = (float) $row['consumo_30_dias'];
        }

        // 4. Merge final
        $result = array_map(function ($item) use ($stockPorBodega, $consumo30) {
            $id            = $item['id_item_general'];
            $stock         = (float) $item['stock_total'];
            $consumoTotal  = $consumo30[$id] ?? null;
            $consumoDiario = $consumoTotal ? round($consumoTotal / 30, 6) : null;
            $diasRestantes = ($consumoDiario && $consumoDiario > 0)
                ? (int) round($stock / $consumoDiario)
                : null;

            return [
                'id_item_general'   => (int) $id,
                'nombre'            => $item['nombre'],
                'codigo'            => $item['codigo'],
                'tipo'              => (int) $item['tipo'],
                'unidad_base'       => $item['unidad_base'],
                'unidad_venta'      => $item['unidad_venta'],
                'stock_total'       => $stock,
                'costo_promedio'    => (float) $item['costo_promedio'],
                'valor_inventario'  => (float) $item['valor_inventario'],
                'bodegas_con_stock' => (int) $item['bodegas_con_stock'],
                'stock_por_bodega'  => $stockPorBodega[$id] ?? [],
                'consumo_30_dias'   => $consumoTotal,
                'consumo_diario'    => $consumoDiario,
                'dias_restantes'    => $diasRestantes,
            ];
        }, $items);

        return $this->respond($result);
    }

    public function traspaso()
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);

        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }

        // Adjuntar responsable del JWT (preferido) o body si vino explícito
        if (empty($data['responsable'])) {
            $data['responsable'] = $this->getUsername();
        }

        $result = $this->model->traspaso($data);
        if ($result) {
            return $this->respond([
                'mensaje' => 'Traspaso realizado correctamente',
            ]);
        }
        return $this->fail('Error al realizar el traspaso');
    }

    // DELETE api/inventario/{item_id}/bodega/{bodega_id}
    public function removeFromBodega(int $itemId, int $bodegaId)
    {
        $responsable = $this->getUsername();
        $motivo      = $this->request->getGet('motivo'); // opcional

        $result = $this->model->removeFromBodega($itemId, $bodegaId, $responsable, $motivo);

        if ($result) {
            return $this->respond([
                'mensaje' => 'Ítem eliminado del inventario correctamente',
            ]);
        }
        return $this->fail('No se encontró el ítem en esta bodega', 404);
    }

    /**
     * POST /api/inventario/ajuste-manual
     * Body: { item_general_id, bodega_id, cantidad, motivo, observacion? }
     *
     * Descuenta `cantidad` del stock de un item en una bodega vía FIFO.
     * Pensado para ajustes operativos: rotura, derrame, conteo, vencimiento.
     * Registra movimiento_inventario tipo AJUSTE con motivo en metadata.
     * NO se puede usar para incrementar stock — eso solo viene por OC o Producción.
     */
    public function ajusteManual()
    {
        $data = json_decode($this->request->getBody(), true) ?? [];

        $itemId      = (int)   ($data['item_general_id'] ?? 0);
        $bodegaId    = (int)   ($data['bodega_id']       ?? 0);
        $cantidad    = (float) ($data['cantidad']        ?? 0);
        $motivo      = trim((string) ($data['motivo']     ?? ''));
        $observacion = trim((string) ($data['observacion'] ?? ''));

        if ($itemId <= 0 || $bodegaId <= 0) {
            return $this->failValidationErrors('item_general_id y bodega_id son obligatorios.');
        }
        if ($cantidad <= 0) {
            return $this->failValidationErrors('La cantidad a descontar debe ser mayor a 0.');
        }
        if ($motivo === '') {
            return $this->failValidationErrors('El motivo del ajuste es obligatorio.');
        }
        $motivosValidos = ['rotura', 'derrame', 'conteo', 'vencimiento', 'otro'];
        if (!in_array(strtolower($motivo), $motivosValidos, true)) {
            return $this->failValidationErrors('Motivo inválido. Usar: ' . implode(', ', $motivosValidos));
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $capasModel = new \App\Models\InventarioCapasModel();

            // Validar stock disponible en la bodega
            $stockActual = $db->query(
                'SELECT COALESCE(SUM(cantidad_disponible), 0) AS s
                 FROM inventario_capas
                 WHERE item_general_id = ? AND bodegas_id = ? AND estado = 1',
                [$itemId, $bodegaId]
            )->getRow();

            $disponible = (float) ($stockActual->s ?? 0);
            if ($disponible < $cantidad) {
                throw new \Exception(sprintf(
                    'Stock insuficiente. Disponible en esta bodega: %.4f, intentando descontar: %.4f',
                    $disponible, $cantidad
                ));
            }

            // Consumir capas FIFO de la bodega
            $consumos = $capasModel->consumirCapasFIFO($itemId, $cantidad, $bodegaId);
            if (empty($consumos)) {
                throw new \Exception('No se pudieron consumir capas para el ajuste.');
            }

            $consumido = array_sum(array_column($consumos, 'cantidad_consumida'));
            $costoTotal = array_sum(array_column($consumos, 'costo_total'));
            $costoUnitProm = $consumido > 0 ? $costoTotal / $consumido : 0;

            // Recalcular promedio ponderado
            $capasModel->recalcularPromedioPonderado($itemId);

            // Audit log
            $movModel = new \App\Models\MovimientoInventarioModel();
            $movModel->registrar([
                'tipo'             => \App\Models\MovimientoInventarioModel::TIPO_AJUSTE,
                'item_general_id'  => $itemId,
                'bodega_id'        => $bodegaId,
                'cantidad'         => $consumido,
                'referencia_tipo'  => \App\Models\MovimientoInventarioModel::REF_AJUSTE,
                'referencia_id'    => null,
                'descripcion'      => "Ajuste manual ({$motivo}): descontó {$consumido}",
                'costo_unitario'   => $costoUnitProm,
                'responsable'      => $this->getUsername(),
                'metadata'         => [
                    'motivo'      => $motivo,
                    'observacion' => $observacion ?: null,
                    'capas'       => array_map(fn($c) => [
                        'capa_id'  => $c['capa_id'],
                        'cantidad' => $c['cantidad_consumida'],
                        'costo_u'  => $c['costo_unitario'],
                    ], $consumos),
                ],
            ]);

            if ($db->transStatus() === false) {
                throw new \Exception('Error al confirmar la transacción.');
            }
            $db->transCommit();

            return $this->respond([
                'mensaje'         => 'Ajuste registrado correctamente',
                'cantidad_descontada' => $consumido,
                'costo_total_perdido' => $costoTotal,
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage(), 400);
        }
    }
}