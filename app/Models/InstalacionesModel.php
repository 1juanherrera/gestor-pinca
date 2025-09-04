<?php
namespace App\Models;

class InstalacionesModel extends BaseModel
{
    protected $table = 'instalaciones';
    protected $primaryKey = 'id_instalaciones';
    protected $allowedFields = [
        'nombre',
        'descripcion',
        'ciudad',
        'direccion',
        'telefono',
        'id_empresa'
    ];

    public function __construct(){
        parent::__construct();
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

    public function get($id, $table)
    {
        $this->table = $table;
        return $this->find($id);
    }

    public function create_instalacion($data, $table)
    {
        $this->table = $table;      
        return $this->insert($data);
    }

    public function update_instalacion($id, $data, $table)
    {
        $this->table = $table;      
        return $this->update($id, $data);
    }

    public function delete_instalacion($id, $table)
    {
        $this->table = $table;      
        return $this->delete($id);
    }
}