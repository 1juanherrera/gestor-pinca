<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Agrega 'salud-sistema' a permisos_rol_modulo.
 * Activo por default para admin + operador + visor (vista de solo lectura).
 */
class AddSaludSistemaModulo extends Migration
{
    private const MODULO = 'salud-sistema';
    private const ROLES  = ['admin', 'operador', 'visor'];

    public function up()
    {
        foreach (self::ROLES as $rol) {
            $existe = $this->db->table('permisos_rol_modulo')
                ->where('rol', $rol)
                ->where('modulo', self::MODULO)
                ->countAllResults();
            if ($existe === 0) {
                $this->db->table('permisos_rol_modulo')->insert([
                    'rol'    => $rol,
                    'modulo' => self::MODULO,
                    'activo' => 1,
                ]);
            }
        }
    }

    public function down()
    {
        $this->db->table('permisos_rol_modulo')
            ->where('modulo', self::MODULO)
            ->delete();
    }
}
