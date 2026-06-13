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
    public function merge(int $keepId, int $removeId, array $opts = []): array
    {
        // $opts: ['combinar_stock' => bool, 'nombre_base' => ?string]
        // - combinar_stock=true: NO exige stock=0; reapunta las capas activas y
        //   recalcula el costo promedio ponderado del keep (decisión del negocio).
        // - nombre_base: renombra el keep al nombre base universal aprobado.
        $combinarStock = (bool) ($opts['combinar_stock'] ?? false);
        $nombreBase    = $opts['nombre_base'] ?? null;

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

        if (!$combinarStock && (float) ($stockRemove['total'] ?? 0) > 0) {
            throw new \Exception('El item a remover tiene stock activo. Activá "combinar stock" o consume/traslada sus capas antes de unificar.');
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

            // 1. item_proveedor — mover catálogo del proveedor al item conservado.
            //    Capturamos los IDs movidos ANTES del update para poder revertir (UNDO).
            $movProv = array_map('intval', array_column(
                $this->db->table('item_proveedor')->select('id_item_proveedor')
                    ->where('item_general_id', $removeId)->get()->getResultArray(),
                'id_item_proveedor'
            ));
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

            // 5. inventario_capas — TODAS las capas del remove (con combinar_stock
            //    incluye las activas). Capturamos IDs movidos para UNDO.
            $movCapas = [];
            if ($this->db->tableExists('inventario_capas')) {
                $movCapas = array_map('intval', array_column(
                    $this->db->table('inventario_capas')->select('id_capa')
                        ->where('item_general_id', $removeId)->get()->getResultArray(),
                    'id_capa'
                ));
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

            // 10. Recalcular el costo promedio ponderado del keep ahora que absorbió las
            //     capas del remove (cada capa conserva su costo_unitario original, así que
            //     el ponderado combinado sale correcto automáticamente).
            $costoKeep = (new \App\Models\InventarioCapasModel())->recalcularPromedioPonderado($keepId);

            // 11. Renombrar el keep al nombre base universal aprobado. La referencia
            //     técnica/marca permanece intacta en item_proveedor.
            $nombreKeepFinal = $keep['nombre'] ?? '';
            if ($nombreBase !== null && trim($nombreBase) !== '') {
                $nombreKeepFinal = substr(trim($nombreBase), 0, 200);
                $this->db->table('item_general')
                    ->where('id_item_general', $keepId)
                    ->update(['nombre' => $nombreKeepFinal]);
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                throw new \Exception('Error de base de datos durante el merge.');
            }

            $this->db->transCommit();

            return [
                'keep_id'             => $keepId,
                'remove_id'           => $removeId,
                'nombre_remove'       => $nuevoNombre,
                'nombre_remove_original' => $nombreOriginal,
                'nombre_keep'         => $nombreKeepFinal,
                'afectados'           => $afectados,
                'detalle_movimientos' => [
                    'item_proveedor'   => $movProv,
                    'inventario_capas' => $movCapas,
                ],
                'costo_keep'          => $costoKeep,
            ];

        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  DEDUPLICACIÓN ASISTIDA POR IA — clusters, fusión en lote, verificación, undo
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Dataset compacto (minimiza tokens) para que la IA clasifique materias primas
     * e insumos por identidad química. Un objeto por item_general activo (tipo 1/2,
     * sin prefijo [MERGED]) con sus referencias de proveedor, uso en fórmulas y stock.
     *
     * @param int|null $tipo  1=MP, 2=Insumo, null=ambos
     */
    public function datasetParaClasificacion(?int $tipo = null): array
    {
        $sql = "
            SELECT
                ig.id_item_general,
                ig.nombre,
                ig.codigo,
                ig.tipo,
                cat.nombre AS categoria,
                ua.nombre  AS unidad,
                COALESCE(ci.costo_unitario, 0) AS costo,
                COALESCE(stock.stock_total, 0) AS stock_kg,
                COALESCE(usos.n, 0) AS usos_en_formulas
            FROM item_general ig
            LEFT JOIN categoria cat ON cat.id_categoria       = ig.categoria_id
            LEFT JOIN unidad ua     ON ua.id_unidad           = ig.unidad_almacenaje_id
            LEFT JOIN costos_item ci ON ci.item_general_id    = ig.id_item_general
            LEFT JOIN (
                SELECT item_general_id, SUM(cantidad_disponible) AS stock_total
                FROM inventario_capas WHERE estado = 1 AND cantidad_disponible > 0
                GROUP BY item_general_id
            ) stock ON stock.item_general_id = ig.id_item_general
            LEFT JOIN (
                SELECT item_general_id, COUNT(*) AS n
                FROM item_general_formulaciones GROUP BY item_general_id
            ) usos ON usos.item_general_id = ig.id_item_general
            WHERE ig.tipo IN (1, 2)
              AND (ig.deleted_at IS NULL)
              AND ig.nombre NOT LIKE '[MERGED%'
        ";
        $params = [];
        if ($tipo !== null) {
            $sql .= " AND ig.tipo = ?";
            $params[] = $tipo;
        }
        $sql .= " ORDER BY ig.tipo, ig.nombre";

        $items = $this->db->query($sql, $params)->getResultArray();
        if (empty($items)) return [];

        // Referencias de proveedor (nombres técnicos/marcas) en una query agrupada.
        $ids = array_column($items, 'id_item_general');
        $ph  = implode(',', array_fill(0, count($ids), '?'));
        $refs = $this->db->query("
            SELECT ip.item_general_id, ip.nombre, ip.codigo, p.nombre_empresa AS proveedor
            FROM item_proveedor ip
            JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
            WHERE ip.item_general_id IN ({$ph}) AND ip.deleted_at IS NULL
        ", $ids)->getResultArray();

        $bucket = [];
        foreach ($refs as $r) {
            $bucket[$r['item_general_id']][] = [
                'nombre_tecnico' => $r['nombre'],
                'codigo'         => $r['codigo'],
                'proveedor'      => $r['proveedor'],
            ];
        }

        foreach ($items as &$it) {
            $it['id_item_general']    = (int) $it['id_item_general'];
            $it['tipo']               = (int) $it['tipo'];
            $it['costo']              = (float) $it['costo'];
            $it['stock_kg']           = (float) $it['stock_kg'];
            $it['usos_en_formulas']   = (int) $it['usos_en_formulas'];
            $it['referencias_proveedor'] = $bucket[$it['id_item_general']] ?? [];
        }
        return $items;
    }

    /**
     * Persiste los clusters propuestos por la IA (o importados de un JSON offline).
     * Idempotente: descarta clusters previos del mismo scope que aún no se fusionaron,
     * para que re-correr la clasificación no duplique.
     *
     * @param array  $clusters  [{identidad_quimica, nombre_base, confianza, razonamiento,
     *                            tipo, keep_id, items:[{item_general_id, confianza, motivo}]}]
     * @param string $lote      id de la corrida
     * @param string $modelo    modelo de IA usado
     */
    public function guardarSugerencias(array $clusters, string $lote, string $modelo = ''): array
    {
        $this->db->transBegin();
        try {
            // Limpiar propuestas anteriores no fusionadas (idempotencia).
            $this->db->table('item_sync_clusters')
                ->whereIn('estado', ['propuesto', 'revisado', 'aprobado'])
                ->delete();

            $now = date('Y-m-d H:i:s');
            $creados = 0;

            foreach ($clusters as $c) {
                $items = $c['items'] ?? [];
                if (count($items) < 2) continue; // un cluster necesita >= 2 miembros

                $keepId = $c['keep_id'] ?? ($items[0]['item_general_id'] ?? null);

                $this->db->table('item_sync_clusters')->insert([
                    'clave_grupo'           => $c['clave_grupo'] ?? $this->normalizar($c['identidad_quimica'] ?? ''),
                    'identidad_quimica'     => $c['identidad_quimica'] ?? null,
                    'nombre_base_propuesto' => $c['nombre_base'] ?? null,
                    'confianza'             => in_array($c['confianza'] ?? 'media', ['alta','media','baja'], true) ? $c['confianza'] : 'media',
                    'razonamiento'          => $c['razonamiento'] ?? null,
                    'tipo'                  => (int) ($c['tipo'] ?? 1),
                    'estado'                => 'propuesto',
                    'keep_id_sugerido'      => $keepId,
                    'lote_ia'               => $lote,
                    'modelo_ia'             => $modelo,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
                $clusterId = $this->db->insertID();

                foreach ($items as $m) {
                    $igid = (int) ($m['item_general_id'] ?? 0);
                    if ($igid <= 0) continue;
                    $this->db->table('item_sync_cluster_items')->insert([
                        'cluster_id'      => $clusterId,
                        'item_general_id' => $igid,
                        'rol'             => $igid === (int) $keepId ? 'keep'
                            : ((($m['confianza'] ?? 'media') === 'baja') ? 'excluido' : 'merge'),
                        'confianza_item'  => in_array($m['confianza'] ?? 'media', ['alta','media','baja'], true) ? $m['confianza'] : 'media',
                        'motivo_revision' => $m['motivo'] ?? null,
                        'created_at'      => $now,
                    ]);
                }
                $creados++;
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                throw new \Exception('Error guardando sugerencias.');
            }
            $this->db->transCommit();
            return ['clusters_creados' => $creados, 'lote' => $lote];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Lista clusters con sus miembros enriquecidos (stock, proveedores, costo).
     */
    public function listarClusters(?string $estado = null, ?string $confianza = null, ?int $tipo = null): array
    {
        $b = $this->db->table('item_sync_clusters');
        if ($estado !== null)    $b->where('estado', $estado);
        if ($confianza !== null) $b->where('confianza', $confianza);
        if ($tipo !== null)      $b->where('tipo', $tipo);
        $clusters = $b->orderBy('FIELD(confianza, "alta","media","baja"), id_cluster DESC', '', false)->get()->getResultArray();
        if (empty($clusters)) return [];

        foreach ($clusters as &$c) {
            $c['id_cluster'] = (int) $c['id_cluster'];
            $c['items'] = $this->itemsDeCluster($c['id_cluster']);
        }
        return $clusters;
    }

    public function detalleCluster(int $clusterId): ?array
    {
        $c = $this->db->table('item_sync_clusters')->where('id_cluster', $clusterId)->get()->getRowArray();
        if (!$c) return null;
        $c['id_cluster'] = (int) $c['id_cluster'];
        $c['items'] = $this->itemsDeCluster($clusterId);
        return $c;
    }

    /**
     * Miembros de un cluster con stock, nº de proveedores y costo (reusa los JOINs
     * del detector de duplicados).
     */
    private function itemsDeCluster(int $clusterId): array
    {
        return $this->db->query("
            SELECT
                ci.id,
                ci.item_general_id,
                ci.rol,
                ci.confianza_item,
                ci.motivo_revision,
                ig.nombre,
                ig.codigo,
                ig.tipo,
                COALESCE(c.costo_unitario, 0) AS costo_unitario,
                COALESCE(stock.stock_total, 0) AS stock_total,
                COALESCE(prov.proveedores_count, 0) AS proveedores_count
            FROM item_sync_cluster_items ci
            JOIN item_general ig ON ig.id_item_general = ci.item_general_id
            LEFT JOIN costos_item c ON c.item_general_id = ig.id_item_general
            LEFT JOIN (
                SELECT item_general_id, SUM(cantidad_disponible) AS stock_total
                FROM inventario_capas WHERE estado = 1 AND cantidad_disponible > 0
                GROUP BY item_general_id
            ) stock ON stock.item_general_id = ig.id_item_general
            LEFT JOIN (
                SELECT item_general_id, COUNT(*) AS proveedores_count
                FROM item_proveedor WHERE disponible = 1 AND deleted_at IS NULL
                GROUP BY item_general_id
            ) prov ON prov.item_general_id = ig.id_item_general
            WHERE ci.cluster_id = ?
            ORDER BY FIELD(ci.rol, 'keep','merge','excluido'), ig.nombre
        ", [$clusterId])->getResultArray();
    }

    /**
     * Aplica correcciones humanas a un cluster (nombre base, keep, estado).
     */
    public function actualizarCluster(int $clusterId, array $data): bool
    {
        $set = [];
        if (array_key_exists('nombre_base_aprobado', $data)) $set['nombre_base_aprobado'] = $data['nombre_base_aprobado'];
        if (array_key_exists('keep_id_aprobado', $data))     $set['keep_id_aprobado']     = (int) $data['keep_id_aprobado'];
        if (array_key_exists('estado', $data) && in_array($data['estado'], ['propuesto','revisado','aprobado','descartado'], true)) {
            $set['estado'] = $data['estado'];
        }
        if (empty($set)) return false;
        $set['updated_at'] = date('Y-m-d H:i:s');

        // Si fijan keep_id_aprobado, sincronizar roles de los miembros.
        if (isset($set['keep_id_aprobado'])) {
            $this->db->table('item_sync_cluster_items')->where('cluster_id', $clusterId)
                ->where('rol', 'keep')->update(['rol' => 'merge']);
            $this->db->table('item_sync_cluster_items')->where('cluster_id', $clusterId)
                ->where('item_general_id', $set['keep_id_aprobado'])->update(['rol' => 'keep']);
        }

        return $this->db->table('item_sync_clusters')->where('id_cluster', $clusterId)->update($set);
    }

    /**
     * Cambia el rol de un miembro (keep/merge/excluido). Si se marca keep, degrada
     * el keep anterior del mismo cluster a merge.
     */
    public function moverItem(int $itemRowId, string $rol): bool
    {
        if (!in_array($rol, ['keep', 'merge', 'excluido'], true)) {
            throw new \Exception('Rol inválido.');
        }
        $row = $this->db->table('item_sync_cluster_items')->where('id', $itemRowId)->get()->getRowArray();
        if (!$row) throw new \Exception('Miembro no encontrado.');

        if ($rol === 'keep') {
            $this->db->table('item_sync_cluster_items')->where('cluster_id', $row['cluster_id'])
                ->where('rol', 'keep')->update(['rol' => 'merge']);
            $this->db->table('item_sync_clusters')->where('id_cluster', $row['cluster_id'])
                ->update(['keep_id_aprobado' => (int) $row['item_general_id']]);
        }
        return $this->db->table('item_sync_cluster_items')->where('id', $itemRowId)->update(['rol' => $rol]);
    }

    public function descartarCluster(int $clusterId): bool
    {
        return $this->db->table('item_sync_clusters')->where('id_cluster', $clusterId)
            ->update(['estado' => 'descartado', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Fusiona TODOS los miembros 'merge' de un cluster hacia el 'keep', en UNA sola
     * transacción (atómico para todo el grupo, vía transacciones anidadas de CI4).
     * Combina stock, recalcula costo, renombra el keep al nombre base aprobado,
     * registra auditoría por par y marca el cluster como fusionado.
     */
    public function fusionarCluster(int $clusterId, string $responsable = 'sistema'): array
    {
        $cluster = $this->db->table('item_sync_clusters')->where('id_cluster', $clusterId)->get()->getRowArray();
        if (!$cluster) throw new \Exception('Cluster no encontrado.');
        if ($cluster['estado'] === 'fusionado') throw new \Exception('Este grupo ya fue fusionado.');

        $miembros = $this->db->table('item_sync_cluster_items')->where('cluster_id', $clusterId)->get()->getResultArray();
        $keepId = (int) ($cluster['keep_id_aprobado'] ?: $cluster['keep_id_sugerido'] ?: 0);
        if ($keepId <= 0) throw new \Exception('El grupo no tiene un ítem "conservar" (keep) definido.');

        $removeIds = [];
        foreach ($miembros as $m) {
            $igid = (int) $m['item_general_id'];
            if ($m['rol'] === 'merge' && $igid !== $keepId) $removeIds[] = $igid;
        }
        if (empty($removeIds)) throw new \Exception('No hay ítems marcados para fusionar (rol=merge).');

        $nombreBase = $cluster['nombre_base_aprobado'] ?: $cluster['nombre_base_propuesto'] ?: null;

        // Costo del keep ANTES, para auditoría.
        $costoAntes = (float) ($this->db->table('costos_item')->select('costo_unitario')
            ->where('item_general_id', $keepId)->get()->getRowArray()['costo_unitario'] ?? 0);
        $nombreKeepAntes = $this->db->table('item_general')->select('nombre')
            ->where('id_item_general', $keepId)->get()->getRowArray()['nombre'] ?? '';

        $this->db->transBegin();
        try {
            $resultados = [];
            foreach ($removeIds as $rid) {
                // Cada merge anida su transacción dentro de ésta (CI4 nesting):
                // su transCommit no hace commit físico hasta cerrar la externa, y un
                // fallo en cualquiera marca toda la transacción para rollback.
                $r = $this->merge($keepId, $rid, [
                    'combinar_stock' => true,
                    'nombre_base'    => $nombreBase,
                ]);
                $this->registrarAuditoria($r, $clusterId, $responsable, $costoAntes, $nombreKeepAntes);
                $resultados[] = $r;
            }

            $this->db->table('item_sync_clusters')->where('id_cluster', $clusterId)->update([
                'estado'               => 'fusionado',
                'keep_id_aprobado'     => $keepId,
                'nombre_base_aprobado' => $nombreBase,
                'aprobado_por'         => $responsable,
                'fusionado_at'         => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s'),
            ]);

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                throw new \Exception('Error de base de datos durante la fusión del grupo.');
            }
            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        return [
            'cluster_id'   => $clusterId,
            'keep_id'      => $keepId,
            'fusionados'   => count($removeIds),
            'remove_ids'   => $removeIds,
            'verificacion' => $this->verificarPostMerge($keepId),
        ];
    }

    /**
     * Registra una fila de auditoría por cada par fusionado (dentro de la transacción).
     */
    private function registrarAuditoria(array $merge, ?int $clusterId, string $responsable, float $costoAntes, string $nombreKeepAntes): void
    {
        $this->db->table('item_sync_auditoria')->insert([
            'cluster_id'             => $clusterId,
            'keep_id'                => $merge['keep_id'],
            'remove_id'              => $merge['remove_id'],
            'nombre_keep_antes'      => $nombreKeepAntes,
            'nombre_keep_despues'    => $merge['nombre_keep'] ?? null,
            'nombre_remove_original' => $merge['nombre_remove_original'] ?? null,
            'costo_keep_antes'       => $costoAntes,
            'costo_keep_despues'     => $merge['costo_keep'] ?? null,
            'afectados'              => json_encode($merge['afectados'] ?? [], JSON_UNESCAPED_UNICODE),
            'detalle_movimientos'    => json_encode($merge['detalle_movimientos'] ?? [], JSON_UNESCAPED_UNICODE),
            'responsable'            => $responsable,
            'revertido'              => 0,
            'created_at'             => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Reporte (no bloqueante) del estado de las fórmulas/costos tras una fusión.
     * Tolera el estado actual de los datos (porcentajes NULL, costos en 0).
     */
    public function verificarPostMerge(int $keepId): array
    {
        $formulas = $this->db->query("
            SELECT DISTINCT igf.formulaciones_id, f.nombre
            FROM item_general_formulaciones igf
            LEFT JOIN formulaciones f ON f.id_formulaciones = igf.formulaciones_id
            WHERE igf.item_general_id = ?
        ", [$keepId])->getResultArray();

        $reporte = [];
        foreach ($formulas as $f) {
            $fid = (int) $f['formulaciones_id'];
            // Suma de porcentajes (tolerancia ±0.5) — o no_definido si todos NULL.
            $row = $this->db->query("
                SELECT
                    COUNT(*) AS n,
                    SUM(CASE WHEN porcentaje IS NULL THEN 1 ELSE 0 END) AS nulos,
                    COALESCE(SUM(porcentaje), 0) AS suma_pct,
                    SUM(CASE WHEN item_general_id = ? THEN 1 ELSE 0 END) AS veces_keep
                FROM item_general_formulaciones WHERE formulaciones_id = ?
            ", [$keepId, $fid])->getRowArray();

            $todosNulos = ((int) $row['nulos'] === (int) $row['n']);
            $suma = (float) $row['suma_pct'];
            $reporte[] = [
                'formulaciones_id'    => $fid,
                'nombre'              => $f['nombre'],
                'porcentaje_estado'   => $todosNulos ? 'no_definido'
                    : (abs($suma - 100) <= 0.5 ? 'ok' : 'fuera_de_rango'),
                'suma_porcentaje'     => round($suma, 2),
                'ingrediente_duplicado' => ((int) $row['veces_keep']) > 1,
            ];
        }

        $costo = $this->db->table('costos_item')->where('item_general_id', $keepId)->get()->getRowArray();

        return [
            'keep_id'           => $keepId,
            'formulas'          => $reporte,
            'formulas_afectadas'=> count($reporte),
            'con_duplicado'     => count(array_filter($reporte, fn($r) => $r['ingrediente_duplicado'])),
            'costo_item_ok'     => $costo !== null,
            'costo_unitario'    => $costo ? (float) $costo['costo_unitario'] : null,
        ];
    }

    /**
     * UNDO PARCIAL de una fusión: reapunta de vuelta los item_proveedor y las capas
     * al remove, restaura nombres y costos. NO revierte la consolidación de
     * ingredientes en fórmulas ni los snapshots de producción (son históricos) →
     * para reversa total usar el backup previo.
     */
    public function revertirMerge(int $auditoriaId, string $responsable = 'sistema'): array
    {
        $a = $this->db->table('item_sync_auditoria')->where('id', $auditoriaId)->get()->getRowArray();
        if (!$a) throw new \Exception('Registro de auditoría no encontrado.');
        if ((int) $a['revertido'] === 1) throw new \Exception('Esta fusión ya fue revertida.');

        $detalle = json_decode($a['detalle_movimientos'] ?? '[]', true) ?: [];
        $keepId   = (int) $a['keep_id'];
        $removeId = (int) $a['remove_id'];

        $this->db->transBegin();
        try {
            // 1. Devolver item_proveedor al remove.
            $provIds = array_map('intval', $detalle['item_proveedor'] ?? []);
            if (!empty($provIds)) {
                $this->db->table('item_proveedor')->whereIn('id_item_proveedor', $provIds)
                    ->update(['item_general_id' => $removeId]);
            }
            // 2. Devolver capas al remove.
            $capaIds = array_map('intval', $detalle['inventario_capas'] ?? []);
            if (!empty($capaIds) && $this->db->tableExists('inventario_capas')) {
                $this->db->table('inventario_capas')->whereIn('id_capa', $capaIds)
                    ->update(['item_general_id' => $removeId]);
            }
            // 3. Restaurar nombres.
            if (!empty($a['nombre_remove_original'])) {
                $this->db->table('item_general')->where('id_item_general', $removeId)
                    ->update(['nombre' => $a['nombre_remove_original']]);
            }
            if (!empty($a['nombre_keep_antes'])) {
                $this->db->table('item_general')->where('id_item_general', $keepId)
                    ->update(['nombre' => $a['nombre_keep_antes']]);
            }
            // 4. Recrear costos_item del remove y recalcular ambos promedios.
            $existeCosto = $this->db->table('costos_item')->where('item_general_id', $removeId)->countAllResults();
            if ($existeCosto === 0) {
                $this->db->table('costos_item')->insert(['item_general_id' => $removeId, 'costo_unitario' => 0]);
            }
            $capasModel = new \App\Models\InventarioCapasModel();
            $capasModel->recalcularPromedioPonderado($removeId);
            $capasModel->recalcularPromedioPonderado($keepId);

            // 5. Marcar revertido.
            $this->db->table('item_sync_auditoria')->where('id', $auditoriaId)->update([
                'revertido'     => 1,
                'revertido_at'  => date('Y-m-d H:i:s'),
                'revertido_por' => $responsable,
            ]);

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                throw new \Exception('Error de base de datos durante la reversión.');
            }
            $this->db->transCommit();

            return [
                'auditoria_id' => $auditoriaId,
                'keep_id'      => $keepId,
                'remove_id'    => $removeId,
                'parcial'      => true,
                'advertencia'  => 'Las cantidades de ingredientes consolidadas en fórmulas y los snapshots de producción NO se revierten. Para reversa total restaurá el backup previo a la fusión.',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Preview: formulaciones que usan un item como ingrediente (para el reemplazo manual).
     * Agrupa por fórmula distinta (la data tiene filas BOM duplicadas) y enriquece con:
     * producto + código, estado, cantidad total de la MP, su costo unitario y el costo en la fórmula,
     * cantidad de ingredientes de la receta, y —si se pasa $toId— si la fórmula YA tiene el reemplazo
     * (se consolidará) o no (se repuntará).
     */
    public function formulasQueUsan(int $itemId, ?int $toId = null): array
    {
        $tieneReemplazo = $toId
            ? "EXISTS(SELECT 1 FROM item_general_formulaciones b
                       WHERE b.formulaciones_id = igf.formulaciones_id AND b.item_general_id = ?)"
            : '0';
        $bind = $toId ? [$toId, $itemId] : [$itemId];

        return $this->db->query("
            SELECT igf.formulaciones_id,
                   MAX(f.nombre)            AS formula_nombre,
                   MAX(f.estado)            AS formula_estado,
                   MAX(p.nombre)            AS producto_nombre,
                   MAX(p.codigo)            AS producto_codigo,
                   SUM(igf.cantidad)        AS cantidad,
                   MAX(ci.costo_unitario)   AS costo_unitario,
                   ROUND(SUM(igf.cantidad) * COALESCE(MAX(ci.costo_unitario), 0), 2) AS costo_en_formula,
                   (SELECT COUNT(*) FROM item_general_formulaciones t
                     WHERE t.formulaciones_id = igf.formulaciones_id) AS ingredientes,
                   {$tieneReemplazo}        AS tiene_reemplazo
            FROM item_general_formulaciones igf
            JOIN formulaciones f ON f.id_formulaciones = igf.formulaciones_id
            LEFT JOIN item_general p ON p.id_item_general = f.item_general_id
            LEFT JOIN costos_item ci ON ci.item_general_id = igf.item_general_id
            WHERE igf.item_general_id = ?
            GROUP BY igf.formulaciones_id
            ORDER BY MAX(p.nombre)
        ", $bind)->getResultArray();
    }

    /**
     * Reemplazo manual tipo "buscar y reemplazar" de una materia prima en el BOM de fórmulas.
     * Reemplaza A ($fromId) por B ($toId) en item_general_formulaciones.
     *  - $formulacionIds null/vacío → TODAS las fórmulas que usan A. Con ids → solo esas.
     *  - Si una fórmula ya tiene A y B → consolida (suma cantidad/porcentaje en B, borra la fila de A).
     *  - Tras el reemplazo, si A queda SIN uso en ninguna fórmula y SIN stock activo → soft-delete de A.
     * Solo toca el BOM (A y B son materiales distintos; NO se tocan capas/costos/inventario).
     */
    public function reemplazarEnFormulas(int $fromId, int $toId, ?array $formulacionIds = null, string $usuario = 'sistema'): array
    {
        if ($fromId === $toId) {
            throw new \InvalidArgumentException('La materia origen y la de reemplazo no pueden ser la misma.');
        }
        $from = $this->db->table('item_general')->where('id_item_general', $fromId)->get()->getRowArray();
        $to   = $this->db->table('item_general')->where('id_item_general', $toId)->get()->getRowArray();
        if (!$from) throw new \InvalidArgumentException('La materia origen no existe.');
        if (!$to)   throw new \InvalidArgumentException('La materia de reemplazo no existe.');

        // Scope de fórmulas (solo enteros positivos); null = todas.
        $scope = null;
        if (is_array($formulacionIds) && count($formulacionIds) > 0) {
            $scope = array_values(array_unique(array_filter(array_map('intval', $formulacionIds), fn($x) => $x > 0)));
            if (empty($scope)) $scope = null;
        }

        $this->db->transBegin();
        try {
            // A prueba de duplicados: agrupo por fórmula DISTINTA y sumo todas las filas de A
            // (la data puede tener el mismo ingrediente repetido). Por cada fórmula, dejo UNA sola
            // fila de B con la cantidad sumada (merge si B ya existía, insert si no), y borro las de A.
            $grpSql  = 'SELECT formulaciones_id, SUM(cantidad) AS sc, SUM(porcentaje) AS sp
                        FROM item_general_formulaciones WHERE item_general_id = ?';
            $grpBind = [$fromId];
            if ($scope !== null) {
                $grpSql .= ' AND formulaciones_id IN (' . implode(',', array_fill(0, count($scope), '?')) . ')';
                $grpBind = array_merge($grpBind, $scope);
            }
            $grpSql .= ' GROUP BY formulaciones_id';
            $formulas = $this->db->query($grpSql, $grpBind)->getResultArray();

            // Snapshot del BOM (A y B en las fórmulas afectadas) ANTES de mutar → permite DESHACER.
            $afectadasIds = array_map(static fn($f) => (int) $f['formulaciones_id'], $formulas);
            $snapshot = [];
            if (!empty($afectadasIds)) {
                $ph = implode(',', array_fill(0, count($afectadasIds), '?'));
                $snapshot = $this->db->query(
                    "SELECT formulaciones_id, item_general_id, cantidad, porcentaje
                     FROM item_general_formulaciones
                     WHERE item_general_id IN (?, ?) AND formulaciones_id IN ($ph)",
                    array_merge([$fromId, $toId], $afectadasIds)
                )->getResultArray();
            }

            $consolidadas = 0;
            $repuntadas   = 0;
            foreach ($formulas as $f) {
                $fid  = (int) $f['formulaciones_id'];
                $sumC = (float) $f['sc'];
                $sumP = (float) $f['sp'];

                // Borrar todas las filas de A en esta fórmula (incluye duplicados).
                $this->db->table('item_general_formulaciones')
                    ->where('formulaciones_id', $fid)->where('item_general_id', $fromId)->delete();

                // ¿B ya está en esta fórmula? (toma una sola fila si hay duplicados de B).
                $bRow = $this->db->table('item_general_formulaciones')
                    ->where('formulaciones_id', $fid)->where('item_general_id', $toId)
                    ->orderBy('id_item_general_formulaciones', 'ASC')->get(1)->getRowArray();

                if ($bRow) {
                    $this->db->table('item_general_formulaciones')
                        ->where('id_item_general_formulaciones', $bRow['id_item_general_formulaciones'])
                        ->update([
                            'cantidad'   => (float) $bRow['cantidad']   + $sumC,
                            'porcentaje' => (float) $bRow['porcentaje'] + $sumP,
                        ]);
                    $consolidadas++;
                } else {
                    $this->db->table('item_general_formulaciones')->insert([
                        'formulaciones_id' => $fid,
                        'item_general_id'  => $toId,
                        'cantidad'         => $sumC,
                        'porcentaje'       => $sumP,
                    ]);
                    $repuntadas++;
                }
            }

            // 3. Soft-delete de A si quedó sin uso (ni fórmulas ni stock activo).
            $usoRestante = (int) ($this->db->query(
                'SELECT COUNT(*) c FROM item_general_formulaciones WHERE item_general_id = ?', [$fromId]
            )->getRow()->c ?? 0);
            $stockActivo = (int) ($this->db->query(
                'SELECT COUNT(*) c FROM inventario_capas WHERE item_general_id = ? AND estado = 1 AND cantidad_disponible > 0',
                [$fromId]
            )->getRow()->c ?? 0);
            $aEliminada = false;
            if ($usoRestante === 0 && $stockActivo === 0) {
                $this->db->table('item_general')
                    ->where('id_item_general', $fromId)
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                $aEliminada = true;
            }

            // 4. Registrar el log con el snapshot (para deshacer).
            $logId = null;
            if (!empty($afectadasIds)) {
                $this->db->table('item_reemplazo_log')->insert([
                    'from_item_id'       => $fromId,
                    'to_item_id'         => $toId,
                    'from_nombre'        => mb_substr((string) ($from['nombre'] ?? ''), 0, 150),
                    'to_nombre'          => mb_substr((string) ($to['nombre'] ?? ''), 0, 150),
                    'formulas_afectadas' => $consolidadas + $repuntadas,
                    'origen_eliminada'   => $aEliminada ? 1 : 0,
                    'snapshot'           => json_encode($snapshot),
                    'usuario'            => mb_substr($usuario, 0, 100),
                    'revertido'          => 0,
                    'created_at'         => date('Y-m-d H:i:s'),
                ]);
                $logId = $this->db->insertID();
            }

            $this->db->transCommit();

            return [
                'ok'                  => true,
                'log_id'              => $logId,
                'consolidadas'        => $consolidadas,
                'repuntadas'          => $repuntadas,
                'formulas_afectadas'  => $consolidadas + $repuntadas,
                'origen_eliminada'    => $aEliminada,
                'origen_uso_restante' => $usoRestante,
                'origen_stock_activo' => $stockActivo,
                'msg' => "Reemplazo aplicado: {$repuntadas} repuntada(s), {$consolidadas} consolidada(s)"
                    . ($aEliminada ? '. La materia origen quedó sin uso y se marcó como eliminada.' : '.'),
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /** Últimos reemplazos aplicados (para el historial / deshacer). */
    public function historialReemplazos(int $limit = 20): array
    {
        return $this->db->table('item_reemplazo_log')
            ->select('id, from_item_id, to_item_id, from_nombre, to_nombre, formulas_afectadas, origen_eliminada, usuario, revertido, created_at, revertido_at')
            ->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')
            ->limit($limit)->get()->getResultArray();
    }

    /**
     * Deshace un reemplazo: restaura el BOM (A y B en las fórmulas afectadas) desde el snapshot
     * y des-elimina A si había quedado soft-deleted. Restaura al estado previo de A/B en esas fórmulas.
     */
    public function revertirReemplazo(int $logId, string $usuario = 'sistema'): array
    {
        $log = $this->db->table('item_reemplazo_log')->where('id', $logId)->get()->getRowArray();
        if (!$log)                       throw new \InvalidArgumentException('Reemplazo no encontrado.');
        if ((int) $log['revertido'] === 1) throw new \InvalidArgumentException('Este reemplazo ya fue deshecho.');

        $fromId = (int) $log['from_item_id'];
        $toId   = (int) $log['to_item_id'];
        $snapshot = json_decode($log['snapshot'] ?? '[]', true) ?: [];
        $afectadas = array_values(array_unique(array_map(static fn($r) => (int) $r['formulaciones_id'], $snapshot)));

        $this->db->transBegin();
        try {
            if (!empty($afectadas)) {
                $ph = implode(',', array_fill(0, count($afectadas), '?'));
                // Borrar el estado ACTUAL de A y B en las fórmulas afectadas...
                $this->db->query(
                    "DELETE FROM item_general_formulaciones
                     WHERE item_general_id IN (?, ?) AND formulaciones_id IN ($ph)",
                    array_merge([$fromId, $toId], $afectadas)
                );
                // ...y restaurar exactamente las filas del snapshot.
                foreach ($snapshot as $r) {
                    $this->db->table('item_general_formulaciones')->insert([
                        'formulaciones_id' => (int) $r['formulaciones_id'],
                        'item_general_id'  => (int) $r['item_general_id'],
                        'cantidad'         => $r['cantidad'],
                        'porcentaje'       => $r['porcentaje'],
                    ]);
                }
            }

            // Des-eliminar A si se había soft-deleteado.
            if ((int) $log['origen_eliminada'] === 1) {
                $this->db->table('item_general')->where('id_item_general', $fromId)->update(['deleted_at' => null]);
            }

            $this->db->table('item_reemplazo_log')->where('id', $logId)->update([
                'revertido'    => 1,
                'revertido_at' => date('Y-m-d H:i:s'),
                'usuario'      => mb_substr($usuario, 0, 100),
            ]);

            $this->db->transCommit();
            return [
                'ok'  => true,
                'msg' => "Reemplazo deshecho: restauradas {$log['formulas_afectadas']} fórmula(s)"
                    . ((int) $log['origen_eliminada'] === 1 ? " y se restauró «{$log['from_nombre']}»." : '.'),
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
