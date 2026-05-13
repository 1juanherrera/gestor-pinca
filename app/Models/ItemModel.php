<?php
namespace App\Models;

class ItemModel extends BaseModel
{
    protected $table = 'item_general';
    protected $primaryKey = 'id_item_general';
    protected $allowedFields = [
        'nombre',
        'codigo',
        'tipo',
        'categoria_id',
        'viscosidad',
        'p_g',
        'color',
        'brillo_60',
        'secado',
        'cubrimiento',
        'molienda',
        'ph',
        'poder_tintoreo',
        'unidad_id',
        'unidad_almacenaje_id',
        'costo_produccion',
        'precio_venta_manual',
        'precio_manual_activo',
    ];

    public function __construct(){
        parent::__construct();
    }

    public function get_all($table, $where = null){ 
        $this->table = $table;
        if ($where) { $this->where($where);  }           
        return $this->findAll();
    }

    public function get_items_all($where = null){

        $sql = 'SELECT
                    ig.*,
                    c.nombre AS categoria,
                    ci.costo_unitario,
                    u.nombre  AS unidad_nombre,
                    u.escala  AS escala_venta,
                    ua.nombre AS unidad_almacenaje,
                    ua.escala AS escala_almacenaje
                FROM item_general ig
                LEFT JOIN categoria c   ON ig.categoria_id         = c.id_categoria
                LEFT JOIN costos_item ci ON ci.item_general_id     = ig.id_item_general
                LEFT JOIN unidad u       ON ig.unidad_id           = u.id_unidad
                LEFT JOIN unidad ua      ON ig.unidad_almacenaje_id = ua.id_unidad';

        $tipos = [
            0 => 'PRODUCTO',
            1 => 'MATERIA PRIMA',
            2 => 'INSUMO',
        ];

        $items = $this->db->query($sql)->getResult();

        foreach ($items as &$item) {
            $item->nombre_tipo = $tipos[$item->tipo] ?? 'Otro';
        }
                
        if (empty($items)) {
            return [];
        }

        return $items;
    }

    public function get_materias_disponibles()
    {
        $sql = "
            SELECT
                ig.id_item_general  AS item_general_id,
                NULL                AS id_item_proveedor,
                ig.nombre,
                ig.codigo,
                COALESCE(ci.costo_unitario, 0) AS costo_unitario,
                'inventario'        AS fuente,
                NULL                AS proveedor_nombre,
                1                   AS comprado
            FROM item_general ig
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
            WHERE ig.tipo = 1

            UNION ALL

            SELECT
                NULL                            AS item_general_id,
                ip.id_item_proveedor,
                ip.nombre,
                ip.codigo,
                COALESCE(ip.precio_unitario, 0) AS costo_unitario,
                'proveedor'                     AS fuente,
                p.nombre_empresa                AS proveedor_nombre,
                0                               AS comprado
            FROM item_proveedor ip
            LEFT JOIN proveedor p ON p.id_proveedor = ip.proveedor_id
            WHERE ip.item_general_id IS NULL

            ORDER BY nombre ASC
        ";

        return $this->db->query($sql)->getResultArray();
    }

    public function get_full_item_details($id)
    {
        // 1. Obtener datos básicos de item_general y sus costos
        $builder = $this->db->table('item_general ig');

        $builder->select(
                    'ig.*, 
                    c.costo_unitario, 
                    c.envase, 
                    c.etiqueta, 
                    c.plastico, 
                    c.volumen, 
                    c.costo_mp_galon, 
                    c.costo_mp_kg, 
                    c.precio_venta,
                    i.cantidad');
        $builder->join('costos_item c', 'c.item_general_id = ig.id_item_general', 'left');
        $builder->join('inventario i', 'i.item_general_id = ig.id_item_general', 'left');
        $builder->where('ig.id_item_general', $id);
        
        $item = $builder->get()->getRowArray();

        if ($item) {
            $formulacion = $this->db->table('formulaciones')
                                ->where('item_general_id', $id)
                                ->get()->getRow();

            if ($formulacion) {
                $item['formulaciones'] = $this->db->table('item_general_formulaciones igf')
                    ->select('igf.item_general_id, ig.nombre, igf.cantidad')
                    ->join('item_general ig', 'ig.id_item_general = igf.item_general_id', 'left')
                    ->where('igf.formulaciones_id', $formulacion->id_formulaciones)
                    ->get()->getResultArray();
            } else {
                $item['formulaciones'] = [];
            }
        }

        return $item;
    }

    public function update_precio_manual($id, $data)
    {
        $allowed = array_intersect_key($data, array_flip(['precio_venta_manual', 'precio_manual_activo']));

        if (empty($allowed)) {
            throw new \Exception('No hay campos válidos para actualizar.');
        }

        $exists = $this->db->table('item_general')->where('id_item_general', $id)->countAllResults();
        if (!$exists) {
            throw new \Exception("Item con ID {$id} no encontrado.");
        }

        $this->db->table('item_general')->where('id_item_general', $id)->update($allowed);
        return true;
    }

    public function create_full_item($data)
    {
        $this->db->transStart();

        try {
            $tipoMapeado = 0;
            if ($data['tipo'] === 'MATERIA PRIMA') {
                $tipoMapeado = 1;
            } elseif ($data['tipo'] === 'INSUMO') {
                $tipoMapeado = 2;
            }

            $itemData = [
                'nombre'           => $data['nombre'],
                'codigo'           => substr($data['codigo'] ?? '', 0, 10),
                'tipo'             => $tipoMapeado,
                'categoria_id'     => $data['categoria_id'] ?? null,
                'viscosidad'       => $data['viscosidad'] ?? null,
                'p_g'              => $data['p_g'] ?? null,
                'color'            => $data['color'] ?? null,
                'brillo_60'        => $data['brillo_60'] ?? null,
                'secado'           => $data['secado'] ?? null,
                'cubrimiento'      => $data['cubrimiento'] ?? null,
                'molienda'         => $data['molienda'] ?? null,
                'ph'               => substr($data['ph'] ?? '', 0, 1),   
                'poder_tintoreo'   => $data['poder_tintoreo'] ?? null,
                'unidad_id'        => $data['unidad_id'] ?? null,
                'costo_produccion' => $data['costo_unitario'] ?? 0
            ];

            $this->db->table('item_general')->insert($itemData);
            $newItemId = $this->db->insertID();

            $this->db->table('costos_item')->insert([
                'item_general_id' => $newItemId,
                'costo_unitario'  => $data['costo_unitario'] ?? 0,
                'costo_mp_galon'  => $data['costo_mp_galon'] ?? 0,
                'costo_cunete'  => $data['costo_cunete'] ?? 0,
                'costo_tambor'  => $data['costo_tambor'] ?? 0,
                'periodo'         => date('Y-m'),
                'metodo_calculo'  => $data['metodo_calculo'] ?? 'Manual',
                'fecha_calculo'   => date('Y-m-d'),
                'costo_mp_kg'     => $data['costo_mp_kg'] ?? 0,
                'envase'          => $data['envase'] ?? 0,   
                'etiqueta'        => $data['etiqueta'] ?? 0,    
                'bandeja'         => $data['bandeja'] ?? 0,   
                'plastico'        => $data['plastico'] ?? 0,
                'precio_venta'    => $data['precio_venta'] ?? 0,
                'costo_mod'       => $data['costo_mod'] ?? 0,
                'volumen'         => $data['volumen'] ?? 1,
                'estado'          => 1
            ]);

            $this->db->table('inventario')->insert([
                'item_general_id'          => $newItemId,
                'bodegas_id'               => $data['bodega_id'] ?? 1,
                'cantidad'                 => $data['cantidad'] ?? 0,
                'fecha_update'             => date('Y-m-d'),
                'apartada'                 => 0,
                'estado'                   => 1,
                'movimiento_inventario_id' => null,
                'tipo'                     => 1 // 1 = Ingreso inicial
            ]);

            if (!empty($data['formulaciones'])) {
                $this->db->table('formulaciones')->insert([
                    'nombre'          => "PREPARACION " . $data['nombre'],
                    'descripcion'     => $data['descripcion_formula'] ?? null,
                    'estado'          => 1,
                    'defecto'         => 1,
                    'item_general_id' => $newItemId
                ]);
                $idFormulacion = $this->db->insertID();

                $batchDetalle = [];
                foreach ($data['formulaciones'] as $f) {
                    if (!empty($f['materia_prima_id'])) {
                        $batchDetalle[] = [
                            'formulaciones_id' => $idFormulacion,
                            'item_general_id'  => $f['materia_prima_id'],
                            'cantidad'         => $f['cantidad'],
                            'porcentaje'       => $f['porcentaje'] ?? 0
                        ];
                    }
                }
                if (!empty($batchDetalle)) {
                    $this->db->table('item_general_formulaciones')->insertBatch($batchDetalle);
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                $error = $this->db->error();
                throw new \Exception("Error SQL detallado: " . $error['message']);
            }

            return $newItemId;

        } catch (\Exception $e) {
            // 1. Capturamos el error de la base de datos antes de limpiar la transacción
            $dbError = $this->db->error(); 
            $this->db->transRollback();
            
            // 2. Si hay un mensaje de SQL, lo lanzamos. Si no, lanzamos el mensaje de la excepción.
            $errorMessage = !empty($dbError['message']) 
                ? "SQL Error: " . $dbError['message'] 
                : $e->getMessage();

            throw new \Exception($errorMessage); 
        }
    }

    public function update_full_item($idItem, $data)
    {
        $this->db->transStart();

        try {
            // 1. Validar que el item existe
            $existingItem = $this->db->table('item_general')->where('id_item_general', $idItem)->get()->getRow();
            if (!$existingItem) {
                throw new \Exception("El item no existe.");
            }

            // 2. Actualizar ITEM_GENERAL
            $itemData = [
                'nombre'           => $data['nombre'],
                'codigo'           => substr($data['codigo'] ?? '', 0, 10),
                'tipo'             => $data['tipo'],
                'categoria_id'     => $data['categoria_id'] ?? null,
                'viscosidad'       => $data['viscosidad'] ?? null,
                'p_g'              => $data['p_g'] ?? null,
                'color'            => $data['color'] ?? null,
                'brillo_60'        => $data['brillo_60'] ?? null,
                'secado'           => $data['secado'] ?? null,
                'cubrimiento'      => $data['cubrimiento'] ?? null,
                'molienda'         => $data['molienda'] ?? null,
                'ph'               => $data['ph'] ?? null,
                'poder_tintoreo'   => $data['poder_tintoreo'] ?? null,
                'unidad_id'        => $data['unidad_id'] ?? null,
            ];
            $this->db->table('item_general')->where('id_item_general', $idItem)->update($itemData);

            // 3. Actualizar COSTOS_ITEM
            $costosData = [
                'costo_unitario'  => $data['costo_unitario'] ?? 0,
                'envase'          => $data['envase'] ?? 0,
                'etiqueta'        => $data['etiqueta'] ?? 0,
                'plastico'        => $data['plastico'] ?? 0,
                'volumen'         => $data['volumen'] ?? 1,
                'fecha_calculo'   => date('Y-m-d'),
                'costo_cunete'    => $data['costo_cunete'] ?? 0, 
                'costo_tambor'    => $data['costo_tambor'] ?? 0,
            ];
            
            $existsCostos = $this->db->table('costos_item')->where('item_general_id', $idItem)->countAllResults();
            if ($existsCostos > 0) {
                $this->db->table('costos_item')->where('item_general_id', $idItem)->update($costosData);
            } else {
                $costosData['item_general_id'] = $idItem;
                $this->db->table('costos_item')->insert($costosData);
            }

            // 4. Actualizar INVENTARIO (CANTIDAD)
            if (isset($data['cantidad']) && isset($data['bodega_id'])) {
                $idItemInt   = (int)$idItem;
                $idBodegaInt = (int)$data['bodega_id'];
                $cantidadVal = (float)$data['cantidad'];

                // Intentamos la actualización
                $this->db->table('inventario')
                    ->where('item_general_id', $idItemInt)
                    ->where('bodegas_id', $idBodegaInt) // Columna exacta de tu imagen
                    ->update(['cantidad' => $cantidadVal]);

                // Si el UPDATE no afectó filas, es porque esa combinación Item-Bodega NO existe
                if ($this->db->affectedRows() === 0) {
                    // Verificamos si realmente no existe o si es que la cantidad era la misma
                    $check = $this->db->table('inventario')
                        ->where('item_general_id', $idItemInt)
                        ->where('bodegas_id', $idBodegaInt)
                        ->get()->getRow();

                    if (!$check) {
                        // Si no existe, lo insertamos
                        $this->db->table('inventario')->insert([
                            'item_general_id' => $idItemInt,
                            'bodegas_id'      => $idBodegaInt,
                            'cantidad'        => $cantidadVal,
                            'estado'          => 0,
                            'tipo'            => 1
                        ]);
                    }
                }
            }
        
            // 4. Actualizar FORMULACIONES
            $formRow = $this->db->table('formulaciones')->where('item_general_id', $idItem)->get()->getRow();
            
            if ($formRow) {
                $idFormulacion = $formRow->id_formulaciones;
                // Actualizar descripción si viene en el data
                if(isset($data['descripcion_formula'])) {
                    $this->db->table('formulaciones')->where('id_formulaciones', $idFormulacion)->update([
                        'descripcion' => $data['descripcion_formula']
                    ]);
                }
            } else {
                // Si no tiene, la creamos (defensa contra datos corruptos)
                $this->db->table('formulaciones')->insert([
                    'nombre'           => "Formulación - " . $data['nombre'],
                    'item_general_id' => $idItem,
                    'estado'           => 1,
                    'defecto'          => 1
                ]);
                $idFormulacion = $this->db->insertID();
            }

            // 5. Sincronizar DETALLE DE FORMULACIÓN (Item_general_formulaciones)
            // Solo procedemos si el frontend envió el array de formulaciones
            if (isset($data['formulaciones'])) {
                // Limpieza
                $this->db->table('item_general_formulaciones')->where('formulaciones_id', $idFormulacion)->delete();

                $batchDetalle = [];
                foreach ($data['formulaciones'] as $f) {
                    // Importante: materia_prima_id es el nombre que enviamos desde el procesado de React
                    if (!empty($f['materia_prima_id'])) {
                        $batchDetalle[] = [
                            'formulaciones_id' => $idFormulacion,
                            'item_general_id'  => $f['materia_prima_id'],
                            'cantidad'         => $f['cantidad'] ?? 0,
                            'porcentaje'       => $f['porcentaje'] ?? 0
                        ];
                    }
                }

                if (!empty($batchDetalle)) {
                    $this->db->table('item_general_formulaciones')->insertBatch($batchDetalle);
                }
            }

            $this->db->transComplete();
            return $this->db->transStatus();

        } catch (\Exception $e) {
            $this->db->transRollback();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Búsqueda fuzzy de item_general por nombre.
     * Maneja errores tipográficos combinando LIKE por token + SOUNDEX fonético.
     * Devuelve hasta $limit resultados ordenados por relevancia descendente.
     */
    /**
     * @param array<int> $tipos  Filtra por tipo: 1=Materia Prima, 2=Insumo, 0=Producto, 3=Otro.
     *                           Array vacío = sin filtro.
     */
    public function buscarFuzzy(string $query, int $limit = 10, array $tipos = []): array
    {
        $query = trim($query);
        if ($query === '') return [];

        $queryUpper = strtoupper($query);
        $tokens     = array_filter(array_unique(explode(' ', $queryUpper)));

        if (empty($tokens)) return [];

        $whereParts = [];
        $scoreParts = [];
        $params     = [];

        foreach ($tokens as $token) {
            if (strlen($token) < 2) continue;

            $whereParts[] = "UPPER(ig.nombre) LIKE ?";
            $scoreParts[] = "CASE WHEN UPPER(ig.nombre) LIKE ? THEN 3 ELSE 0 END";
            $params[]     = "%{$token}%";
            $params[]     = "%{$token}%";

            if (strlen($token) > 3) {
                $truncado     = substr($token, 0, -1);
                $whereParts[] = "UPPER(ig.nombre) LIKE ?";
                $scoreParts[] = "CASE WHEN UPPER(ig.nombre) LIKE ? THEN 1 ELSE 0 END";
                $params[]     = "%{$truncado}%";
                $params[]     = "%{$truncado}%";
            }

            $whereParts[] = "SOUNDEX(ig.nombre) LIKE CONCAT(SOUNDEX(?), '%')";
            $scoreParts[] = "CASE WHEN SOUNDEX(ig.nombre) LIKE CONCAT(SOUNDEX(?), '%') THEN 1 ELSE 0 END";
            $params[]     = $token;
            $params[]     = $token;
        }

        if (empty($whereParts)) return [];

        $whereClause = '(' . implode(' OR ', $whereParts) . ')';
        $scoreExpr   = '(' . implode(' + ', $scoreParts) . ')';

        // Filtro de tipos
        if (!empty($tipos)) {
            $placeholders = implode(',', array_fill(0, count($tipos), '?'));
            $whereClause .= " AND ig.tipo IN ({$placeholders})";
            array_push($params, ...$tipos);
        }

        $sql = "
            SELECT
                ig.id_item_general,
                ig.nombre,
                ig.codigo,
                ig.tipo,
                ci.costo_unitario,
                (SELECT COUNT(*) FROM item_proveedor ip
                    WHERE ip.item_general_id = ig.id_item_general AND ip.disponible = 1) AS total_proveedores,
                (SELECT MIN(ip2.precio_unitario) FROM item_proveedor ip2
                    WHERE ip2.item_general_id = ig.id_item_general AND ip2.disponible = 1) AS precio_min,
                (SELECT MAX(ip3.precio_unitario) FROM item_proveedor ip3
                    WHERE ip3.item_general_id = ig.id_item_general AND ip3.disponible = 1) AS precio_max,
                (SELECT GROUP_CONCAT(
                    CONCAT(COALESCE(p.nombre_empresa, p.nombre_encargado), '|', ip4.precio_unitario)
                    ORDER BY ip4.precio_unitario ASC SEPARATOR ';;;')
                 FROM item_proveedor ip4
                 JOIN proveedor p ON p.id_proveedor = ip4.proveedor_id
                 WHERE ip4.item_general_id = ig.id_item_general AND ip4.disponible = 1) AS proveedores_lista,
                {$scoreExpr} AS relevancia
            FROM item_general ig
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general
            WHERE {$whereClause}
            ORDER BY relevancia DESC, ig.nombre ASC
            LIMIT ?
        ";

        $params[] = $limit;

        return $this->db->query($sql, $params)->getResultArray();
    }
}

