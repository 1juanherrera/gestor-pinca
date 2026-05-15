<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * El módulo costos-indirectos se consolidó dentro de la tab "Indirectos"
 * del módulo Costos. Eliminamos el permiso del módulo standalone.
 */
class RemoveCostosIndirectosModulo extends Migration
{
    public function up()
    {
        $this->db->table('permisos_rol_modulo')
            ->where('modulo', 'costos-indirectos')
            ->delete();
    }

    public function down()
    {
        $rows = [
            ['rol' => 'admin', 'modulo' => 'costos-indirectos', 'activo' => 1],
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
}
