<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabla costos_snapshot — guarda fotografías periódicas del costo total
 * de cada producto. Útil para construir gráficos de evolución y detectar
 * subas de precios de MP antes de que coman el margen.
 *
 * Se llena vía comando `php spark snapshot:costos` (ejecutar mensualmente).
 */
class CreateCostosSnapshot extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_costos_snapshot' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'item_general_id' => [
                'type'     => 'INT',
                'null'     => false,
                'comment'  => 'FK a item_general',
            ],
            'fecha' => [
                'type'    => 'DATE',
                'null'    => false,
                'comment' => 'Fecha del snapshot (YYYY-MM-DD)',
            ],
            'estado' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => 'completo | incompleto al momento del snapshot',
            ],
            'volumen_base' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
                'null'       => false,
                'default'    => 1,
            ],
            'costo_mp_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
                'comment'    => 'NULL si producto incompleto',
            ],
            'costo_mp_por_unidad' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'costo_empaque_mod' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'default'    => 0,
            ],
            'costo_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'porcentaje_utilidad' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
                'default'    => 0,
            ],
            'precio_venta_calc' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'mps_total' => [
                'type' => 'INT',
                'null' => false,
                'default' => 0,
            ],
            'mps_cubiertas' => [
                'type' => 'INT',
                'null' => false,
                'default' => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addPrimaryKey('id_costos_snapshot');
        $this->forge->addUniqueKey(['item_general_id', 'fecha'], 'uq_snapshot_item_fecha');
        $this->forge->addKey(['item_general_id', 'fecha']);
        $this->forge->addKey('fecha');
        $this->forge->createTable('costos_snapshot');
    }

    public function down()
    {
        $this->forge->dropTable('costos_snapshot', true);
    }
}
