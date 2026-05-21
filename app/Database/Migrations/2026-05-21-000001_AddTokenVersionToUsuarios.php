<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Agrega `usuarios.token_version` (entero, default 1).
 *
 * Cada JWT emitido incluye el `token_version` actual del usuario en su payload.
 * Cuando un admin cambia el rol de alguien (o el usuario cambia su password),
 * el backend incrementa este contador → cualquier JWT viejo deja de validar.
 *
 * Esto cierra el gap de "cambio de rol no invalida sesiones activas" — antes,
 * un usuario degradado seguía operando con el rol viejo hasta que el token
 * caducaba (8h por default).
 */
class AddTokenVersionToUsuarios extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('token_version', 'usuarios')) {
            $this->forge->addColumn('usuarios', [
                'token_version' => [
                    'type'       => 'INT',
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 1,
                    'after'      => 'password_must_change',
                    'comment'    => 'Incrementa al cambiar rol o password — invalida tokens viejos.',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('token_version', 'usuarios')) {
            $this->forge->dropColumn('usuarios', 'token_version');
        }
    }
}
