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

    public function bodega_inventario($id_bodega = null, $page = 1, $perPage = 10)
    {
        if ($id_bodega === null) return null;

        // 1. Info básica de la bodega
        $bodega = $this->db->query('SELECT * FROM bodegas WHERE id_bodegas = ?', [$id_bodega])->getRow();

        if ($bodega) {
            // 2. Forzar tipos enteros para evitar error 500 en el LIMIT
            $perPage = (int)$perPage;
            $page    = (int)$page;
            $offset  = ($page - 1) * $perPage;

            // 3. Contar total de registros para la paginación
            $totalItems = $this->db->query('SELECT COUNT(*) as total FROM inventario WHERE bodegas_id = ?', [$id_bodega])->getRow()->total;

            // 4. Query con Joins y Paginación
            $sql = "SELECT 
                        ig.id_item_general, ig.nombre, ig.codigo, 
                        inv.cantidad, ig.tipo, ca.nombre AS categoria,
                        u.nombre AS unidad, c.costo_mp_galon, c.precio_venta
                    FROM inventario inv
                    JOIN item_general ig ON inv.item_general_id = ig.id_item_general
                    LEFT JOIN costos_item c ON c.item_general_id = ig.id_item_general
                    LEFT JOIN categoria ca ON ig.categoria_id = ca.id_categoria
                    LEFT JOIN unidad u ON ig.unidad_id = u.id_unidad
                    WHERE inv.bodegas_id = ?
                    LIMIT " . $perPage . " OFFSET " . $offset;

            $inventario = $this->db->query($sql, [$id_bodega])->getResult();

            return [
                'id_bodegas'       => $bodega->id_bodegas,
                'nombre'           => $bodega->nombre,
                'instalaciones_id' => $bodega->instalaciones_id,
                'inventario'       => $inventario,
                'pagination'       => [
                    'totalItems'   => (int)$totalItems,
                    'totalPages'   => ceil($totalItems / $perPage),
                    'currentPage'  => $page,
                    'perPage'      => $perPage
                ]
            ];
        }
        return null;
    }

}
