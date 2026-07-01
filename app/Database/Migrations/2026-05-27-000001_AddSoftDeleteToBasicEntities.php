<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Soft-delete para las entidades básicas del catálogo:
 *   - categoria
 *   - unidad
 *   - bodegas
 *   - instalaciones
 *
 * Hasta ahora se borraban con DELETE físico. Esto puede romper FKs históricas
 * (item_general.categoria_id, item_general.unidad_id, inventario_capas.bodegas_id,
 * bodegas.instalaciones_id…) y se pierde el historial. Con soft-delete los
 * registros se ocultan de los listados normales (BaseModel filtra automáticamente
 * cuando el modelo declara `useSoftDeletes = true`) pero quedan accesibles para
 * trazabilidad y JOINs con datos antiguos.
 *
 * Se complementa con `useSoftDeletes = true` en los 4 modelos correspondientes.
 */
class AddSoftDeleteToBasicEntities extends Migration
{
    /** @var string[] */
    private array $tablas = ['categoria', 'unidad', 'bodegas', 'instalaciones'];

    public function up()
    {
        foreach ($this->tablas as $tabla) {
            if (!$this->db->tableExists($tabla)) continue;

            // Columna deleted_at
            if (!$this->db->fieldExists('deleted_at', $tabla)) {
                $this->forge->addColumn($tabla, [
                    'deleted_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                        'default' => null,
                    ],
                ]);
            }

            // Índice idx_deleted_at (solo si no existe)
            $indexName = 'idx_deleted_at';
            $existe = $this->db->query(
                "SHOW INDEX FROM {$tabla} WHERE Key_name = ?",
                [$indexName]
            )->getRow();
            if (!$existe) {
                $this->db->query("CREATE INDEX {$indexName} ON {$tabla} (deleted_at)");
            }
        }
    }

    public function down()
    {
        foreach ($this->tablas as $tabla) {
            if (!$this->db->tableExists($tabla)) continue;

            $indexName = 'idx_deleted_at';
            // MySQL no soporta DROP INDEX IF EXISTS; chequeo manual vía INFORMATION_SCHEMA
            $exists = (int) $this->db->query(
                "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
                [$tabla, $indexName]
            )->getRow()->c > 0;
            if ($exists) {
                $this->db->query("ALTER TABLE {$tabla} DROP INDEX {$indexName}");
            }

            if ($this->db->fieldExists('deleted_at', $tabla)) {
                $this->forge->dropColumn($tabla, 'deleted_at');
            }
        }
    }
}
