<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRolToUsuarios extends Migration
{
    public function up()
    {
        $cols = $this->db->query("SHOW COLUMNS FROM usuarios")->getResultArray();
        $existing = array_column($cols, 'Field');

        if (!in_array('rol', $existing)) {
            $this->forge->addColumn('usuarios', [
                'rol' => [
                    'type'       => "ENUM('admin','operador','visor')",
                    'null'       => false,
                    'default'    => 'operador',
                    'after'      => 'password',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('usuarios', 'rol');
    }
}
