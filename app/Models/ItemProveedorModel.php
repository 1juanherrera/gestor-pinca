<?php
namespace App\Models;

class ItemProveedorModel extends BaseModel
{
    protected $table      = 'item_proveedor';
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
        'proveedor_id',       // ← corregido: era 'id_proveedor'
        'item_general_id',    // ← nuevo: vínculo con item_general
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // ── Lista completa con datos del proveedor e ítem vinculado ──────────
    public function get_item_proveedores()
    {
        $sql = 'SELECT
                    ip.*,
                    p.nombre_encargado,
                    p.nombre_empresa,
                    p.telefono,
                    p.email,
                    ig.nombre  AS item_general_nombre,
                    ig.codigo  AS item_general_codigo
                FROM item_proveedor ip
                LEFT JOIN proveedor    p  ON p.id_proveedor    = ip.proveedor_id
                LEFT JOIN item_general ig ON ig.id_item_general = ip.item_general_id
        ';

        $items = $this->db->query($sql)->getResult();

        $formatted = [];
        foreach ($items as $item) {
            $item = (array) $item;

            $formatted[] = [
                'id_item_proveedor'   => $item['id_item_proveedor'],
                'nombre'              => $item['nombre'],
                'codigo'              => $item['codigo'],
                'tipo'                => $item['tipo'],
                'unidad_empaque'      => $item['unidad_empaque'],
                'precio_unitario'     => (float) $item['precio_unitario'],
                'precio_con_iva'      => (float) $item['precio_con_iva'],
                'disponible'          => $item['disponible'],
                'descripcion'         => $item['descripcion'],
                'proveedor_id'        => $item['proveedor_id'],
                'nombre_encargado'    => $item['nombre_encargado'],
                'nombre_empresa'      => $item['nombre_empresa'],
                'telefono'            => $item['telefono'],
                'email'               => $item['email'],
                // Vínculo con item_general
                'item_general_id'     => $item['item_general_id'],
                'item_general_nombre' => $item['item_general_nombre'],
                'item_general_codigo' => $item['item_general_codigo'],
            ];
        }

        return $formatted;
    }

    // ── Vincular o desvincular un item_proveedor con item_general ────────
    // $itemGeneralId = int  → vincula
    // $itemGeneralId = null → desvincula
    public function vincular(int $id, ?int $itemGeneralId): bool
    {
        return $this->update($id, ['item_general_id' => $itemGeneralId]);
    }
}