<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTrazabilidadModulo extends Migration
{
    public function up()
    {
        $rows = [
            ['rol' => 'admin',    'modulo' => 'trazabilidad', 'activo' => 1],
            ['rol' => 'operador', 'modulo' => 'trazabilidad', 'activo' => 1],
            ['rol' => 'visor',    'modulo' => 'trazabilidad', 'activo' => 1],
        ];

        foreach ($rows as $row) {
            $exists = $this->db->table('permisos_rol_modulo')
                ->where('rol', $row['rol'])
                ->where('modulo', $row['modulo'])
                ->countAllResults();
            if ($exists === 0) {
                $this->db->table('permisos_rol_modulo')->insert($row);
            }
        }
    }

    public function down()
    {
        $this->db->table('permisos_rol_modulo')
            ->where('modulo', 'trazabilidad')
            ->delete();
    }
}
