<?php

namespace App\Models;

use Exception;
use App\Models\MovimientoInventarioModel;
use App\Models\InventarioCapasModel;

class PreparacionesModel extends BaseModel
{
    // Mass-assignment whitelist para la tabla `preparaciones`.
    // Nota: este modelo NO declara $table y hace todos sus inserts/updates con
    // query builder directo (raw SQL + `$this->db->table('...')`), incluyendo
    // inserts cross-table (preparaciones_has_item_general, preparaciones_costos_indirectos,
    // produccion_insumos_detalle). $allowedFields no afecta esos statements; solo
    // protege un eventual save()/insert() del propio modelo contra mass-assignment.
    protected $allowedFields = [
        'fecha_creacion',
        'fecha_inicio',
        'fecha_fin',
        'cantidad',
        'observaciones',
        'estado',
        'item_general_id',
        'formulacion_version_id',
        'unidad_id',
    ];

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

        // Validar que el producto a producir no esté soft-deleted
        $itemActivo = $this->db->query(
            'SELECT id_item_general FROM item_general
             WHERE id_item_general = ? AND deleted_at IS NULL LIMIT 1',
            [$itemId]
        )->getRow();
        if (!$itemActivo) {
            throw new Exception("El item a producir no existe o fue archivado.");
        }

        $formulacion = $this->db->query(
            'SELECT f.id_formulaciones, f.version_actual FROM formulaciones f
             INNER JOIN item_general ig ON ig.id_item_general = f.item_general_id
             WHERE f.item_general_id = ? AND f.estado = 1
               AND ig.deleted_at IS NULL
             LIMIT 1',
            [$itemId]
        )->getRow();

        if (!$formulacion) {
            throw new Exception("El item no tiene una formulación activa.");
        }

        // Capturar la versión exacta de fórmula que se va a usar (snapshot inmutable)
        $formulacionVersionId = null;
        if (!empty($formulacion->version_actual)) {
            $verRow = $this->db->table('formulaciones_versiones')
                ->select('id')
                ->where('formulacion_id', $formulacion->id_formulaciones)
                ->where('version_num', $formulacion->version_actual)
                ->get()->getRowArray();
            $formulacionVersionId = $verRow ? (int) $verRow['id'] : null;
        }

        $ingredientes = $this->db->query(
            'SELECT igf.item_general_id, igf.cantidad
             FROM item_general_formulaciones igf
             INNER JOIN item_general ig ON ig.id_item_general = igf.item_general_id
             WHERE igf.formulaciones_id = ?
               AND ig.deleted_at IS NULL',
            [$formulacion->id_formulaciones]
        )->getResult();

        if (empty($ingredientes)) {
            throw new Exception("La formulación no tiene ingredientes asignados (o fueron archivados).");
        }

        // Detalle precalculado desde el frontend (prioridad)
        $detalleMap = [];
        if (!empty($data['detalle']) && is_array($data['detalle'])) {
            foreach ($data['detalle'] as $d) {
                $detalleMap[(int) $d['item_general_id']] = (float) $d['cantidad'];
            }
        }

        // Selección de capas/proveedor por ingrediente
        $capasSeleccion = [];
        if (!empty($data['detalle']) && is_array($data['detalle'])) {
            foreach ($data['detalle'] as $d) {
                $dItemId = (int) ($d['item_general_id'] ?? 0);
                if ($dItemId && (isset($d['modo_consumo']) || isset($d['capas']) || isset($d['bodega_id']) || isset($d['proveedor_id']))) {
                    $capasSeleccion[$dItemId] = [
                        'modo'        => $d['modo_consumo'] ?? 'FIFO',
                        'capas'       => $d['capas'] ?? [],
                        'bodega_id'   => isset($d['bodega_id'])   ? (int) $d['bodega_id']   : null,
                        'proveedor_id'=> isset($d['proveedor_id']) ? (int) $d['proveedor_id'] : null,
                    ];
                }
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

        $responsable = $data['responsable'] ?? null;

        $this->db->transBegin();
        try {
            $this->db->query(
                'INSERT INTO preparaciones
                    (fecha_creacion, fecha_inicio, fecha_fin, cantidad, observaciones, estado, item_general_id, formulacion_version_id, unidad_id)
                 VALUES (NOW(), ?, ?, ?, ?, 0, ?, ?, ?)',
                [
                    $data['fecha_inicio']  ?? null,
                    $data['fecha_fin']     ?? null,
                    $cantidadEnvases,
                    $data['observaciones'] ?? null,
                    $itemId,
                    $formulacionVersionId,
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

            $this->_ajustarInventarioPorPreparacion($preparacionId, -1, $responsable, $capasSeleccion);

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw new Exception("Error al guardar la preparación: " . $e->getMessage());
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

        if ($multiplicador > 0) {
            // Cancelación: restaurar capas y borrar histórico de costos
            $capasModel->restaurarCapas($prepId);
            $this->db->table('produccion_insumos_detalle')
                ->where('preparacion_id', $prepId)
                ->delete();
        }

        foreach ($ingredientes as $ing) {
            $itemId        = (int) $ing->item_general_id;
            $cantidadAbs   = (float) $ing->cantidad;
            $diff          = $cantidadAbs * $multiplicador;
            $costoUnitario = (float) $ing->costo_unitario;

            if ($diff == 0) continue;

            $seleccion           = $capasSeleccion[$itemId] ?? null;
            $seleccionProveedorId = isset($seleccion['proveedor_id']) ? (int) $seleccion['proveedor_id'] : null;

            // ── Consumo por capas (solo al descontar) ──────────────────────────
            $consumosCapas = [];
            if ($multiplicador < 0 && $capasModel->tieneCapas($itemId)) {
                if ($seleccion && $seleccion['modo'] === 'MANUAL' && !empty($seleccion['capas'])) {
                    $consumosCapas = $capasModel->consumirCapasManual($seleccion['capas'], $itemId);
                    $consumido = array_sum(array_column($consumosCapas, 'cantidad_consumida'));
                    if (abs($consumido - $cantidadAbs) > 0.0001) {
                        throw new Exception(
                            "La selección manual de capas para el ingrediente #{$itemId} no cubre la cantidad requerida. "
                            . "Seleccionado: {$consumido} kg, Requerido: {$cantidadAbs} kg"
                        );
                    }

                } elseif ($seleccionProveedorId) {
                    $consumosCapas = $capasModel->consumirCapasPorProveedor(
                        $itemId, $cantidadAbs, $seleccionProveedorId, $seleccion['bodega_id'] ?? null
                    );
                    $consumido = array_sum(array_column($consumosCapas, 'cantidad_consumida'));
                    if ($consumido < $cantidadAbs - 0.001) {
                        throw new Exception(
                            "Stock insuficiente del proveedor #{$seleccionProveedorId} para el ingrediente #{$itemId}. "
                            . "Disponible: {$consumido} kg, Requerido: {$cantidadAbs} kg"
                        );
                    }

                } else {
                    $consumosCapas = $capasModel->consumirCapasFIFO(
                        $itemId, $cantidadAbs, $seleccion['bodega_id'] ?? null
                    );
                }

                if (!empty($consumosCapas)) {
                    $capasModel->registrarConsumos($prepId, $consumosCapas);
                    $costoReal = array_sum(array_column($consumosCapas, 'costo_total'));
                    $qtyReal   = array_sum(array_column($consumosCapas, 'cantidad_consumida'));
                    $costoUnitario = $qtyReal > 0 ? $costoReal / $qtyReal : $costoUnitario;
                }
            }

            // ── Inventario agregado (compatibilidad) ───────────────────────────
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

            // ── Histórico de costos congelados ─────────────────────────────────
            if ($multiplicador < 0) {
                // Derivar lote_proveedor del snapshot. Si todas las capas consumidas
                // comparten un único lote, lo guardamos directo (caso típico). Si hay
                // varios, queda NULL y la trazabilidad granular se obtiene via JOIN
                // a preparacion_consumo_capas → inventario_capas.
                $loteSnapshot = null;
                if (!empty($consumosCapas)) {
                    $capaIds = array_filter(array_column($consumosCapas, 'capa_id'));
                    if (!empty($capaIds)) {
                        $lotes = $this->db->table('inventario_capas')
                            ->select('lote_proveedor')
                            ->distinct()
                            ->whereIn('id_capa', $capaIds)
                            ->where('lote_proveedor IS NOT NULL')
                            ->get()->getResultArray();
                        if (count($lotes) === 1) {
                            $loteSnapshot = $lotes[0]['lote_proveedor'];
                        }
                    }
                }

                $this->db->table('produccion_insumos_detalle')->insert([
                    'preparacion_id'  => $prepId,
                    'item_general_id' => $itemId,
                    'proveedor_id'    => $seleccionProveedorId,
                    'lote_proveedor'  => $loteSnapshot,
                    'bodega_id'       => $bodegaId,
                    'cantidad'        => $cantidadAbs,
                    'costo_unitario'  => $costoUnitario,
                    'subtotal'        => round($cantidadAbs * $costoUnitario, 4),
                    'created_at'      => date('Y-m-d H:i:s'),
                ]);
            }

            // ── Kardex ────────────────────────────────────────────────────────
            $tipoMovimiento = $multiplicador < 0 ? 'SALIDA' : 'ENTRADA';
            $descripcion    = $multiplicador < 0
                ? "Consumo por orden de producción #{$prepId}"
                : "Reintegro por cancelación de orden #{$prepId}";

            $movimientoModel->registrar([
                'tipo'             => $tipoMovimiento,
                'item_general_id'  => $itemId,
                'bodega_id'        => $bodegaId,
                'cantidad'         => abs($diff),
                'referencia_tipo'  => MovimientoInventarioModel::REF_PRODUCCION,
                'referencia_id'    => $prepId,
                'descripcion'      => $descripcion,
                'costo_unitario'   => $costoUnitario,
                'saldo_anterior'   => $saldoAnterior,
                'saldo_nuevo'      => $saldoNuevo,
                'responsable'      => $responsable,
                'metadata'         => [
                    'preparacion_id' => $prepId,
                    'multiplicador'  => $multiplicador,
                    'subtotal'       => round(abs($diff) * $costoUnitario, 4),
                ],
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
                    u.nombre AS unidad_nombre, u.escala,
                    fv.version_num   AS formulacion_version_num,
                    fv.notas         AS formulacion_version_notas,
                    fv.created_at    AS formulacion_version_fecha,
                    fv.created_by    AS formulacion_version_autor,
                    fv.formulacion_id AS formulacion_id
             FROM preparaciones p
             INNER JOIN item_general ig ON ig.id_item_general = p.item_general_id
             INNER JOIN unidad u ON u.id_unidad = p.unidad_id
             LEFT  JOIN formulaciones_versiones fv ON fv.id = p.formulacion_version_id
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
             LEFT JOIN (
                 SELECT ci1.item_general_id, ci1.costo_unitario
                 FROM costos_item ci1
                 INNER JOIN (
                     SELECT item_general_id, MAX(id_costos_item) AS max_id
                     FROM costos_item
                     GROUP BY item_general_id
                 ) ci_max ON ci_max.item_general_id = ci1.item_general_id
                          AND ci_max.max_id = ci1.id_costos_item
             ) ci ON ci.item_general_id = ig.id_item_general
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

        // Normalizar estado: aceptamos string (PENDIENTE/EN_PROCESO/COMPLETADA/CANCELADA)
        // o entero. Un "(int) 'CANCELADA'" caería a 0 silenciosamente y no
        // dispararía la rama de cancelación más abajo.
        if (isset($fields['estado'])) {
            $estadoMap = ['PENDIENTE' => 0, 'EN_PROCESO' => 1, 'COMPLETADA' => 2, 'CANCELADA' => 3];
            if (is_string($fields['estado']) && !ctype_digit($fields['estado'])) {
                $key = strtoupper(trim($fields['estado']));
                if (!isset($estadoMap[$key])) {
                    throw new Exception("Estado '{$fields['estado']}' inválido. Permitidos: " . implode(', ', array_keys($estadoMap)));
                }
                $fields['estado'] = $estadoMap[$key];
            } else {
                $intEstado = (int) $fields['estado'];
                if (!in_array($intEstado, [0, 1, 2, 3], true)) {
                    throw new Exception("Estado {$intEstado} inválido. Permitidos: 0..3.");
                }
                $fields['estado'] = $intEstado;
            }
        }

        $oldPrep = $this->db->query('SELECT estado FROM preparaciones WHERE id_preparaciones = ?', [$id])->getRow();
        if (!$oldPrep) {
            throw new Exception("Preparación con ID {$id} no encontrada.");
        }
        $oldEstado = (int) $oldPrep->estado;

        $set      = implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($fields)));
        $values   = array_values($fields);
        $values[] = $id;

        $this->db->transBegin();
        try {
            $this->db->query("UPDATE preparaciones SET {$set} WHERE id_preparaciones = ?", $values);

            if (isset($fields['estado'])) {
                $newEstado = (int) $fields['estado'];
                if ($oldEstado !== 3 && $newEstado === 3) {
                    $this->_ajustarInventarioPorPreparacion($id, 1, $responsable);
                } elseif ($oldEstado === 3 && $newEstado !== 3) {
                    $this->_ajustarInventarioPorPreparacion($id, -1, $responsable);
                }
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw new Exception('Error al actualizar la preparación: ' . $e->getMessage());
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