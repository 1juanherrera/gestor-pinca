<?php
namespace App\Models;

class ProveedorModel extends BaseModel
{

    protected $table = 'proveedor';
    protected $primaryKey = 'id_proveedor';
    protected $allowedFields = [
        'nombre_encargado',
        'nombre_empresa',
        'numero_documento',
        'direccion',
        'telefono',
        'email'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // public function get_item_proveedores()
    // {
    //     $sql = 'SELECT p.*, ip.nombre AS item_proveedor, ip.tipo, ip.codigo AS codigo_item_proveedor
    //     FROM proveedor p
    //     LEFT JOIN item_proveedor ip ON ip.id_item_proveedor = p.item_general_id';
    //     $data = $this->db->query($sql)->getResult();
    //     $datos = [];

    //     if (!empty($data)) {
    //         foreach ($data as $item) {

    //         }
    // }

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