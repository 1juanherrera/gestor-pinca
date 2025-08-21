<?php
namespace App\Models;

use CodeIgniter\Model;

class BodegasModel extends Model
{
    public function __construct(){
        parent::__construct();
    }

    public function get_all($table, $where = null){ 
        $this->table = $table;
        if ($where) { $this->where($where);  }           
        return $this->findAll();
    }

    public function get_bodegas_by_instalacion(){
        $sql = 'SELECT
                    b.id_bodega,
                    b.nombre,
                    b.descripcion,
                    i.nombre AS instalacion
            FROM bodegas b
            INNER JOIN instalaciones i ON b.instalacion_id = i.id_instalaciones';
        $data = $this->db->query($sql)->getResult();
        $datos = [];
        if (!empty($data)) {
            foreach ($data as $row) {
                $datos[] = [
                    'id' => $row->id_bodega,
                    'nombre' => $row->nombre,
                    'descripcion' => $row->descripcion,
                    'instalacion' => $row->instalacion
                ];
            }
        }
        return $datos;
    }
}