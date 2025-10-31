<?php
namespace App\Models;

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

    public function calculate_costs_new_volume($itemId, $newVolume) 
    {

        if (empty($itemId) || empty($newVolume) || !is_numeric($newVolume) || $newVolume <= 0) {
            throw new Exception('Parámetros inválidos: itemId o newVolume incorrectos.');
        }

        $sql = "SELECT 
                    ig.id_item_general,
                    ig.nombre,
                    ig.codigo,
                    ig.tipo,
                    COALESCE(cp.costo_unitario, 0) as costo_unitario,
                    COALESCE(cp.costo_mp_galon, 0) as costo_mp_galon,
                    COALESCE(cp.costo_mp_kg, 0) as costo_mp_kg,
                    COALESCE(cp.envase, 0) as envase,
                    COALESCE(cp.etiqueta, 0) as etiqueta,
                    COALESCE(cp.bandeja, 0) as bandeja,
                    COALESCE(cp.plastico, 0) as plastico,
                    COALESCE(cp.costo_total, 0) as costo_total_actual,
                    COALESCE(cp.volumen, 1) as volumen_actual,
                    COALESCE(cp.precio_venta, 0) as precio_venta_actual,
                    COALESCE(cp.cantidad_total, 0) as cantidad_total_actual,
                    COALESCE(cp.costo_mod, 0) as costo_mod,
                    cp.costo_total as costo_total_raw,
                    cp.precio_venta as precio_venta_raw
                FROM item_general ig
                LEFT JOIN costos_item cp ON ig.id_item_general = cp.id
                WHERE ig.id_item_general = ?
                ";

         $item = $this->db->query($sql, [$itemId])->getRow();

        if (!$item) {
            throw new Exception("Item con ID {$itemId} no encontrado.");
        }

        $formulacionesSql = "SELECT 
                                f.id_item_general_formulaciones,
                                f.item_general_id,
                                f.formulaciones_id,
                                f.cantidad,
                                ig.nombre AS materia_prima_nombre,
                                ig.codigo AS materia_prima_codigo,
                                COALESCE(ci.costo_unitario, 0) as materia_prima_costo_unitario,
                                (f.cantidad * COALESCE(ci.costo_unitario, 0)) as costo_total_materia
                            FROM item_general_formulaciones f
                            INNER JOIN item_general ig ON f.item_general_id = ig.id_item_general
                            LEFT JOIN costos_item ci ON ig.id_item_general = ci.item_general_id
                            WHERE f.formulaciones_id = ?;
                            ";
    }
}    
