<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRequisicionesCompraTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id_requisicion' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'preparacion_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'item_general_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'item_proveedor_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
            ],
            'proveedor_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
            ],
            'cantidad_necesaria' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4',
                'null'       => false,
            ],
            'cantidad_disponible' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4',
                'null'       => false,
                'default'    => 0,
            ],
            'cantidad_solicitada' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,4',
                'null'       => false,
            ],
            'precio_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '14,2',
                'null'       => true,
                'default'    => null,
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['PENDIENTE', 'APROBADA', 'CONVERTIDA', 'CANCELADA'],
                'default'    => 'PENDIENTE',
            ],
            'observaciones' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'orden_compra_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'default'  => null,
            ],
            'fecha_creacion' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => '0000-00-00 00:00:00',
            ],
        ]);

        $this->forge->addKey('id_requisicion', true);
        $this->forge->addKey('preparacion_id');
        $this->forge->addKey('estado');

        $this->forge->createTable('requisiciones_compra');
    }

    public function down(): void
    {
        $this->forge->dropTable('requisiciones_compra', true);
    }
}
