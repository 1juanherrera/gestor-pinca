<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnidadBaseAndItemProveedorCompra extends Migration
{
    public function up()
    {
        // 1. Agregar KILO como unidad base para materias primas
        $this->db->query("
            INSERT INTO unidad (nombre, descripcion, estados, escala)
            SELECT 'KILO', 'Unidad base para materias primas', 1, 1.000000
            WHERE NOT EXISTS (SELECT 1 FROM unidad WHERE nombre = 'KILO')
        ");

        // 2. Agregar unidad_compra_id y factor_conversion a item_proveedor
        $fields = [
            'unidad_compra_id' => [
                'type'       => 'INT',
                'null'       => true,
                'after'      => 'unidad_empaque',
            ],
            'factor_conversion' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,6',
                'null'       => false,
                'default'    => 1.000000,
                'after'      => 'unidad_compra_id',
            ],
        ];

        // Solo agregar si no existen
        $cols = $this->db->query("SHOW COLUMNS FROM item_proveedor")->getResultArray();
        $existing = array_column($cols, 'Field');

        foreach ($fields as $col => $def) {
            if (!in_array($col, $existing)) {
                $this->forge->addColumn('item_proveedor', [$col => $def]);
            }
        }

        // 3. FK unidad_compra_id → unidad.id_unidad
        if (!in_array('unidad_compra_id', $existing)) {
            $this->db->query("
                ALTER TABLE item_proveedor
                ADD CONSTRAINT fk_item_proveedor_unidad_compra
                FOREIGN KEY (unidad_compra_id) REFERENCES unidad(id_unidad)
                ON DELETE SET NULL
            ");
        }
    }

    public function down()
    {
        $this->db->query("ALTER TABLE item_proveedor DROP FOREIGN KEY fk_item_proveedor_unidad_compra");
        $this->forge->dropColumn('item_proveedor', ['unidad_compra_id', 'factor_conversion']);
        $this->db->query("DELETE FROM unidad WHERE nombre = 'KILO'");
    }
}
