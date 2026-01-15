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
                    c.precio_venta');
        $builder->join('costos_item c', 'c.item_general_id = ig.id_item_general', 'left');
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
                'codigo'           => substr($data['codigo'] ?? '', 0, 6),
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
            $this->db->transRollback();
            throw new \Exception($e->getMessage()); 
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
                'codigo'           => substr($data['codigo'] ?? '', 0, 6),
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
                // Agregamos los campos que calculamos en el create para mantener coherencia
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
}

