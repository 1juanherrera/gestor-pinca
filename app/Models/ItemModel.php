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

    public function create_complete_item(array $data)
    {
        $db = $this->db; 
        $db->transBegin();
        
        try {
            $itemGeneralData = array_intersect_key($data, array_flip($this->allowedFields));
            
            $itemGeneralData['unidad_id'] = $itemGeneralData['unidad_id'] ?? null;
            $itemGeneralData['cantidad'] = $itemGeneralData['cantidad'] ?? 0;
            $itemGeneralData['costo_produccion'] = $itemGeneralData['costo_produccion'] ?? null;
            
            if (!$this->insert($itemGeneralData)) {
                $errorDetail = $this->errors() ? json_encode($this->errors()) : "Error de base de datos desconocido.";
                throw new \Exception("Error al insertar Item General: " . $errorDetail);
            }

            $idItem = $this->insertID();

            // Insertar en inventario
            $db->table('inventario')->insert([
                'item_general_id' => $idItem,
                'cantidad'        => $data['cantidad'] ?? 0,
                'bodegas_id'      => $data['bodegas_id'] ?? 1
            ]);

            // Insertar en costos_item 
            $db->table('costos_item')->insert([
                'item_general_id' => $idItem,
                'costo_unitario'  => $data['costo_unitario'] ?? 0
            ]);

            // Insertar formulaciones 
            if (!empty($data['formulaciones']) && is_array($data['formulaciones'])) {
                $formulasBatch = [];
                foreach ($data['formulaciones'] as $formula) {

                    $formulasBatch[] = [
                        'item_general_id'    => $idItem,
                        'materia_prima_id'   => $formula['materia_prima_id'],
                        'cantidad'           => $formula['cantidad'],
                        'unidad'             => $formula['unidad']
                    ];
                }
                if (!empty($formulasBatch)) {
                    $db->table('formulaciones')->insertBatch($formulasBatch);
                }
            }

            $db->transCommit();

            return $idItem;

        } catch (\Exception $e) {
            $db->transRollback();
            
            return ['error' => 'No se pudo crear el item completo.']; 
        }
    }

public function create_full_item($data)
    {
        $this->db->transStart();

        try {
            // ---------------------------------------------------------
            // 1. Insertar ITEM_GENERAL (Producto Padre)
            // ---------------------------------------------------------
            $itemData = [
                'bodega_id'      => $data['bodega_id'] ?? 1,
                'categoria_id'   => $data['categoria_id'] ?? 1,
                'nombre'         => $data['nombre'],
                'codigo'         => $data['codigo'],
                // Mapeo de tipos: 0=Producto, 1=Materia Prima, 2=Insumo
                'tipo'           => match($data['tipo']) { 'PRODUCTO' => 0, 'MATERIA PRIMA' => 1, 'INSUMO' => 2, default => 0 },
                'cantidad'       => $data['cantidad'] ?? 0,
                'costo_unitario' => $data['costo_unitario'] ?? 0,
                'estado'         => 1, // Activo
                
                // Propiedades
                'viscosidad'     => $data['viscosidad'] ?? null,
                'p_g'            => $data['p_g'] ?? null,
                'color'          => $data['color'] ?? null,
                'brillo_60'      => $data['brillo_60'] ?? null,
                'secado'         => $data['secado'] ?? null,
                'cubrimiento'    => $data['cubrimiento'] ?? null,
                'molienda'       => $data['molienda'] ?? null,
                'ph'             => $data['ph'] ?? null,
                'poder_tintoreo' => $data['poder_tintoreo'] ?? null,
                'volumen'        => $data['volumen'] ?? null,
            ];

            $this->insert($itemData);
            $newItemId = $this->insertID();

            // ---------------------------------------------------------
            // 2. Inicializar COSTOS_ITEM
            // ---------------------------------------------------------
            $this->db->table('costos_item')->insert([
                'item_general_id' => $newItemId,
                'costo_unitario'  => $data['costo_unitario'] ?? 0,
                'cantidad_total'  => $data['cantidad'] ?? 0,
                'volumen'         => $data['volumen'] ?? 1,
                'fecha_calculo'   => date('Y-m-d H:i:s')
            ]);

            // ---------------------------------------------------------
            // 3. Inicializar INVENTARIO (Stock)
            // ---------------------------------------------------------
            $this->db->table('inventario')->insert([
                'item_general_id' => $newItemId,
                'bodega_id'       => $data['bodega_id'] ?? 1,
                'cantidad'        => $data['cantidad'] ?? 0,
                'fecha_actualizacion' => date('Y-m-d H:i:s')
            ]);

            // ---------------------------------------------------------
            // 4. Crear la FORMULACIÓN (Cabecera y Detalle)
            // ---------------------------------------------------------
            // Solo si es PRODUCTO (0) o INSUMO (2) y trae formulaciones
            if (in_array($itemData['tipo'], [0, 2]) && !empty($data['formulaciones'])) {
                
                // A. Insertar Cabecera en 'formulaciones'
                $formuHeaderData = [
                    'item_general_id' => $newItemId, // El ID del producto padre
                    'estado'          => 1,
                    // Si tienes campos de fecha en esta tabla, agrégalos aquí:
                    // 'fecha_creacion' => date('Y-m-d H:i:s') 
                ];
                
                $this->db->table('formulaciones')->insert($formuHeaderData);
                $formulacionId = $this->db->insertID();

                // B. Insertar Detalles en 'item_general_formulaciones'
                $ingredientesBatch = [];
                foreach ($data['formulaciones'] as $form) {
                    if (!empty($form['materia_prima_id'])) {
                        $ingredientesBatch[] = [
                            'formulaciones_id' => $formulacionId,          // El ID de la cabecera creada arriba
                            'item_general_id'  => $form['materia_prima_id'], // El ingrediente (Materia Prima)
                            'cantidad'         => $form['cantidad'],
                            'porcentaje'       => 0 // Opcional: calcularlo si lo necesitas
                        ];
                    }
                }

                if (!empty($ingredientesBatch)) {
                    $this->db->table('item_general_formulaciones')->insertBatch($ingredientesBatch);
                }
            }

        $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                // Obtenemos el error de base de datos si la transacción falló
                $error = $this->db->error();
                return ['error' => 'Error de transacción: ' . $error['message']];
            }

            return $newItemId;

        } catch (\Exception $e) {
            // AQUÍ ESTÁ EL CAMBIO CLAVE:
            // Devolvemos el mensaje exacto de la excepción (FK fallida, columna faltante, etc.)
            return ['error' => $e->getMessage()]; 
        }
    }
}

