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
        // withDeleted() desactiva el scope automático de soft-delete de CI4 (que usa $this->table +
        // $deletedField). Acá $this->table puede ser una tabla SIN deleted_at (p.ej. *_detalle), y el
        // scope automático generaba "Unknown column '<tabla>.deleted_at'" → 500. El filtro real lo
        // hace el $this->where de arriba solo cuando la tabla sí tiene la columna.
        return $this->withDeleted()->findAll();
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
        // Tabla sin deleted_at: withDeleted() evita que CI4 agregue el filtro automático (que
        // rompería con "Unknown column deleted_at" si el modelo tiene useSoftDeletes=true).
        return $this->withDeleted()->find($id);
    }

    /**
     * Devuelve la tabla "natural" del modelo (la declarada como `protected $table`
     * en la clase hija, antes de cualquier mutación por get_all/get/update_table).
     */
    private function naturalTable(): ?string
    {
        $defaults = (new \ReflectionClass($this))->getDefaultProperties();
        return $defaults['table'] ?? null;
    }

    /**
     * Devuelve los allowedFields "naturales" declarados por el modelo hijo
     * (antes de mutaciones), o null si no hay declaración.
     */
    private function naturalAllowedFields(): ?array
    {
        $defaults = (new \ReflectionClass($this))->getDefaultProperties();
        $af = $defaults['allowedFields'] ?? null;
        return is_array($af) && !empty($af) ? $af : null;
    }

    // CREAR
    //
    // Cambio 2026-05-25: ya no se auto-genera `allowedFields` desde las columnas
    // de la tabla (era un riesgo de mass assignment — el cliente podía setear
    // cualquier columna existente, incluyendo PKs o audit cols).
    //
    // Política:
    //   - Si la tabla destino coincide con la tabla natural del modelo, exigimos
    //     que el modelo declare `$allowedFields` explícitamente.
    //   - Si la tabla destino NO coincide (cross-table insert, ej. RemisionesModel
    //     insertando en `facturas_detalle`), se mantiene comportamiento legacy con
    //     un warning visible en logs para que se migre a un modelo dedicado.
    public function create_table($data, $table)
    {
        $naturalTable    = $this->naturalTable();
        $naturalAllowed  = $this->naturalAllowedFields();
        $this->table     = $table;

        $mismaTabla = $naturalTable && $naturalTable === $table;

        if ($mismaTabla) {
            if (empty($naturalAllowed)) {
                throw new \RuntimeException(
                    sprintf(
                        'Model %s must declare $allowedFields explicitly (table=%s). ' .
                        'Auto-fill from getFieldNames() was removed to prevent mass assignment.',
                        static::class,
                        $table
                    )
                );
            }
            $this->allowedFields = $naturalAllowed;
        } else {
            log_message(
                'warning',
                sprintf(
                    '[BaseModel::create_table] cross-table insert: model=%s, modelTable=%s, targetTable=%s. ' .
                    'Falling back to getFieldNames(). Considerá crear un modelo dedicado para %s.',
                    static::class,
                    $naturalTable ?: '(none)',
                    $table,
                    $table
                )
            );
            $this->allowedFields = $this->db->getFieldNames($table);
        }

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
