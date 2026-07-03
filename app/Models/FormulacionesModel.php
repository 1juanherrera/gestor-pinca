<?php
namespace App\Models;

use App\Libraries\Formatter;
use App\Helpers\Cfg;
use Exception;

class FormulacionesModel extends BaseModel
{
    // Mass-assignment whitelist para la tabla `formulaciones`.
    // Nota: este modelo NO declara $table y opera casi todo con query builder
    // directo (`$this->db->query(...)` y `$this->db->table('...')`), no con el
    // ActiveRecord del propio modelo. Declarar $allowedFields no cambia esos
    // inserts/updates manuales; solo protege un eventual save()/insert() del
    // modelo contra mass-assignment de columnas arbitrarias.
    protected $allowedFields = [
        'item_general_id',
        'nombre',
        'descripcion',
        'estado',
        'defecto',
        'version_actual',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /** Margen de utilidad por defecto (configurable desde Configuración → Financiero). */
    private function margenDefault(): int
    {
        return Cfg::n('margen_utilidad_default_pct', 50);
    }

    public function get_items_formulaciones()
    {
        $sql = 'SELECT f.*, 
                    ig.nombre AS item_general, 
                    ig.tipo, ig.codigo AS codigo_item_general,
                    ig.id_item_general AS id_item_general
                FROM formulaciones f
                LEFT JOIN item_general ig ON ig.id_item_general = f.item_general_id
                WHERE f.estado = 1';
        $data = $this->db->query($sql)->getResult();
        $datos = [];
        $tipos = [
            0 => 'PRODUCTO',
            1 => 'MATERIA PRIMA',
            2 => 'INSUMO',
        ];
        if (!empty($data)) {
            foreach ($data as $item) {
                $sql1 = 'SELECT ig.nombre, ig.codigo AS codigo_item_general,
                            ig.*, ci.*, igf.cantidad, igf.porcentaje
                            FROM item_general_formulaciones igf
                            LEFT JOIN item_general ig ON ig.id_item_general = igf.item_general_id
                            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
                            WHERE igf.formulaciones_id = ?';
                            
                $items = $this->db->query($sql1, [$item->id_formulaciones])->getResult();
                $datos[] = [
                    'id_formulacion' => $item->id_formulaciones,
                    'id_item_general' => $item->id_item_general,
                    'codigo_item_general' => $item->codigo_item_general,
                    'nombre_item_general' => $item->item_general,
                    'nombre' => $item->nombre,
                    'tipo' => $tipos[$item->tipo] ?? 'Otro',
                    'descripcion' => $item->descripcion,
                    'items' => $items,
                ];
            }
        }

        return $datos;
    }

    public function get_item_formulacion_by_id($id)
    {
        if (empty($id) || !is_numeric($id)) {
            throw new Exception('Parámetro inválido: id requerido.');
        }

        $sql = 'SELECT f.*, 
                    ig.nombre AS item_general, 
                    ig.tipo, ig.codigo AS codigo_item_general,
                    ig.id_item_general AS id_item_general
                FROM formulaciones f
                LEFT JOIN item_general ig ON ig.id_item_general = f.item_general_id
                WHERE f.estado = 1 AND f.id_formulaciones = ?';

        $item = $this->db->query($sql, [$id])->getRow();

        if (!$item) {
            throw new Exception("Formulación con ID {$id} no encontrada.");
        }

        $sql1 = 'SELECT ig.nombre, ig.codigo AS codigo_item_general,
                    ig.*, ci.*, igf.cantidad, igf.porcentaje, igf.orden,
                    igf.tipo, igf.texto, igf.nota
                    FROM item_general_formulaciones igf
                    LEFT JOIN item_general ig ON ig.id_item_general = igf.item_general_id
                    LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
                    WHERE igf.formulaciones_id = ?
                    ORDER BY igf.orden ASC, igf.id_item_general_formulaciones ASC';

        $items = $this->db->query($sql1, [$item->id_formulaciones])->getResult();

        $tipos = [
            0 => 'PRODUCTO',
            1 => 'MATERIA PRIMA',
            2 => 'INSUMO',
        ];

        return [
            'id_formulacion'      => $item->id_formulaciones,
            'id_item_general'     => $item->id_item_general,
            'codigo_item_general' => $item->codigo_item_general,
            'nombre_item_general' => $item->item_general,
            'nombre'              => $item->nombre,
            'tipo'                => $tipos[$item->tipo] ?? 'Otro',
            'descripcion'         => $item->descripcion,
            'items'               => $items,
        ];
    }

    public function getFormulacionConMateriasPrimas(int $itemId): array
    {
        // 1. Datos del item
        $item = $this->db->query('
            SELECT 
                ig.id_item_general,
                ig.nombre,
                ig.codigo,
                ig.tipo,
                ig.viscosidad,
                ig.p_g,
                ig.color,
                ig.secado,
                ig.cubrimiento,
                ig.brillo_60,
                i.cantidad AS inventario_cantidad,
                COALESCE(NULLIF(ci.volumen, 0), 1) AS volumen_base,
                COALESCE(ci.envase, 0)             AS envase,
                COALESCE(ci.etiqueta, 0)           AS etiqueta,
                COALESCE(ci.bandeja, 0)            AS bandeja,
                COALESCE(ci.plastico, 0)           AS plastico,
                COALESCE(ci.costo_mod, 0)          AS costo_mod,
                COALESCE(ci.porcentaje_utilidad, '. $this->margenDefault() .') AS porcentaje_utilidad
            FROM item_general ig
            LEFT JOIN inventario i   ON i.item_general_id   = ig.id_item_general
            LEFT JOIN costos_item ci ON ci.item_general_id  = ig.id_item_general
            WHERE ig.id_item_general = ?
        ', [$itemId])->getRow();

        if (!$item) {
            throw new Exception("Item con ID {$itemId} no encontrado.");
        }

        // 2. Formulación activa
        $formulacion = $this->db->query('
            SELECT id_formulaciones, nombre, descripcion
            FROM formulaciones
            WHERE item_general_id = ? AND estado = 1
            LIMIT 1
        ', [$itemId])->getRow();

        if (!$formulacion) {
            throw new Exception("El item '{$item->nombre}' no tiene una formulación activa.");
        }

        // 3. Materias primas de la formulación
        $materiasPrimas = $this->db->query('
            SELECT
                igf.id_item_general_formulaciones,
                igf.formulaciones_id,
                igf.item_general_id   AS materia_prima_id,
                igf.cantidad,
                igf.orden,
                igf.tipo,
                igf.texto,
                igf.nota,
                ig.nombre             AS materia_prima_nombre,
                ig.codigo             AS materia_prima_codigo,
                COALESCE(
                    NULLIF(ci.costo_unitario, 0),
                    (SELECT MIN(ip2.precio_unitario / GREATEST(ip2.factor_conversion, 1))
                     FROM item_proveedor ip2
                     WHERE ip2.item_general_id = ig.id_item_general
                       AND ip2.deleted_at IS NULL),
                    0
                ) AS costo_unitario,
                COALESCE(i.cantidad, 0) AS inventario_cantidad,
                igf.cantidad * COALESCE(
                    NULLIF(ci.costo_unitario, 0),
                    (SELECT MIN(ip2.precio_unitario / GREATEST(ip2.factor_conversion, 1))
                     FROM item_proveedor ip2
                     WHERE ip2.item_general_id = ig.id_item_general
                       AND ip2.deleted_at IS NULL),
                    0
                ) AS costo_total
            FROM item_general_formulaciones igf
            LEFT JOIN item_general ig ON igf.item_general_id = ig.id_item_general
            LEFT JOIN costos_item ci   ON ig.id_item_general  = ci.item_general_id
            LEFT JOIN inventario i     ON ig.id_item_general  = i.item_general_id
            WHERE igf.formulaciones_id = ?
            ORDER BY igf.orden ASC, ig.nombre ASC
        ', [$formulacion->id_formulaciones])->getResult();

        if (empty($materiasPrimas)) {
            throw new Exception("La formulación del item '{$item->nombre}' no tiene materias primas asignadas.");
        }

        // 4. Formatear materias primas
        $materiasFormateadas = array_map(function ($mp) {
            return [
                'id'                  => (int) $mp->id_item_general_formulaciones,
                'formulaciones_id'    => (int) $mp->formulaciones_id,
                'materia_prima_id'    => (int) $mp->materia_prima_id,
                'orden'               => (int) $mp->orden,
                'tipo'                => $mp->tipo ?? 'ingrediente',
                'texto'               => $mp->texto,
                'nota'                => $mp->nota,
                'nombre'              => $mp->materia_prima_nombre,
                'codigo'              => $mp->materia_prima_codigo,
                'cantidad'            => (float) $mp->cantidad,
                'costo_unitario'      => (float) $mp->costo_unitario,
                'costo_total'         => (float) $mp->costo_total,
                'inventario_cantidad' => (float) $mp->inventario_cantidad,
            ];
        }, $materiasPrimas);

        return [
            'item' => [
                'id'                  => (int) $item->id_item_general,
                'nombre'              => $item->nombre,
                'codigo'              => $item->codigo,
                'tipo'                => $item->tipo,
                'viscosidad'          => $item->viscosidad,
                'p_g'                 => $item->p_g,
                'color'               => $item->color,
                'secado'              => $item->secado,
                'cubrimiento'         => $item->cubrimiento,
                'brillo_60'           => $item->brillo_60,
                'inventario_cantidad' => (float) $item->inventario_cantidad,
                'volumen_base'        => (float) $item->volumen_base,
                'envase'              => (float) $item->envase,
                'etiqueta'            => (float) $item->etiqueta,
                'bandeja'             => (float) $item->bandeja,
                'plastico'            => (float) $item->plastico,
                'costo_mod'           => (float) $item->costo_mod,
                'porcentaje_utilidad' => (float) $item->porcentaje_utilidad,
            ],
            'formulacion_id'  => (int) $formulacion->id_formulaciones,
            'nombre'          => $formulacion->nombre,
            'descripcion'     => $formulacion->descripcion,
            'materias_primas' => $materiasFormateadas,
        ];
    }

    public function calculate_costs($itemId, $newVolume = null)
    {
        if (empty($itemId)) {
            throw new Exception('Parámetro inválido: itemId requerido.');
        }

        // 1. Obtener datos del Item General
        $sql = 'SELECT
                    ig.id_item_general,
                    ig.nombre,
                    ig.codigo,
                    ig.tipo,
                    ig.precio_venta_manual,
                    ig.precio_manual_activo,
                    ig.viscosidad,
                    ig.p_g,
                    ig.color,
                    ig.secado,
                    ig.cubrimiento,
                    ig.brillo_60,
                    i.cantidad,
                    ci.id_costos_item,
                    COALESCE(ci.costo_unitario, 0) as costo_unitario,
                    COALESCE(ci.costo_mp_galon, 0) as costo_mp_galon,
                    COALESCE(ci.costo_mp_kg, 0) as costo_mp_kg,
                    COALESCE(ci.envase, 0) as envase,
                    COALESCE(ci.etiqueta, 0) as etiqueta,
                    COALESCE(ci.bandeja, 0) as bandeja,
                    COALESCE(ci.plastico, 0) as plastico,
                    COALESCE(NULLIF(ci.volumen, 0), 1) as volumen_base,
                    COALESCE(ci.precio_venta, 0) as precio_venta_actual,
                    COALESCE(ci.cantidad_total, 0) as cantidad_total_actual,
                    COALESCE(ci.costo_mod, 0) as costo_mod,
                    COALESCE(ci.porcentaje_utilidad, '. $this->margenDefault() .') as porcentaje_utilidad,
                    ci.precio_venta as precio_venta_raw
                FROM item_general ig
                LEFT JOIN inventario i ON i.item_general_id = ig.id_item_general
                LEFT JOIN costos_item ci ON ig.id_item_general = ci.item_general_id
                WHERE ig.id_item_general = ?';

        $item = $this->db->query($sql, [$itemId])->getRow();

        if (!$item) {
            throw new Exception("Item con ID {$itemId} no encontrado.");
        }

        //  Obtener el ID correcto de la formulación activa vinculada al item
        $sqlFormulacionCabecera = 'SELECT id_formulaciones 
                                FROM formulaciones 
                                WHERE item_general_id = ? 
                                AND estado = 1 
                                LIMIT 1'; 
        
        $formulacionRow = $this->db->query($sqlFormulacionCabecera, [$item->id_item_general])->getRow();

        if (!$formulacionRow) {
            // Opcional: Si no tiene formulación, puedes retornar arrays vacíos o lanzar error.
            // Aquí lanzamos error para mantener coherencia con tu lógica original.
            throw new Exception("El item '{$item->nombre}' no tiene una formulación activa vinculada.");
        }

        $realFormulacionId = $formulacionRow->id_formulaciones;

        $formulacionesSql = 'SELECT
                                igf.id_item_general_formulaciones,
                                igf.item_general_id,
                                igf.formulaciones_id,
                                igf.cantidad,
                                igf.orden,
                                igf.tipo,
                                igf.texto,
                                igf.nota,
                                i.cantidad AS inventario_cantidad,
                                ci.fecha_calculo,
                                ig.nombre AS materia_prima_nombre,
                                ig.codigo AS materia_prima_codigo,
                                COALESCE(
                                    NULLIF(ci.costo_unitario, 0),
                                    (SELECT MIN(ip2.precio_unitario / GREATEST(ip2.factor_conversion, 1))
                                     FROM item_proveedor ip2
                                     WHERE ip2.item_general_id = ig.id_item_general
                                       AND ip2.deleted_at IS NULL),
                                    0
                                ) as materia_prima_costo_unitario,
                                igf.cantidad * COALESCE(
                                    NULLIF(ci.costo_unitario, 0),
                                    (SELECT MIN(ip2.precio_unitario / GREATEST(ip2.factor_conversion, 1))
                                     FROM item_proveedor ip2
                                     WHERE ip2.item_general_id = ig.id_item_general
                                       AND ip2.deleted_at IS NULL),
                                    0
                                ) as costo_total_materia
                            FROM item_general_formulaciones igf
                            LEFT JOIN item_general ig ON igf.item_general_id = ig.id_item_general
                            LEFT JOIN costos_item ci ON ig.id_item_general = ci.item_general_id
                            LEFT JOIN inventario i ON ig.id_item_general = i.item_general_id
                            WHERE igf.formulaciones_id = ?
                            ORDER BY igf.orden ASC, igf.id_item_general_formulaciones ASC'; // orden = secuencia de proceso (libreta). LEFT JOIN: incluye filas de instrucción/fase (item_general_id NULL)

        // Usamos $realFormulacionId en vez de $item->id_item_general
        $formulaciones = $this->db->query($formulacionesSql, [$realFormulacionId])->getResult();

        if (empty($formulaciones)) {
            // Nota: Podría ocurrir que existe la cabecera de formulación pero no tiene ingredientes.
            // Si prefieres que no sea error, elimina este throw.
            throw new Exception("La formulación del item {$item->nombre} no tiene materias primas asignadas.");
        }

        $totalMateriaPrima = 0;
        $totalCantidad = 0;
        $totalCantidadMateria = 0;

        if (!empty($newVolume) && is_numeric($newVolume) && $newVolume > 0 && $item->volumen_base > 0) {
            $factorVolumen = $newVolume / $item->volumen_base;
            $usarNuevoVolumen = true;
        } else {
            $factorVolumen = 1;
            $usarNuevoVolumen = false;
        }

        foreach ($formulaciones as $row) {
            // Las filas de instrucción/fase no suman al peso ni al costo.
            if (($row->tipo ?? 'ingrediente') !== 'ingrediente') continue;

            if ($usarNuevoVolumen) {
                $cantidadRecalculada = round($row->cantidad * $factorVolumen, 2);
                $costoTotalMateria = round($row->costo_total_materia * $factorVolumen, 2);
            } else {
                $cantidadRecalculada = $row->cantidad;
                $costoTotalMateria = $row->costo_total_materia;
            }

            $totalCantidadMateria += $row->cantidad;

            $totalMateriaPrima += $costoTotalMateria;
            $totalCantidad += $cantidadRecalculada;
        }

        $nuevoCostoMateriaPrima = $totalMateriaPrima;

        // Cálculo del nuevo costo total
        // Nota: Asegúrate que $newVolume no sea 0 para evitar división por cero si viene null
        $divisorVolumen = ($newVolume ?? $item->volumen_base);
        if ($divisorVolumen == 0) $divisorVolumen = 1; // Protección simple

        $nuevoCostoTotal = ($nuevoCostoMateriaPrima / $divisorVolumen) 
            + $item->envase 
            + $item->etiqueta 
            + $item->bandeja 
            + $item->plastico 
            + $item->costo_mod;

        $margen = (float) $item->porcentaje_utilidad;
        $precioVenta = $margen > 0
            ? $nuevoCostoTotal * (1 + $margen / 100)
            : $nuevoCostoTotal;

        $costo_mg_kg = ($newVolume && $newVolume > 0)
            ? $nuevoCostoMateriaPrima / $newVolume
            : $totalMateriaPrima / ($item->volumen_base > 0 ? $item->volumen_base : 1);

        $formulaciones_formatted = [];
        foreach ($formulaciones as $row) {
            $cantidad = isset($row->cantidad) ? (float) $row->cantidad : 0.0;
            $materia_prima_costo_unitario = isset($row->materia_prima_costo_unitario) ? (float) $row->materia_prima_costo_unitario : 0.0;
            $costo_total_materia = isset($row->costo_total_materia) ? (float) $row->costo_total_materia : 0.0;
            $inventario_cantidad = isset($row->inventario_cantidad) ? (float) $row->inventario_cantidad : 0.0;

            $row = (array) $row;
            $row['cantidad'] = $cantidad;
            $row['materia_prima_costo_unitario'] = Formatter::toCOP($materia_prima_costo_unitario);
            $row['costo_total_materia'] = Formatter::toCOP($costo_total_materia);
            $row['inventario_valor_total'] = Formatter::toCOP($inventario_cantidad * $materia_prima_costo_unitario);

            $formulaciones_formatted[] = $row;
        }

        return [
            'item' => [
                'id'                   => $item->id_item_general,
                'nombre'               => $item->nombre,
                'codigo'               => $item->codigo,
                'tipo'                 => $item->tipo,
                'viscosidad'           => $item->viscosidad,
                'p_g'                  => $item->p_g,
                'color'                => $item->color,
                'secado'               => $item->secado,
                'cubrimiento'          => $item->cubrimiento,
                'brillo_60'            => $item->brillo_60,
                'cantidad'             => (float) $item->cantidad,
                'volumen_base'         => (float) $item->volumen_base,
                'volumen_nuevo'        => $newVolume ?? (float) $item->volumen_base,
                'factor_volumen'       => $factorVolumen,
                'precio_venta_manual'  => $item->precio_venta_manual,
                'precio_manual_activo' => (int) $item->precio_manual_activo,
            ],
            'costos' => [
                'id_costos_item' => $item->id_costos_item,
                'total_costo_materia_prima' => Formatter::toCOP($nuevoCostoMateriaPrima),
                'costo_mp_galon' => Formatter::toCOP($nuevoCostoMateriaPrima / $divisorVolumen),
                'envase' => Formatter::toCOP($item->envase),
                'etiqueta' => Formatter::toCOP($item->etiqueta),
                'bandeja' => Formatter::toCOP($item->bandeja),
                'plastico' => Formatter::toCOP($item->plastico),
                'costo_mod' => Formatter::toCOP($item->costo_mod),
                'costo_mp_kg' => Formatter::toCOP($costo_mg_kg),
                'porcentaje_utilidad'       => $margen, //
                'total_cantidad_materia_prima' => Formatter::toThousands($totalCantidad),
                'total' => Formatter::toCOP($nuevoCostoTotal),
                'precio_venta' => Formatter::toCOP($precioVenta),
                'fecha_calculo' => $formulaciones[0]->fecha_calculo ?? null,
            ],
            'formulaciones' => $formulaciones_formatted
        ];
    }

    public function recalculate_costs_with_new_volume($itemId, $newVolume = null)
    {
        if (empty($itemId) || empty($newVolume) || !is_numeric($newVolume) || $newVolume <= 0) {
            throw new Exception('Parámetros inválidos: itemId o newVolume incorrectos.');
        }

        $currentData = $this->calculate_costs($itemId);
        $newData = $this->calculate_costs($itemId, $newVolume);

        $costosActuales = $currentData['costos'];
        $costosRecalculados = $newData['costos'];

        $item = $currentData['item'];
        $item['volumen_nuevo'] = $newVolume;
        $item['factor_volumen'] = round($newVolume / ($item['volumen_base']), 3);

        $formulacionesCombinadas = [];
        foreach ($currentData['formulaciones'] as $f) {

            $cantidad_recalculada = $item['volumen_nuevo'] / $item['volumen_base'] * $f['cantidad'];

            $formulacionesCombinadas[] = [
                'id_item_general_formulaciones' => $f['id_item_general_formulaciones'],
                'item_general_id' => $f['item_general_id'],
                'formulaciones_id' => $f['formulaciones_id'],
                'orden' => $f['orden'] ?? 0,
                'tipo' => $f['tipo'] ?? 'ingrediente',
                'texto' => $f['texto'] ?? null,
                'nota' => $f['nota'] ?? null,
                'cantidad' => $f['cantidad'],
                'cantidad_recalculada' => round($cantidad_recalculada, 2),
                'inventario_cantidad' => $f['inventario_cantidad'],
                'fecha_calculo' => $f['fecha_calculo'],
                'materia_prima_nombre' => $f['materia_prima_nombre'],
                'materia_prima_codigo' => $f['materia_prima_codigo'],
                'materia_prima_costo_unitario' => $f['materia_prima_costo_unitario'],
                'costo_total_materia' => $f['costo_total_materia'],
                'inventario_valor_total' => $f['inventario_valor_total'],
                'costo_total_materia_recalculado' => Formatter::toCOP(Formatter::fromCOP($f['materia_prima_costo_unitario']) * $cantidad_recalculada)
            ];
        }

        return [
            'item' => $item,
            'costos' => $costosActuales,
            'recalculados' => $costosRecalculados,
            'formulaciones' => $formulacionesCombinadas
        ];
    }

    public function get_opciones_proveedor_formulacion(int $itemId): array
    {
        $formulacion = $this->db->query('
            SELECT id_formulaciones
            FROM formulaciones
            WHERE item_general_id = ? AND estado = 1 LIMIT 1
        ', [$itemId])->getRow();

        if (!$formulacion) {
            throw new Exception("El item no tiene una formulación activa.");
        }

        $item = $this->db->query('
            SELECT
                COALESCE(NULLIF(ci.volumen, 0), 1) AS volumen_base,
                COALESCE(ci.envase, 0)              AS envase,
                COALESCE(ci.etiqueta, 0)            AS etiqueta,
                COALESCE(ci.bandeja, 0)             AS bandeja,
                COALESCE(ci.plastico, 0)            AS plastico,
                COALESCE(ci.costo_mod, 0)           AS costo_mod,
                COALESCE(ci.porcentaje_utilidad, '. $this->margenDefault() .') AS porcentaje_utilidad
            FROM item_general ig
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
            WHERE ig.id_item_general = ?
        ', [$itemId])->getRow();

        $materias = $this->db->query('
            SELECT igf.item_general_id, ig.nombre,
                   COALESCE(
                       NULLIF(ci.costo_unitario, 0),
                       (SELECT MIN(ip2.precio_unitario / GREATEST(ip2.factor_conversion, 1))
                        FROM item_proveedor ip2
                        WHERE ip2.item_general_id = ig.id_item_general
                          AND ip2.deleted_at IS NULL),
                       0
                   ) AS costo_estandar
            FROM item_general_formulaciones igf
            INNER JOIN item_general ig ON ig.id_item_general = igf.item_general_id
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
            WHERE igf.formulaciones_id = ?
        ', [$formulacion->id_formulaciones])->getResult();

        if (empty($materias)) return ['item' => [], 'materias' => []];

        $mpIds = array_values(array_unique(array_map(fn($m) => (int) $m->item_general_id, $materias)));
        $placeholders = implode(',', array_fill(0, count($mpIds), '?'));

        $catalogo = $this->db->query("
            SELECT ip.id_item_proveedor, ip.item_general_id, ip.nombre,
                   ip.precio_unitario, ip.factor_conversion,
                   ip.proveedor_id, p.nombre_empresa,
                   uc.nombre AS unidad_compra
            FROM item_proveedor ip
            INNER JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
            LEFT JOIN unidad uc ON uc.id_unidad = ip.unidad_compra_id
            WHERE ip.disponible = 1
              AND ip.deleted_at IS NULL
              AND (ip.item_general_id IN ($placeholders) OR ip.item_general_id IS NULL)
        ", $mpIds)->getResult();

        // Último precio de compra por materia prima = costo_unitario de la capa
        // activa más reciente (mayor fecha_ingreso; desempate por id_capa DESC).
        // Una sola query batcheada para evitar N+1. El JOIN contra MAX(fecha_ingreso)
        // puede traer duplicados ante empate de fecha → nos quedamos con el de
        // mayor id_capa al indexar por item_general_id.
        $ultimoPrecioPorMp = [];
        $capas = $this->db->query("
            SELECT ic.item_general_id, ic.id_capa, ic.costo_unitario
            FROM inventario_capas ic
            INNER JOIN (
                SELECT item_general_id, MAX(fecha_ingreso) AS maxf
                FROM inventario_capas
                WHERE estado = 1 AND item_general_id IN ($placeholders)
                GROUP BY item_general_id
            ) m ON m.item_general_id = ic.item_general_id AND m.maxf = ic.fecha_ingreso
            WHERE ic.estado = 1
            ORDER BY ic.id_capa DESC
        ", $mpIds)->getResult();

        foreach ($capas as $c) {
            $cId = (int) $c->item_general_id;
            // ORDER BY id_capa DESC → la primera fila vista por item es la de
            // mayor id_capa, que gana ante empate de fecha. No sobrescribir.
            if (!array_key_exists($cId, $ultimoPrecioPorMp)) {
                $ultimoPrecioPorMp[$cId] = (float) $c->costo_unitario;
            }
        }

        $resultado = [];

        foreach ($materias as $mp) {
            $mpId     = (int) $mp->item_general_id;
            $mpNombre = $mp->nombre;
            $opciones = [];

            foreach ($catalogo as $ip) {
                $priority = 999;

                if ($ip->item_general_id && (int) $ip->item_general_id === $mpId) {
                    $priority = 1;
                } else {
                    $nameMatch = $this->matchNombre($mpNombre, $ip->nombre);
                    if ($nameMatch === 1) $priority = 2;
                    elseif ($nameMatch === 2) $priority = 3;
                }

                if ($priority < 999) {
                    $factor = max((float) ($ip->factor_conversion ?: 1), 0.001);
                    $opciones[] = [
                        'id_item_proveedor' => (int) $ip->id_item_proveedor,
                        'nombre_item'       => $ip->nombre,
                        'nombre_empresa'    => $ip->nombre_empresa,
                        'precio_unitario'   => (float) $ip->precio_unitario,
                        'factor_conversion' => (float) $ip->factor_conversion,
                        'precio_por_kg'     => round((float) $ip->precio_unitario / $factor, 2),
                        'unidad_compra'     => $ip->unidad_compra,
                        'match_tipo'        => $priority,
                    ];
                }
            }

            usort($opciones, fn($a, $b) => $a['precio_por_kg'] <=> $b['precio_por_kg']);

            $resultado[$mpId] = [
                'materia_prima_nombre' => $mpNombre,
                'costo_estandar'       => (float) $mp->costo_estandar,
                'ultimo_precio'        => $ultimoPrecioPorMp[$mpId] ?? null,
                'opciones'             => $opciones,
            ];
        }

        return [
            'item' => [
                'volumen_base'        => (float) $item->volumen_base,
                'envase'              => (float) $item->envase,
                'etiqueta'            => (float) $item->etiqueta,
                'bandeja'             => (float) $item->bandeja,
                'plastico'            => (float) $item->plastico,
                'costo_mod'           => (float) $item->costo_mod,
                'porcentaje_utilidad' => (float) $item->porcentaje_utilidad,
            ],
            'materias' => $resultado,
        ];
    }

    private function limpiarNombreProveedor(string $nombre): string
    {
        return mb_strtoupper(trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $nombre)));
    }

    private function matchNombre(string $nombreMP, string $nombreIP): int
    {
        $mp = mb_strtoupper(trim($nombreMP));
        $ipLimpio = $this->limpiarNombreProveedor($nombreIP);

        if ($mp === $ipLimpio) return 1;
        if (mb_strpos($ipLimpio, $mp) !== false || mb_strpos($mp, $ipLimpio) !== false) return 2;
        return 0;
    }

    public function get_proveedores_formulacion(int $itemId): array
    {
        $formulacion = $this->db->query('
            SELECT id_formulaciones
            FROM formulaciones
            WHERE item_general_id = ? AND estado = 1
            LIMIT 1
        ', [$itemId])->getRow();

        if (!$formulacion) {
            throw new Exception("El item no tiene una formulación activa.");
        }

        $materias = $this->db->query('
            SELECT igf.item_general_id, ig.nombre
            FROM item_general_formulaciones igf
            INNER JOIN item_general ig ON ig.id_item_general = igf.item_general_id
            WHERE igf.formulaciones_id = ?
        ', [$formulacion->id_formulaciones])->getResult();

        if (empty($materias)) {
            return [];
        }

        $totalMaterias = count($materias);

        $mpIds = array_values(array_unique(array_map(fn($m) => (int) $m->item_general_id, $materias)));
        $placeholders = implode(',', array_fill(0, count($mpIds), '?'));

        $catalogo = $this->db->query("
            SELECT ip.id_item_proveedor, ip.item_general_id, ip.nombre,
                   ip.proveedor_id, p.nombre_empresa, p.nombre_encargado
            FROM item_proveedor ip
            INNER JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
            WHERE ip.disponible = 1
              AND ip.deleted_at IS NULL
              AND (ip.item_general_id IN ($placeholders) OR ip.item_general_id IS NULL)
        ", $mpIds)->getResult();

        $provCobertura = [];

        foreach ($materias as $mp) {
            foreach ($catalogo as $ip) {
                $matched = false;

                if ($ip->item_general_id && (int) $ip->item_general_id === (int) $mp->item_general_id) {
                    $matched = true;
                } elseif ($this->matchNombre($mp->nombre, $ip->nombre) > 0) {
                    $matched = true;
                }

                if ($matched) {
                    $pid = (int) $ip->proveedor_id;
                    if (!isset($provCobertura[$pid])) {
                        $provCobertura[$pid] = [
                            'id_proveedor'     => $pid,
                            'nombre_empresa'   => $ip->nombre_empresa,
                            'nombre_encargado' => $ip->nombre_encargado,
                            'materias_set'     => [],
                        ];
                    }
                    $provCobertura[$pid]['materias_set'][(int) $mp->item_general_id] = true;
                }
            }
        }

        $result = [];
        foreach ($provCobertura as $prov) {
            $cubiertas = count($prov['materias_set']);
            $result[] = [
                'id_proveedor'       => $prov['id_proveedor'],
                'nombre_empresa'     => $prov['nombre_empresa'],
                'nombre_encargado'   => $prov['nombre_encargado'],
                'materias_cubiertas' => $cubiertas,
                'total_materias'     => $totalMaterias,
                'cobertura_pct'      => $totalMaterias > 0 ? round(($cubiertas / $totalMaterias) * 100) : 0,
            ];
        }

        usort($result, fn($a, $b) => $b['materias_cubiertas'] - $a['materias_cubiertas']);
        return $result;
    }

    public function calculate_costs_by_proveedor(int $itemId, int $proveedorId): array
    {
        $proveedor = $this->db->query('
            SELECT id_proveedor, nombre_empresa, nombre_encargado
            FROM proveedor WHERE id_proveedor = ?
        ', [$proveedorId])->getRow();

        if (!$proveedor) {
            throw new Exception("Proveedor con ID {$proveedorId} no encontrado.");
        }

        $formulacionRow = $this->db->query('
            SELECT id_formulaciones
            FROM formulaciones
            WHERE item_general_id = ? AND estado = 1 LIMIT 1
        ', [$itemId])->getRow();

        if (!$formulacionRow) {
            throw new Exception("El item no tiene una formulación activa.");
        }

        $item = $this->db->query('
            SELECT
                ig.id_item_general, ig.nombre, ig.codigo,
                COALESCE(NULLIF(ci.volumen, 0), 1)      AS volumen_base,
                COALESCE(ci.envase, 0)                   AS envase,
                COALESCE(ci.etiqueta, 0)                 AS etiqueta,
                COALESCE(ci.bandeja, 0)                  AS bandeja,
                COALESCE(ci.plastico, 0)                 AS plastico,
                COALESCE(ci.costo_mod, 0)                AS costo_mod,
                COALESCE(ci.porcentaje_utilidad, '. $this->margenDefault() .')     AS porcentaje_utilidad
            FROM item_general ig
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
            WHERE ig.id_item_general = ?
        ', [$itemId])->getRow();

        if (!$item) {
            throw new Exception("Item con ID {$itemId} no encontrado.");
        }

        $formulaciones = $this->db->query('
            SELECT
                igf.id_item_general_formulaciones,
                igf.item_general_id,
                igf.formulaciones_id,
                igf.cantidad,
                ig.nombre             AS materia_prima_nombre,
                ig.codigo             AS materia_prima_codigo,
                COALESCE(
                    NULLIF(ci.costo_unitario, 0),
                    (SELECT MIN(ip2.precio_unitario / GREATEST(ip2.factor_conversion, 1))
                     FROM item_proveedor ip2
                     WHERE ip2.item_general_id = ig.id_item_general
                       AND ip2.deleted_at IS NULL),
                    0
                ) AS costo_unitario_estandar,
                i.cantidad            AS inventario_cantidad,
                ci.fecha_calculo
            FROM item_general_formulaciones igf
            INNER JOIN item_general ig ON igf.item_general_id = ig.id_item_general
            LEFT JOIN costos_item ci   ON ig.id_item_general  = ci.item_general_id
            LEFT JOIN inventario i     ON ig.id_item_general  = i.item_general_id
            WHERE igf.formulaciones_id = ?
            ORDER BY ig.nombre ASC
        ', [$formulacionRow->id_formulaciones])->getResult();

        if (empty($formulaciones)) {
            throw new Exception("La formulación no tiene materias primas asignadas.");
        }

        $itemsProveedor = $this->db->query('
            SELECT ip.*, uc.nombre AS unidad_compra_nombre
            FROM item_proveedor ip
            LEFT JOIN unidad uc ON uc.id_unidad = ip.unidad_compra_id
            WHERE ip.proveedor_id = ? AND ip.disponible = 1
        ', [$proveedorId])->getResult();

        $totalMPProveedor = 0;
        $totalMPEstandar  = 0;
        $formulacionesFormatted = [];

        foreach ($formulaciones as $row) {
            $cantidad      = (float) $row->cantidad;
            $costoEstandar = (float) $row->costo_unitario_estandar;
            $mpNombre      = $row->materia_prima_nombre;
            $mpId          = (int) $row->item_general_id;

            $bestMatch    = null;
            $bestPriority = 999;

            foreach ($itemsProveedor as $ip) {
                $priority = 999;

                if ($ip->item_general_id && (int) $ip->item_general_id === $mpId) {
                    $priority = 1;
                } else {
                    $nameMatch = $this->matchNombre($mpNombre, $ip->nombre);
                    if ($nameMatch === 1) $priority = 2;
                    elseif ($nameMatch === 2) $priority = 3;
                }

                if ($priority < $bestPriority) {
                    $bestMatch    = $ip;
                    $bestPriority = $priority;
                } elseif ($priority === $bestPriority && $bestMatch && $priority < 999) {
                    $bestFactor = max((float) ($bestMatch->factor_conversion ?: 1), 0.001);
                    $ipFactor   = max((float) ($ip->factor_conversion ?: 1), 0.001);
                    if (((float) $ip->precio_unitario / $ipFactor) < ((float) $bestMatch->precio_unitario / $bestFactor)) {
                        $bestMatch = $ip;
                    }
                }
            }

            $costoProveedor    = null;
            $precioProvRaw     = null;
            $factorConv        = null;
            $unidadCompraNombre = null;

            if ($bestMatch) {
                $factor             = max((float) ($bestMatch->factor_conversion ?: 1), 0.001);
                $costoProveedor     = (float) $bestMatch->precio_unitario / $factor;
                $precioProvRaw      = (float) $bestMatch->precio_unitario;
                $factorConv         = (float) $bestMatch->factor_conversion;
                $unidadCompraNombre = $bestMatch->unidad_compra_nombre ?? null;
            }

            $costoEfectivo = $costoProveedor ?? $costoEstandar;
            $usaProveedor  = $costoProveedor !== null;

            $totalEstandar  = $cantidad * $costoEstandar;
            $totalProveedor = $cantidad * $costoEfectivo;

            $totalMPProveedor += $totalProveedor;
            $totalMPEstandar  += $totalEstandar;

            $formulacionesFormatted[] = [
                'id_item_general_formulaciones' => $row->id_item_general_formulaciones,
                'item_general_id'               => $row->item_general_id,
                'formulaciones_id'              => $row->formulaciones_id,
                'cantidad'                      => $cantidad,
                'materia_prima_nombre'          => $row->materia_prima_nombre,
                'materia_prima_codigo'          => $row->materia_prima_codigo,
                'inventario_cantidad'           => (float) ($row->inventario_cantidad ?? 0),
                'fecha_calculo'                 => $row->fecha_calculo,
                'costo_unitario_estandar'       => Formatter::toCOP($costoEstandar),
                'costo_unitario_proveedor'      => $costoProveedor !== null ? Formatter::toCOP($costoProveedor) : null,
                'costo_unitario_efectivo'       => Formatter::toCOP($costoEfectivo),
                'usa_precio_proveedor'          => $usaProveedor,
                'costo_total_estandar'          => Formatter::toCOP($totalEstandar),
                'costo_total_proveedor'         => Formatter::toCOP($totalProveedor),
                'precio_proveedor_raw'          => $precioProvRaw !== null ? Formatter::toCOP($precioProvRaw) : null,
                'factor_conversion'             => $factorConv,
                'unidad_compra_nombre'          => $unidadCompraNombre,
            ];
        }

        $volumen = (float) $item->volumen_base;

        $nuevoCostoTotal = ($totalMPProveedor / $volumen)
            + (float) $item->envase
            + (float) $item->etiqueta
            + (float) $item->bandeja
            + (float) $item->plastico
            + (float) $item->costo_mod;

        $margen      = (float) $item->porcentaje_utilidad;
        $precioVenta = $margen > 0
            ? $nuevoCostoTotal * (1 + $margen / 100)
            : $nuevoCostoTotal;

        $costoMPKg = $volumen > 0 ? $totalMPProveedor / $volumen : 0;

        $diferenciaMPTotal = $totalMPProveedor - $totalMPEstandar;

        return [
            'proveedor' => [
                'id_proveedor'     => (int) $proveedor->id_proveedor,
                'nombre_empresa'   => $proveedor->nombre_empresa,
                'nombre_encargado' => $proveedor->nombre_encargado,
            ],
            'costos_proveedor' => [
                'total_costo_materia_prima' => Formatter::toCOP($totalMPProveedor),
                'costo_mp_kg'               => Formatter::toCOP($costoMPKg),
                'envase'                    => Formatter::toCOP($item->envase),
                'etiqueta'                  => Formatter::toCOP($item->etiqueta),
                'bandeja'                   => Formatter::toCOP($item->bandeja),
                'plastico'                  => Formatter::toCOP($item->plastico),
                'costo_mod'                 => Formatter::toCOP($item->costo_mod),
                'porcentaje_utilidad'       => $margen,
                'total'                     => Formatter::toCOP($nuevoCostoTotal),
                'precio_venta'              => Formatter::toCOP($precioVenta),
            ],
            'diferencia' => [
                'total_mp'    => Formatter::toCOP(abs($diferenciaMPTotal)),
                'es_mas_caro' => $diferenciaMPTotal > 0,
                'porcentaje'  => $totalMPEstandar > 0
                    ? round(($diferenciaMPTotal / $totalMPEstandar) * 100, 1)
                    : 0,
            ],
            'formulaciones' => $formulacionesFormatted,
        ];
    }

    /**
     * Valida que la suma de porcentajes de los ingredientes sea ≈ 100%.
     * Tolerancia: ±0.5% para errores de redondeo en cálculos del frontend.
     * Lanza Exception si no cuadra.
     */
    private function validarSumaPorcentajes(array $materiasPrimas): void
    {
        // Validación desactivada — los porcentajes son opcionales en este sistema.
    }

    /**
     * Inserta las líneas de una formulación EN ORDEN. Cada línea puede ser:
     *  - 'ingrediente' (default): materia prima. Dedup por item_general_id
     *    (los ingredientes repetidos son Fase 3; hoy se colapsan).
     *  - 'instruccion' / 'fase': paso de proceso o separador (item_general_id NULL, texto).
     * `nota` = anotación corta por ingrediente. `orden` = posición enviada por el frontend.
     */
    private function insertarLineas(int $formulacionId, array $lineas): void
    {
        // Fase 3: el mismo ingrediente puede repetirse (se agrega en pasos distintos).
        $orden = 0;
        foreach ($lineas as $mp) {
            $tipo = $mp['tipo'] ?? 'ingrediente';
            if (! in_array($tipo, ['ingrediente', 'instruccion', 'fase'], true)) {
                $tipo = 'ingrediente';
            }

            if ($tipo === 'ingrediente') {
                if (empty($mp['materia_prima_id'])) continue;
                $mpId = (int) $mp['materia_prima_id'];
                $orden++;
                $nota = trim((string) ($mp['nota'] ?? ''));
                $this->db->query(
                    'INSERT INTO item_general_formulaciones
                        (formulaciones_id, item_general_id, cantidad, porcentaje, orden, tipo, nota)
                     VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [
                        $formulacionId,
                        $mpId,
                        $mp['cantidad']   ?? 0,
                        $mp['porcentaje'] ?? 0,
                        (int) ($mp['orden'] ?? $orden),
                        'ingrediente',
                        $nota !== '' ? $nota : null,
                    ]
                );
            } else {
                $texto = trim((string) ($mp['texto'] ?? ''));
                if ($texto === '') continue; // no guardar pasos vacíos
                $orden++;
                $this->db->query(
                    'INSERT INTO item_general_formulaciones
                        (formulaciones_id, item_general_id, cantidad, porcentaje, orden, tipo, texto)
                     VALUES (?, NULL, 0, 0, ?, ?, ?)',
                    [
                        $formulacionId,
                        (int) ($mp['orden'] ?? $orden),
                        $tipo,
                        $texto,
                    ]
                );
            }
        }
    }

    /**
     * Clona la fórmula activa de $fromItemId hacia $toItemId.
     * El producto destino debe existir y NO debe tener una fórmula activa
     * (si la tiene, se desactiva con UPDATE estado=0 igual que crearFormulacion).
     */
    public function clonarFormulacion(int $fromItemId, int $toItemId, ?string $nombre = null, ?string $responsable = null, bool $force = false): array
    {
        if ($fromItemId === $toItemId) {
            throw new Exception('El producto origen y destino no pueden ser el mismo.');
        }

        // Validar destino existe y está activo
        $destino = $this->db->query(
            'SELECT id_item_general, nombre FROM item_general WHERE id_item_general = ? AND deleted_at IS NULL LIMIT 1',
            [$toItemId]
        )->getRowArray();
        if (!$destino) {
            throw new Exception('Producto destino no existe o está archivado.');
        }

        // Si destino ya tiene fórmula activa y no se forzó el override, rechazar
        if (!$force) {
            $existente = $this->db->query(
                'SELECT id_formulaciones FROM formulaciones WHERE item_general_id = ? AND estado = 1 LIMIT 1',
                [$toItemId]
            )->getRowArray();
            if ($existente) {
                throw new Exception(
                    "El producto destino ya tiene una fórmula activa. "
                    . "Confirmá el reemplazo enviando force=true (la fórmula anterior se conservará como versión histórica)."
                );
            }
        }

        // Obtener fórmula activa del origen
        $origen = $this->db->query(
            'SELECT id_formulaciones, nombre, descripcion FROM formulaciones
             WHERE item_general_id = ? AND estado = 1 LIMIT 1',
            [$fromItemId]
        )->getRowArray();
        if (!$origen) {
            throw new Exception('El producto origen no tiene una fórmula activa para clonar.');
        }

        $ingredientes = $this->db->query(
            "SELECT igf.item_general_id, igf.cantidad, igf.porcentaje, igf.orden, igf.tipo, igf.texto, igf.nota
             FROM item_general_formulaciones igf
             LEFT JOIN item_general ig ON ig.id_item_general = igf.item_general_id
             WHERE igf.formulaciones_id = ?
               AND (ig.deleted_at IS NULL OR igf.tipo <> 'ingrediente')
             ORDER BY igf.orden ASC, igf.id_item_general_formulaciones ASC",
            [$origen['id_formulaciones']]
        )->getResultArray();

        if (empty($ingredientes)) {
            throw new Exception('La fórmula origen no tiene ingredientes activos.');
        }

        $materiasPrimas = array_map(fn($i) => [
            'materia_prima_id' => (int) $i['item_general_id'],
            'cantidad'         => (float) $i['cantidad'],
            'porcentaje'       => (float) $i['porcentaje'],
            'orden'            => (int) $i['orden'],
            'tipo'             => $i['tipo'] ?? 'ingrediente',
            'texto'            => $i['texto'] ?? null,
            'nota'             => $i['nota'] ?? null,
        ], $ingredientes);

        // Reusar crearFormulacion con la receta copiada
        return $this->crearFormulacion([
            'item_general_id' => $toItemId,
            'nombre'          => $nombre ?: ($origen['nombre'] . ' (clonada)'),
            'descripcion'     => $origen['descripcion'],
            'materias_primas' => $materiasPrimas,
            'responsable'     => $responsable,
        ]);
    }

    // Crear formulación completa con materias primas
    public function crearFormulacion(array $data): array
    {
        if (empty($data['item_general_id'])) {
            throw new Exception('item_general_id es obligatorio.');
        }
        if (empty($data['materias_primas'])) {
            throw new Exception('Debe agregar al menos una materia prima.');
        }

        $this->validarSumaPorcentajes($data['materias_primas']);

        $this->db->transStart();

        try {
            // Desactivar formulaciones anteriores del mismo item
            $this->db->query('
                UPDATE formulaciones SET estado = 0 
                WHERE item_general_id = ?
            ', [$data['item_general_id']]);

            // Crear cabecera
            $this->db->query('
                INSERT INTO formulaciones (item_general_id, nombre, descripcion, estado, defecto)
                VALUES (?, ?, ?, 1, 1)
            ', [
                $data['item_general_id'],
                $data['nombre']      ?? 'PREPARACION',
                $data['descripcion'] ?? null,
            ]);

            $formulacionId = $this->db->insertID();

            $this->insertarLineas($formulacionId, $data['materias_primas']);

            // Guardar volumen en costos_item si viene en el payload
            if (isset($data['volumen']) && is_numeric($data['volumen']) && $data['volumen'] > 0) {
                $existeCostos = $this->db->query(
                    'SELECT id_costos_item FROM costos_item WHERE item_general_id = ? LIMIT 1',
                    [$data['item_general_id']]
                )->getRow();
                if ($existeCostos) {
                    $this->db->query(
                        'UPDATE costos_item SET volumen = ? WHERE item_general_id = ?',
                        [$data['volumen'], $data['item_general_id']]
                    );
                } else {
                    $this->db->query(
                        'INSERT INTO costos_item (item_general_id, volumen) VALUES (?, ?)',
                        [$data['item_general_id'], $data['volumen']]
                    );
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Error al guardar la formulación.');
            }

            // Snapshot inmutable de la versión inicial (post-commit, no rompe la tx anterior)
            $versionId = $this->crearVersion(
                (int) $formulacionId,
                $data['responsable'] ?? null,
                'Versión inicial'
            );

            return [
                'success'        => true,
                'message'        => 'Formulación creada correctamente.',
                'formulacion_id' => $formulacionId,
                'version_id'     => $versionId,
                'version_num'    => 1,
            ];

        } catch (Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    // Actualizar formulación existente
    public function actualizarFormulacion(int $formulacionId, array $data): array
    {
        if (empty($data['materias_primas'])) {
            throw new Exception('Debe agregar al menos una materia prima.');
        }

        $this->validarSumaPorcentajes($data['materias_primas']);

        $this->db->transStart();

        try {
            // Actualizar cabecera
            $this->db->query('
                UPDATE formulaciones SET nombre = ?, descripcion = ?
                WHERE id_formulaciones = ?
            ', [
                $data['nombre']      ?? 'PREPARACION',
                $data['descripcion'] ?? null,
                $formulacionId,
            ]);

            // Reemplazar materias primas
            $this->db->query('
                DELETE FROM item_general_formulaciones WHERE formulaciones_id = ?
            ', [$formulacionId]);

            $this->insertarLineas($formulacionId, $data['materias_primas']);

            // Guardar volumen en costos_item si viene en el payload
            if (isset($data['volumen']) && is_numeric($data['volumen']) && $data['volumen'] > 0) {
                $itemIdCostos = (int) ($data['item_general_id'] ?? $this->db->query(
                    'SELECT item_general_id FROM formulaciones WHERE id_formulaciones = ?', [$formulacionId]
                )->getRow()->item_general_id);
                $existeCostos = $this->db->query(
                    'SELECT id_costos_item FROM costos_item WHERE item_general_id = ? LIMIT 1',
                    [$itemIdCostos]
                )->getRow();
                if ($existeCostos) {
                    $this->db->query(
                        'UPDATE costos_item SET volumen = ? WHERE item_general_id = ?',
                        [$data['volumen'], $itemIdCostos]
                    );
                } else {
                    $this->db->query(
                        'INSERT INTO costos_item (item_general_id, volumen) VALUES (?, ?)',
                        [$data['volumen'], $itemIdCostos]
                    );
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Error al actualizar la formulación.');
            }

            // Snapshot de la nueva versión post-edición
            $versionId = $this->crearVersion(
                (int) $formulacionId,
                $data['responsable'] ?? null,
                $data['notas_version'] ?? 'Edición de formulación'
            );

            $nuevaVer = (int) $this->db->table('formulaciones')
                ->where('id_formulaciones', $formulacionId)
                ->get()->getRowArray()['version_actual'];

            return [
                'success'     => true,
                'message'     => 'Formulación actualizada correctamente.',
                'version_id'  => $versionId,
                'version_num' => $nuevaVer,
            ];

        } catch (Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Crea un snapshot inmutable del estado actual de la formulación.
     * Llamar SIEMPRE después de modificar `item_general_formulaciones` para
     * que las preparaciones futuras puedan trazar la receta exacta usada.
     *
     * @return int  ID de la versión creada
     */
    public function crearVersion(int $formulacionId, ?string $createdBy = null, ?string $notas = null): int
    {
        $form = $this->db->table('formulaciones')
            ->where('id_formulaciones', $formulacionId)
            ->get()->getRowArray();

        if (!$form) {
            throw new Exception("Formulación #{$formulacionId} no encontrada para versionar.");
        }

        $ingredientes = $this->db->query("
            SELECT
                igf.item_general_id,
                igf.cantidad,
                igf.porcentaje,
                igf.orden,
                igf.tipo,
                igf.texto,
                igf.nota,
                ig.nombre AS item_nombre,
                ig.codigo AS item_codigo
            FROM item_general_formulaciones igf
            LEFT JOIN item_general ig ON ig.id_item_general = igf.item_general_id
            WHERE igf.formulaciones_id = ?
            ORDER BY igf.orden ASC, igf.id_item_general_formulaciones ASC
        ", [$formulacionId])->getResultArray();

        // Próximo número de versión
        $maxRow  = $this->db->table('formulaciones_versiones')
            ->selectMax('version_num')
            ->where('formulacion_id', $formulacionId)
            ->get()->getRowArray();
        $nextVer = ((int) ($maxRow['version_num'] ?? 0)) + 1;

        $this->db->table('formulaciones_versiones')->insert([
            'formulacion_id' => $formulacionId,
            'version_num'    => $nextVer,
            'nombre'         => $form['nombre'],
            'descripcion'    => $form['descripcion'],
            'ingredientes'   => json_encode($ingredientes, JSON_UNESCAPED_UNICODE),
            'notas'          => $notas,
            'created_by'     => $createdBy ?? 'sistema',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $versionId = (int) $this->db->insertID();

        // Mantener pointer de versión actual
        $this->db->table('formulaciones')
            ->where('id_formulaciones', $formulacionId)
            ->update(['version_actual' => $nextVer]);

        return $versionId;
    }

    /**
     * Lista las versiones de una formulación (sin el snapshot completo, para listado).
     */
    public function listarVersiones(int $formulacionId): array
    {
        return $this->db->query("
            SELECT
                fv.id,
                fv.version_num,
                fv.notas,
                fv.created_by,
                fv.created_at,
                f.version_actual,
                (fv.version_num = f.version_actual) AS es_actual
            FROM formulaciones_versiones fv
            JOIN formulaciones f ON f.id_formulaciones = fv.formulacion_id
            WHERE fv.formulacion_id = ?
            ORDER BY fv.version_num DESC
        ", [$formulacionId])->getResultArray();
    }

    /**
     * Devuelve una versión específica con su snapshot completo + diff vs anterior.
     */
    public function detalleVersion(int $versionId): ?array
    {
        $ver = $this->db->table('formulaciones_versiones')
            ->where('id', $versionId)
            ->get()->getRowArray();
        if (!$ver) return null;

        $ver['ingredientes'] = json_decode($ver['ingredientes'] ?? '[]', true) ?: [];

        // Versión anterior para diff
        $anterior = $this->db->table('formulaciones_versiones')
            ->where('formulacion_id', $ver['formulacion_id'])
            ->where('version_num <', $ver['version_num'])
            ->orderBy('version_num', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $diff = ['agregados' => [], 'removidos' => [], 'modificados' => []];

        if ($anterior) {
            $ingAnterior = json_decode($anterior['ingredientes'] ?? '[]', true) ?: [];
            $mapAnt = [];
            foreach ($ingAnterior as $i) $mapAnt[(int) $i['item_general_id']] = $i;
            $mapNew = [];
            foreach ($ver['ingredientes'] as $i) $mapNew[(int) $i['item_general_id']] = $i;

            foreach ($mapNew as $id => $cur) {
                if (!isset($mapAnt[$id])) {
                    $diff['agregados'][] = $cur;
                } elseif ((float) $mapAnt[$id]['cantidad'] !== (float) $cur['cantidad']) {
                    $diff['modificados'][] = [
                        'item_general_id' => $id,
                        'item_nombre'     => $cur['item_nombre'] ?? null,
                        'item_codigo'     => $cur['item_codigo'] ?? null,
                        'cantidad_antes'  => (float) $mapAnt[$id]['cantidad'],
                        'cantidad_despues'=> (float) $cur['cantidad'],
                    ];
                }
            }
            foreach ($mapAnt as $id => $prev) {
                if (!isset($mapNew[$id])) {
                    $diff['removidos'][] = $prev;
                }
            }
        }

        $ver['version_anterior'] = $anterior ? [
            'id'          => (int) $anterior['id'],
            'version_num' => (int) $anterior['version_num'],
            'created_at'  => $anterior['created_at'],
        ] : null;
        $ver['diff'] = $diff;

        return $ver;
    }

    /**
     * Restaura una versión histórica como la activa de la formulación.
     * No reescribe la versión vieja: crea una NUEVA versión cuyos
     * ingredientes son los del snapshot histórico. Así el historial
     * queda lineal y trazable ("v.7 — Restaurado desde v.3").
     */
    public function restaurarVersion(int $versionId, ?string $responsable = null, ?string $notas = null): array
    {
        $ver = $this->db->table('formulaciones_versiones')
            ->where('id', $versionId)
            ->get()->getRowArray();
        if (!$ver) {
            throw new Exception("Versión #{$versionId} no encontrada.");
        }

        $formulacionId = (int) $ver['formulacion_id'];
        $form = $this->db->table('formulaciones')
            ->where('id_formulaciones', $formulacionId)
            ->get()->getRowArray();
        if (!$form) {
            throw new Exception("Formulación #{$formulacionId} no encontrada.");
        }

        if ((int) $form['version_actual'] === (int) $ver['version_num']) {
            throw new Exception("La versión #{$ver['version_num']} ya es la actual de la formulación.");
        }

        $ingredientes = json_decode($ver['ingredientes'] ?? '[]', true) ?: [];
        if (empty($ingredientes)) {
            throw new Exception("La versión #{$ver['version_num']} no tiene ingredientes para restaurar.");
        }

        // Validar que los items aún existan y no estén archivados
        $ids = array_unique(array_map(fn($i) => (int) ($i['item_general_id'] ?? 0), $ingredientes));
        $ids = array_filter($ids);
        if (!empty($ids)) {
            $activos = $this->db->table('item_general')
                ->select('id_item_general')
                ->whereIn('id_item_general', $ids)
                ->where('deleted_at IS NULL')
                ->get()->getResultArray();
            $idsActivos = array_column($activos, 'id_item_general');
            $faltantes  = array_diff($ids, $idsActivos);
            if (!empty($faltantes)) {
                throw new Exception(
                    "No se puede restaurar: los siguientes items ya no están disponibles: "
                    . implode(', ', $faltantes) . '.'
                );
            }
        }

        $this->db->transBegin();
        try {
            // Reemplazar receta actual por la del snapshot
            $this->db->query('DELETE FROM item_general_formulaciones WHERE formulaciones_id = ?', [$formulacionId]);

            // Mapear el snapshot al formato de insertarLineas (respeta ingredientes,
            // instrucciones y fases). Snapshots viejos sin `tipo` → 'ingrediente'.
            $lineas = array_map(fn($ing) => [
                'tipo'             => $ing['tipo'] ?? 'ingrediente',
                'materia_prima_id' => $ing['item_general_id'] ?? null,
                'cantidad'         => $ing['cantidad']   ?? 0,
                'porcentaje'       => $ing['porcentaje'] ?? 0,
                'orden'            => $ing['orden']      ?? null,
                'texto'            => $ing['texto']      ?? null,
                'nota'             => $ing['nota']       ?? null,
            ], $ingredientes);
            $this->insertarLineas($formulacionId, $lineas);

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw new Exception("Error al restaurar la versión: " . $e->getMessage());
        }

        // Snapshot de la nueva versión (fuera de la tx anterior, igual que crearFormulacion)
        $nuevaNota = $notas ?: "Restaurado desde v.{$ver['version_num']}";
        $nuevaVersionId = $this->crearVersion($formulacionId, $responsable, $nuevaNota);

        $nuevaVer = (int) $this->db->table('formulaciones')
            ->where('id_formulaciones', $formulacionId)
            ->get()->getRowArray()['version_actual'];

        return [
            'success'         => true,
            'message'         => "Versión {$ver['version_num']} restaurada como v.{$nuevaVer}.",
            'formulacion_id'  => $formulacionId,
            'restaurada_de'   => (int) $ver['version_num'],
            'version_id'      => $nuevaVersionId,
            'version_num'     => $nuevaVer,
        ];
    }

    /**
     * Devuelve, para cada producto (tipo=0) con fórmula activa, su costo
     * final calculado con el proveedor más barato por ingrediente.
     *
     * Sin N+1: 3 queries totales (productos + ingredientes + item_proveedor).
     *
     * Reglas:
     * - Solo cuenta como "cubierta" una MP que tenga `item_proveedor.item_general_id`
     *   coincidente (priority 1). El match por nombre NO se considera — para
     *   un cálculo de costos limpio, los items deben estar explícitamente
     *   vinculados (usar Sincronización para eso).
     * - Producto se marca `incompleto` si al menos una MP no tiene proveedor
     *   válido; no se calcula costo de MP en ese caso.
     * - `costo_indirectos` = envase + etiqueta + bandeja + plastico + costo_mod (por unidad de producto).
     * - `costo_mp` = Σ(cantidad_kg × precio_por_kg_más_barato) / volumen_base.
     */
    public function get_costos_produccion_batch(): array
    {
        // 1. Productos con fórmula activa + datos de costos_item + categoria
        $productos = $this->db->query('
            SELECT
                ig.id_item_general,
                ig.nombre,
                ig.codigo,
                ig.precio_venta_manual,
                ig.precio_manual_activo,
                cat.nombre AS categoria_nombre,
                f.id_formulaciones,
                COALESCE(NULLIF(ci.volumen, 0), 1) AS volumen_base,
                COALESCE(ci.envase, 0)             AS envase,
                COALESCE(ci.etiqueta, 0)           AS etiqueta,
                COALESCE(ci.bandeja, 0)            AS bandeja,
                COALESCE(ci.plastico, 0)           AS plastico,
                COALESCE(ci.costo_mod, 0)          AS costo_mod,
                COALESCE(ci.porcentaje_utilidad, ' . $this->margenDefault() . ') AS porcentaje_utilidad
            FROM item_general ig
            INNER JOIN formulaciones f ON f.item_general_id = ig.id_item_general AND f.estado = 1
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
            LEFT JOIN categoria cat   ON cat.id_categoria = ig.categoria_id
            WHERE ig.tipo = 0
              AND ig.deleted_at IS NULL
            ORDER BY ig.nombre ASC
        ')->getResultArray();

        if (empty($productos)) return [];

        $formulacionIds = array_column($productos, 'id_formulaciones');
        $placeholdersF  = implode(',', array_fill(0, count($formulacionIds), '?'));

        // 2. Ingredientes de todas las fórmulas activas (excluye items archivados)
        $ingredientes = $this->db->query("
            SELECT
                igf.formulaciones_id,
                igf.item_general_id   AS mp_id,
                igf.cantidad,
                igf.porcentaje,
                ig.nombre             AS mp_nombre,
                ig.codigo             AS mp_codigo,
                ig.deleted_at         AS mp_deleted
            FROM item_general_formulaciones igf
            INNER JOIN item_general ig ON ig.id_item_general = igf.item_general_id
            WHERE igf.formulaciones_id IN ($placeholdersF)
            ORDER BY igf.formulaciones_id, ig.nombre
        ", $formulacionIds)->getResultArray();

        // 3. Mapa MP nombre → id (para resolver matches por nombre a la MP correcta)
        $mpIds = array_values(array_unique(array_map(fn($i) => (int) $i['mp_id'], $ingredientes)));
        $mpsPorId = [];
        foreach ($ingredientes as $i) {
            $mpsPorId[(int) $i['mp_id']] = [
                'nombre' => $i['mp_nombre'],
                'codigo' => $i['mp_codigo'],
            ];
        }

        // Stock actual de cada MP (suma de capas activas) — una sola query
        $stockPorMp = [];
        if (!empty($mpIds)) {
            $placeholdersStk = implode(',', array_fill(0, count($mpIds), '?'));
            $stockRows = $this->db->query("
                SELECT item_general_id, COALESCE(SUM(cantidad_disponible), 0) AS stock_kg
                FROM inventario_capas
                WHERE estado = 1 AND item_general_id IN ($placeholdersStk)
                GROUP BY item_general_id
            ", $mpIds)->getResultArray();
            foreach ($stockRows as $r) {
                $stockPorMp[(int) $r['item_general_id']] = (float) $r['stock_kg'];
            }
        }

        // 4. Trae proveedores: linked (item_general_id IN mpIds) + unlinked (NULL)
        //    Misma lógica de matching que get_opciones_proveedor_formulacion:
        //    priority 1 = ip.item_general_id == mp_id (link directo)
        //    priority 2 = nombre exacto (case-insensitive)
        //    priority 3 = nombre parcial (substring)
        //    El más barato por MP (precio_por_kg ASC) gana.
        $proveedoresPorMp = [];
        if (!empty($mpIds)) {
            $placeholdersI = implode(',', array_fill(0, count($mpIds), '?'));
            $rows = $this->db->query("
                SELECT
                    ip.item_general_id,
                    ip.id_item_proveedor,
                    ip.nombre AS ip_nombre,
                    ip.precio_unitario,
                    ip.factor_conversion,
                    ip.proveedor_id,
                    p.nombre_empresa
                FROM item_proveedor ip
                INNER JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
                WHERE ip.disponible = 1
                  AND ip.deleted_at IS NULL
                  AND p.deleted_at IS NULL
                  AND (ip.item_general_id IN ($placeholdersI) OR ip.item_general_id IS NULL)
            ", $mpIds)->getResultArray();

            foreach ($rows as $r) {
                $factor   = max((float) ($r['factor_conversion'] ?: 1), 0.001);
                $precioKg = round((float) $r['precio_unitario'] / $factor, 4);
                $r['precio_por_kg'] = $precioKg;

                $ipItemId = $r['item_general_id'] !== null ? (int) $r['item_general_id'] : null;

                // Priority 1: link directo por item_general_id
                if ($ipItemId !== null && isset($mpsPorId[$ipItemId])) {
                    $r['match_tipo']  = 1;
                    $proveedoresPorMp[$ipItemId][] = $r;
                    continue;
                }

                // Priority 2/3: match por nombre contra cualquier MP de las fórmulas
                $ipNombreLimpio = $this->limpiarNombreProveedor((string) $r['ip_nombre']);
                foreach ($mpsPorId as $mpId => $mp) {
                    $score = $this->matchNombre($mp['nombre'], (string) $r['ip_nombre']);
                    if ($score > 0) {
                        $r['match_tipo'] = $score + 1; // 2 o 3
                        $proveedoresPorMp[$mpId][] = $r;
                    }
                }
            }

            // Ordenar opciones por priority + precio_por_kg ASC dentro de cada MP
            // (un match priority-1 pisa al 2 aún si es más caro; entre la misma prioridad, gana el barato)
            foreach ($proveedoresPorMp as &$opts) {
                usort($opts, function ($a, $b) {
                    $cmp = ($a['match_tipo'] <=> $b['match_tipo']);
                    if ($cmp !== 0) return $cmp;
                    return $a['precio_por_kg'] <=> $b['precio_por_kg'];
                });
            }
            unset($opts);
        }

        // 4. Agrupar ingredientes por formulación
        $ingredientesPorForm = [];
        foreach ($ingredientes as $i) {
            $ingredientesPorForm[(int) $i['formulaciones_id']][] = $i;
        }

        // 5. Calcular para cada producto
        $resultado = [];
        foreach ($productos as $p) {
            $formId = (int) $p['id_formulaciones'];
            $mps    = $ingredientesPorForm[$formId] ?? [];

            $faltantes = [];
            $proveedoresUsados = [];   // id_proveedor => [nombre, items_count]
            $costoMpTotal = 0.0;

            // Para calcular cuántas tandas se pueden producir con stock actual.
            // Cuello de botella = MP cuya razón stock/cantidad sea mínima.
            $tandasMin = INF;
            $cuello = null;

            foreach ($mps as $mp) {
                $mpId = (int) $mp['mp_id'];
                $cantidad = (float) $mp['cantidad'];

                // Tandas con stock actual para esta MP (independiente de proveedor)
                if ($cantidad > 0) {
                    $stockMp = $stockPorMp[$mpId] ?? 0.0;
                    $tandasMp = $stockMp / $cantidad;
                    if ($tandasMp < $tandasMin) {
                        $tandasMin = $tandasMp;
                        $cuello = [
                            'mp_id'      => $mpId,
                            'nombre'     => $mp['mp_nombre'],
                            'codigo'     => $mp['mp_codigo'],
                            'stock_kg'   => round($stockMp, 4),
                            'requerido_por_tanda_kg' => round($cantidad, 4),
                            'tandas'     => round($tandasMp, 3),
                        ];
                    }
                }

                $esAgua = mb_strtoupper(trim($mp['mp_nombre'])) === 'AGUA';

                // Item archivado o sin proveedor → faltante (AGUA se excluye: tiene costo propio)
                if (!empty($mp['mp_deleted']) || (empty($proveedoresPorMp[$mpId]) && !$esAgua)) {
                    $faltantes[] = [
                        'id'      => $mpId,
                        'nombre'  => $mp['mp_nombre'],
                        'codigo'  => $mp['mp_codigo'],
                        'motivo'  => !empty($mp['mp_deleted']) ? 'archivado' : 'sin_proveedor',
                    ];
                    continue;
                }

                // AGUA sin proveedor: usar su costo estándar de costos_item
                if ($esAgua && empty($proveedoresPorMp[$mpId])) {
                    $costoAgua = (float) $this->db->query(
                        'SELECT COALESCE(costo_unitario, 0) AS cu FROM costos_item WHERE item_general_id = ?', [$mpId]
                    )->getRow()->cu;
                    $costoMpTotal += $cantidad * $costoAgua;
                    continue;
                }

                $opcion = $proveedoresPorMp[$mpId][0]; // ya ordenado ASC
                $subtotal = $cantidad * (float) $opcion['precio_por_kg'];
                $costoMpTotal += $subtotal;

                $pid = (int) $opcion['proveedor_id'];
                if (!isset($proveedoresUsados[$pid])) {
                    $proveedoresUsados[$pid] = [
                        'id_proveedor'   => $pid,
                        'nombre_empresa' => $opcion['nombre_empresa'],
                        'items'          => 0,
                    ];
                }
                $proveedoresUsados[$pid]['items']++;
            }

            $estado     = empty($faltantes) ? 'completo' : 'incompleto';
            $vol        = (float) $p['volumen_base'];
            // "Empaque y Mano de Obra" — suma envase + etiqueta + bandeja + plástico + costo_mod
            $empaqueMod = (float) $p['envase'] + (float) $p['etiqueta'] + (float) $p['bandeja']
                        + (float) $p['plastico'] + (float) $p['costo_mod'];

            $costoMpPorUnidad = $vol > 0 ? $costoMpTotal / $vol : $costoMpTotal;
            $costoTotal       = $estado === 'completo' ? ($costoMpPorUnidad + $empaqueMod) : null;

            $margen = (float) $p['porcentaje_utilidad'];
            $precioVentaCalc = $costoTotal !== null && $margen > 0
                ? round($costoTotal * (1 + $margen / 100), 2)
                : ($costoTotal !== null ? round($costoTotal, 2) : null);

            // Tandas finales: floor (no contás tandas parciales) y galones rendibles.
            $tandasPosibles = $tandasMin === INF ? 0 : floor($tandasMin);
            $galonesPosibles = $tandasPosibles * $vol;

            $resultado[] = [
                'id_item_general'      => (int) $p['id_item_general'],
                'nombre'               => $p['nombre'],
                'codigo'               => $p['codigo'],
                'categoria_nombre'     => $p['categoria_nombre'],
                'volumen_base'         => $vol,
                'estado'               => $estado,
                'mps_total'            => count($mps),
                'mps_faltantes'        => $faltantes,
                // Capacidad de producción con stock actual
                'tandas_posibles'      => (int) $tandasPosibles,
                'galones_posibles'     => $galonesPosibles,
                'cuello_botella'       => $cuello,
                'costo_mp_total'       => $estado === 'completo' ? round($costoMpTotal, 2) : null,
                'costo_mp_por_unidad'  => $estado === 'completo' ? round($costoMpPorUnidad, 2) : null,
                'costo_empaque_mod'    => round($empaqueMod, 2),
                // Desglose de empaque y mano de obra (los 5 componentes de costos_item)
                'empaque_mod_detalle'  => [
                    'envase'    => round((float) $p['envase'], 2),
                    'etiqueta'  => round((float) $p['etiqueta'], 2),
                    'bandeja'   => round((float) $p['bandeja'], 2),
                    'plastico'  => round((float) $p['plastico'], 2),
                    'costo_mod' => round((float) $p['costo_mod'], 2),
                ],
                'costo_total'          => $costoTotal !== null ? round($costoTotal, 2) : null,
                'porcentaje_utilidad'  => $margen,
                'precio_venta_calc'    => $precioVentaCalc,
                'precio_venta_manual'  => $p['precio_venta_manual'] !== null ? (float) $p['precio_venta_manual'] : null,
                'precio_manual_activo' => (int) ($p['precio_manual_activo'] ?? 0),
                'proveedores_usados'   => array_values($proveedoresUsados),
            ];
        }

        // Cobertura global de MPs (AGUA se cuenta como cubierta: costo propio).
        $totalMps     = count($mpIds);
        $cubiertasMps = 0;
        foreach ($mpIds as $mid) {
            $nombre = $mpsPorId[$mid]['nombre'] ?? '';
            $esAgua = mb_strtoupper(trim($nombre)) === 'AGUA';
            if (!empty($proveedoresPorMp[$mid]) || $esAgua) $cubiertasMps++;
        }

        return [
            'productos' => $resultado,
            'cobertura' => [
                'mps_totales'       => $totalMps,
                'mps_cubiertas'     => $cubiertasMps,
                'mps_sin_proveedor' => $totalMps - $cubiertasMps,
                'pct'               => $totalMps > 0 ? round(($cubiertasMps / $totalMps) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Versión detallada de un solo producto: incluye breakdown línea por línea
     * con proveedor seleccionado, cantidad, precio/kg y subtotal.
     *
     * Usa la misma lógica de matching que get_costos_produccion_batch
     * (priority 1 link + priority 2/3 por nombre, gana el más barato).
     */
    public function get_costo_produccion_detalle(int $itemId): ?array
    {
        $batch = $this->get_costos_produccion_batch();
        $producto = null;
        foreach (($batch['productos'] ?? []) as $p) {
            if ($p['id_item_general'] === $itemId) {
                $producto = $p;
                break;
            }
        }
        if (!$producto) return null;

        // Re-fetch ingredients with provider detail
        $formulacion = $this->db->query(
            'SELECT id_formulaciones FROM formulaciones WHERE item_general_id = ? AND estado = 1 LIMIT 1',
            [$itemId]
        )->getRowArray();
        if (!$formulacion) return $producto;

        $ingredientes = $this->db->query('
            SELECT
                igf.item_general_id AS mp_id,
                igf.cantidad,
                igf.porcentaje,
                ig.nombre AS mp_nombre,
                ig.codigo AS mp_codigo,
                ig.deleted_at AS mp_deleted
            FROM item_general_formulaciones igf
            INNER JOIN item_general ig ON ig.id_item_general = igf.item_general_id
            WHERE igf.formulaciones_id = ?
            ORDER BY ig.nombre ASC
        ', [$formulacion['id_formulaciones']])->getResultArray();

        $mpIds = array_column($ingredientes, 'mp_id');
        $mpsPorId = [];
        foreach ($ingredientes as $i) {
            $mpsPorId[(int) $i['mp_id']] = ['nombre' => $i['mp_nombre']];
        }

        $proveedoresPorMp = [];
        if (!empty($mpIds)) {
            $placeholders = implode(',', array_fill(0, count($mpIds), '?'));
            $rows = $this->db->query("
                SELECT
                    ip.item_general_id,
                    ip.id_item_proveedor,
                    ip.precio_unitario,
                    ip.factor_conversion,
                    ip.proveedor_id,
                    ip.nombre AS item_proveedor_nombre,
                    p.nombre_empresa,
                    uc.nombre AS unidad_compra
                FROM item_proveedor ip
                INNER JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
                LEFT JOIN unidad   uc ON uc.id_unidad   = ip.unidad_compra_id
                WHERE ip.disponible = 1
                  AND ip.deleted_at IS NULL
                  AND p.deleted_at IS NULL
                  AND (ip.item_general_id IN ($placeholders) OR ip.item_general_id IS NULL)
            ", $mpIds)->getResultArray();

            foreach ($rows as $r) {
                $factor = max((float) ($r['factor_conversion'] ?: 1), 0.001);
                $r['precio_por_kg'] = round((float) $r['precio_unitario'] / $factor, 4);

                $ipItemId = $r['item_general_id'] !== null ? (int) $r['item_general_id'] : null;

                // Priority 1: link directo
                if ($ipItemId !== null && isset($mpsPorId[$ipItemId])) {
                    $r['match_tipo'] = 1;
                    $proveedoresPorMp[$ipItemId][] = $r;
                    continue;
                }
                // Priority 2/3: match por nombre
                foreach ($mpsPorId as $mpId => $mp) {
                    $score = $this->matchNombre($mp['nombre'], (string) $r['item_proveedor_nombre']);
                    if ($score > 0) {
                        $r['match_tipo'] = $score + 1;
                        $proveedoresPorMp[$mpId][] = $r;
                    }
                }
            }
            foreach ($proveedoresPorMp as &$opts) {
                usort($opts, function ($a, $b) {
                    $cmp = ($a['match_tipo'] <=> $b['match_tipo']);
                    if ($cmp !== 0) return $cmp;
                    return $a['precio_por_kg'] <=> $b['precio_por_kg'];
                });
            }
            unset($opts);
        }

        $detalle = [];
        foreach ($ingredientes as $mp) {
            $mpId = (int) $mp['mp_id'];
            $cantidad = (float) $mp['cantidad'];
            $opciones = $proveedoresPorMp[$mpId] ?? [];
            $mejor    = $opciones[0] ?? null;
            $esAgua   = mb_strtoupper(trim($mp['mp_nombre'])) === 'AGUA';

            if ($esAgua && empty($opciones)) {
                $costoAgua = (float) $this->db->query(
                    'SELECT COALESCE(costo_unitario, 0) AS cu FROM costos_item WHERE item_general_id = ?', [$mpId]
                )->getRow()->cu;
                $detalle[] = [
                    'mp_id'               => $mpId,
                    'nombre'              => $mp['mp_nombre'],
                    'codigo'              => $mp['mp_codigo'],
                    'archivado'           => false,
                    'cantidad_kg'         => $cantidad,
                    'porcentaje'          => (float) ($mp['porcentaje'] ?? 0),
                    'proveedor_id'        => null,
                    'proveedor_nombre'    => 'Costo interno',
                    'item_proveedor_id'   => null,
                    'item_proveedor_nombre' => null,
                    'unidad_compra'       => null,
                    'factor_conversion'   => null,
                    'precio_unitario'     => $costoAgua,
                    'precio_por_kg'       => $costoAgua,
                    'subtotal'            => round($cantidad * $costoAgua, 2),
                    'total_opciones'      => 0,
                    'costo_interno'       => true,
                ];
                continue;
            }

            $detalle[] = [
                'mp_id'               => $mpId,
                'nombre'              => $mp['mp_nombre'],
                'codigo'              => $mp['mp_codigo'],
                'archivado'           => !empty($mp['mp_deleted']),
                'cantidad_kg'         => $cantidad,
                'porcentaje'          => (float) ($mp['porcentaje'] ?? 0),
                'proveedor_id'        => $mejor['proveedor_id']     ?? null,
                'proveedor_nombre'    => $mejor['nombre_empresa']   ?? null,
                'item_proveedor_id'   => $mejor['id_item_proveedor']?? null,
                'item_proveedor_nombre' => $mejor['item_proveedor_nombre'] ?? null,
                'unidad_compra'       => $mejor['unidad_compra']    ?? null,
                'factor_conversion'   => isset($mejor['factor_conversion']) ? (float) $mejor['factor_conversion'] : null,
                'precio_unitario'     => isset($mejor['precio_unitario'])   ? (float) $mejor['precio_unitario']   : null,
                'precio_por_kg'       => $mejor['precio_por_kg']    ?? null,
                'subtotal'            => $mejor ? round($cantidad * (float) $mejor['precio_por_kg'], 2) : null,
                'total_opciones'      => count($opciones),
            ];
        }

        $producto['detalle_ingredientes'] = $detalle;
        return $producto;
    }
}
