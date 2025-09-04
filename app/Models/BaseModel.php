<?php
namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected $db;
    protected $table;

    public function __construct()
    {
        parent::__construct();
    }

    // OBTENER TODOS
    public function get_all($table, $where = null)
    {
        $this->table = $table;
        if ($where) {
            $this->where($where);
        }
        return $this->findAll();
    }

    // OBTENER UNO
    public function get($id, $table)
    {
        $this->table = $table;
        $this->primaryKey = 'id_' . $table;
        return $this->find($id);
    }

    // CREAR
    public function create_table($data, $table)
    {
        $this->table = $table;
        // Resetear allowedFields cada vez
        $this->allowedFields = $this->db->getFieldNames($table); //array_keys($firstRow);
        // Detectar si es batch o normal
        $isBatch = isset($data[0]) && is_array($data[0]);

        if ($isBatch) {
            $result = $this->insertBatch($data);
        } else {
            $result = $this->insert($data);
        }

        if ($result === false) {
            return $this->errors();
        }

        return $result;
    }

    public function update_table($id, $data, $table)
    {   
        $this->table = $table;
        $this->primaryKey = 'id_' . $table;

        // 🔥 allowedFields dinámicos desde $data
        if (empty($this->allowedFields)) {
            $this->allowedFields = array_keys($data);
        }
        $updated = $this->update($id, $data);

        if ($updated === false) {
            return $this->errors();
        }

        return $updated;
    }

    public function delete_table($id, $table)
    {
        $this->table = $table;
        $this->primaryKey = 'id_' . $table;

        $this->delete($id);

        if ($this->db->affectedRows() === 0) {
            return false;
        }

        return true;
    }
}
