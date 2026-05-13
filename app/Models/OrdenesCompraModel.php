<?php

namespace App\Models;

class OrdenesCompraModel extends BaseModel
{
    protected $table      = 'ordenes_compra';
    protected $primaryKey = 'id_orden';
    protected $allowedFields = [
        'numero', 'proveedor_id', 'bodegas_id', 'fecha',
        'fecha_esperada', 'estado', 'total', 'observaciones',
    ];

    protected $dbc; // ✅ conexión propia

    public function __construct()
    {
        parent::__construct();
        $this->dbc = \Config\Database::connect(); // ✅
    }

    public function generarNumero(): string
    {
        $ultimo = $this->dbc->table('ordenes_compra')
            ->select('numero')
            ->orderBy('id_orden', 'DESC')
            ->limit(1)
            ->get()->getRow();

        if (!$ultimo) return 'OC-001';
        $num = (int) substr($ultimo->numero, 3);
        return 'OC-' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    public function listar(): array
    {
        return $this->dbc->query('
            SELECT
                oc.*,
                p.nombre_empresa,
                p.nombre_encargado,
                b.nombre AS bodega_nombre
            FROM ordenes_compra oc
            LEFT JOIN proveedor p ON p.id_proveedor = oc.proveedor_id
            LEFT JOIN bodegas   b ON b.id_bodegas   = oc.bodegas_id
            ORDER BY oc.id_orden DESC
        ')->getResult('array');
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
            WHERE oc.id_orden = ?
        ', [$id])->getRow(); // ✅ sin 'array'

        if (!$ordenRaw) return null;

        // ✅ Convertir a array explícitamente
        $orden = (array) $ordenRaw;

        $lineasRaw = $this->dbc->query('
            SELECT 
                ocd.*,
                ip.nombre AS item_nombre, ip.codigo AS item_codigo, uc.nombre AS unidad_empaque,
                ig.nombre AS item_general_nombre
            FROM ordenes_compra_detalle ocd
            LEFT JOIN item_proveedor ip ON ip.id_item_proveedor = ocd.item_proveedor_id
            LEFT JOIN item_general ig   ON ig.id_item_general   = ocd.item_general_id
            LEFT JOIN unidad       uc   ON uc.id_unidad         = ip.unidad_compra_id
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

        $orden['total']  = (float) $orden['total'];
        $orden['lineas'] = $lineas;

        return $orden;
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
            WHERE ordenes_compra_id = ? AND recibido_en IS NULL
        ', [$id])->getRow('array')['total'] ?? 0;

        return (int) $pendientes === 0;
    }
}