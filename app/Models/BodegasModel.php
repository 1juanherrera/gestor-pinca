<?php
namespace App\Models;
use Exception;

class BodegasModel extends BaseModel
{
    protected $table = 'bodegas';
    protected $primaryKey = 'id_bodegas';
    protected $allowedFields = [
        "nombre",
        "descripcion",
        "estado",
        "instalaciones_id",
    ];

    public function __construct(){

        parent::__construct();
    }

    public function bodega_inventario($id_bodega = null, $page = 1, $perPage = 10, $search = '', $tipo = '')
    {
        if ($id_bodega === null) return null;

        $bodega = $this->db->query('SELECT * FROM bodegas WHERE id_bodegas = ?', [$id_bodega])->getRow();

        if ($bodega) {
            $perPage = (int)$perPage;
            $page    = (int)$page;
            $offset  = ($page - 1) * $perPage;

            $params = [$id_bodega];
            $whereConditions = " WHERE inv.bodegas_id = ? ";

            if (!empty($search)) {
                $whereConditions .= " AND (ig.nombre LIKE ? OR ig.codigo LIKE ?) ";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if ($tipo !== '' && $tipo !== null) {
                $whereConditions .= " AND ig.tipo = ? ";
                $params[] = $tipo;
            }

            $countSql = "SELECT COUNT(*) as total 
                        FROM inventario inv 
                        JOIN item_general ig ON inv.item_general_id = ig.id_item_general 
                        $whereConditions";
            $totalItems = $this->db->query($countSql, $params)->getRow()->total;

            $sql = "SELECT 
                        ig.id_item_general, ig.nombre, ig.codigo, 
                        inv.cantidad, ig.tipo, ca.nombre AS categoria,
                        u.nombre AS unidad, c.costo_mp_galon, c.precio_venta,
                        u.id_unidad AS unidad_id, ca.id_categoria AS categoria_id,
                        f.id_formulaciones AS formulacion_id
                    FROM inventario inv
                    JOIN item_general ig ON inv.item_general_id = ig.id_item_general
                    LEFT JOIN costos_item c ON c.item_general_id = ig.id_item_general
                    LEFT JOIN categoria ca ON ig.categoria_id = ca.id_categoria
                    LEFT JOIN unidad u ON ig.unidad_id = u.id_unidad
                    LEFT JOIN formulaciones f 
                        ON f.id_formulaciones = (
                            SELECT id_formulaciones 
                            FROM formulaciones 
                            WHERE item_general_id = ig.id_item_general 
                            AND estado = 1 
                            LIMIT 1
                        )
                    $whereConditions 
                    LIMIT $perPage OFFSET $offset";

            $inventario = $this->db->query($sql, $params)->getResult();

            $db = $this->db; // 👈 extraer antes del array_map

            $inventario = array_map(function ($item) use ($db) {
                $item = (array) $item;
                $item['formulacion_id'] = $item['formulacion_id'] !== null
                    ? (int) $item['formulacion_id']
                    : null;

                if ($item['formulacion_id'] !== null) {

                    $itemData = $db->query('
                        SELECT 
                            ig.viscosidad, ig.p_g, ig.color, ig.secado, ig.cubrimiento, ig.brillo_60,
                            COALESCE(NULLIF(ci.volumen, 0), 1) AS volumen_base,
                            COALESCE(ci.envase, 0)             AS envase,
                            COALESCE(ci.etiqueta, 0)           AS etiqueta,
                            COALESCE(ci.bandeja, 0)            AS bandeja,
                            COALESCE(ci.plastico, 0)           AS plastico,
                            COALESCE(ci.costo_mod, 0)          AS costo_mod,
                            COALESCE(ci.porcentaje_utilidad, 50) AS porcentaje_utilidad
                        FROM item_general ig
                        LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
                        WHERE ig.id_item_general = ?
                    ', [$item['id_item_general']])->getRow();

                    $materiasPrimas = $db->query('
                        SELECT
                            igf.id_item_general_formulaciones,
                            igf.formulaciones_id,
                            igf.item_general_id             AS materia_prima_id,
                            igf.cantidad,
                            ig.nombre                       AS nombre,
                            ig.codigo                       AS codigo,
                            COALESCE(ci.costo_unitario, 0)  AS costo_unitario,
                            (igf.cantidad * COALESCE(ci.costo_unitario, 0)) AS costo_total,
                            (
                                SELECT COALESCE(SUM(i.cantidad), 0)
                                FROM inventario i
                                WHERE i.item_general_id = ig.id_item_general
                            ) AS inventario_cantidad
                        FROM item_general_formulaciones igf
                        INNER JOIN item_general ig ON igf.item_general_id = ig.id_item_general
                        LEFT JOIN costos_item ci   ON ig.id_item_general  = ci.item_general_id
                        WHERE igf.formulaciones_id = ?
                        ORDER BY ig.nombre ASC
                    ', [$item['formulacion_id']])->getResult();

                    $item['formulacion'] = [
                        'item' => [
                            'viscosidad'          => $itemData->viscosidad,
                            'p_g'                 => $itemData->p_g,
                            'color'               => $itemData->color,
                            'secado'              => $itemData->secado,
                            'cubrimiento'         => $itemData->cubrimiento,
                            'brillo_60'           => $itemData->brillo_60,
                            'volumen_base'        => (float) $itemData->volumen_base,
                            'envase'              => (float) $itemData->envase,
                            'etiqueta'            => (float) $itemData->etiqueta,
                            'bandeja'             => (float) $itemData->bandeja,
                            'plastico'            => (float) $itemData->plastico,
                            'costo_mod'           => (float) $itemData->costo_mod,
                            'porcentaje_utilidad' => (float) $itemData->porcentaje_utilidad,
                        ],
                        'materias_primas' => array_map(function ($mp) {
                            return [
                                'id'                  => (int)  $mp->id_item_general_formulaciones,
                                'formulaciones_id'    => (int)  $mp->formulaciones_id,
                                'materia_prima_id'    => (int)  $mp->materia_prima_id,
                                'nombre'              =>        $mp->nombre,
                                'codigo'              =>        $mp->codigo,
                                'cantidad'            => (float) $mp->cantidad,
                                'costo_unitario'      => (float) $mp->costo_unitario,
                                'costo_total'         => (float) $mp->costo_total,
                                'inventario_cantidad' => (float) $mp->inventario_cantidad,
                            ];
                        }, $materiasPrimas)
                    ];

                } else {
                    $item['formulacion'] = null;
                }

                return $item;
            }, $inventario);

            return [
                'id_bodegas'       => $bodega->id_bodegas,
                'nombre'           => $bodega->nombre,
                'instalaciones_id' => $bodega->instalaciones_id,
                'inventario'       => $inventario,
                'pagination'       => [
                    'totalItems'  => (int)$totalItems,
                    'totalPages'  => ceil($totalItems / $perPage),
                    'currentPage' => $page,
                    'perPage'     => $perPage
                ]
            ];
        }
        return null;
    }

    public function createItemDesdeBodega(array $data): array
    {
        if (empty($data['nombre']) || empty($data['codigo'])) {
            throw new Exception('nombre y codigo son obligatorios.');
        }
        if (empty($data['bodega_id'])) {
            throw new Exception('bodega_id es obligatorio.');
        }

        $this->db->transStart();

        try {
            // 1. INSERT item_general
            $this->db->query('
                INSERT INTO item_general 
                    (nombre, codigo, tipo, categoria_id, unidad_id, viscosidad, p_g, color, brillo_60, secado, cubrimiento, molienda, ph, poder_tintoreo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ', [
                $data['nombre'],
                $data['codigo'],
                $data['tipo'],
                $data['categoria_id']   ?? null,
                $data['unidad_id']      ?? null,
                $data['viscosidad']     ?? null,
                $data['p_g']            ?? null,
                $data['color']          ?? null,
                $data['brillo_60']      ?? null,
                $data['secado']         ?? null,
                $data['cubrimiento']    ?? null,
                $data['molienda']       ?? null,
                $data['ph']             ?? null,
                $data['poder_tintoreo'] ?? null,
            ]);

            $itemId = $this->db->insertID();

            // 2. INSERT costos_item
            $this->db->query('
                INSERT INTO costos_item (item_general_id, costo_unitario, envase, etiqueta, plastico)
                VALUES (?, ?, ?, ?, ?)
            ', [
                $itemId,
                $data['costo_unitario'] ?? 0,
                $data['envase']         ?? 0,
                $data['etiqueta']       ?? 0,
                $data['plastico']       ?? 0,
            ]);

            // 3. INSERT inventario
            $this->db->query('
                INSERT INTO inventario (item_general_id, bodegas_id, cantidad)
                VALUES (?, ?, ?)
            ', [
                $itemId,
                $data['bodega_id'],
                $data['cantidad'] ?? 0,
            ]);

            // 4. INSERT formulación + materias primas (solo si es producto tipo 0)
            if (!empty($data['formulaciones']) && is_array($data['formulaciones'])) {

                // Crear cabecera de formulación
                $this->db->query('
                    INSERT INTO formulaciones (item_general_id, nombre, estado)
                    VALUES (?, ?, 1)
                ', [
                    $itemId,
                    $data['nombre']
                ]);

                $formulacionId = $this->db->insertID();

                // Insertar cada materia prima
                foreach ($data['formulaciones'] as $mp) {
                    if (empty($mp['materia_prima_id'])) continue;

                    $this->db->query('
                        INSERT INTO item_general_formulaciones (formulaciones_id, item_general_id, cantidad)
                        VALUES (?, ?, ?)
                    ', [
                        $formulacionId,
                        $mp['materia_prima_id'],
                        $mp['cantidad'] ?? 0,
                    ]);

                    // Actualizar costo_unitario de la materia prima si viene
                    if (!empty($mp['costo_unitario'])) {
                        $this->db->query('
                            UPDATE costos_item SET costo_unitario = ?
                            WHERE item_general_id = ?
                        ', [
                            $mp['costo_unitario'],
                            $mp['materia_prima_id']
                        ]);
                    }
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Error al guardar el item.');
            }

            return [
                'success' => true,
                'message' => 'Item creado correctamente.',
                'id'      => $itemId
            ];

        } catch (Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
    public function updateItemDesdeBodega(int $itemId, array $data): array
    {

        if (empty($data['nombre']) || empty($data['codigo'])) {
            throw new Exception('nombre y codigo son obligatorios.');
        }
        if (empty($data['bodega_id'])) {
            throw new Exception('bodega_id es obligatorio.');
        }

        $this->db->transStart();

        try {
            // 1. UPDATE item_general
            $this->db->query('
                UPDATE item_general SET
                    nombre        = ?,
                    codigo        = ?,
                    tipo          = ?,
                    categoria_id  = ?,
                    unidad_id     = ?,
                    viscosidad    = ?,
                    p_g           = ?,
                    color         = ?,
                    brillo_60     = ?,
                    secado        = ?,
                    cubrimiento   = ?,
                    molienda      = ?,
                    ph            = ?,
                    poder_tintoreo = ?
                WHERE id_item_general = ?
            ', [
                $data['nombre'],
                $data['codigo'],
                $data['tipo'],
                $data['categoria_id'],
                $data['unidad_id'],
                $data['viscosidad']    ?? null,
                $data['p_g']           ?? null,
                $data['color']         ?? null,
                $data['brillo_60']     ?? null,
                $data['secado']        ?? null,
                $data['cubrimiento']   ?? null,
                $data['molienda']      ?? null,
                $data['ph']            ?? null,
                $data['poder_tintoreo'] ?? null,
                $itemId
            ]);

            // 2. UPSERT costos_item
            $costoExiste = $this->db->query('
                SELECT id_costos_item FROM costos_item WHERE item_general_id = ?
            ', [$itemId])->getRow();

            if ($costoExiste) {
                $this->db->query('
                    UPDATE costos_item SET
                        costo_unitario = ?,
                        envase         = ?,
                        etiqueta       = ?,
                        plastico       = ?
                    WHERE item_general_id = ?
                ', [
                    $data['costo_unitario'] ?? 0,
                    $data['envase']         ?? 0,
                    $data['etiqueta']       ?? 0,
                    $data['plastico']       ?? 0,
                    $itemId
                ]);
            } else {
                $this->db->query('
                    INSERT INTO costos_item (item_general_id, costo_unitario, envase, etiqueta, plastico)
                    VALUES (?, ?, ?, ?, ?)
                ', [
                    $itemId,
                    $data['costo_unitario'] ?? 0,
                    $data['envase']         ?? 0,
                    $data['etiqueta']       ?? 0,
                    $data['plastico']       ?? 0,
                ]);
            }

            // 3. UPSERT inventario
            $inventarioExiste = $this->db->query('
                SELECT id_inventario FROM inventario 
                WHERE item_general_id = ? AND bodegas_id = ?
            ', [$itemId, $data['bodega_id']])->getRow();

            if ($inventarioExiste) {
                $this->db->query('
                    UPDATE inventario SET cantidad = ?
                    WHERE item_general_id = ? AND bodegas_id = ?
                ', [
                    $data['cantidad'] ?? 0,
                    $itemId,
                    $data['bodega_id']
                ]);
            } else {
                $this->db->query('
                    INSERT INTO inventario (item_general_id, bodegas_id, cantidad)
                    VALUES (?, ?, ?)
                ', [
                    $itemId,
                    $data['bodega_id'],
                    $data['cantidad'] ?? 0,
                ]);
            }

            // 4. UPDATE materias primas (cantidad y costo_total)
            if (!empty($data['formulaciones']) && is_array($data['formulaciones'])) {
                foreach ($data['formulaciones'] as $mp) {
                    if (empty($mp['id'])) continue;

                    // Actualizar cantidad en item_general_formulaciones
                    $this->db->query('
                        UPDATE item_general_formulaciones SET cantidad = ?
                        WHERE id_item_general_formulaciones = ?
                    ', [
                        $mp['cantidad'] ?? 0,
                        $mp['id']
                    ]);

                    // Usar materia_prima_id que ya viene del frontend (evita SELECT extra)
                    if (!empty($mp['materia_prima_id'])) {
                        $this->db->query('
                            UPDATE item_general SET nombre = ?
                            WHERE id_item_general = ?
                        ', [$mp['nombre'] ?? '', $mp['materia_prima_id']]);

                        $this->db->query('
                            UPDATE costos_item SET costo_unitario = ?
                            WHERE item_general_id = ?
                        ', [$mp['costo_unitario'] ?? 0, $mp['materia_prima_id']]);
                    }
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Error al guardar los cambios.');
            }

            return ['success' => true, 'message' => 'Item actualizado correctamente.'];

        } catch (Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
