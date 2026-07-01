<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Dropea la tabla `tambores`.
 *
 * El módulo Tambores se eliminó en la sesión 2026-05-21 (controller, modelo,
 * rutas y permisos RBAC borrados). La tabla quedó huérfana y vacía. Esta
 * migración la elimina definitivamente.
 *
 * `down()` recrea un schema mínimo solo para no romper un rollback. El módulo
 * NO se reactiva con esto — la tabla queda vacía y sin uso.
 */
class DropTamboresTable extends Migration
{
    public function up()
    {
        $this->forge->dropTable('tambores', true);
    }

    public function down()
    {
        // Schema mínimo de cortesía para no romper migrate:rollback.
        // El módulo Tambores ya no existe; esto solo recrea un esqueleto vacío.
        $this->forge->addField([
            'id_tambores' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_tambores', true);
        $this->forge->createTable('tambores', true);
    }
}
