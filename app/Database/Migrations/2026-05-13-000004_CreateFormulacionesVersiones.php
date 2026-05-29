<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Versionado inmutable de formulaciones.
 *
 * Cada vez que se crea o edita una formulación, se guarda un snapshot
 * inmutable en `formulaciones_versiones`. Las preparaciones de producción
 * referencian la versión exacta usada vía `formulacion_version_id`, así
 * podés saber con qué receta se hizo cada lote de producto, aunque la
 * fórmula original cambie después.
 *
 * Backfill: para cada formulación existente se crea la versión 1 con el
 * snapshot actual de ingredientes.
 */
class CreateFormulacionesVersiones extends Migration
{
    public function up()
    {
        // ─── 1. Tabla formulaciones_versiones ─────────────────────────
        if (!$this->db->tableExists('formulaciones_versiones')) {
            $this->forge->addField([
                'id'              => ['type' => 'INT', 'auto_increment' => true],
                'formulacion_id'  => ['type' => 'INT', 'null' => false],
                'version_num'     => ['type' => 'INT', 'null' => false],
                'nombre'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'descripcion'     => ['type' => 'TEXT', 'null' => true],
                'ingredientes'    => ['type' => 'JSON', 'null' => false],
                'notas'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_by'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'created_at'      => ['type' => 'DATETIME', 'null' => false],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['formulacion_id', 'version_num'], false, true, 'uq_formulacion_version');
            $this->forge->addKey('formulacion_id', false, false, 'idx_fv_formulacion');
            $this->forge->addForeignKey('formulacion_id', 'formulaciones', 'id_formulaciones', 'CASCADE', 'CASCADE');
            $this->forge->createTable('formulaciones_versiones');
        }

        // ─── 2. Columna version_actual en formulaciones ───────────────
        if (!$this->db->fieldExists('version_actual', 'formulaciones')) {
            $this->forge->addColumn('formulaciones', [
                'version_actual' => [
                    'type'       => 'INT',
                    'null'       => false,
                    'default'    => 1,
                    'after'      => 'defecto',
                ],
            ]);
        }

        // ─── 3. Columna formulacion_version_id en preparaciones ───────
        if (!$this->db->fieldExists('formulacion_version_id', 'preparaciones')) {
            $this->forge->addColumn('preparaciones', [
                'formulacion_version_id' => [
                    'type'       => 'INT',
                    'null'       => true,
                    'after'      => 'item_general_id',
                ],
            ]);
            $this->indiceSiNoExiste('preparaciones', 'idx_prep_form_ver', 'formulacion_version_id');
        }

        // ─── 4. Backfill: versión 1 para formulaciones existentes ─────
        $formulaciones = $this->db->query("
            SELECT id_formulaciones, nombre, descripcion
            FROM formulaciones
            WHERE NOT EXISTS (
                SELECT 1 FROM formulaciones_versiones fv
                WHERE fv.formulacion_id = formulaciones.id_formulaciones
            )
        ")->getResultArray();

        $now = date('Y-m-d H:i:s');
        foreach ($formulaciones as $form) {
            $ingredientes = $this->db->query("
                SELECT
                    igf.item_general_id,
                    igf.cantidad,
                    igf.porcentaje,
                    ig.nombre AS item_nombre,
                    ig.codigo AS item_codigo
                FROM item_general_formulaciones igf
                LEFT JOIN item_general ig ON ig.id_item_general = igf.item_general_id
                WHERE igf.formulaciones_id = ?
            ", [$form['id_formulaciones']])->getResultArray();

            $this->db->table('formulaciones_versiones')->insert([
                'formulacion_id' => $form['id_formulaciones'],
                'version_num'    => 1,
                'nombre'         => $form['nombre'],
                'descripcion'    => $form['descripcion'],
                'ingredientes'   => json_encode($ingredientes, JSON_UNESCAPED_UNICODE),
                'notas'          => 'Versión inicial (backfill automático)',
                'created_by'     => 'sistema',
                'created_at'     => $now,
            ]);
        }

        // Asegurar version_actual = 1 para formulaciones backfilleadas
        $this->db->query("UPDATE formulaciones SET version_actual = 1 WHERE version_actual IS NULL OR version_actual = 0");
    }

    public function down()
    {
        if ($this->db->fieldExists('formulacion_version_id', 'preparaciones')) {
            // MySQL no soporta DROP INDEX IF EXISTS; chequeo manual vía INFORMATION_SCHEMA.
            $existe = (int) $this->db->query(
                "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
                ['preparaciones', 'idx_prep_form_ver']
            )->getRow()->c > 0;
            if ($existe) {
                $this->db->query("ALTER TABLE preparaciones DROP INDEX idx_prep_form_ver");
            }
            $this->forge->dropColumn('preparaciones', 'formulacion_version_id');
        }
        if ($this->db->fieldExists('version_actual', 'formulaciones')) {
            $this->forge->dropColumn('formulaciones', 'version_actual');
        }
        $this->forge->dropTable('formulaciones_versiones', true);
    }

    private function indiceSiNoExiste(string $tabla, string $nombre, string $columnas): void
    {
        $existe = $this->db->query("SHOW INDEX FROM {$tabla} WHERE Key_name = ?", [$nombre])->getRow();
        if (!$existe) {
            $this->db->query("CREATE INDEX {$nombre} ON {$tabla} ({$columnas})");
        }
    }
}
