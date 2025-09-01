<?php
namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
    }

    public function get_all($table, $where = null)
    {
        $this->table = $table;
        if ($where) {
            $this->where($where);
        }
        return $this->findAll();
    }

    public function get($id, $table)
    {
        $this->table = $table;
        $this->primaryKey = 'id_' . $table;
        return $this->find($id);
    }

    public function create_table($data, $table)
    {
        $this->table = $table;
        $this->primaryKey = 'id_' . $table;

        if ($this->insert($data) === false) {
            return $this->errors();
        }

        return $this->getInsertID();
    }

    public function update_table($id, $data, $table)
    {
        $this->table = $table;
        $this->primaryKey = 'id_' . $table;
        return $this->update($id, $data);
    }

    public function delete_table($id, $table)
    {
        $this->table = $table;
        $this->primaryKey = 'id_' . $table;
        return $this->delete($id);
    }
}
