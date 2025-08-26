<?php
namespace App\Models;

use CodeIgniter\Model;

class InstalacionesModel extends Model
{
    public function __construct(){
        parent::__construct();
    }

    public function get_all($table, $where = null) { 
        $this->table = $table;
        if ($where) { $this->where($where);  }           
        return $this->findAll();
    }

    public function instalaciones_with_bodegas() {
        $sql = 'SELECT * FROM instalaciones';
        $instalaciones = $this->db->query($sql)->getResult();
        $datos = [];

        if (!empty($instalaciones)) {
            foreach ($instalaciones as $instalacion) {

                $sql1 = 'SELECT id_bodegas, nombre, descripcion, estado
                                FROM bodegas
                                WHERE instalaciones_id = ?';
                $bodegas = $this->db->query($sql1, [$instalacion->id_instalaciones])->getResult();
                $datos[] = [
                    'id_instalaciones' => $instalacion->id_instalaciones,
                    'nombre'           => $instalacion->nombre,
                    'descripcion'      => $instalacion->descripcion,
                    'ciudad'           => $instalacion->ciudad,
                    'direccion'        => $instalacion->direccion,
                    'telefono'         => $instalacion->telefono,
                    'id_empresa'       => $instalacion->id_empresa,
                    'bodegas'          => $bodegas
                ];
            }
        }
        return $datos;
    }
}