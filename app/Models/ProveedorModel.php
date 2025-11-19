<?php

namespace App\Models;
use App\Libraries\Formatter;

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

    public function get_item_proveedores($id = null)
    {
        $sql = 'SELECT * FROM proveedor';
        $params = [];

        if ($id !== null) {
            $sql .= ' WHERE id_proveedor = ?';
            $params[] = $id;
        }

        $proveedores = $this->db->query($sql, $params)->getResult();

        if (!empty($proveedores)) {
            foreach ($proveedores as &$proveedor) {
                $sqlItems = 'SELECT ip.* FROM item_proveedor ip WHERE ip.proveedor_id = ?';
                $items = $this->db->query($sqlItems, [$proveedor->id_proveedor])->getResult();

                // 🔹 Aquí formateas los precios de los ítems
                foreach ($items as &$item) {
                    // Formato con 2 decimales y separadores de miles
                    $item->precio_unitario = (float) $item->precio_unitario;
                    $item->precio_con_iva = (float) $item->precio_con_iva;
                }

                $proveedor->items = $items;
            }
        }

        return $id !== null ? ($proveedores[0] ?? null) : $proveedores;
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