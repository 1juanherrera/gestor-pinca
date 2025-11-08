<?php
namespace App\Models;

use App\Libraries\Formatter;

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

    public function get_item_proveedores()
    {
        $sql = 'SELECT ip.*,
            p.nombre_encargado,
            p.nombre_empresa,
            p.telefono,
            p.email    
            FROM item_proveedor ip
            LEFT JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
        ';

        $items = $this->db->query($sql)->getResult();

        $formatted = [];
        foreach ($items as $item) {
            $item = (array) $item;

            $formatted[] = [
                'id_item_proveedor' => $item['id_item_proveedor'],
                'nombre' => $item['nombre'],
                'codigo' => $item['codigo'],
                'tipo' => $item['tipo'],
                'unidad_empaque' => $item['unidad_empaque'],
                'precio_unitario' => Formatter::toCOP($item['precio_unitario']),
                'precio_con_iva' => Formatter::toCOP($item['precio_con_iva']),
                'disponible' => $item['disponible'],
                'descripcion' => $item['descripcion'],
                'proveedor_id' => $item['proveedor_id'],
                'nombre_encargado' => $item['nombre_encargado'],
                'nombre_empresa' => $item['nombre_empresa'],
                'telefono' => $item['telefono'],
                'email' => $item['email'],
            ];
        }

        return $formatted;
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