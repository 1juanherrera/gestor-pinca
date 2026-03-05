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

    public function bodega_inventario($id_bodega = null, $page = 1, $perPage = 10, $search = '', $tipo = '')
    {
        if ($id_bodega === null) return null;

        $bodega = $this->db->query('SELECT * FROM bodegas WHERE id_bodegas = ?', [$id_bodega])->getRow();

        if ($bodega) {
            $perPage = (int)$perPage;
            $page    = (int)$page;
            $offset  = ($page - 1) * $perPage;

            // 1. Parámetros iniciales
            $params = [$id_bodega];
            $whereConditions = " WHERE inv.bodegas_id = ? ";

            // 2. Filtro de Búsqueda (Nombre o Código)
            if (!empty($search)) {
                $whereConditions .= " AND (ig.nombre LIKE ? OR ig.codigo LIKE ?) ";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            // 3. Filtro por TIPO (Lo que viene de NavTabs)
            // Usamos !== '' porque el valor '0' es un string válido pero empty() lo vería como falso
            if ($tipo !== '' && $tipo !== null) {
                $whereConditions .= " AND ig.tipo = ? ";
                $params[] = $tipo;
            }

            // 4. Conteo Total con Filtros (Indispensable para paginación)
            $countSql = "SELECT COUNT(*) as total 
                        FROM inventario inv 
                        JOIN item_general ig ON inv.item_general_id = ig.id_item_general 
                        $whereConditions";
            $totalItems = $this->db->query($countSql, $params)->getRow()->total;

            // 5. Query Principal
            $sql = "SELECT 
                        ig.id_item_general, ig.nombre, ig.codigo, 
                        inv.cantidad, ig.tipo, ca.nombre AS categoria,
                        u.nombre AS unidad, c.costo_mp_galon, c.precio_venta
                    FROM inventario inv
                    JOIN item_general ig ON inv.item_general_id = ig.id_item_general
                    LEFT JOIN costos_item c ON c.item_general_id = ig.id_item_general
                    LEFT JOIN categoria ca ON ig.categoria_id = ca.id_categoria
                    LEFT JOIN unidad u ON ig.unidad_id = u.id_unidad
                    $whereConditions 
                    LIMIT $perPage OFFSET $offset";

            $inventario = $this->db->query($sql, $params)->getResult();

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
