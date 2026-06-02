<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Auditoría de fusiones de materias primas (deduplicación).
 *
 * Registra cada par keep←remove fusionado, con los nombres/costos antes y después,
 * el resumen de filas reapuntadas (`afectados`) y el detalle de IDs movidos
 * (`detalle_movimientos`) que permite un UNDO parcial (revertir proveedores y capas).
 * La reversa TOTAL se garantiza con el backup previo a la fusión en lote.
 */
class CreateItemSyncAuditoria extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'cluster_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'comment'  => 'Cluster que originó la fusión (null si merge manual suelto).',
            ],
            'keep_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'remove_id' => [
                'type' => 'INT',
                'null' => false,
            ],
            'nombre_keep_antes' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nombre_keep_despues' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nombre_remove_original' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'costo_keep_antes' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
                'null'       => true,
            ],
            'costo_keep_despues' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
                'null'       => true,
            ],
            'afectados' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => 'Conteo de filas reapuntadas por tabla.',
            ],
            'detalle_movimientos' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => 'IDs movidos (item_proveedor, inventario_capas) para UNDO parcial.',
            ],
            'responsable' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'revertido' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'revertido_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'revertido_por' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('keep_id');
        $this->forge->addKey('cluster_id');
        $this->forge->addKey('revertido');
        $this->forge->createTable('item_sync_auditoria', true);
    }

    public function down()
    {
        $this->forge->dropTable('item_sync_auditoria', true);
    }
}
