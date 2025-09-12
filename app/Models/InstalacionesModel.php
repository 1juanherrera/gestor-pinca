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

    public function instalacion_with_bodegas($id_instalacion = null) 
    {
        if ($id_instalacion === null) {
            // Retornar todas las instalaciones con bodegas
            return $this->instalaciones_with_bodegas();
        } else {
            // Retornar solo la instalación específica con bodegas
            $sql = 'SELECT * FROM instalaciones WHERE id_instalaciones = ?';
            $instalacion = $this->db->query($sql, [$id_instalacion])->getRow();

            if ($instalacion) {
                $sql1 = 'SELECT id_bodegas, nombre, descripcion, estado
                        FROM bodegas
                        WHERE instalaciones_id = ?';
                $bodegas = $this->db->query($sql1, [$id_instalacion])->getResult();

                return [
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
            return null;
        }
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