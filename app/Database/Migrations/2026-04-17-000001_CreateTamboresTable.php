<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTamboresTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id_tambor' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'numero_tambor' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'item_general_id' => [
                'type'     => 'INT',
                'null'     => false,
            ],
            'bodegas_id' => [
                'type'     => 'INT',
                'null'     => false,
            ],
            'cantidad_inicial' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
            ],
            'cantidad_actual' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
            ],
            'estado' => [
                'type'       => 'TINYINT',
                'default'    => 0,
                'comment'    => '0=cerrado 1=abierto 2=vacío',
            ],
            'fecha_ingreso' => [
                'type'    => 'DATE',
                'null'    => true,
            ],
        ]);

        $this->forge->addKey('id_tambor', true);
        $this->forge->addForeignKey('item_general_id', 'item_general', 'id_item_general', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('bodegas_id', 'bodegas', 'id_bodegas', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('tambores');

        $this->forge->addField([
            'id_tambor_movimiento' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'tambor_id' => [
                'type'     => 'INT',
                'null'     => false,
            ],
            'tipo' => [
                'type'    => 'TINYINT',
                'null'    => true,
                'comment' => '1=entrada 2=salida',
            ],
            'cantidad' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'referencia_tipo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'referencia_id' => [
                'type'     => 'INT',
                'null'     => true,
            ],
            'fecha' => [
                'type'     => 'DATE',
                'null'     => true,
            ],
        ]);

        $this->forge->addKey('id_tambor_movimiento', true);
        $this->forge->addForeignKey('tambor_id', 'tambores', 'id_tambor', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tambor_movimientos');
    }

    public function down(): void
    {
        $this->forge->dropTable('tambor_movimientos', true);
        $this->forge->dropTable('tambores', true);
    }
}
