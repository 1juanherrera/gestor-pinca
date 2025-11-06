<?php
namespace App\Models;

use App\Libraries\Formatter;
use Exception;

class ProveedorModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id, $table)
    {
        $this->table = $table;
        return $this->find($id);
    }

    public function create_proveedor($data, $table)
    {
        $this->table = $table;      
        return $this->insert($data);
    }

    public function update_proveedor($id, $data, $table)
    {
        $this->table = $table;      
        return $this->update($id, $data);
    }

    public function delete_proveedor($id, $table)
    {
        $this->table = $table;      
        return $this->delete($id);
    }
}