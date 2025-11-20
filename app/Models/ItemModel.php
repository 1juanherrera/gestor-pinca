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
}

