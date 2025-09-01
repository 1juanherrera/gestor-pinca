<?php
namespace App\Models;

use PhpParser\Node\Expr\FuncCall;

class BodegasModel extends BaseModel
{
    protected $table      = 'bodegas';
    protected $primaryKey = 'id_bodegas';
    protected $allowedFields = ['nombre', 'direccion', 'telefono'];

    public function __construct()
    {
        parent::__construct();
    }

    public function get_bodegas_all(){
        return  $this->get_all($this->table);
    }
    
}
