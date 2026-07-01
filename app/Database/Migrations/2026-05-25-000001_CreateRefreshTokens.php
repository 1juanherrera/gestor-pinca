<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabla `refresh_tokens` — soporte para rotación de refresh tokens.
 *
 * El refresh token plano NUNCA se almacena: solo su hash SHA-256 (token_hash).
 * `login()` emite un refresh token de larga vida (7 días); el endpoint
 * `POST /api/auth/refresh` lo intercambia por un JWT nuevo + un refresh token
 * rotado (marca el viejo revoked=1 y crea uno nuevo). `logout()` revoca todos
 * los refresh tokens del usuario.
 */
class CreateRefreshTokens extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'usuario_id' => [
                // Coincide con usuarios.id_usuarios (INT con signo) — requisito
                // para la FK. NO usar unsigned: rompe la constraint.
                'type' => 'INT',
                'null' => false,
            ],
            'token_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'comment'    => 'Hash SHA-256 del refresh token. NUNCA el token plano.',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'revoked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('token_hash');
        $this->forge->addKey('usuario_id');

        // FK a usuarios(id_usuarios) con borrado en cascada.
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id_usuarios', 'CASCADE', 'CASCADE');

        $this->forge->createTable('refresh_tokens', true);
    }

    public function down()
    {
        // MySQL no soporta DROP INDEX IF EXISTS — dropear la tabla entera es seguro.
        $this->forge->dropTable('refresh_tokens', true);
    }
}
