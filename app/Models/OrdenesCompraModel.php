<?php

namespace App\Models;

class OrdenesCompraModel extends BaseModel
{
    protected $table      = 'ordenes_compra';
    protected $primaryKey = 'id_orden';
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields = [
        'numero', 'proveedor_id', 'bodegas_id', 'fecha',
        'fecha_esperada', 'estado', 'total', 'iva_pct', 'observaciones',
    ];

    protected $dbc; // ✅ conexión propia

    public function __construct()
    {
        parent::__construct();
        $this->dbc = \Config\Database::connect(); // ✅
    }

    // generarNumero ELIMINADO — reemplazado por
    // (new NumeracionModel())->reservar('orden_compra') que usa SELECT … FOR UPDATE.

    public function listar(): array
    {
        $rows = $this->dbc->query('
            SELECT
                oc.*,
                p.nombre_empresa,
                p.nombre_encargado,
                b.nombre AS bodega_nombre
            FROM ordenes_compra oc
            LEFT JOIN proveedor p ON p.id_proveedor = oc.proveedor_id
            LEFT JOIN bodegas   b ON b.id_bodegas   = oc.bodegas_id
            WHERE oc.deleted_at IS NULL
            ORDER BY oc.id_orden DESC
        ')->getResult('array');

        return array_map([$this, 'enrichWithIva'], $rows);
    }

    /**
     * Enriquece una fila de OC con campos derivados de IVA.
     * `total` conserva su semántica histórica (subtotal sin IVA).
     * Agrega `iva_monto` y `total_con_iva` calculados desde `iva_pct`.
     */
    private function enrichWithIva(array $row): array
    {
        $total  = (float) ($row['total']   ?? 0);
        $ivaPct = (float) ($row['iva_pct'] ?? 0);

        $row['total']         = round($total, 2);
        $row['iva_pct']       = $ivaPct;
        $row['iva_monto']     = round($total * $ivaPct / 100, 2);
        $row['total_con_iva'] = round($total + $row['iva_monto'], 2);

        return $row;
    }

    public function detalle(int $id): ?array
    {
        $ordenRaw = $this->dbc->query('
            SELECT
                oc.*,
                p.nombre_empresa, p.nombre_encargado, p.telefono, p.email,
                b.nombre AS bodega_nombre
            FROM ordenes_compra oc
            LEFT JOIN proveedor p ON p.id_proveedor = oc.proveedor_id
            LEFT JOIN bodegas b   ON b.id_bodegas   = oc.bodegas_id
            WHERE oc.id_orden = ? AND oc.deleted_at IS NULL
        ', [$id])->getRow(); // ✅ sin 'array'

        if (!$ordenRaw) return null;

        // ✅ Convertir a array explícitamente
        $orden = (array) $ordenRaw;

        $lineasRaw = $this->dbc->query('
            SELECT
                ocd.*,
                ip.nombre AS item_nombre, ip.codigo AS item_codigo,
                ip.factor_conversion AS factor_conversion,
                ip.unidad_compra_id  AS unidad_compra_id,
                uc.nombre AS unidad_empaque,
                uc.nombre AS unidad_compra_nombre,
                ig.nombre AS item_general_nombre,
                ig.unidad_almacenaje_id AS unidad_base_id,
                ub.nombre AS unidad_base_nombre
            FROM ordenes_compra_detalle ocd
            LEFT JOIN item_proveedor ip ON ip.id_item_proveedor = ocd.item_proveedor_id
            LEFT JOIN item_general ig   ON ig.id_item_general   = ocd.item_general_id
            LEFT JOIN unidad       uc   ON uc.id_unidad         = ip.unidad_compra_id
            LEFT JOIN unidad       ub   ON ub.id_unidad         = ig.unidad_almacenaje_id
            WHERE ocd.ordenes_compra_id = ?
        ', [$id])->getResult(); // ✅ sin 'array'

        $lineas = [];
        foreach ($lineasRaw as $l) {
            $l = (array) $l; // ✅ convertir cada fila
            $l['cantidad']          = (float) $l['cantidad'];
            $l['precio_unit']       = (float) $l['precio_unit'];
            $l['subtotal']          = (float) $l['subtotal'];
            $l['cantidad_recibida'] = (float) ($l['cantidad_recibida'] ?? 0);
            $lineas[] = $l;
        }

        $orden['lineas'] = $lineas;
        return $this->enrichWithIva($orden);
    }

    public function recalcularTotal(int $id): void
    {
        $total = $this->dbc->query('
            SELECT COALESCE(SUM(subtotal), 0) as total 
            FROM ordenes_compra_detalle 
            WHERE ordenes_compra_id = ?
        ', [$id])->getRow('array')['total'] ?? 0;

        $this->dbc->query('
            UPDATE ordenes_compra SET total = ? WHERE id_orden = ?
        ', [$total, $id]);
    }

    public function todasRecibidas(int $id): bool
    {
        $pendientes = $this->dbc->query('
            SELECT COUNT(*) as total
            FROM ordenes_compra_detalle
            WHERE ordenes_compra_id = ?
              AND (recibido_en IS NULL OR COALESCE(cantidad_recibida, 0) < cantidad)
        ', [$id])->getRow('array')['total'] ?? 0;

        return (int) $pendientes === 0;
    }
}