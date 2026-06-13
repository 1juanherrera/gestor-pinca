<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Log de reemplazos de materia prima en fórmulas (buscar y reemplazar), con snapshot del BOM
 * previo para poder DESHACER. Idempotente.
 */
class CreateItemReemplazoLog extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('item_reemplazo_log')) {
            return;
        }

        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'auto_increment' => true],
            'from_item_id'       => ['type' => 'INT', 'null' => false],
            'to_item_id'         => ['type' => 'INT', 'null' => false],
            'from_nombre'        => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'to_nombre'          => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'formulas_afectadas' => ['type' => 'INT', 'null' => false, 'default' => 0],
            'origen_eliminada'   => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'snapshot'           => ['type' => 'LONGTEXT', 'null' => true], // JSON: BOM previo de A y B en las fórmulas afectadas
            'usuario'            => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'revertido'          => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'created_at'         => ['type' => 'DATETIME', 'null' => false],
            'revertido_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at', false, false, 'idx_irl_fecha');
        $this->forge->createTable('item_reemplazo_log');
    }

    public function down()
    {
        $this->forge->dropTable('item_reemplazo_log', true);
    }
}
