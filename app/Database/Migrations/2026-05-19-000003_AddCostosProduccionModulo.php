<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Agrega el módulo 'costos-produccion' a permisos_rol_modulo.
 *
 * Módulo nuevo (Análisis): vista agregada de costos finales por producto,
 * usando el proveedor más barato por ingrediente y marcando productos con
 * MPs sin proveedor como incompletos.
 *
 * Default activo para admin + operador + visor (vista de solo lectura).
 */
class AddCostosProduccionModulo extends Migration
{
    private const MODULO = 'costos-produccion';
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
