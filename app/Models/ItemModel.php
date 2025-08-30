<?php
namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
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
                    ig.id_item_general as id, 
                    ig.nombre,
                    ig.codigo,
                    ig.tipo,
                    ie.viscosidad,
                    ie.p_g,
                    ie.color,
                    ie.brillo_60,
                    ie.secado,
                    ie.cubrimiento,
                    ie.molienda,
                    ie.ph,
                    ie.poder_tintoreo,
                    ie.volumen,
                    c.nombre AS categoria,
                    ci.costo_unitario
            FROM item_especifico ie
            INNER JOIN item_general ig ON ig.item_especifico_id = ie.id_item_especifico
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

    public function get_items_formulaciones(): array{
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
                                    ie.*, ci.*, ief.cantidad, ief.porcentaje
                            FROM item_especifico_formulaciones ief 
                            LEFT JOIN item_especifico ie ON ie.id_item_especifico = ief.item_especifico_id 
                            LEFT JOIN item_general ig ON ig.item_especifico_id = ie.id_item_especifico
                            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
                            WHERE ief.formulaciones_id = ?';
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
    
    public function get_item($id, $table)
    {
        $this->table = $table;                      
        return $this->find($id);
    }

    public function create_item($data, $table)
    {
        $this->table = $table;      
        return $this->insert($data);
    }

    public function update_item($id, $data, $table)
    {
        $this->table = $table;      
        return $this->update($id, $data);
    }

    public function delete_item($id, $table)
    {
        $this->table = $table;      
        return $this->delete($id);
    }
}

