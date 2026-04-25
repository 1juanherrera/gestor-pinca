<?php

namespace App\Models;

class CatalogoModel extends BaseModel
{
    protected $table      = 'item_general';
    protected $primaryKey = 'id_item_general';
    protected $allowedFields = [
        'nombre', 'codigo', 'tipo', 'categoria_id',
        'viscosidad', 'p_g', 'color', 'brillo_60', 'secado',
        'cubrimiento', 'molienda', 'ph', 'poder_tintoreo',
        'unidad_id', 'unidad_almacenaje_id', 'costo_produccion',
        'precio_venta_manual', 'precio_manual_activo',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function listar(?int $tipo = null, ?int $categoriaId = null, ?string $busqueda = null): array
    {
        $sql = "
            SELECT
                ig.id_item_general,
                ig.nombre,
                ig.codigo,
                ig.tipo,
                ig.categoria_id,
                ig.unidad_id,
                ig.unidad_almacenaje_id,
                ig.viscosidad, ig.p_g, ig.color, ig.brillo_60,
                ig.secado, ig.cubrimiento, ig.molienda, ig.ph, ig.poder_tintoreo,
                ig.precio_venta_manual, ig.precio_manual_activo,
                c.nombre       AS categoria_nombre,
                u.nombre       AS unidad_nombre,
                ua.nombre      AS unidad_almacenaje_nombre,
                ci.costo_unitario,
                ci.precio_venta,
                COALESCE(stock.stock_total, 0) AS stock_total,
                COALESCE(prov.total_proveedores, 0) AS total_proveedores
            FROM item_general ig
            LEFT JOIN categoria   c  ON c.id_categoria         = ig.categoria_id
            LEFT JOIN unidad      u  ON u.id_unidad            = ig.unidad_id
            LEFT JOIN unidad      ua ON ua.id_unidad           = ig.unidad_almacenaje_id
            LEFT JOIN costos_item ci ON ci.item_general_id     = ig.id_item_general
            LEFT JOIN (
                SELECT item_general_id, SUM(cantidad_disponible) AS stock_total
                FROM inventario_capas
                WHERE estado = 1 AND cantidad_disponible > 0
                GROUP BY item_general_id
            ) stock ON stock.item_general_id = ig.id_item_general
            LEFT JOIN (
                SELECT item_general_id, COUNT(*) AS total_proveedores
                FROM item_proveedor
                WHERE disponible = 1
                GROUP BY item_general_id
            ) prov ON prov.item_general_id = ig.id_item_general
            WHERE 1=1
        ";

        $params = [];

        if ($tipo !== null) {
            $sql .= " AND ig.tipo = ?";
            $params[] = $tipo;
        }

        if ($categoriaId !== null) {
            $sql .= " AND ig.categoria_id = ?";
            $params[] = $categoriaId;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= " AND (UPPER(ig.nombre) LIKE ? OR UPPER(ig.codigo) LIKE ?)";
            $term = '%' . strtoupper($busqueda) . '%';
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY ig.nombre ASC";

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function detalle(int $id): ?array
    {
        $sql = "
            SELECT
                ig.*,
                c.nombre       AS categoria_nombre,
                u.nombre       AS unidad_nombre,
                ua.nombre      AS unidad_almacenaje_nombre,
                ci.costo_unitario,
                ci.precio_venta,
                ci.envase, ci.etiqueta, ci.plastico, ci.volumen,
                ci.costo_mp_galon, ci.costo_mp_kg,
                ci.costo_cunete, ci.costo_tambor
            FROM item_general ig
            LEFT JOIN categoria   c  ON c.id_categoria         = ig.categoria_id
            LEFT JOIN unidad      u  ON u.id_unidad            = ig.unidad_id
            LEFT JOIN unidad      ua ON ua.id_unidad           = ig.unidad_almacenaje_id
            LEFT JOIN costos_item ci ON ci.item_general_id     = ig.id_item_general
            WHERE ig.id_item_general = ?
        ";

        $item = $this->db->query($sql, [$id])->getRowArray();
        if (!$item) return null;

        $item['proveedores'] = $this->db->query("
            SELECT
                ip.id_item_proveedor,
                ip.nombre,
                ip.codigo,
                ip.tipo,
                ip.precio_unitario,
                ip.precio_con_iva,
                ip.disponible,
                ip.descripcion,
                ip.proveedor_id,
                ip.unidad_compra_id,
                ip.factor_conversion,
                p.nombre_empresa,
                p.nombre_encargado,
                uc.nombre AS unidad_compra_nombre
            FROM item_proveedor ip
            LEFT JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
            LEFT JOIN unidad uc   ON uc.id_unidad   = ip.unidad_compra_id
            WHERE ip.item_general_id = ?
            ORDER BY ip.disponible DESC, ip.precio_unitario ASC
        ", [$id])->getResultArray();

        $item['stock_por_bodega'] = $this->db->query("
            SELECT
                ic.bodegas_id,
                b.nombre AS bodega_nombre,
                SUM(ic.cantidad_disponible) AS cantidad,
                COUNT(*)                    AS capas_activas
            FROM inventario_capas ic
            LEFT JOIN bodegas b ON b.id_bodegas = ic.bodegas_id
            WHERE ic.item_general_id = ?
              AND ic.estado = 1
              AND ic.cantidad_disponible > 0
            GROUP BY ic.bodegas_id, b.nombre
            ORDER BY b.nombre
        ", [$id])->getResultArray();

        $stockTotal = 0;
        foreach ($item['stock_por_bodega'] as $s) {
            $stockTotal += (float) $s['cantidad'];
        }
        $item['stock_total'] = round($stockTotal, 4);

        return $item;
    }

    public function crearItem(array $data): int
    {
        $this->db->transStart();

        try {
            $tipoMap = [
                'MATERIA PRIMA'      => 1,
                'INSUMO'             => 2,
                'PRODUCTO TERMINADO' => 0,
                'PRODUCTO'           => 0,
            ];
            $tipo = is_numeric($data['tipo'] ?? '')
                ? (int) $data['tipo']
                : ($tipoMap[strtoupper($data['tipo'] ?? '')] ?? 0);

            $itemData = [
                'nombre'              => $data['nombre'],
                'codigo'              => substr($data['codigo'] ?? '', 0, 10),
                'tipo'                => $tipo,
                'categoria_id'        => $data['categoria_id'] ?? null,
                'viscosidad'          => $data['viscosidad'] ?? null,
                'p_g'                 => $data['p_g'] ?? null,
                'color'               => $data['color'] ?? null,
                'brillo_60'           => $data['brillo_60'] ?? null,
                'secado'              => $data['secado'] ?? null,
                'cubrimiento'         => $data['cubrimiento'] ?? null,
                'molienda'            => $data['molienda'] ?? null,
                'ph'                  => $data['ph'] ?? null,
                'poder_tintoreo'      => $data['poder_tintoreo'] ?? null,
                'unidad_id'           => $data['unidad_id'] ?? null,
                'unidad_almacenaje_id'=> $data['unidad_almacenaje_id'] ?? null,
                'costo_produccion'    => 0,
            ];

            $this->db->table('item_general')->insert($itemData);
            $newId = $this->db->insertID();

            $this->db->table('costos_item')->insert([
                'item_general_id' => $newId,
                'costo_unitario'  => 0,
                'costo_mp_galon'  => 0,
                'costo_mp_kg'     => 0,
                'costo_cunete'    => 0,
                'costo_tambor'    => 0,
                'periodo'         => date('Y-m'),
                'metodo_calculo'  => 'Catálogo',
                'fecha_calculo'   => date('Y-m-d'),
                'envase'          => 0,
                'etiqueta'        => 0,
                'bandeja'         => 0,
                'plastico'        => 0,
                'precio_venta'    => 0,
                'costo_mod'       => 0,
                'volumen'         => 1,
                'estado'          => 1,
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error al confirmar la transacción.');
            }

            return $newId;

        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function actualizarItem(int $id, array $data): bool
    {
        $exists = $this->db->table('item_general')
            ->where('id_item_general', $id)->countAllResults();
        if (!$exists) {
            throw new \Exception("Ítem con ID {$id} no encontrado.");
        }

        $tipoMap = [
            'MATERIA PRIMA'      => 1,
            'INSUMO'             => 2,
            'PRODUCTO TERMINADO' => 0,
            'PRODUCTO'           => 0,
        ];
        $tipo = is_numeric($data['tipo'] ?? '')
            ? (int) $data['tipo']
            : ($tipoMap[strtoupper($data['tipo'] ?? '')] ?? 0);

        $itemData = [
            'nombre'              => $data['nombre'],
            'codigo'              => substr($data['codigo'] ?? '', 0, 10),
            'tipo'                => $tipo,
            'categoria_id'        => $data['categoria_id'] ?? null,
            'viscosidad'          => $data['viscosidad'] ?? null,
            'p_g'                 => $data['p_g'] ?? null,
            'color'               => $data['color'] ?? null,
            'brillo_60'           => $data['brillo_60'] ?? null,
            'secado'              => $data['secado'] ?? null,
            'cubrimiento'         => $data['cubrimiento'] ?? null,
            'molienda'            => $data['molienda'] ?? null,
            'ph'                  => $data['ph'] ?? null,
            'poder_tintoreo'      => $data['poder_tintoreo'] ?? null,
            'unidad_id'           => $data['unidad_id'] ?? null,
            'unidad_almacenaje_id'=> $data['unidad_almacenaje_id'] ?? null,
        ];

        $this->db->table('item_general')
            ->where('id_item_general', $id)
            ->update($itemData);

        return true;
    }

    public function proveedoresDeItem(int $itemGeneralId): array
    {
        return $this->db->query("
            SELECT
                ip.id_item_proveedor,
                ip.nombre,
                ip.codigo,
                ip.tipo,
                ip.precio_unitario,
                ip.precio_con_iva,
                ip.disponible,
                ip.descripcion,
                ip.proveedor_id,
                ip.unidad_compra_id,
                ip.factor_conversion,
                p.nombre_empresa,
                p.nombre_encargado,
                p.telefono,
                p.email,
                uc.nombre AS unidad_compra_nombre
            FROM item_proveedor ip
            LEFT JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
            LEFT JOIN unidad uc   ON uc.id_unidad   = ip.unidad_compra_id
            WHERE ip.item_general_id = ?
            ORDER BY ip.disponible DESC, ip.precio_unitario ASC
        ", [$itemGeneralId])->getResultArray();
    }
}
