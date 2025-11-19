<?php
namespace App\Models;

class BodegasModel extends BaseModel
{

    protected $table = 'bodegas';
    protected $primaryKey = 'id_bodegas';
    protected $allowedFields = [
        "nombre",
        "descripcion",
        "estado",
        "instalaciones_id",
    ];

    public function __construct(){

        parent::__construct();
    }

    public function bodega_inventario($id_bodega = null)
    {
        if ($id_bodega === null) {
            return $this->bodega_inventario();
        } else {
            $sql = 'SELECT * FROM bodegas WHERE id_bodegas = ?';
            $bodega = $this->db->query($sql, [$id_bodega])->getRow();

            if ($bodega) {
                $sql1 = 'SELECT ig.*, inv.cantidad
                        FROM inventario inv
                        JOIN item_general ig ON inv.item_general_id = ig.id_item_general
                        WHERE inv.bodegas_id = ?';
                $inventario = $this->db->query($sql1, [$id_bodega])->getResult();

                return [
                    'id_bodegas' => $bodega->id_bodegas,
                    'nombre'     => $bodega->nombre,
                    'descripcion'=> $bodega->descripcion,
                    'estado'     => $bodega->estado,
                    'instalaciones_id' => $bodega->instalaciones_id,
                    'inventario' => $inventario
                ];
            }
            return null;
        }
    }

}
