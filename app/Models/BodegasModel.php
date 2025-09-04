<?php
namespace App\Models;

class BodegasModel extends BaseModel{
    

    public function __construct(){

        parent::__construct();
    }

    public function migrar_inventario()
    {
        // Inicializa la tabla antes de usar get_all
        $this->setTable('item_general');
        $datos = $this->findAll();
        if (!empty($datos)) {
            foreach ($datos as $item) {
                $data = [
                    'cantidad'        => random_int(1, 100),
                    'estado'          => 1,
                    'bodegas_id'      => 1,
                    'item_general_id' => $item['id_item_general']
                ];
                $this->qbInsert('inventario', $data);
            }
        }
        return true;
    }

}
