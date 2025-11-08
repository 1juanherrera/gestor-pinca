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
        $sql = 'SELECT f.*, ig.nombre AS item_general, ig.tipo, ig.codigo AS codigo_item_general 
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

    public function calculate_costs_new_volume($itemId, $newVolume = null)
    {

        if (empty($itemId)) {
            throw new Exception('Parámetro inválido: itemId requerido.');
        }

        $sql = 'SELECT 
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
                    i.cantidad,
                    COALESCE(ci.costo_unitario, 0) as costo_unitario,
                    COALESCE(ci.costo_mp_galon, 0) as costo_mp_galon,
                    COALESCE(ci.costo_mp_kg, 0) as costo_mp_kg,
                    COALESCE(ci.envase, 0) as envase,
                    COALESCE(ci.etiqueta, 0) as etiqueta,
                    COALESCE(ci.bandeja, 0) as bandeja,
                    COALESCE(ci.plastico, 0) as plastico,
                    COALESCE(ci.costo_total, 0) as costo_total_actual,
                    COALESCE(NULLIF(ci.volumen, 0), 1) as volumen_base,
                    COALESCE(ci.precio_venta, 0) as precio_venta_actual,
                    COALESCE(ci.cantidad_total, 0) as cantidad_total_actual,
                    COALESCE(ci.costo_mod, 0) as costo_mod,
                    ci.costo_total as costo_total_raw,
                    ci.precio_venta as precio_venta_raw
                FROM item_general ig
                LEFT JOIN inventario i ON i.item_general_id = ig.id_item_general
                LEFT JOIN costos_item ci ON ig.id_item_general = ci.item_general_id
                WHERE ig.id_item_general = ?
                ';

        $item = $this->db->query($sql, [$itemId])->getRow();

        if (!$item) {
            throw new Exception("Item con ID {$itemId} no encontrado.");
        }

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
                            WHERE igf.formulaciones_id = ?;
                            ';

        $formulaciones = $this->db->query($formulacionesSql, [$item->id_item_general])->getResult();

        if (empty($formulaciones)) {
            throw new Exception("No se encontraron formulaciones para el item {$item->nombre}.");
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

        $nuevoCostoTotal = $nuevoCostoMateriaPrima / ($newVolume ?? $item->volumen_base) 
            + $item->envase 
            + $item->etiqueta 
            + $item->bandeja 
            + $item->plastico 
            + $item->costo_mod;

        $precioVenta = $nuevoCostoTotal * 1.4;

        $costo_mg_kg = ($newVolume && $newVolume > 0)
            ? $nuevoCostoMateriaPrima / $newVolume
            : $totalMateriaPrima / $item->volumen_base;

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
                'id' => $item->id_item_general,
                'nombre' => $item->nombre,
                'codigo' => $item->codigo,
                'tipo' => $item->tipo,
                'viscosidad' => $item->viscosidad,
                'p_g' => $item->p_g,
                'color' => $item->color,
                'secado' => $item->secado,
                'cubrimiento' => $item->cubrimiento,
                'brillo_60' => $item->brillo_60,
                'cantidad' => (float) $item->cantidad,
                'volumen_base' => (float) $item->volumen_base,
                'volumen_nuevo' => $newVolume ?? (float) $item->volumen_base,
                'factor_volumen' => $factorVolumen
            ],
            'costos' => [
                'total_costo_materia_prima' => Formatter::toCOP($nuevoCostoMateriaPrima),
                'envase' => Formatter::toCOP($item->envase),
                'etiqueta' => Formatter::toCOP($item->etiqueta),
                'bandeja' => Formatter::toCOP($item->bandeja),
                'plastico' => Formatter::toCOP($item->plastico),
                'mod' => Formatter::toCOP($item->costo_mod),
                'costo_mg_kg' => Formatter::toCOP($costo_mg_kg),
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

        $currentData = $this->calculate_costs_new_volume($itemId);
        $newData = $this->calculate_costs_new_volume($itemId, $newVolume);

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
}
