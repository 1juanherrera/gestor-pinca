<?php
namespace App\Models;

class ItemProveedorModel extends BaseModel
{

    protected $table = 'item_proveedor';
    protected $primaryKey = 'id_item_proveedor';
    protected $allowedFields = [
        'nombre',
        'codigo',
        'tipo',
        'unidad_empaque',
        'precio_unitario',
        'precio_con_iva',
        'disponible',
        'descripcion',
        'id_proveedor'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function get($id, $table)
    {
        $this->table = $table;
        return $this->find($id);
    }

    public function create_proveedor($data, $table)
    {
        $this->table = $table;      
        return $this->insert($data);
    }

    public function update_proveedor($id, $data, $table)
    {
        $this->table = $table;      
        return $this->update($id, $data);
    }

    public function delete_proveedor($id, $table)
    {
        $this->table = $table;      
        return $this->delete($id);
    }
}