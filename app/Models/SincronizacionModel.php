<?php
namespace App\Models;

class SincronizacionModel extends BaseModel
{
    protected $table      = 'item_general';
    protected $primaryKey = 'id_item_general';

    // Mass-assignment whitelist para la tabla natural `item_general`.
    // Este modelo es de solo-lectura/auditoría: opera todo con query builder
    // directo y solo hace UPDATEs puntuales de `nombre` (merge → prefijo [MERGED]).
    // $allowedFields protege un eventual save()/insert()/update() del modelo.
    // Subconjunto conservador de columnas de item_general realmente escritas aquí.
    protected $allowedFields = [
        'nombre',
        'codigo',
        'tipo',
        'categoria_id',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * KPIs globales para el dashboard de sincronización.
     *
     * @param bool $incluirDuplicados  Si true, calcula duplicados (caro, O(n²) Levenshtein).
     *                                  Si false, retorna null en `duplicados_potenciales`.
     *                                  Para callers como `/api/dashboard` que necesitan respuesta rápida,
     *                                  pasar false. Para la vista de Sincronización dejar true.
     */
    public function stats(bool $incluirDuplicados = true): array
    {
        // 1. Conteos de MP (tipo=1) y su distribución por número de proveedores activos
        $row = $this->db->query("
            SELECT
                COUNT(*) AS total_mp,
                SUM(CASE WHEN prov_count >= 1 THEN 1 ELSE 0 END) AS mp_con_proveedor,
                SUM(CASE WHEN prov_count = 0  THEN 1 ELSE 0 END) AS mp_sin_proveedor,
                SUM(CASE WHEN prov_count = 1  THEN 1 ELSE 0 END) AS mp_un_solo_proveedor,
                SUM(CASE WHEN prov_count >= 2 THEN 1 ELSE 0 END) AS mp_dos_o_mas_proveedores
            FROM (
                SELECT ig.id_item_general,
                    (SELECT COUNT(*) FROM item_proveedor ip
                     WHERE ip.item_general_id = ig.id_item_general
                       AND ip.disponible = 1) AS prov_count
                FROM item_general ig
                WHERE ig.tipo = 1
            ) t
        ")->getRowArray() ?: [];

        // 2. Items proveedor: total + pendientes
        $itemsProv = $this->db->query("
            SELECT
                COUNT(*) AS items_proveedor_total,
                SUM(CASE WHEN item_general_id IS NULL THEN 1 ELSE 0 END) AS items_proveedor_pendientes
            FROM item_proveedor
        ")->getRowArray() ?: [];

        // 3. Ahorro potencial: para cada MP con stock y >= 2 proveedores,
        //    estimar (costo_actual - mejor_precio_kg) * stock_total
        $ahorro = $this->db->query("
            SELECT COALESCE(SUM(
                CASE
                    WHEN best.precio_min_kg > 0
                     AND ci.costo_unitario  > best.precio_min_kg
                    THEN (ci.costo_unitario - best.precio_min_kg) * COALESCE(stock.stock_total, 0)
                    ELSE 0
                END
            ), 0) AS ahorro_potencial
            FROM item_general ig
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
            LEFT JOIN (
                SELECT item_general_id,
                    MIN(precio_unitario / NULLIF(factor_conversion, 0)) AS precio_min_kg
                FROM item_proveedor
                WHERE disponible = 1
                  AND item_general_id IS NOT NULL
                  AND factor_conversion > 0
                GROUP BY item_general_id
            ) best ON best.item_general_id = ig.id_item_general
            LEFT JOIN (
                SELECT item_general_id, SUM(cantidad_disponible) AS stock_total
                FROM inventario_capas
                WHERE estado = 1 AND cantidad_disponible > 0
                GROUP BY item_general_id
            ) stock ON stock.item_general_id = ig.id_item_general
            WHERE ig.tipo = 1
        ")->getRowArray() ?: [];

        // 4. Duplicados potenciales (conteo cacheable). Skip si caller no lo necesita
        $duplicados = $incluirDuplicados ? count($this->detectarDuplicados(70)) : null;

        return [
            'total_mp'                 => (int) ($row['total_mp'] ?? 0),
            'mp_con_proveedor'         => (int) ($row['mp_con_proveedor'] ?? 0),
            'mp_sin_proveedor'         => (int) ($row['mp_sin_proveedor'] ?? 0),
            'mp_un_solo_proveedor'     => (int) ($row['mp_un_solo_proveedor'] ?? 0),
            'mp_dos_o_mas_proveedores' => (int) ($row['mp_dos_o_mas_proveedores'] ?? 0),
            'items_proveedor_total'      => (int) ($itemsProv['items_proveedor_total'] ?? 0),
            'items_proveedor_pendientes' => (int) ($itemsProv['items_proveedor_pendientes'] ?? 0),
            'duplicados_potenciales'   => $duplicados,
            'ahorro_potencial'         => round((float) ($ahorro['ahorro_potencial'] ?? 0), 2),
        ];
    }

    /**
     * Tabla maestra de items (MP + Insumos) con agregados de proveedores.
     */
    public function maestro(?string $search = null, ?string $cobertura = null, ?int $tipo = null): array
    {
        $sql = "
            SELECT
                ig.id_item_general,
                ig.codigo,
                ig.nombre,
                ig.tipo,
                ig.categoria_id,
                cat.nombre AS categoria_nombre,
                COALESCE(ci.costo_unitario, 0) AS costo_unitario,
                COALESCE(stock.stock_total, 0) AS stock_total,
                COALESCE(prov.proveedores_count, 0) AS proveedores_count,
                prov.precio_min_kg,
                prov.precio_max_kg,
                CASE
                    WHEN prov.precio_min_kg > 0 AND prov.precio_max_kg > 0
                    THEN ROUND(((prov.precio_max_kg - prov.precio_min_kg) / prov.precio_min_kg) * 100, 1)
                    ELSE 0
                END AS spread_pct
            FROM item_general ig
            LEFT JOIN categoria   cat ON cat.id_categoria      = ig.categoria_id
            LEFT JOIN costos_item ci  ON ci.item_general_id    = ig.id_item_general
            LEFT JOIN (
                SELECT item_general_id, SUM(cantidad_disponible) AS stock_total
                FROM inventario_capas
                WHERE estado = 1 AND cantidad_disponible > 0
                GROUP BY item_general_id
            ) stock ON stock.item_general_id = ig.id_item_general
            LEFT JOIN (
                SELECT
                    item_general_id,
                    COUNT(*) AS proveedores_count,
                    MIN(precio_unitario / NULLIF(factor_conversion, 0)) AS precio_min_kg,
                    MAX(precio_unitario / NULLIF(factor_conversion, 0)) AS precio_max_kg
                FROM item_proveedor
                WHERE disponible = 1
                  AND item_general_id IS NOT NULL
                  AND factor_conversion > 0
                GROUP BY item_general_id
            ) prov ON prov.item_general_id = ig.id_item_general
            WHERE ig.tipo IN (1, 2)
        ";

        $params = [];

        if ($tipo !== null) {
            $sql .= " AND ig.tipo = ?";
            $params[] = $tipo;
        }

        if ($search !== null && $search !== '') {
            $sql .= " AND (UPPER(ig.nombre) LIKE ? OR UPPER(ig.codigo) LIKE ?)";
            $term = '%' . strtoupper($search) . '%';
            $params[] = $term;
            $params[] = $term;
        }

        if ($cobertura === 'sin') {
            $sql .= " AND COALESCE(prov.proveedores_count, 0) = 0";
        } elseif ($cobertura === 'uno') {
            $sql .= " AND COALESCE(prov.proveedores_count, 0) = 1";
        } elseif ($cobertura === 'dos_mas') {
            $sql .= " AND COALESCE(prov.proveedores_count, 0) >= 2";
        }

        $sql .= " ORDER BY ig.nombre ASC";

        $items = $this->db->query($sql, $params)->getResultArray();
        if (empty($items)) return [];

        // Cargar subarray proveedores en una sola query y agrupar en PHP (evita N+1)
        $ids = array_column($items, 'id_item_general');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $proveedores = $this->db->query("
            SELECT
                ip.item_general_id,
                ip.id_item_proveedor,
                ip.precio_unitario,
                ip.factor_conversion,
                ip.unidad_compra_id,
                uc.nombre AS unidad_compra_nombre,
                p.id_proveedor,
                p.nombre_empresa,
                CASE
                    WHEN ip.factor_conversion > 0
                    THEN ROUND(ip.precio_unitario / ip.factor_conversion, 2)
                    ELSE NULL
                END AS precio_kg
            FROM item_proveedor ip
            JOIN proveedor p   ON p.id_proveedor = ip.proveedor_id
            LEFT JOIN unidad uc ON uc.id_unidad   = ip.unidad_compra_id
            WHERE ip.item_general_id IN ({$placeholders})
              AND ip.disponible = 1
            ORDER BY ip.item_general_id, precio_kg ASC
        ", $ids)->getResultArray();

        $bucket = [];
        foreach ($proveedores as $p) {
            $bucket[$p['item_general_id']][] = [
                'id_item_proveedor'    => (int) $p['id_item_proveedor'],
                'id_proveedor'         => (int) $p['id_proveedor'],
                'nombre_empresa'       => $p['nombre_empresa'],
                'precio_unitario'      => (float) $p['precio_unitario'],
                'factor_conversion'    => (float) $p['factor_conversion'],
                'unidad_compra_id'     => $p['unidad_compra_id'] !== null ? (int) $p['unidad_compra_id'] : null,
                'unidad_compra_nombre' => $p['unidad_compra_nombre'],
                'precio_kg'            => $p['precio_kg'] !== null ? (float) $p['precio_kg'] : null,
            ];
        }

        foreach ($items as &$it) {
            $it['proveedores']     = $bucket[$it['id_item_general']] ?? [];
            $it['stock_total']     = (float) $it['stock_total'];
            $it['costo_unitario']  = (float) $it['costo_unitario'];
            $it['precio_min_kg']   = $it['precio_min_kg'] !== null ? (float) $it['precio_min_kg'] : null;
            $it['precio_max_kg']   = $it['precio_max_kg'] !== null ? (float) $it['precio_max_kg'] : null;
            $it['spread_pct']      = (float) $it['spread_pct'];
            $it['proveedores_count'] = (int) $it['proveedores_count'];
        }

        return $items;
    }

    /**
     * item_proveedor sin item_general_id, con sugerencias top 3 vía buscarFuzzy.
     */
    public function pendientes(): array
    {
        $pendientes = $this->db->query("
            SELECT
                ip.id_item_proveedor,
                ip.nombre,
                ip.codigo,
                ip.precio_unitario,
                ip.factor_conversion,
                ip.unidad_compra_id,
                ip.proveedor_id,
                ip.tipo,
                p.nombre_empresa,
                uc.nombre AS unidad_compra_nombre
            FROM item_proveedor ip
            JOIN proveedor p   ON p.id_proveedor = ip.proveedor_id
            LEFT JOIN unidad uc ON uc.id_unidad   = ip.unidad_compra_id
            WHERE ip.item_general_id IS NULL
            ORDER BY ip.id_item_proveedor DESC
        ")->getResultArray();

        if (empty($pendientes)) return [];

        $itemModel = new ItemModel();

        foreach ($pendientes as &$p) {
            $matches = $itemModel->buscarFuzzy($p['nombre'] ?? '', 3, [1, 2]);
            $sugerencias = [];

            // Score normalizado simple basado en similar_text
            foreach ($matches as $m) {
                similar_text(
                    strtoupper($p['nombre'] ?? ''),
                    strtoupper($m['nombre'] ?? ''),
                    $score
                );
                $sugerencias[] = [
                    'id_item_general' => (int) $m['id_item_general'],
                    'nombre'          => $m['nombre'],
                    'codigo'          => $m['codigo'] ?? null,
                    'tipo'            => (int) ($m['tipo'] ?? 1),
                    'score'           => (int) round($score),
                ];
            }

            // Asegurar orden por score desc
            usort($sugerencias, fn($a, $b) => $b['score'] <=> $a['score']);

            $p['sugerencias']      = $sugerencias;
            $p['precio_unitario']  = (float) $p['precio_unitario'];
            $p['factor_conversion']= $p['factor_conversion'] !== null ? (float) $p['factor_conversion'] : null;
        }

        return $pendientes;
    }

    /**
     * Detecta pares de item_general (tipo=1) similares.
     * Levenshtein normalizado + bonus por categoría compartida. Threshold 70.
     */
    public function duplicados(int $threshold = 70): array
    {
        $pairs = $this->detectarDuplicados($threshold);
        if (empty($pairs)) return [];

        // Enriquecer cada lado con stock y proveedores_count
        $ids = [];
        foreach ($pairs as $pair) {
            $ids[$pair['a_id']] = true;
            $ids[$pair['b_id']] = true;
        }
        $ids = array_keys($ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $info = $this->db->query("
            SELECT
                ig.id_item_general,
                ig.codigo,
                ig.nombre,
                COALESCE(stock.stock_total, 0) AS stock_total,
                COALESCE(prov.proveedores_count, 0) AS proveedores_count
            FROM item_general ig
            LEFT JOIN (
                SELECT item_general_id, SUM(cantidad_disponible) AS stock_total
                FROM inventario_capas WHERE estado = 1 AND cantidad_disponible > 0
                GROUP BY item_general_id
            ) stock ON stock.item_general_id = ig.id_item_general
            LEFT JOIN (
                SELECT item_general_id, COUNT(*) AS proveedores_count
                FROM item_proveedor WHERE disponible = 1
                GROUP BY item_general_id
            ) prov ON prov.item_general_id = ig.id_item_general
            WHERE ig.id_item_general IN ({$placeholders})
        ", $ids)->getResultArray();

        $byId = [];
        foreach ($info as $r) {
            $byId[(int) $r['id_item_general']] = [
                'id_item_general'   => (int) $r['id_item_general'],
                'codigo'            => $r['codigo'],
                'nombre'            => $r['nombre'],
                'stock_total'       => (float) $r['stock_total'],
                'proveedores_count' => (int) $r['proveedores_count'],
            ];
        }

        $out = [];
        foreach ($pairs as $pair) {
            if (!isset($byId[$pair['a_id']], $byId[$pair['b_id']])) continue;
            $out[] = [
                'score' => $pair['score'],
                'a'     => $byId[$pair['a_id']],
                'b'     => $byId[$pair['b_id']],
            ];
        }

        usort($out, fn($x, $y) => $y['score'] <=> $x['score']);

        return $out;
    }

    /**
     * Algoritmo: normaliza nombres, calcula Levenshtein y bonifica categoría compartida.
     * Devuelve [{a_id, b_id, score}].
     */
    private function detectarDuplicados(int $threshold = 70): array
    {
        $items = $this->db->query("
            SELECT id_item_general, nombre, categoria_id
            FROM item_general
            WHERE tipo = 1
        ")->getResultArray();

        $n = count($items);
        if ($n < 2) return [];

        // Pre-normalizar
        foreach ($items as &$it) {
            $it['_norm'] = $this->normalizar($it['nombre'] ?? '');
        }
        unset($it);

        $pairs = [];
        for ($i = 0; $i < $n; $i++) {
            $a = $items[$i];
            $lenA = strlen($a['_norm']);
            if ($lenA === 0) continue;

            for ($j = $i + 1; $j < $n; $j++) {
                $b = $items[$j];
                $lenB = strlen($b['_norm']);
                if ($lenB === 0) continue;

                // PHP nativo: levenshtein limitado a strings <= 255
                if ($lenA > 255 || $lenB > 255) continue;

                $max = max($lenA, $lenB);
                $dist = levenshtein($a['_norm'], $b['_norm']);
                $score = (int) round((1 - $dist / $max) * 100);

                if (
                    $a['categoria_id'] !== null
                    && $a['categoria_id'] === $b['categoria_id']
                ) {
                    $score += 10;
                }

                if ($score >= $threshold) {
                    $pairs[] = [
                        'a_id'  => (int) $a['id_item_general'],
                        'b_id'  => (int) $b['id_item_general'],
                        'score' => min($score, 100),
                    ];
                }
            }
        }

        return $pairs;
    }

    private function normalizar(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = strtr($s, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u',
        ]);
        $s = preg_replace('/[^a-z0-9 ]+/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }

    /**
     * MP sin proveedores activos vinculados.
     */
    public function huerfanos(): array
    {
        $sql = "
            SELECT
                ig.id_item_general,
                ig.codigo,
                ig.nombre,
                ig.categoria_id,
                cat.nombre AS categoria_nombre,
                COALESCE(stock.stock_total, 0) AS stock_total,
                ultima.ultima_compra
            FROM item_general ig
            LEFT JOIN categoria cat ON cat.id_categoria = ig.categoria_id
            LEFT JOIN (
                SELECT item_general_id, SUM(cantidad_disponible) AS stock_total
                FROM inventario_capas
                WHERE estado = 1 AND cantidad_disponible > 0
                GROUP BY item_general_id
            ) stock ON stock.item_general_id = ig.id_item_general
            LEFT JOIN (
                SELECT
                    ip.item_general_id,
                    MAX(oc.fecha) AS ultima_compra
                FROM ordenes_compra_detalle ocd
                JOIN ordenes_compra oc ON oc.id_orden = ocd.ordenes_compra_id
                JOIN item_proveedor ip ON ip.id_item_proveedor = ocd.item_proveedor_id
                WHERE ip.item_general_id IS NOT NULL
                GROUP BY ip.item_general_id
            ) ultima ON ultima.item_general_id = ig.id_item_general
            WHERE ig.tipo = 1
              AND NOT EXISTS (
                SELECT 1 FROM item_proveedor ip2
                WHERE ip2.item_general_id = ig.id_item_general
                  AND ip2.disponible = 1
              )
            ORDER BY ig.nombre ASC
        ";

        $rows = $this->db->query($sql)->getResultArray();
        foreach ($rows as &$r) {
            $r['stock_total'] = (float) $r['stock_total'];
        }
        return $rows;
    }

    /**
     * Merge: traslada TODAS las referencias item_general_id de remove_id → keep_id.
     * Cubre: item_proveedor, item_general_formulaciones (ingredientes), costos_indirectos_item,
     * inventario (legacy), inventario_capas (históricas), movimiento_inventario,
     * produccion_insumos_detalle (snapshot).
     *
     * Reglas de seguridad:
     *  - Ambos items deben existir y tener el mismo `tipo` (no se mergea MP con producto).
     *  - El remove no puede tener stock activo en inventario_capas (estado=1, cantidad>0).
     *  - Para costos_indirectos_item se evita duplicar pares (item, costo_indirecto): si keep
     *    ya tiene la fila, se borra la de remove.
     *  - costos_item del remove se elimina (keep ya tiene la suya).
     *  - El remove se marca con prefijo [MERGED→keepId] en su nombre, no se elimina (preserva
     *    integridad referencial histórica).
     */
    public function merge(int $keepId, int $removeId): array
    {
        if ($keepId === $removeId) {
            throw new \Exception('keep_id y remove_id no pueden ser iguales.');
        }

        $keep   = $this->db->table('item_general')->where('id_item_general', $keepId)->get()->getRowArray();
        $remove = $this->db->table('item_general')->where('id_item_general', $removeId)->get()->getRowArray();

        if (!$keep || !$remove) {
            throw new \Exception('Uno o ambos items no existen.');
        }

        if ((int) $keep['tipo'] !== (int) $remove['tipo']) {
            throw new \Exception('Los items deben tener el mismo tipo (MP, Insumo, Producto) para poder unificarse.');
        }

        // Verificar que remove no tenga inventario activo
        $stockRemove = $this->db->query("
            SELECT COALESCE(SUM(cantidad_disponible), 0) AS total
            FROM inventario_capas
            WHERE item_general_id = ? AND estado = 1 AND cantidad_disponible > 0
        ", [$removeId])->getRowArray();

        if ((float) ($stockRemove['total'] ?? 0) > 0) {
            throw new \Exception('El item a remover tiene stock activo. Traslada primero a otra bodega o consume sus capas antes de unificar.');
        }

        $this->db->transBegin();

        try {
            $afectados = [
                'proveedores'        => 0,
                'formulaciones'      => 0,
                'costos_indirectos'  => 0,
                'inventario_legacy'  => 0,
                'capas_historicas'   => 0,
                'movimientos'        => 0,
                'produccion_snapshot'=> 0,
                'costos_item_removed'=> 0,
            ];

            // 1. item_proveedor — mover catálogo del proveedor al item conservado
            $this->db->table('item_proveedor')
                ->where('item_general_id', $removeId)
                ->update(['item_general_id' => $keepId]);
            $afectados['proveedores'] = $this->db->affectedRows();

            // 2. item_general_formulaciones — referencias como INGREDIENTE en recetas
            //    Crítico: si una formulación usa A y B (mismo ingrediente duplicado), tras el
            //    merge tendría dos filas con item_general_id = keepId. Las consolidamos sumando
            //    cantidad/porcentaje para no duplicar el ingrediente en la BOM.
            if ($this->db->tableExists('item_general_formulaciones')) {
                $duplicadas = $this->db->query("
                    SELECT a.formulaciones_id,
                           a.cantidad   AS cant_remove,
                           a.porcentaje AS pct_remove,
                           b.cantidad   AS cant_keep,
                           b.porcentaje AS pct_keep
                    FROM item_general_formulaciones a
                    JOIN item_general_formulaciones b
                      ON b.formulaciones_id = a.formulaciones_id
                     AND b.item_general_id  = ?
                    WHERE a.item_general_id = ?
                ", [$keepId, $removeId])->getResultArray();

                foreach ($duplicadas as $d) {
                    // Suma cantidades en la fila del keep
                    $this->db->table('item_general_formulaciones')
                        ->where('formulaciones_id', $d['formulaciones_id'])
                        ->where('item_general_id', $keepId)
                        ->update([
                            'cantidad'   => (float) $d['cant_keep'] + (float) $d['cant_remove'],
                            'porcentaje' => (float) $d['pct_keep']  + (float) $d['pct_remove'],
                        ]);
                    // Borra la fila duplicada del remove
                    $this->db->table('item_general_formulaciones')
                        ->where('formulaciones_id', $d['formulaciones_id'])
                        ->where('item_general_id', $removeId)
                        ->delete();
                }

                // Las que NO eran duplicadas: solo update item_general_id
                $this->db->table('item_general_formulaciones')
                    ->where('item_general_id', $removeId)
                    ->update(['item_general_id' => $keepId]);
                $afectados['formulaciones'] = $this->db->affectedRows() + count($duplicadas);
            }

            // 3. costos_indirectos_item — evitar duplicar pares (item, costo_indirecto)
            if ($this->db->tableExists('costos_indirectos_item')) {
                $dup = $this->db->query("
                    SELECT a.costos_indirectos_id
                    FROM costos_indirectos_item a
                    JOIN costos_indirectos_item b
                      ON b.costos_indirectos_id = a.costos_indirectos_id
                     AND b.item_general_id      = ?
                    WHERE a.item_general_id = ?
                ", [$keepId, $removeId])->getResultArray();

                foreach ($dup as $d) {
                    $this->db->table('costos_indirectos_item')
                        ->where('item_general_id', $removeId)
                        ->where('costos_indirectos_id', $d['costos_indirectos_id'])
                        ->delete();
                }

                $this->db->table('costos_indirectos_item')
                    ->where('item_general_id', $removeId)
                    ->update(['item_general_id' => $keepId]);
                $afectados['costos_indirectos'] = $this->db->affectedRows() + count($dup);
            }

            // 4. inventario (legacy) — mover registros si existen
            if ($this->db->tableExists('inventario')) {
                $this->db->table('inventario')
                    ->where('item_general_id', $removeId)
                    ->update(['item_general_id' => $keepId]);
                $afectados['inventario_legacy'] = $this->db->affectedRows();
            }

            // 5. inventario_capas — capas históricas/agotadas (las activas ya las validamos = 0)
            if ($this->db->tableExists('inventario_capas')) {
                $this->db->table('inventario_capas')
                    ->where('item_general_id', $removeId)
                    ->update(['item_general_id' => $keepId]);
                $afectados['capas_historicas'] = $this->db->affectedRows();
            }

            // 6. movimiento_inventario — histórico de movimientos
            if ($this->db->tableExists('movimiento_inventario')) {
                $this->db->table('movimiento_inventario')
                    ->where('item_general_id', $removeId)
                    ->update(['item_general_id' => $keepId]);
                $afectados['movimientos'] = $this->db->affectedRows();
            }

            // 7. produccion_insumos_detalle — snapshots de costo congelado por preparación
            if ($this->db->tableExists('produccion_insumos_detalle')) {
                $this->db->table('produccion_insumos_detalle')
                    ->where('item_general_id', $removeId)
                    ->update(['item_general_id' => $keepId]);
                $afectados['produccion_snapshot'] = $this->db->affectedRows();
            }

            // 8. costos_item — keep ya tiene su fila; eliminar la del remove para evitar
            //    huérfanos (PK lógica es item_general_id).
            $this->db->table('costos_item')
                ->where('item_general_id', $removeId)
                ->delete();
            $afectados['costos_item_removed'] = $this->db->affectedRows();

            // 9. Marcar el item removido como [MERGED] (no se elimina por integridad histórica)
            $nombreOriginal = $remove['nombre'] ?? '';
            $nuevoNombre = '[MERGED→' . $keepId . '] ' . substr($nombreOriginal, 0, 200);
            $this->db->table('item_general')
                ->where('id_item_general', $removeId)
                ->update(['nombre' => $nuevoNombre]);

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                throw new \Exception('Error de base de datos durante el merge.');
            }

            $this->db->transCommit();

            return [
                'keep_id'        => $keepId,
                'remove_id'      => $removeId,
                'nombre_remove'  => $nuevoNombre,
                'nombre_keep'    => $keep['nombre'] ?? '',
                'afectados'      => $afectados,
            ];

        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
