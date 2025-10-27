<?php
namespace App\Models;

class ItemModel extends BaseModel
{
    protected $table = 'item_general';
    protected $primaryKey = 'id_item_general';
    protected $allowedFields = [
        'nombre',
        'codigo',
        'tipo',
        'categoria_id',
        'item_especifico_id'
    ];

    public function __construct(){
        parent::__construct();
    }

    public function get_all($table, $where = null){ 
        $this->table = $table;
        if ($where) { $this->where($where);  }           
        return $this->findAll();
    }

    public function get_items_all($where = null){

        $sql = 'SELECT 
                    ig.*,
                    c.nombre AS categoria,
                    ci.costo_unitario
            FROM item_general ig
            LEFT JOIN categoria c ON ig.categoria_id = c.id_categoria
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general';

        $tipos = [
            0 => 'PRODUCTO',
            1 => 'MATERIA PRIMA',
            2 => 'INSUMO',
        ];

        $items = $this->db->query($sql)->getResult();

        foreach ($items as &$item) {
            $item->nombre_tipo = $tipos[$item->tipo] ?? 'Otro';
        }
                
        if (empty($items)) {
            return [];
        }

        return $items;
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
}

