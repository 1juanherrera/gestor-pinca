<?php
namespace App\Models;

use CodeIgniter\Model;

class BodegasModel extends Model
{
    public function __construct(){
        parent::__construct();
    }

    public function get_all($table, $where = null){ 
        $this->table = $table;
        if ($where) { $this->where($where);  }           
        return $this->findAll();
    }
}