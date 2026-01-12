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
        'volumen',
        'cantidad',
        'unidad_id',
        'costo_produccion',
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
                    ci.costo_unitario
            FROM item_general ig
            LEFT JOIN categoria c ON ig.categoria_id = c.id_categoria
            LEFT JOIN costos_item ci ON ci.item_general_id = ig.id_item_general';

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

    public function create_full_item($data)
    {
        $this->db->transStart();

        try {
            $tipoMapeado = is_numeric($data['tipo']) ? (int)$data['tipo'] : 
                        match($data['tipo']) { 'PRODUCTO' => 0, 'MATERIA PRIMA' => 1, 'INSUMO' => 2, default => 0 };

            $itemData = [
                'nombre'           => $data['nombre'],
                'codigo'           => substr($data['codigo'] ?? '', 0, 6), // Límite varchar(6)
                'tipo'             => $tipoMapeado,
                'categoria_id'     => $data['categoria_id'] ?? null,
                'viscosidad'       => $data['viscosidad'] ?? null,
                'p_g'              => $data['p_g'] ?? null,
                'color'            => substr($data['color'] ?? '', 0, 3), // Límite varchar(3)
                'brillo_60'        => $data['brillo_60'] ?? null,
                'secado'           => $data['secado'] ?? null,
                'cubrimiento'      => $data['cubrimiento'] ?? null,
                'molienda'         => $data['molienda'] ?? null,
                'ph'               => substr($data['ph'] ?? '', 0, 1),    // Límite varchar(1)
                'poder_tintoreo'   => $data['poder_tintoreo'] ?? null,
                'volumen'          => $data['volumen'] ?? null,
                'cantidad'         => $data['cantidad'] ?? 0,
                'unidad_id'        => $data['unidad_id'] ?? null,
                'costo_produccion' => $data['costo_unitario'] ?? 0
            ];

            $this->db->table('item_general')->insert($itemData);
            $newItemId = $this->db->insertID();

            // 3. INSERT en costos_item (Incluyendo envases, etiquetas, etc.)
            $this->db->table('costos_item')->insert([
                'item_general_id' => $newItemId,
                'costo_unitario'  => $data['costo_unitario'] ?? 0,
                'costo_mp_galon'  => $data['costo_mp_galon'] ?? 0,
                'periodo'         => date('Y-m'),
                'metodo_calculo'  => $data['metodo_calculo'] ?? 'Manual',
                'fecha_calculo'   => date('Y-m-d'),
                'costo_mp_kg'     => $data['costo_mp_kg'] ?? 0,
                'envase'          => $data['envase'] ?? 0,      // <--- Campo nuevo
                'etiqueta'        => $data['etiqueta'] ?? 0,    // <--- Campo nuevo
                'bandeja'         => $data['bandeja'] ?? 0,     // <--- Campo nuevo
                'plastico'        => $data['plastico'] ?? 0,    // <--- Campo nuevo
                'costo_total'     => $data['costo_total'] ?? ($data['costo_unitario'] ?? 0),
                'volumen'         => $data['volumen_numero'] ?? 1,
                'precio_venta'    => $data['precio_venta'] ?? 0,
                'cantidad_total'  => $data['cantidad'] ?? 0,
                'costo_mod'       => $data['costo_mod'] ?? 0,
                'estado'          => 1
            ]);

            // 4. INSERT en inventario (Campos técnicos de stock)
            $this->db->table('inventario')->insert([
                'item_general_id'          => $newItemId,
                'bodegas_id'               => $data['bodega_id'] ?? 1,
                'cantidad'                 => $data['cantidad'] ?? 0,
                'fecha_update'             => '', // Tu SQL lo define como varchar(0)
                'apartada'                 => 0,
                'estado'                   => 1,
                'movimiento_inventario_id' => null,
                'tipo'                     => 1 // 1 = Ingreso inicial
            ]);

            // 5. INSERT en formulaciones e item_general_formulaciones
            if (!empty($data['formulaciones'])) {
                $this->db->table('formulaciones')->insert([
                    'nombre'          => "Formulación - " . $data['nombre'],
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
            $this->db->transRollback();
            return ['error' => $e->getMessage()];
        }
    }
}

