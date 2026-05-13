<?php

namespace App\Models;

use Exception;

class RequisicionesCompraModel extends BaseModel
{
    protected $table      = 'requisiciones_compra';
    protected $primaryKey = 'id_requisicion';
    protected $allowedFields = [
        'preparacion_id', 'item_general_id', 'item_proveedor_id', 'proveedor_id',
        'cantidad_necesaria', 'cantidad_disponible', 'cantidad_solicitada',
        'precio_unitario', 'estado', 'observaciones', 'orden_compra_id', 'fecha_creacion',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Calcula la disponibilidad de materiales para una preparación potencial.
     * Recibe: item_general_id (producto final), cantidad (en unidad dada), unidad_id.
     * Retorna cada ingrediente con su stock actual y proveedores disponibles para los déficits.
     */
    public function verificarDisponibilidad(int $itemId, float $volumenGalones, int $unidadId): array
    {
        $unidad = $this->db->query(
            'SELECT escala FROM unidad WHERE id_unidad = ? AND estados = 1',
            [$unidadId]
        )->getRow();

        if (!$unidad) {
            throw new Exception("Unidad con ID {$unidadId} no encontrada o inactiva.");
        }

        $formulacion = $this->db->query(
            'SELECT id_formulaciones FROM formulaciones WHERE item_general_id = ? AND estado = 1 LIMIT 1',
            [$itemId]
        )->getRow();

        if (!$formulacion) {
            throw new Exception("El item no tiene una formulación activa.");
        }

        $ingredientes = $this->db->query(
            'SELECT igf.item_general_id, igf.cantidad AS cantidad_base,
                    ig.nombre, ig.codigo
             FROM item_general_formulaciones igf
             INNER JOIN item_general ig ON ig.id_item_general = igf.item_general_id
             WHERE igf.formulaciones_id = ?',
            [$formulacion->id_formulaciones]
        )->getResult();

        if (empty($ingredientes)) {
            throw new Exception("La formulación no tiene ingredientes.");
        }

        $itemCosto = $this->db->query(
            'SELECT COALESCE(NULLIF(volumen, 0), 1) AS volumen_base FROM costos_item WHERE item_general_id = ? LIMIT 1',
            [$itemId]
        )->getRow();

        $volumenBase   = (float) ($itemCosto->volumen_base ?? 1);
        $factorVolumen = $volumenBase > 0 ? $volumenGalones / $volumenBase : 1;

        $resultado = [];
        $todosDisponibles = true;

        foreach ($ingredientes as $ing) {
            $ingId           = (int) $ing->item_general_id;
            $cantidadNecesaria = round((float) $ing->cantidad_base * $factorVolumen, 4);

            $stock = $this->db->query(
                'SELECT COALESCE(SUM(cantidad), 0) AS total FROM inventario WHERE item_general_id = ?',
                [$ingId]
            )->getRow();

            $cantidadDisponible = (float) ($stock->total ?? 0);
            $deficit            = max(0, $cantidadNecesaria - $cantidadDisponible);
            $tieneDeficit       = $deficit > 0;

            if ($tieneDeficit) {
                $todosDisponibles = false;
            }

            $proveedores = [];
            if ($tieneDeficit) {
                $proveedores = $this->_getProveedoresPorItem($ingId);
            }

            $resultado[] = [
                'item_general_id'    => $ingId,
                'nombre'             => $ing->nombre,
                'codigo'             => $ing->codigo,
                'cantidad_necesaria' => $cantidadNecesaria,
                'cantidad_disponible'=> $cantidadDisponible,
                'deficit'            => round($deficit, 4),
                'tiene_deficit'      => $tieneDeficit,
                'proveedores'        => $proveedores,
            ];
        }

        return [
            'todos_disponibles' => $todosDisponibles,
            'materiales'        => $resultado,
        ];
    }

    /**
     * Retorna proveedores que venden un item_general específico, con precio y empaque.
     */
    private function _getProveedoresPorItem(int $itemGeneralId): array
    {
        $rows = $this->db->query(
            'SELECT ip.id_item_proveedor, ip.nombre AS item_proveedor_nombre,
                    uc.nombre AS unidad_empaque, ip.precio_unitario, ip.precio_con_iva,
                    p.id_proveedor, p.nombre_empresa, p.nombre_encargado,
                    p.telefono, p.email
             FROM item_proveedor ip
             INNER JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
             LEFT  JOIN unidad   uc ON uc.id_unidad   = ip.unidad_compra_id
             WHERE ip.item_general_id = ? AND ip.disponible = 1
             ORDER BY ip.precio_unitario ASC',
            [$itemGeneralId]
        )->getResult();

        return array_map(fn($r) => [
            'id_item_proveedor'     => $r->id_item_proveedor,
            'item_proveedor_nombre' => $r->item_proveedor_nombre,
            'unidad_empaque'        => $r->unidad_empaque,
            'precio_unitario'       => (float) $r->precio_unitario,
            'precio_con_iva'        => (float) $r->precio_con_iva,
            'id_proveedor'          => $r->id_proveedor,
            'nombre_empresa'        => $r->nombre_empresa,
            'nombre_encargado'      => $r->nombre_encargado,
            'telefono'              => $r->telefono,
            'email'                 => $r->email,
        ], $rows);
    }

    /**
     * Crea una o varias requisiciones ligadas a una preparación.
     * Cada elemento del array debe tener: preparacion_id, item_general_id,
     * item_proveedor_id, proveedor_id, cantidad_necesaria, cantidad_disponible,
     * cantidad_solicitada, precio_unitario (opcional), observaciones (opcional).
     */
    public function crearRequisiciones(array $items, string $estadoInicial = 'PENDIENTE'): array
    {
        if (empty($items)) {
            throw new Exception('Debe enviar al menos una requisición.');
        }

        $estadosValidos = ['SUGERIDA', 'PENDIENTE', 'APROBADA'];
        if (!in_array($estadoInicial, $estadosValidos, true)) {
            $estadoInicial = 'PENDIENTE';
        }

        $created = [];
        $now = date('Y-m-d H:i:s');

        $this->db->transStart();

        foreach ($items as $item) {
            foreach (['preparacion_id', 'item_general_id', 'cantidad_solicitada'] as $f) {
                if (empty($item[$f])) {
                    throw new Exception("Campo requerido faltante: {$f}");
                }
            }

            $this->db->query(
                'INSERT INTO requisiciones_compra
                    (preparacion_id, item_general_id, item_proveedor_id, proveedor_id,
                     cantidad_necesaria, cantidad_disponible, cantidad_solicitada,
                     precio_unitario, estado, observaciones, fecha_creacion)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    (int)   $item['preparacion_id'],
                    (int)   $item['item_general_id'],
                    isset($item['item_proveedor_id']) ? (int) $item['item_proveedor_id'] : null,
                    isset($item['proveedor_id'])      ? (int) $item['proveedor_id']      : null,
                    (float) ($item['cantidad_necesaria']  ?? 0),
                    (float) ($item['cantidad_disponible'] ?? 0),
                    (float) $item['cantidad_solicitada'],
                    isset($item['precio_unitario']) ? (float) $item['precio_unitario'] : null,
                    $estadoInicial,
                    $item['observaciones'] ?? null,
                    $now,
                ]
            );

            $created[] = $this->db->insertID();
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new Exception('Error al guardar las requisiciones.');
        }

        return $this->listarPorIds($created);
    }

    /**
     * MRP automático: simula la producción de un item para detectar déficit
     * de materias primas y genera requisiciones SUGERIDA con el mejor proveedor
     * por cada MP faltante.
     *
     * @param int   $itemId          Item a producir
     * @param float $volumenGalones  Volumen objetivo
     * @param int   $unidadId        Unidad del volumen
     * @param int|null $preparacionId Si se vincula a una prep ya creada (opcional)
     *
     * @return array {
     *   creadas: array — requisiciones generadas
     *   sin_proveedor: array — MPs en déficit que NO tienen proveedor activo (manual)
     *   sin_deficit: bool — true si todo estaba disponible (no se creó nada)
     * }
     */
    public function sugerirRequisicionesMRP(int $itemId, float $volumenGalones, int $unidadId, ?int $preparacionId = null): array
    {
        $disponibilidad = $this->verificarDisponibilidad($itemId, $volumenGalones, $unidadId);

        if (!empty($disponibilidad['todos_disponibles'])) {
            return ['creadas' => [], 'sin_proveedor' => [], 'sin_deficit' => true];
        }

        $items         = [];
        $sinProveedor  = [];

        foreach ($disponibilidad['materiales'] as $mat) {
            if (!$mat['tiene_deficit']) continue;

            // Elegir el proveedor con mejor precio por kg.
            // verificarDisponibilidad ya retorna proveedores ordenados por precio_unitario ASC,
            // pero la métrica correcta es precio/kg → factor_conversion. Lo recalculamos.
            $mejor = $this->_pickMejorProveedor($mat['item_general_id']);

            if (!$mejor) {
                $sinProveedor[] = [
                    'item_general_id' => $mat['item_general_id'],
                    'nombre'          => $mat['nombre'],
                    'codigo'          => $mat['codigo'],
                    'deficit'         => $mat['deficit'],
                ];
                continue;
            }

            $items[] = [
                'preparacion_id'      => $preparacionId ?? 0,
                'item_general_id'     => $mat['item_general_id'],
                'item_proveedor_id'   => $mejor['id_item_proveedor'],
                'proveedor_id'        => $mejor['id_proveedor'],
                'cantidad_necesaria'  => $mat['cantidad_necesaria'],
                'cantidad_disponible' => $mat['cantidad_disponible'],
                'cantidad_solicitada' => $mat['deficit'],
                'precio_unitario'     => $mejor['precio_unitario'],
                'observaciones'       => "Sugerida automáticamente por MRP. Mejor precio/kg: $" . number_format($mejor['precio_kg'], 2),
            ];
        }

        $creadas = [];
        if (!empty($items)) {
            // Si no hay preparación previa, usamos preparacion_id=0 — el campo es NOT NULL
            // pero la columna soporta el valor 0 (sin lock referencial). Si en el futuro
            // se pone FK strict, habría que crear placeholder.
            $creadas = $this->crearRequisiciones($items, 'SUGERIDA');
        }

        return [
            'creadas'       => $creadas,
            'sin_proveedor' => $sinProveedor,
            'sin_deficit'   => false,
        ];
    }

    /**
     * Devuelve el proveedor con mejor precio_unitario / factor_conversion
     * (precio por kg en unidad base) para un item. NULL si no hay activos.
     */
    private function _pickMejorProveedor(int $itemGeneralId): ?array
    {
        $row = $this->db->query(
            'SELECT ip.id_item_proveedor,
                    ip.precio_unitario,
                    ip.factor_conversion,
                    p.id_proveedor,
                    p.nombre_empresa,
                    CASE WHEN ip.factor_conversion > 0
                         THEN ip.precio_unitario / ip.factor_conversion
                         ELSE ip.precio_unitario END AS precio_kg
             FROM item_proveedor ip
             JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
             WHERE ip.item_general_id = ? AND ip.disponible = 1
             ORDER BY precio_kg ASC
             LIMIT 1',
            [$itemGeneralId]
        )->getRow();

        if (!$row) return null;
        return [
            'id_item_proveedor' => (int) $row->id_item_proveedor,
            'id_proveedor'      => (int) $row->id_proveedor,
            'nombre_empresa'    => $row->nombre_empresa,
            'precio_unitario'   => (float) $row->precio_unitario,
            'factor_conversion' => (float) $row->factor_conversion,
            'precio_kg'         => (float) $row->precio_kg,
        ];
    }

    /**
     * Lista todas las requisiciones con sus joins.
     */
    public function listar(string $estado = null): array
    {
        $sql = '
            SELECT rc.*,
                   ig.nombre  AS item_nombre,  ig.codigo  AS item_codigo,
                   ip.nombre  AS item_proveedor_nombre, uc.nombre AS unidad_empaque,
                   p.nombre_empresa, p.nombre_encargado,
                   prep.item_general_id AS prep_item_id,
                   prod.nombre AS prep_item_nombre
            FROM requisiciones_compra rc
            INNER JOIN item_general ig   ON ig.id_item_general   = rc.item_general_id
            LEFT  JOIN item_proveedor ip ON ip.id_item_proveedor  = rc.item_proveedor_id
            LEFT  JOIN proveedor p       ON p.id_proveedor        = rc.proveedor_id
            LEFT  JOIN unidad uc         ON uc.id_unidad          = ip.unidad_compra_id
            LEFT  JOIN preparaciones prep ON prep.id_preparaciones = rc.preparacion_id
            LEFT  JOIN item_general prod  ON prod.id_item_general  = prep.item_general_id
        ';

        $params = [];
        if ($estado) {
            $sql   .= ' WHERE rc.estado = ?';
            $params[] = $estado;
        }

        $sql .= ' ORDER BY rc.fecha_creacion DESC';

        return array_map(
            fn($r) => $this->_formatRow($r),
            $this->db->query($sql, $params)->getResult()
        );
    }

    /**
     * Lista requisiciones de una preparación específica.
     */
    public function listarPorPreparacion(int $prepId): array
    {
        $rows = $this->db->query(
            'SELECT rc.*,
                    ig.nombre AS item_nombre, ig.codigo AS item_codigo,
                    ip.nombre AS item_proveedor_nombre, uc.nombre AS unidad_empaque,
                    p.nombre_empresa, p.nombre_encargado
             FROM requisiciones_compra rc
             INNER JOIN item_general ig   ON ig.id_item_general   = rc.item_general_id
             LEFT  JOIN item_proveedor ip ON ip.id_item_proveedor  = rc.item_proveedor_id
             LEFT  JOIN proveedor p       ON p.id_proveedor        = rc.proveedor_id
             LEFT  JOIN unidad uc         ON uc.id_unidad          = ip.unidad_compra_id
             WHERE rc.preparacion_id = ?
             ORDER BY rc.fecha_creacion ASC',
            [$prepId]
        )->getResult();

        return array_map(fn($r) => $this->_formatRow($r), $rows);
    }

    /**
     * Actualiza el estado de una requisición.
     * Estados válidos: PENDIENTE, APROBADA, CANCELADA (CONVERTIDA solo vía convertirAOC).
     */
    public function actualizarEstado(int $id, string $estado): array
    {
        $validos = ['SUGERIDA', 'PENDIENTE', 'APROBADA', 'CANCELADA'];
        if (!in_array($estado, $validos)) {
            throw new Exception("Estado inválido. Valores permitidos: " . implode(', ', $validos));
        }

        $req = $this->db->query(
            'SELECT * FROM requisiciones_compra WHERE id_requisicion = ?', [$id]
        )->getRow();

        if (!$req) {
            throw new Exception("Requisición con ID {$id} no encontrada.");
        }

        if ($req->estado === 'CONVERTIDA') {
            throw new Exception("No se puede modificar una requisición ya convertida a OC.");
        }

        $this->db->query(
            'UPDATE requisiciones_compra SET estado = ? WHERE id_requisicion = ?',
            [$estado, $id]
        );

        return $this->_formatRow(
            $this->db->query(
                'SELECT rc.*, ig.nombre AS item_nombre, ig.codigo AS item_codigo,
                        ip.nombre AS item_proveedor_nombre, uc.nombre AS unidad_empaque,
                        p.nombre_empresa, p.nombre_encargado
                 FROM requisiciones_compra rc
                 INNER JOIN item_general ig   ON ig.id_item_general  = rc.item_general_id
                 LEFT  JOIN item_proveedor ip ON ip.id_item_proveedor = rc.item_proveedor_id
                 LEFT  JOIN proveedor p       ON p.id_proveedor       = rc.proveedor_id
                 LEFT  JOIN unidad uc         ON uc.id_unidad         = ip.unidad_compra_id
                 WHERE rc.id_requisicion = ?',
                [$id]
            )->getRow()
        );
    }

    /**
     * Convierte una o varias requisiciones APROBADAS en una Orden de Compra.
     * Agrupa por proveedor automáticamente si los ids corresponden al mismo proveedor.
     * Devuelve las órdenes de compra creadas.
     */
    public function convertirAOC(array $ids, int $bodegaId, string $observaciones = null): array
    {
        if (empty($ids)) {
            throw new Exception('Debe enviar al menos una requisición.');
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $requisiciones = $this->db->query(
            "SELECT rc.*, ip.precio_unitario AS precio_ip, ip.nombre AS item_proveedor_nombre
             FROM requisiciones_compra rc
             LEFT JOIN item_proveedor ip ON ip.id_item_proveedor = rc.item_proveedor_id
             WHERE rc.id_requisicion IN ({$placeholders})",
            $ids
        )->getResult();

        if (count($requisiciones) !== count($ids)) {
            throw new Exception('Una o más requisiciones no fueron encontradas.');
        }

        foreach ($requisiciones as $r) {
            if ($r->estado !== 'APROBADA') {
                throw new Exception("La requisición #{$r->id_requisicion} debe estar en estado APROBADA para convertir.");
            }
            if (!$r->proveedor_id) {
                throw new Exception("La requisición #{$r->id_requisicion} no tiene proveedor asignado.");
            }
        }

        // Agrupar por proveedor
        $grupos = [];
        foreach ($requisiciones as $r) {
            $grupos[$r->proveedor_id][] = $r;
        }

        $this->db->transStart();

        $ocCreadas = [];

        foreach ($grupos as $proveedorId => $reqs) {
            // Generar número de OC
            $ultimo = $this->db->query(
                'SELECT numero FROM ordenes_compra ORDER BY id_orden DESC LIMIT 1'
            )->getRow();
            $num   = $ultimo ? (int) substr($ultimo->numero, 3) + 1 : 1;
            $numOC = 'OC-' . str_pad($num, 3, '0', STR_PAD_LEFT);

            $this->db->query(
                'INSERT INTO ordenes_compra (numero, proveedor_id, bodegas_id, fecha, estado, total, observaciones)
                 VALUES (?, ?, ?, NOW(), \'Borrador\', 0, ?)',
                [$numOC, $proveedorId, $bodegaId, $observaciones]
            );
            $ocId = $this->db->insertID();

            $total = 0;

            foreach ($reqs as $r) {
                $precio   = (float) ($r->item_proveedor_id ? $r->precio_ip : ($r->precio_unitario ?? 0));
                $subtotal = round($r->cantidad_solicitada * $precio, 2);
                $total   += $subtotal;

                $this->db->query(
                    'INSERT INTO ordenes_compra_detalle
                        (ordenes_compra_id, item_proveedor_id, item_general_id, cantidad, precio_unit, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?)',
                    [$ocId, $r->item_proveedor_id, $r->item_general_id,
                     $r->cantidad_solicitada, $precio, $subtotal]
                );

                // Marcar requisición como convertida
                $this->db->query(
                    'UPDATE requisiciones_compra SET estado = \'CONVERTIDA\', orden_compra_id = ? WHERE id_requisicion = ?',
                    [$ocId, $r->id_requisicion]
                );
            }

            // Actualizar total de la OC
            $this->db->query('UPDATE ordenes_compra SET total = ? WHERE id_orden = ?', [$total, $ocId]);

            $ocCreadas[] = $ocId;
        }

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new Exception('Error al convertir las requisiciones a órdenes de compra.');
        }

        return $ocCreadas;
    }

    private function listarPorIds(array $ids): array
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->query(
            "SELECT rc.*, ig.nombre AS item_nombre, ig.codigo AS item_codigo,
                    ip.nombre AS item_proveedor_nombre, uc.nombre AS unidad_empaque,
                    p.nombre_empresa, p.nombre_encargado
             FROM requisiciones_compra rc
             INNER JOIN item_general ig   ON ig.id_item_general   = rc.item_general_id
             LEFT  JOIN item_proveedor ip ON ip.id_item_proveedor  = rc.item_proveedor_id
             LEFT  JOIN proveedor p       ON p.id_proveedor        = rc.proveedor_id
             LEFT  JOIN unidad uc         ON uc.id_unidad          = ip.unidad_compra_id
             WHERE rc.id_requisicion IN ({$placeholders})",
            $ids
        )->getResult();

        return array_map(fn($r) => $this->_formatRow($r), $rows);
    }

    private function _formatRow(object $r): array
    {
        return [
            'id_requisicion'        => (int)   $r->id_requisicion,
            'preparacion_id'        => (int)   $r->preparacion_id,
            'item_general_id'       => (int)   $r->item_general_id,
            'item_nombre'           => $r->item_nombre ?? null,
            'item_codigo'           => $r->item_codigo ?? null,
            'item_proveedor_id'     => $r->item_proveedor_id ? (int) $r->item_proveedor_id : null,
            'item_proveedor_nombre' => $r->item_proveedor_nombre ?? null,
            'unidad_empaque'        => $r->unidad_empaque ?? null,
            'proveedor_id'          => $r->proveedor_id ? (int) $r->proveedor_id : null,
            'nombre_empresa'        => $r->nombre_empresa ?? null,
            'nombre_encargado'      => $r->nombre_encargado ?? null,
            'cantidad_necesaria'    => (float) $r->cantidad_necesaria,
            'cantidad_disponible'   => (float) $r->cantidad_disponible,
            'cantidad_solicitada'   => (float) $r->cantidad_solicitada,
            'precio_unitario'       => $r->precio_unitario !== null ? (float) $r->precio_unitario : null,
            'estado'                => $r->estado,
            'observaciones'         => $r->observaciones,
            'orden_compra_id'       => $r->orden_compra_id ? (int) $r->orden_compra_id : null,
            'fecha_creacion'        => $r->fecha_creacion,
        ];
    }
}
