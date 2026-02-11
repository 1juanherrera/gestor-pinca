<?php
namespace App\Models;

class CostosItemModel extends BaseModel
{

    protected $table = 'costos_item';
    protected $primaryKey = 'id_costos_item';
    protected $allowedFields = [
        "envase",
        "etiqueta",
        "bandeja",
        "plastico",
        "costo_mod"
    ];

    public function __construct(){

        parent::__construct();
    }

    public function update_costos_item($id, $data, $table)
    {
        $this->table = $table;      
        return $this->update($id, $data);
    }
}