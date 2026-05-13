<?php

namespace App\Models;

class ProveedorModel extends BaseModel
{
    protected $table      = 'proveedor';
    protected $primaryKey = 'id_proveedor';
    protected $allowedFields = [
        'nombre_encargado',
        'nombre_empresa',
        'numero_documento',
        'direccion',
        'telefono',
        'email',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // ── Proveedor con sus items anidados ──────────────────────────────────
    public function get_item_proveedores($id = null)
    {
        $sql    = 'SELECT * FROM proveedor';
        $params = [];

        if ($id !== null) {
            $sql     .= ' WHERE id_proveedor = ?';
            $params[] = $id;
        }

        $proveedores = $this->db->query($sql, $params)->getResult();

        foreach ($proveedores as &$proveedor) {
            $items = $this->db->query(
                'SELECT ip.* FROM item_proveedor ip WHERE ip.proveedor_id = ?',
                [$proveedor->id_proveedor]
            )->getResult();

            foreach ($items as &$item) {
                $item->precio_unitario = (float) $item->precio_unitario;
                $item->precio_con_iva  = (float) $item->precio_con_iva;
            }

            $proveedor->items = $items;
        }

        return $id !== null ? ($proveedores[0] ?? null) : $proveedores;
    }
}