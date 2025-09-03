<?php
namespace App\Models;

use CodeIgniter\Model;

class BodegasModel extends BaseModel
{
    protected $table         = 'bodega';
    protected $primaryKey    = 'id_bodega';
    protected $allowedFields = [
        'nombre',
        'descripcion',
        'estado',
        'instalaciones_id'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function get_bodegas_all(){
        return $this->get_all($this->table);
    }

    public function get_bodega($id, $table)
    {                    
        return $this->get($id, $table);
    }

    public function create_bodega($data, $table)
    {
        return $this->create_table($data, $table);
    }

    public function update_bodega($id, $data)
    {
        return $this->update_table($id, $data, $this->table);
    }

    public function delete_bodega($id)
    {
        return $this->delete_table($id);
    }
}
