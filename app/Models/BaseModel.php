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

    /**
     * Tablas cuyo primary key NO sigue la convención `id_<tabla>`.
     */
    private const PK_OVERRIDE = [
        'ordenes_compra' => 'id_orden',
    ];

    private function pkOf(string $table): string
    {
        return self::PK_OVERRIDE[$table] ?? ('id_' . $table);
    }

    /**
     * Detecta si la tabla soporta soft-deletes (tiene columna `deleted_at`).
     */
    private function tieneSoftDelete(string $table): bool
    {
        return $this->db->fieldExists('deleted_at', $table);
    }

    // OBTENER TODOS — filtra automáticamente registros soft-deleted
    public function get_all($table, $where = null)
    {
        $this->table = $table;
        if ($where) {
            $this->where($where);
        }
        if ($this->tieneSoftDelete($table)) {
            $this->where("{$table}.deleted_at IS NULL");
        }
        return $this->findAll();
    }

    // OBTENER UNO — también filtra soft-deleted (find devuelve null si está borrado)
    public function get($id, $table)
    {
        $this->table = $table;
        $this->primaryKey = $this->pkOf($table);

        if ($this->tieneSoftDelete($table)) {
            // Builder directo para combinar where previo + PK lookup
            $row = $this->db->table($table)
                ->where($this->primaryKey, $id)
                ->where('deleted_at', null)
                ->get()->getRowArray();
            return $row ?: null;
        }
        return $this->find($id);
    }

    // CREAR
    public function create_table($data, $table)
    {
        $this->table = $table;
        $this->allowedFields = $this->db->getFieldNames($table);
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
        $this->primaryKey = $this->pkOf($table);

        // Bloquear updates sobre registros soft-deleted: si alguien
        // saltó el get() previo y llama directo, no debe poder editarlo.
        if ($this->tieneSoftDelete($table)) {
            $archivado = $this->db->table($table)
                ->where($this->primaryKey, $id)
                ->where('deleted_at IS NOT NULL')
                ->countAllResults();
            if ($archivado > 0) {
                return ['archivado' => "El registro #{$id} de {$table} está archivado y no puede editarse. Restauralo primero."];
            }
        }

        if (empty($this->allowedFields)) {
            $this->allowedFields = array_keys($data);
        }
        $updated = $this->update($id, $data);

        if ($updated === false) {
            return $this->errors();
        }

        return $updated;
    }

    /**
     * DELETE inteligente:
     *   - Si la tabla tiene `deleted_at` → SOFT delete (UPDATE)
     *   - Si no → DELETE físico (comportamiento original)
     */
    public function delete_table($id, $table)
    {
        $this->table = $table;
        $this->primaryKey = $this->pkOf($table);

        if ($this->tieneSoftDelete($table)) {
            // Soft delete — marcar como borrado, conservar referencias FK
            $this->db->table($table)
                ->where($this->pkOf($table), $id)
                ->where('deleted_at', null)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            return $this->db->affectedRows() > 0;
        }

        $this->delete($id);
        return $this->db->affectedRows() > 0;
    }

    /**
     * Restaura un registro soft-deleted.
     * Solo aplicable si la tabla tiene `deleted_at`.
     */
    public function restore_table($id, $table): bool
    {
        if (!$this->tieneSoftDelete($table)) return false;

        $this->db->table($table)
            ->where($this->pkOf($table), $id)
            ->where('deleted_at IS NOT NULL')
            ->update(['deleted_at' => null]);

        return $this->db->affectedRows() > 0;
    }
}
