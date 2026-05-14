<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Extiende `empresa` con campos que estaban hardcodeados en el frontend
 * (4 archivos de export PDF + InventarioGlobalPage). Una sola fuente de verdad.
 */
class ExtendEmpresa extends Migration
{
    public function up()
    {
        $columns = [
            'direccion' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true,  'after' => 'ciudad'],
            'email'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true,  'after' => 'pagina_web'],
            'celular'   => ['type' => 'VARCHAR', 'constraint' => 45,  'null' => true,  'after' => 'telefono'],
            'locale'    => ['type' => 'VARCHAR', 'constraint' => 10,  'default' => 'es-CO', 'null' => false, 'after' => 'email'],
            'moneda'    => ['type' => 'VARCHAR', 'constraint' => 5,   'default' => 'COP',   'null' => false, 'after' => 'locale'],
            'logo_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true,  'after' => 'moneda'],
        ];

        foreach ($columns as $name => $spec) {
            if (!$this->db->fieldExists($name, 'empresa')) {
                $this->forge->addColumn('empresa', [$name => $spec]);
            }
        }

        // Backfill con los valores que estaban hardcodeados en el frontend
        $this->db->table('empresa')
            ->where('id_empresa', 1)
            ->update([
                'direccion' => 'Calle 99 # 6-59',
                'email'     => 'pinca.sas@hotmail.com',
                'celular'   => '+57 3019794729',
                'locale'    => 'es-CO',
                'moneda'    => 'COP',
            ]);
    }

    public function down()
    {
        foreach (['direccion', 'email', 'celular', 'locale', 'moneda', 'logo_path'] as $col) {
            if ($this->db->fieldExists($col, 'empresa')) {
                $this->forge->dropColumn('empresa', $col);
            }
        }
    }
}
