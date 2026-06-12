<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Crea la tabla `costos_indirectos_item` (asignación de un costo indirecto a un item_general).
 *
 * El código YA la referencia (CostosIndirectosModel::asignarItem/costosItem, SincronizacionModel::merge)
 * pero nunca se había creado el esquema → los endpoints costos_indirectos/item/:id daban 500.
 * Tabla idempotente: solo se crea si no existe.
 */
class CreateCostosIndirectosItem extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('costos_indirectos_item')) {
            return;
        }

        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'auto_increment' => true],
            'item_general_id'      => ['type' => 'INT', 'null' => false],
            'costos_indirectos_id' => ['type' => 'INT', 'null' => false],
            'valor_asignado'       => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['item_general_id', 'costos_indirectos_id'], 'uq_cii_item_costo');
        $this->forge->addKey('costos_indirectos_id', false, false, 'idx_cii_costo');
        $this->forge->createTable('costos_indirectos_item');
    }

    public function down()
    {
        $this->forge->dropTable('costos_indirectos_item', true);
    }
}
