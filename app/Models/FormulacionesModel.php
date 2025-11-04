<?php
namespace App\Models;

use App\Libraries\CurrencyFormatter;
use Exception;

class FormulacionesModel extends BaseModel
{
    public function __construct(){
        parent::__construct();
    }

    public function get_items_formulaciones() {
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
                    COALESCE(NULLIF(ci.volumen, 0), 1) as volumen_actual,
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
        foreach ($formulaciones as $row) {
            $totalMateriaPrima += $row->costo_total_materia;
            $totalCantidad += $row->cantidad;
        }

        if (!empty($newVolume) && is_numeric($newVolume) && $newVolume > 0 && $item->volumen_actual > 0) {
            $factorVolumen = $newVolume / $item->volumen_actual;
        } else {
            $factorVolumen = 1;
        }

        $nuevoCostoMateriaPrima = $totalMateriaPrima * $factorVolumen;
        $nuevoCostoTotal = $nuevoCostoMateriaPrima 
                            / ($newVolume ?? $item->volumen_actual)
                            + $item->envase 
                            + $item->etiqueta 
                            + $item->bandeja 
                            + $item->plastico 
                            + $item->costo_mod;

        $precioVenta = $nuevoCostoTotal * 1.4;  
        $costo_mg_kg = $totalMateriaPrima / ($newVolume ?? $item->volumen_actual);

        $formulaciones_formatted = [];
        foreach ($formulaciones as $row) {
            $cantidad = isset($row->cantidad) ? (float) $row->cantidad : 0.0;
            $materia_prima_costo_unitario = isset($row->materia_prima_costo_unitario) ? (float) $row->materia_prima_costo_unitario : 0.0;
            $costo_total_materia = isset($row->costo_total_materia) ? (float) $row->costo_total_materia : 0.0;
            $inventario_cantidad = isset($row->inventario_cantidad) ? (float) $row->inventario_cantidad : 0.0;

            $row = (array) $row;
            $row['cantidad'] = $cantidad;
            $row['materia_prima_costo_unitario'] = CurrencyFormatter::toCOP($materia_prima_costo_unitario);
            $row['costo_total_materia'] = CurrencyFormatter::toCOP($costo_total_materia);
            $row['inventario_valor_total'] = CurrencyFormatter::toCOP($inventario_cantidad * $materia_prima_costo_unitario);



            $formulaciones_formatted[] = $row;
        }

        $totalCantidadRounded = round((float) $totalCantidad, 2);

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
                'volumen_actual' => (float) $item->volumen_actual,
                'nuevo_volumen' => $newVolume ?? (float) $item->volumen_actual,
                'factor_volumen' => round($factorVolumen, 2),
            ],
            'costos' => [
                'total_costo_materia_prima' => CurrencyFormatter::toCOP($nuevoCostoMateriaPrima),
                'envase' => CurrencyFormatter::toCOP($item->envase),
                'etiqueta' => CurrencyFormatter::toCOP($item->etiqueta),
                'bandeja' => CurrencyFormatter::toCOP($item->bandeja),
                'plastico' => CurrencyFormatter::toCOP($item->plastico),
                'mod' => CurrencyFormatter::toCOP($item->costo_mod),
                'costo_mg_kg' => CurrencyFormatter::toCOP($costo_mg_kg),
                'total_cantidad_materia_prima' => CurrencyFormatter::toThousands($totalCantidadRounded),
                'total' => CurrencyFormatter::toCOP($nuevoCostoTotal),
                'precio_venta' => CurrencyFormatter::toCOP($precioVenta),
                'fecha_calculo' => $formulaciones[0]->fecha_calculo ?? null,
            ],
            'formulaciones' => $formulaciones_formatted
        ];
    }
}    
