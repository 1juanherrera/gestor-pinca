<?php
namespace App\Models;

class ItemProveedorModel extends BaseModel
{
    protected $table      = 'item_proveedor';
    protected $primaryKey = 'id_item_proveedor';
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $dateFormat     = 'datetime';
    protected $allowedFields = [
        'nombre',
        'codigo',
        'tipo',
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
                    uc.nombre  AS unidad_compra_nombre,
                    ua.nombre  AS unidad_almacenaje_nombre
                FROM item_proveedor ip
                LEFT JOIN proveedor    p  ON p.id_proveedor       = ip.proveedor_id
                LEFT JOIN item_general ig ON ig.id_item_general   = ip.item_general_id
                LEFT JOIN unidad       uc ON uc.id_unidad         = ip.unidad_compra_id
                LEFT JOIN unidad       ua ON ua.id_unidad         = ig.unidad_almacenaje_id
                WHERE ip.deleted_at IS NULL
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
                'unidad_compra_id'         => $item['unidad_compra_id'],
                'unidad_compra_nombre'     => $item['unidad_compra_nombre'],
                'factor_conversion'        => (float) ($item['factor_conversion'] ?? 1),
                'unidad_almacenaje_nombre' => $item['unidad_almacenaje_nombre'],
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

        // Lock pesimista vía transacción + LOCK TABLES alternative usando
        // GET_LOCK con clave derivada del nombre. Garantiza que dos requests
        // simultáneas con el mismo nombre no creen duplicados.
        // Clave: prefijo + hash del nombre (max 64 chars MySQL).
        $lockKey = 'item_general_create:' . md5($nombre);
        $this->db->query('SELECT GET_LOCK(?, 5) AS got', [$lockKey]);

        try {
            // 1. Buscar existente por nombre (insensible a mayúsculas).
            //    Incluimos soft-deleted para no recrear nombres "ocupados".
            $existente = $this->db->query(
                "SELECT id_item_general, deleted_at FROM item_general WHERE UPPER(TRIM(nombre)) = ? LIMIT 1",
                [$nombre]
            )->getRowArray();

            if ($existente) {
                if ($existente['deleted_at']) {
                    throw new \Exception(
                        "Ya existe un ítem '{$nombre}' archivado (soft-deleted). " .
                        'Restauralo desde Catálogo o usá un nombre distinto.'
                    );
                }
                $data['item_general_id'] = (int) $existente['id_item_general'];
                return $data['item_general_id'];
            }

            // 2. No existe → crear automáticamente
            $tipoMap = ['Materia Prima' => 1, 'Insumo' => 2, 'Producto' => 0];
            $tipo    = $tipoMap[$data['tipo'] ?? ''] ?? 1;

            $kiloId = $this->db->query(
                "SELECT id_unidad FROM unidad WHERE nombre = 'KILO' LIMIT 1"
            )->getRowArray()['id_unidad'] ?? null;

            $codigo      = !empty($data['catalogo_codigo'])              ? substr($data['catalogo_codigo'], 0, 10) : null;
            $categoriaId = !empty($data['catalogo_categoria_id'])        ? (int) $data['catalogo_categoria_id']     : null;
            $unidadId    = !empty($data['catalogo_unidad_id'])           ? (int) $data['catalogo_unidad_id']        : null;
            $unidadAlmId = !empty($data['catalogo_unidad_almacenaje_id'])? (int) $data['catalogo_unidad_almacenaje_id'] : $kiloId;

            $this->db->query(
                "INSERT INTO item_general (nombre, codigo, tipo, categoria_id, unidad_id, unidad_almacenaje_id) VALUES (?, ?, ?, ?, ?, ?)",
                [$nombre, $codigo, $tipo, $categoriaId, $unidadId, $unidadAlmId]
            );

            $nuevoId = $this->db->insertID();
            if (!$nuevoId) {
                throw new \Exception("No se pudo crear el ítem general para '{$nombre}'.");
            }

            $this->db->table('costos_item')->insert([
                'item_general_id' => $nuevoId,
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

            $data['item_general_id'] = $nuevoId;
            return $nuevoId;
        } finally {
            $this->db->query('SELECT RELEASE_LOCK(?)', [$lockKey]);
        }
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