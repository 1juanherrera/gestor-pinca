<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProduccionInsumosDetalle extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('produccion_insumos_detalle')) {
            return;
        }

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'auto_increment' => true],
            'preparacion_id' => ['type' => 'INT', 'null' => false],
            'item_general_id'=> ['type' => 'INT', 'null' => false],
            'proveedor_id'   => ['type' => 'INT', 'null' => true],
            'bodega_id'      => ['type' => 'INT', 'null' => true],
            'cantidad'       => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
            'costo_unitario' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
            'subtotal'       => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
            'created_at'     => ['type' => 'DATETIME', 'null' => false],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('preparacion_id', false, false, 'idx_pid_insumos');
        $this->forge->addKey('item_general_id', false, false, 'idx_item_insumos');
        $this->forge->addKey('proveedor_id', false, false, 'idx_prov_insumos');

        $this->forge->addForeignKey('preparacion_id', 'preparaciones', 'id_preparaciones', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_general_id', 'item_general', 'id_item_general');

        $this->forge->createTable('produccion_insumos_detalle');
    }

    public function down()
    {
        $this->forge->dropTable('produccion_insumos_detalle', true);
    }
}
