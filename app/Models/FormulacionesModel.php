<?php
namespace App\Models;

use App\Libraries\Formatter;
use Exception;

class FormulacionesModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
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
                    ig.*, ci.*, igf.cantidad, igf.porcentaje
                    FROM item_general_formulaciones igf
                    LEFT JOIN item_general ig ON ig.id_item_general = igf.item_general_id
                    LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
                    WHERE igf.formulaciones_id = ?';

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
                COALESCE(ci.porcentaje_utilidad, 50) AS porcentaje_utilidad
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
            SELECT id_formulaciones
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
                ig.nombre             AS materia_prima_nombre,
                ig.codigo             AS materia_prima_codigo,
                COALESCE(ci.costo_unitario, 0)      AS costo_unitario,
                COALESCE(i.cantidad, 0)             AS inventario_cantidad,
                (igf.cantidad * COALESCE(ci.costo_unitario, 0)) AS costo_total
            FROM item_general_formulaciones igf
            INNER JOIN item_general ig ON igf.item_general_id = ig.id_item_general
            LEFT JOIN costos_item ci   ON ig.id_item_general  = ci.item_general_id
            LEFT JOIN inventario i     ON ig.id_item_general  = i.item_general_id
            WHERE igf.formulaciones_id = ?
            ORDER BY ig.nombre ASC
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
                    COALESCE(ci.porcentaje_utilidad, 50) as porcentaje_utilidad,
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
                                i.cantidad AS inventario_cantidad,
                                ci.fecha_calculo,
                                ig.nombre AS materia_prima_nombre,
                                ig.codigo AS materia_prima_codigo,
                                COALESCE(ci.costo_unitario, 0) as materia_prima_costo_unitario,
                                (igf.cantidad * COALESCE(ci.costo_unitario, 0)) as costo_total_materia
                            FROM item_general_formulaciones igf
                            INNER JOIN item_general ig ON igf.item_general_id = ig.id_item_general
                            LEFT JOIN costos_item ci ON ig.id_item_general = ci.item_general_id
                            LEFT JOIN inventario i ON ig.id_item_general = i.item_general_id
                            WHERE igf.formulaciones_id = ?'; // <--- Ahora usará el ID correcto

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
                COALESCE(ci.porcentaje_utilidad, 50) AS porcentaje_utilidad
            FROM item_general ig
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
            WHERE ig.id_item_general = ?
        ', [$itemId])->getRow();

        $materias = $this->db->query('
            SELECT igf.item_general_id, ig.nombre,
                   COALESCE(ci.costo_unitario, 0) AS costo_estandar
            FROM item_general_formulaciones igf
            INNER JOIN item_general ig ON ig.id_item_general = igf.item_general_id
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
            WHERE igf.formulaciones_id = ?
        ', [$formulacion->id_formulaciones])->getResult();

        if (empty($materias)) return ['item' => [], 'materias' => []];

        $catalogo = $this->db->query('
            SELECT ip.id_item_proveedor, ip.item_general_id, ip.nombre,
                   ip.precio_unitario, ip.factor_conversion,
                   ip.proveedor_id, p.nombre_empresa,
                   uc.nombre AS unidad_compra
            FROM item_proveedor ip
            INNER JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
            LEFT JOIN unidad uc ON uc.id_unidad = ip.unidad_compra_id
            WHERE ip.disponible = 1
        ')->getResult();

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

        $catalogo = $this->db->query('
            SELECT ip.id_item_proveedor, ip.item_general_id, ip.nombre,
                   ip.proveedor_id, p.nombre_empresa, p.nombre_encargado
            FROM item_proveedor ip
            INNER JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
            WHERE ip.disponible = 1
        ')->getResult();

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
                COALESCE(ci.porcentaje_utilidad, 50)     AS porcentaje_utilidad
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
                COALESCE(ci.costo_unitario, 0) AS costo_unitario_estandar,
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

    // Crear formulación completa con materias primas
    public function crearFormulacion(array $data): array
    {
        if (empty($data['item_general_id'])) {
            throw new Exception('item_general_id es obligatorio.');
        }
        if (empty($data['materias_primas'])) {
            throw new Exception('Debe agregar al menos una materia prima.');
        }

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

            // Insertar materias primas
            foreach ($data['materias_primas'] as $mp) {
                if (empty($mp['materia_prima_id'])) continue;
                $this->db->query('
                    INSERT INTO item_general_formulaciones (formulaciones_id, item_general_id, cantidad, porcentaje)
                    VALUES (?, ?, ?, ?)
                ', [
                    $formulacionId,
                    $mp['materia_prima_id'],
                    $mp['cantidad']   ?? 0,
                    $mp['porcentaje'] ?? 0,
                ]);

                // Actualizar costo_unitario de la materia prima si viene
                if (!empty($mp['costo_unitario'])) {
                    $this->db->query('
                        UPDATE costos_item SET costo_unitario = ?
                        WHERE item_general_id = ?
                    ', [$mp['costo_unitario'], $mp['materia_prima_id']]);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Error al guardar la formulación.');
            }

            return [
                'success'       => true,
                'message'       => 'Formulación creada correctamente.',
                'formulacion_id' => $formulacionId,
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

            foreach ($data['materias_primas'] as $mp) {
                if (empty($mp['materia_prima_id'])) continue;
                $this->db->query('
                    INSERT INTO item_general_formulaciones (formulaciones_id, item_general_id, cantidad, porcentaje)
                    VALUES (?, ?, ?, ?)
                ', [
                    $formulacionId,
                    $mp['materia_prima_id'],
                    $mp['cantidad']   ?? 0,
                    $mp['porcentaje'] ?? 0,
                ]);

                if (!empty($mp['costo_unitario'])) {
                    $this->db->query('
                        UPDATE costos_item SET costo_unitario = ?
                        WHERE item_general_id = ?
                    ', [$mp['costo_unitario'], $mp['materia_prima_id']]);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Error al actualizar la formulación.');
            }

            return [
                'success' => true,
                'message' => 'Formulación actualizada correctamente.',
            ];

        } catch (Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
