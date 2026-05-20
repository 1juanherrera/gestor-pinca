<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Rol nuevo 'superadmin' (por encima de admin).
 *
 *  - ALTER ENUM usuarios.rol para incluir 'superadmin'
 *  - ADD COLUMN usuarios.password_must_change TINYINT(1) DEFAULT 0
 *    (para forzar cambio de password al primer login)
 *  - INSERT permisos en permisos_rol_modulo para 'superadmin' (todos activos)
 *  - INSERT usuario Juan Herrera (1juanherrera) con must_change=1
 *
 * Diferencia funcional vs admin:
 *  - superadmin = único rol que puede mutar permisos_rol_modulo (gestión de roles)
 *  - admin = sigue viendo todo, pero ya NO puede gestionar roles
 */
class AddSuperadminRol extends Migration
{
    public function up()
    {
        // 1. Ampliar ENUM
        $this->db->query("
            ALTER TABLE usuarios
            MODIFY rol ENUM('superadmin','admin','operador','visor') NOT NULL DEFAULT 'operador'
        ");

        // 2. Columna para forzar cambio de password
        if (! $this->db->fieldExists('password_must_change', 'usuarios')) {
            $this->forge->addColumn('usuarios', [
                'password_must_change' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => false,
                    'default'    => 0,
                    'after'      => 'rol',
                    'comment'    => 'Si 1, el usuario debe cambiar password al próximo login.',
                ],
            ]);
        }

        // 3. Permisos: superadmin ve TODOS los módulos
        $modulosExistentes = $this->db->table('permisos_rol_modulo')
            ->select('modulo')
            ->distinct()
            ->get()->getResultArray();

        foreach ($modulosExistentes as $m) {
            $existe = $this->db->table('permisos_rol_modulo')
                ->where('rol', 'superadmin')
                ->where('modulo', $m['modulo'])
                ->countAllResults();
            if ($existe === 0) {
                $this->db->table('permisos_rol_modulo')->insert([
                    'rol'    => 'superadmin',
                    'modulo' => $m['modulo'],
                    'activo' => 1,
                ]);
            }
        }

        // 4. Usuario Juan Herrera — solo si no existe ya
        $existe = $this->db->table('usuarios')
            ->where('username', '1juanherrera')
            ->countAllResults();

        if ($existe === 0) {
            // Hash bcrypt de 'root' — generado vía password_hash() y luego cambia con must_change
            $this->db->table('usuarios')->insert([
                'username'             => '1juanherrera',
                'nombre'               => 'Juan Herrera',
                'password'             => '$2y$10$gbWzSg/j.GwJHlMT9LKKMujex17dmeanFGm2myxFAyRl7f.jh/J8a',
                'rol'                  => 'superadmin',
                'password_must_change' => 1,
            ]);
        } else {
            // Si ya existe (por algún seed previo), forzar rol superadmin sin tocar password
            $this->db->table('usuarios')
                ->where('username', '1juanherrera')
                ->update(['rol' => 'superadmin']);
        }
    }

    public function down()
    {
        // Revertir: eliminar al usuario, sus permisos, y volver el ENUM al estado previo.
        $this->db->table('usuarios')->where('username', '1juanherrera')->delete();
        $this->db->table('permisos_rol_modulo')->where('rol', 'superadmin')->delete();

        if ($this->db->fieldExists('password_must_change', 'usuarios')) {
            $this->forge->dropColumn('usuarios', 'password_must_change');
        }

        $this->db->query("
            ALTER TABLE usuarios
            MODIFY rol ENUM('admin','operador','visor') NOT NULL DEFAULT 'operador'
        ");
    }
}
