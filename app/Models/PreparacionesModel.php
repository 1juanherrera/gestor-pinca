<?php

namespace App\Models;

use Exception;

class PreparacionesModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Crea una orden de preparación y guarda el desglose de materias primas.
     *
     * Payload esperado:
     * {
     *   "item_general_id": 1,
     *   "unidad_id":       3,
     *   "cantidad":        100,     ← volumen TOTAL en galones a producir
     *   "fecha_inicio":    "2025-07-01",   (opcional)
     *   "fecha_fin":       "2025-07-02",   (opcional)
     *   "observaciones":   "...",          (opcional)
     *   "detalle": [                       ← cantidades ya recalculadas desde el frontend
     *     { "item_general_id": 31, "cantidad": 251.89 },
     *     ...
     *   ]
     * }
     */
    public function create_preparacion(array $data): array
    {
        // ── Validación básica ──────────────────────────────────────────────
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

        // ── Obtener escala de la unidad ────────────────────────────────────
        $unidad = $this->db->query(
            'SELECT * FROM unidad WHERE id_unidad = ? AND estados = 1',
            [$unidadId]
        )->getRow();

        if (!$unidad) {
            throw new Exception("Unidad con ID {$unidadId} no encontrada o inactiva.");
        }

        $escala          = (float) $unidad->escala;
        $cantidadEnvases = $escala > 0 ? $volumenGalones / $escala : 0;

        // ── Obtener formulación activa del item ────────────────────────────
        $formulacion = $this->db->query(
            'SELECT id_formulaciones FROM formulaciones
             WHERE item_general_id = ? AND estado = 1 LIMIT 1',
            [$itemId]
        )->getRow();

        if (!$formulacion) {
            throw new Exception("El item no tiene una formulación activa.");
        }

        // ── Obtener ingredientes base de la formulación ────────────────────
        $ingredientes = $this->db->query(
            'SELECT igf.item_general_id, igf.cantidad
             FROM item_general_formulaciones igf
             WHERE igf.formulaciones_id = ?',
            [$formulacion->id_formulaciones]
        )->getResult();

        if (empty($ingredientes)) {
            throw new Exception("La formulación no tiene ingredientes asignados.");
        }

        // ── Detalle precalculado desde el frontend ─────────────────────────
        // Si viene detalle del frontend (cantidades ya recalculadas para el volumen
        // solicitado), lo usamos directamente. Si no, calculamos con factor de volumen.
        $detalleExterno = $data['detalle'] ?? null;

        // Indexar detalle externo por item_general_id para lookup O(1)
        $detalleMap = [];
        if (!empty($detalleExterno) && is_array($detalleExterno)) {
            foreach ($detalleExterno as $d) {
                $detalleMap[(int) $d['item_general_id']] = (float) $d['cantidad'];
            }
        }

        // Si no hay detalle externo, necesitamos el volumen base para calcular factor
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

        // Calcular total para porcentajes
        $totalCantidadBase = array_sum(array_map(fn($i) => (float) $i->cantidad, $ingredientes));

        // ── Transacción ────────────────────────────────────────────────────
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

            // Prioridad: detalle del frontend → fallback: factor calculado
            if (isset($detalleMap[$ingId])) {
                $cantidadEscalada = round($detalleMap[$ingId], 4);
            } else {
                $cantidadEscalada = round((float) $ing->cantidad * $factorVolumen, 4);
            }

            // Porcentaje sobre el total de la formulación base
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

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            $error = $this->db->error();
            log_message('error', 'Preparacion transaction failed: ' . json_encode($error));
            throw new Exception("Error al guardar la preparación: " . ($error['message'] ?? 'error desconocido'));
        }

        return $this->get_preparacion_by_id($preparacionId);
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
                    ig.nombre, ig.codigo
             FROM preparaciones_has_item_general phig
             INNER JOIN item_general ig ON ig.id_item_general = phig.item_general_id
             WHERE phig.preparaciones_id_preparaciones = ?
             ORDER BY phig.item_general_id ASC',
            [$id]
        )->getResult();

        $estados = [0 => 'PENDIENTE', 1 => 'EN_PROCESO', 2 => 'COMPLETADA', 3 => 'CANCELADA'];
        $escala  = (float) $prep->escala;

        return [
            'id_preparaciones' => $prep->id_preparaciones,
            'item_general_id'  => $prep->item_general_id,
            'item_nombre'      => $prep->item_nombre,
            'item_codigo'      => $prep->item_codigo,
            'unidad_id'        => $prep->unidad_id,
            'unidad_nombre'    => $prep->unidad_nombre,
            'escala'           => $escala,
            'cantidad'         => (float) $prep->cantidad,
            'volumen_galones'  => round((float) $prep->cantidad * $escala, 4),
            'observaciones'    => $prep->observaciones,
            'estado'           => $estados[$prep->estado] ?? 'PENDIENTE',
            'fecha_creacion'   => $prep->fecha_creacion,
            'fecha_inicio'     => $prep->fecha_inicio,
            'fecha_fin'        => $prep->fecha_fin,
            'detalle'          => array_map(fn($d) => [
                'item_general_id' => $d->item_general_id,
                'nombre'          => $d->nombre,
                'codigo'          => $d->codigo,
                'cantidad'        => (float) $d->cantidad,
                'porcentajes'     => (float) $d->porcentajes,
            ], $detalle),
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

        if (empty($fields)) {
            throw new Exception('No hay campos válidos para actualizar.');
        }

        $set      = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($fields)));
        $values   = array_values($fields);
        $values[] = $id;

        $this->db->query("UPDATE preparaciones SET {$set} WHERE id_preparaciones = ?", $values);

        return $this->get_preparacion_by_id($id);
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