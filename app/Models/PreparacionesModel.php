<?php

namespace App\Models;

use Exception;
use App\Models\MovimientoInventarioModel;
use App\Models\InventarioCapasModel;

class PreparacionesModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Crea UNA orden de preparación.
     * Soporta detalle precalculado desde el frontend.
     */
    public function create_preparacion(array $data): array
    {
        foreach (['item_general_id', 'unidad_id', 'cantidad'] as $field) {
            if (empty($data[$field])) {
                throw new Exception("Campo requerido faltante: {$field}");
            }
        }

        $itemId         = (int)   $data['item_general_id'];
        $unidadId       = (int)   $data['unidad_id'];
        $volumenGalones = (float) $data['cantidad'];

        if ($volumenGalones <= 0) {
            throw new Exception('El volumen debe ser mayor a 0.');
        }

        $unidad = $this->db->query(
            'SELECT * FROM unidad WHERE id_unidad = ? AND estados = 1',
            [$unidadId]
        )->getRow();

        if (!$unidad) {
            throw new Exception("Unidad con ID {$unidadId} no encontrada o inactiva.");
        }

        $escala          = (float) $unidad->escala;
        $cantidadEnvases = $escala > 0 ? $volumenGalones / $escala : 0;

        $formulacion = $this->db->query(
            'SELECT id_formulaciones FROM formulaciones
             WHERE item_general_id = ? AND estado = 1 LIMIT 1',
            [$itemId]
        )->getRow();

        if (!$formulacion) {
            throw new Exception("El item no tiene una formulación activa.");
        }

        $ingredientes = $this->db->query(
            'SELECT igf.item_general_id, igf.cantidad
             FROM item_general_formulaciones igf
             WHERE igf.formulaciones_id = ?',
            [$formulacion->id_formulaciones]
        )->getResult();

        if (empty($ingredientes)) {
            throw new Exception("La formulación no tiene ingredientes asignados.");
        }

        // Detalle precalculado desde el frontend (prioridad)
        $detalleMap = [];
        if (!empty($data['detalle']) && is_array($data['detalle'])) {
            foreach ($data['detalle'] as $d) {
                $detalleMap[(int) $d['item_general_id']] = (float) $d['cantidad'];
            }
        }

        $factorVolumen = 1;
        if (empty($detalleMap)) {
            $itemCosto = $this->db->query(
                'SELECT COALESCE(NULLIF(volumen, 0), 1) as volumen_base
                 FROM costos_item WHERE item_general_id = ? LIMIT 1',
                [$itemId]
            )->getRow();
            $volumenBase   = (float) ($itemCosto->volumen_base ?? 1);
            $factorVolumen = $volumenBase > 0 ? $volumenGalones / $volumenBase : 1;
        }

        $totalCantidadBase = array_sum(array_map(fn($i) => (float) $i->cantidad, $ingredientes));

        $this->db->transStart();

        $this->db->query(
            'INSERT INTO preparaciones
                (fecha_creacion, fecha_inicio, fecha_fin, cantidad, observaciones, estado, item_general_id, unidad_id)
             VALUES (NOW(), ?, ?, ?, ?, 0, ?, ?)',
            [
                $data['fecha_inicio']  ?? null,
                $data['fecha_fin']     ?? null,
                $cantidadEnvases,
                $data['observaciones'] ?? null,
                $itemId,
                $unidadId,
            ]
        );

        $preparacionId = $this->db->insertID();

        foreach ($ingredientes as $ing) {
            $ingId = (int) $ing->item_general_id;
            $cantidadEscalada = isset($detalleMap[$ingId])
                ? round($detalleMap[$ingId], 4)
                : round((float) $ing->cantidad * $factorVolumen, 4);

            $porcentaje = $totalCantidadBase > 0
                ? round(((float) $ing->cantidad / $totalCantidadBase) * 100, 4)
                : 0;

            $this->db->query(
                'INSERT INTO preparaciones_has_item_general
                    (preparaciones_id_preparaciones, item_general_id, cantidad, porcentajes)
                 VALUES (?, ?, ?, ?)',
                [$preparacionId, $ingId, $cantidadEscalada, $porcentaje]
            );
        }

        // Costos indirectos para esta preparación
        if (!empty($data['costos_indirectos']) && is_array($data['costos_indirectos'])) {
            foreach ($data['costos_indirectos'] as $ci) {
                $nombre        = trim($ci['nombre']        ?? '');
                $categoria     = trim($ci['categoria']     ?? 'otros');
                $valorAplicado = (float) ($ci['valor_aplicado'] ?? 0);
                if ($nombre && $valorAplicado > 0) {
                    $this->db->query(
                        'INSERT INTO preparaciones_costos_indirectos
                            (preparaciones_id, costos_indirectos_id, valor_aplicado, nombre, categoria)
                         VALUES (?, NULL, ?, ?, ?)',
                        [$preparacionId, $valorAplicado, $nombre, $categoria]
                    );
                }
            }
        }

        // Descontar las cantidades del inventario (con soporte de capas)
        $responsable      = $data['responsable'] ?? null;
        $capasSeleccion   = [];
        if (!empty($data['detalle']) && is_array($data['detalle'])) {
            foreach ($data['detalle'] as $d) {
                $itemId = (int) ($d['item_general_id'] ?? 0);
                if ($itemId && (isset($d['modo_consumo']) || isset($d['capas']) || isset($d['bodega_id']))) {
                    $capasSeleccion[$itemId] = [
                        'modo'      => $d['modo_consumo'] ?? 'FIFO',
                        'capas'     => $d['capas'] ?? [],
                        'bodega_id' => isset($d['bodega_id']) ? (int) $d['bodega_id'] : null,
                    ];
                }
            }
        }
        $this->_ajustarInventarioPorPreparacion($preparacionId, -1, $responsable, $capasSeleccion);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            $error = $this->db->error();
            log_message('error', 'Preparacion transaction failed: ' . json_encode($error));
            throw new Exception("Error al guardar la preparación: " . ($error['message'] ?? 'error desconocido'));
        }

        return $this->get_preparacion_by_id($preparacionId);
    }

    /**
     * Ajusta el inventario de las materias primas para una preparación.
     * $multiplicador = -1 para descontar (al crear o reactivar)
     * $multiplicador =  1 para sumar (al cancelar)
     * $capasSeleccion = mapa por item_general_id con modo (MANUAL/FIFO), capas seleccionadas y bodega
     */
    private function _ajustarInventarioPorPreparacion(int $prepId, int $multiplicador, string $responsable = null, array $capasSeleccion = []): void
    {
        $ingredientes = $this->db->query(
            'SELECT phig.item_general_id, phig.cantidad, COALESCE(ci.costo_unitario, 0) as costo_unitario
             FROM preparaciones_has_item_general phig
             LEFT JOIN costos_item ci ON ci.item_general_id = phig.item_general_id
             WHERE phig.preparaciones_id_preparaciones = ?',
            [$prepId]
        )->getResult();

        $movimientoModel = new MovimientoInventarioModel();
        $capasModel      = new InventarioCapasModel();

        // Para cancelaciones, restaurar capas
        if ($multiplicador > 0) {
            $capasModel->restaurarCapas($prepId);
        }

        foreach ($ingredientes as $ing) {
            $itemId        = (int) $ing->item_general_id;
            $cantidadAbs   = (float) $ing->cantidad;
            $diff          = $cantidadAbs * $multiplicador;
            $costoUnitario = (float) $ing->costo_unitario;

            if ($diff == 0) continue;

            // ── Consumo por capas (solo al descontar, no al cancelar) ──
            $consumosCapas = [];
            if ($multiplicador < 0 && $capasModel->tieneCapas($itemId)) {
                $seleccion = $capasSeleccion[$itemId] ?? null;

                if ($seleccion && $seleccion['modo'] === 'MANUAL' && !empty($seleccion['capas'])) {
                    $consumosCapas = $capasModel->consumirCapasManual($seleccion['capas']);
                } else {
                    $bodegaFiltro = $seleccion['bodega_id'] ?? null;
                    $consumosCapas = $capasModel->consumirCapasFIFO($itemId, $cantidadAbs, $bodegaFiltro);
                }

                if (!empty($consumosCapas)) {
                    $capasModel->registrarConsumos($prepId, $consumosCapas);
                    $costoReal = array_sum(array_column($consumosCapas, 'costo_total'));
                    $qtyReal   = array_sum(array_column($consumosCapas, 'cantidad_consumida'));
                    $costoUnitario = $qtyReal > 0 ? $costoReal / $qtyReal : $costoUnitario;
                }
            }

            // ── Actualizar inventario agregado (compatibilidad) ──
            $stock = $this->db->query(
                'SELECT id_inventario, cantidad, bodegas_id FROM inventario WHERE item_general_id = ? ORDER BY cantidad DESC LIMIT 1',
                [$itemId]
            )->getRow();

            $bodegaId      = $stock ? (int) $stock->bodegas_id : 1;
            $saldoAnterior = $stock ? (float) $stock->cantidad : 0.0;
            $saldoNuevo    = $saldoAnterior + $diff;

            if (!$stock) {
                $this->db->query(
                    'INSERT INTO inventario (item_general_id, bodegas_id, cantidad, estado, tipo, fecha_update) VALUES (?, ?, ?, 1, 1, NOW())',
                    [$itemId, $bodegaId, $diff]
                );
            } else {
                $this->db->query(
                    'UPDATE inventario SET cantidad = cantidad + ?, fecha_update = NOW() WHERE id_inventario = ?',
                    [$diff, $stock->id_inventario]
                );
            }

            // ── Kardex ──
            $tipoMovimiento = $multiplicador < 0 ? 'SALIDA' : 'ENTRADA';
            $descripcion    = $multiplicador < 0
                ? "Consumo por orden de producción #{$prepId}"
                : "Reintegro por cancelación de orden #{$prepId}";

            $movimientoModel->registrarMovimiento([
                'tipo_movimiento'  => $tipoMovimiento,
                'cantidad'         => abs($diff),
                'fecha_movimiento' => date('Y-m-d H:i:s'),
                'descripcion'      => $descripcion,
                'referencia_tipo'  => 'ORDEN_PRODUCCION',
                'item_general_id'  => $itemId,
                'bodega_id'        => $bodegaId,
                'referencia_id'    => $prepId,
                'costo_unitario'   => $costoUnitario,
                'saldo_anterior'   => $saldoAnterior,
                'saldo_nuevo'      => $saldoNuevo,
                'responsable'      => $responsable,
            ]);
        }
    }

    /**
     * Crea múltiples preparaciones en una sola llamada (para combinaciones).
     * Cada elemento del array $lote sigue el mismo formato que create_preparacion.
     *
     * Devuelve un array con todas las preparaciones creadas.
     */
    public function create_preparaciones_lote(array $lote): array
    {
        if (empty($lote)) {
            throw new Exception('El lote no puede estar vacío.');
        }

        $creadas = [];
        foreach ($lote as $idx => $data) {
            try {
                $creadas[] = $this->create_preparacion($data);
            } catch (Exception $e) {
                // Si alguna falla, se reporta cuál con su índice
                throw new Exception("Error en preparación #{$idx}: " . $e->getMessage());
            }
        }

        return $creadas;
    }

    /**
     * Obtiene una preparación con su desglose de materias primas.
     */
    public function get_preparacion_by_id(int $id): array
    {
        $prep = $this->db->query(
            'SELECT p.*, ig.nombre AS item_nombre, ig.codigo AS item_codigo,
                    u.nombre AS unidad_nombre, u.escala
             FROM preparaciones p
             INNER JOIN item_general ig ON ig.id_item_general = p.item_general_id
             INNER JOIN unidad u ON u.id_unidad = p.unidad_id
             WHERE p.id_preparaciones = ?',
            [$id]
        )->getRow();

        if (!$prep) {
            throw new Exception("Preparación con ID {$id} no encontrada.");
        }

        $detalle = $this->db->query(
            'SELECT phig.item_general_id, phig.cantidad, phig.porcentajes,
                    ig.nombre, ig.codigo,
                    COALESCE(ci.costo_unitario, 0) as materia_prima_costo_unitario,
                    (phig.cantidad * COALESCE(ci.costo_unitario, 0)) as costo_total_materia
             FROM preparaciones_has_item_general phig
             INNER JOIN item_general ig ON ig.id_item_general = phig.item_general_id
             LEFT JOIN costos_item ci ON ig.id_item_general = ci.item_general_id
             WHERE phig.preparaciones_id_preparaciones = ?
             ORDER BY phig.item_general_id ASC',
            [$id]
        )->getResult();

        $costosIndirectos = $this->db->query(
            'SELECT id, nombre, categoria, valor_aplicado
             FROM preparaciones_costos_indirectos
             WHERE preparaciones_id = ?
             ORDER BY categoria, nombre',
            [$id]
        )->getResult();

        $consumoCapas = $this->db->query(
            'SELECT pcc.*, ic.proveedor_id, ic.lote_proveedor, ic.fecha_ingreso,
                    p.nombre_empresa AS proveedor_nombre, b.nombre AS bodega_nombre
             FROM preparacion_consumo_capas pcc
             INNER JOIN inventario_capas ic ON ic.id_capa = pcc.capa_id
             LEFT JOIN proveedor p ON p.id_proveedor = ic.proveedor_id
             LEFT JOIN bodegas b ON b.id_bodegas = ic.bodegas_id
             WHERE pcc.preparacion_id = ?
             ORDER BY pcc.item_general_id, ic.fecha_ingreso',
            [$id]
        )->getResult();

        $estados = [0 => 'PENDIENTE', 1 => 'EN_PROCESO', 2 => 'COMPLETADA', 3 => 'CANCELADA'];
        $escala  = (float) $prep->escala;

        return [
            'id_preparaciones'  => $prep->id_preparaciones,
            'item_general_id'   => $prep->item_general_id,
            'item_nombre'       => $prep->item_nombre,
            'item_codigo'       => $prep->item_codigo,
            'unidad_id'         => $prep->unidad_id,
            'unidad_nombre'     => $prep->unidad_nombre,
            'escala'            => $escala,
            'cantidad'          => (float) $prep->cantidad,
            'volumen_galones'   => round((float) $prep->cantidad * $escala, 4),
            'observaciones'     => $prep->observaciones,
            'estado'            => $estados[$prep->estado] ?? 'PENDIENTE',
            'fecha_creacion'    => $prep->fecha_creacion,
            'fecha_inicio'      => $prep->fecha_inicio,
            'fecha_fin'         => $prep->fecha_fin,
            'detalle'           => array_map(fn($d) => [
                'item_general_id' => $d->item_general_id,
                'nombre'          => $d->nombre,
                'codigo'          => $d->codigo,
                'cantidad'        => (float) $d->cantidad,
                'porcentajes'     => (float) $d->porcentajes,
                'materia_prima_costo_unitario' => (float) $d->materia_prima_costo_unitario,
                'costo_total_materia'          => (float) $d->costo_total_materia,
            ], $detalle),
            'costos_indirectos' => array_map(fn($ci) => [
                'id'             => $ci->id,
                'nombre'         => $ci->nombre,
                'categoria'      => $ci->categoria,
                'valor_aplicado' => (float) $ci->valor_aplicado,
            ], $costosIndirectos),
            'consumo_capas' => array_map(fn($cc) => [
                'id'                 => (int) $cc->id,
                'capa_id'            => (int) $cc->capa_id,
                'item_general_id'    => (int) $cc->item_general_id,
                'cantidad_consumida' => (float) $cc->cantidad_consumida,
                'costo_unitario'     => (float) $cc->costo_unitario,
                'costo_total'        => (float) $cc->costo_total,
                'proveedor_nombre'   => $cc->proveedor_nombre,
                'lote_proveedor'     => $cc->lote_proveedor,
                'bodega_nombre'      => $cc->bodega_nombre,
                'fecha_ingreso'      => $cc->fecha_ingreso,
            ], $consumoCapas),
        ];
    }

    /**
     * Lista todas las preparaciones con paginación.
     */
    public function get_all_preparaciones(int $page = 1, int $limit = 20): array
    {
        $offset  = ($page - 1) * $limit;
        $estados = [0 => 'PENDIENTE', 1 => 'EN_PROCESO', 2 => 'COMPLETADA', 3 => 'CANCELADA'];

        $rows = $this->db->query(
            'SELECT p.*, ig.nombre AS item_nombre, ig.codigo AS item_codigo,
                    ig.id_item_general,
                    u.nombre AS unidad_nombre, u.escala
             FROM preparaciones p
             INNER JOIN item_general ig ON ig.id_item_general = p.item_general_id
             INNER JOIN unidad u ON u.id_unidad = p.unidad_id
             ORDER BY p.fecha_creacion DESC
             LIMIT ? OFFSET ?',
            [$limit, $offset]
        )->getResult();

        $total = $this->db->query('SELECT COUNT(*) as total FROM preparaciones')->getRow()->total;

        return [
            'data' => array_map(fn($r) => [
                'id_preparaciones' => $r->id_preparaciones,
                'item_general_id'  => $r->id_item_general,
                'item_nombre'      => $r->item_nombre,
                'item_codigo'      => $r->item_codigo,
                'unidad_nombre'    => $r->unidad_nombre,
                'escala'           => (float) $r->escala,
                'cantidad'         => (float) $r->cantidad,
                'volumen_galones'  => round((float) $r->cantidad * (float) $r->escala, 4),
                'observaciones'    => $r->observaciones,
                'estado'           => $estados[$r->estado] ?? 'PENDIENTE',
                'fecha_creacion'   => $r->fecha_creacion,
                'fecha_inicio'     => $r->fecha_inicio,
                'fecha_fin'        => $r->fecha_fin,
            ], $rows),
            'meta' => [
                'total' => (int) $total,
                'page'  => $page,
                'limit' => $limit,
                'pages' => (int) ceil($total / $limit),
            ],
        ];
    }

    /**
     * Actualiza estado u observaciones de una preparación.
     */
    public function update_preparacion(int $id, array $data): array
    {
        $allowed = ['estado', 'observaciones', 'fecha_inicio', 'fecha_fin'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        $responsable = $data['responsable'] ?? null;

        if (empty($fields)) {
            throw new Exception('No hay campos válidos para actualizar.');
        }

        $oldPrep = $this->db->query('SELECT estado FROM preparaciones WHERE id_preparaciones = ?', [$id])->getRow();
        if (!$oldPrep) {
            throw new Exception("Preparación con ID {$id} no encontrada.");
        }
        $oldEstado = (int) $oldPrep->estado;

        $set      = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($fields)));
        $values   = array_values($fields);
        $values[] = $id;

        $this->db->transStart();

        $this->db->query("UPDATE preparaciones SET {$set} WHERE id_preparaciones = ?", $values);

        if (isset($fields['estado'])) {
            $newEstado = (int) $fields['estado'];
            if ($oldEstado !== 3 && $newEstado === 3) {
                $this->_ajustarInventarioPorPreparacion($id, 1, $responsable);
            } elseif ($oldEstado === 3 && $newEstado !== 3) {
                $this->_ajustarInventarioPorPreparacion($id, -1, $responsable);
            }
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new Exception('Error al actualizar la preparación y el inventario.');
        }

        return $this->get_preparacion_by_id($id);
    }

    // ── Costos indirectos por preparación ────────────────────────────────────────

    public function add_costo_indirecto(int $prepId, string $nombre, string $categoria, float $valor): array
    {
        $this->db->query(
            'INSERT INTO preparaciones_costos_indirectos
                (preparaciones_id, costos_indirectos_id, valor_aplicado, nombre, categoria)
             VALUES (?, NULL, ?, ?, ?)',
            [$prepId, $valor, $nombre, $categoria]
        );
        return [
            'id'             => $this->db->insertID(),
            'nombre'         => $nombre,
            'categoria'      => $categoria,
            'valor_aplicado' => $valor,
        ];
    }

    public function update_costo_indirecto(int $id, array $data): bool
    {
        $allowed = ['nombre', 'categoria', 'valor_aplicado'];
        $fields  = array_intersect_key($data, array_flip($allowed));
        if (empty($fields)) return false;
        $set    = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($fields)));
        $values = array_values($fields);
        $values[] = $id;
        $this->db->query("UPDATE preparaciones_costos_indirectos SET {$set} WHERE id = ?", $values);
        return true;
    }

    public function delete_costo_indirecto(int $id): bool
    {
        $this->db->query('DELETE FROM preparaciones_costos_indirectos WHERE id = ?', [$id]);
        return true;
    }

    /**
     * Lista todas las preparaciones de un item.
     */
    public function get_preparaciones_by_item(int $itemId): array
    {
        $rows = $this->db->query(
            'SELECT p.*, u.nombre AS unidad_nombre, u.escala
             FROM preparaciones p
             INNER JOIN unidad u ON u.id_unidad = p.unidad_id
             WHERE p.item_general_id = ?
             ORDER BY p.fecha_creacion DESC',
            [$itemId]
        )->getResult();

        $estados = [0 => 'PENDIENTE', 1 => 'EN_PROCESO', 2 => 'COMPLETADA', 3 => 'CANCELADA'];

        return array_map(fn($r) => [
            'id_preparaciones' => $r->id_preparaciones,
            'unidad_nombre'    => $r->unidad_nombre,
            'escala'           => (float) $r->escala,
            'cantidad'         => (float) $r->cantidad,
            'volumen_galones'  => round((float) $r->cantidad * (float) $r->escala, 4),
            'observaciones'    => $r->observaciones,
            'estado'           => $estados[$r->estado] ?? 'PENDIENTE',
            'fecha_creacion'   => $r->fecha_creacion,
            'fecha_inicio'     => $r->fecha_inicio,
            'fecha_fin'        => $r->fecha_fin,
        ], $rows);
    }
}