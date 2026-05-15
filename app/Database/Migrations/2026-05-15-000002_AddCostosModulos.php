<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCostosModulos extends Migration
{
    public function up()
    {
        $rows = [
            // Costos (análisis financiero) — admin lee/escribe, visor solo lee
            ['rol' => 'admin',    'modulo' => 'costos',            'activo' => 1],
            ['rol' => 'visor',    'modulo' => 'costos',            'activo' => 1],

            // Costos Indirectos (CRUD de costos fijos mensuales) — solo admin
            ['rol' => 'admin',    'modulo' => 'costos-indirectos', 'activo' => 1],
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
            ->whereIn('modulo', ['costos', 'costos-indirectos'])
            ->delete();
    }
}
