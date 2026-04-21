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
        'proveedor_id',
        'item_general_id',
        'unidad_compra_id',
        'factor_conversion',
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
                    ig.codigo  AS item_general_codigo,
                    uc.nombre  AS unidad_compra_nombre
                FROM item_proveedor ip
                LEFT JOIN proveedor    p  ON p.id_proveedor    = ip.proveedor_id
                LEFT JOIN item_general ig ON ig.id_item_general = ip.item_general_id
                LEFT JOIN unidad       uc ON uc.id_unidad       = ip.unidad_compra_id
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
                'item_general_id'     => $item['item_general_id'],
                'item_general_nombre' => $item['item_general_nombre'],
                'item_general_codigo' => $item['item_general_codigo'],
                'unidad_compra_id'    => $item['unidad_compra_id'],
                'unidad_compra_nombre'=> $item['unidad_compra_nombre'],
                'factor_conversion'   => (float) ($item['factor_conversion'] ?? 1),
            ];
        }

        return $formatted;
    }

    // ── Resuelve o crea el item_general para un item_proveedor ───────────
    // Si ya viene item_general_id en $data lo respeta.
    // Si no, busca por nombre exacto (case-insensitive); si no existe lo crea.
    // Devuelve el id_item_general resuelto.
    public function resolverItemGeneral(array &$data): int
    {
        // Ya viene vinculado → nada que hacer
        if (!empty($data['item_general_id'])) {
            return (int) $data['item_general_id'];
        }

        $nombre = strtoupper(trim($data['nombre'] ?? ''));
        if ($nombre === '') {
            throw new \Exception('El nombre es obligatorio para auto-vincular el ítem general.');
        }

        // 1. Buscar existente por nombre (insensible a mayúsculas)
        $existente = $this->db->query(
            "SELECT id_item_general FROM item_general WHERE UPPER(TRIM(nombre)) = ? LIMIT 1",
            [$nombre]
        )->getRowArray();

        if ($existente) {
            $data['item_general_id'] = (int) $existente['id_item_general'];
            return $data['item_general_id'];
        }

        // 2. No existe → crear automáticamente
        // Mapear tipo de item_proveedor al tipo numérico de item_general
        $tipoMap = ['Materia Prima' => 1, 'Insumo' => 2, 'Producto' => 0];
        $tipo    = $tipoMap[$data['tipo'] ?? ''] ?? 1;

        // Obtener id de la unidad KILO como unidad_almacenaje por defecto
        $kiloId = $this->db->query(
            "SELECT id_unidad FROM unidad WHERE nombre = 'KILO' LIMIT 1"
        )->getRowArray()['id_unidad'] ?? null;

        $this->db->query(
            "INSERT INTO item_general (nombre, tipo, unidad_almacenaje_id) VALUES (?, ?, ?)",
            [$nombre, $tipo, $kiloId]
        );

        $nuevoId = $this->db->insertID();
        if (!$nuevoId) {
            throw new \Exception("No se pudo crear el ítem general para '{$nombre}'.");
        }

        $data['item_general_id'] = $nuevoId;
        return $nuevoId;
    }

    // ── Vincular o desvincular un item_proveedor con item_general ────────
    // $itemGeneralId = int  → vincula
    // $itemGeneralId = null → desvincula
    public function vincular(int $id, ?int $itemGeneralId, ?int $unidadCompraId = null, float $factorConversion = 1.0): bool
    {
        return $this->update($id, [
            'item_general_id'  => $itemGeneralId,
            'unidad_compra_id' => $unidadCompraId,
            'factor_conversion'=> $factorConversion,
        ]);
    }
}