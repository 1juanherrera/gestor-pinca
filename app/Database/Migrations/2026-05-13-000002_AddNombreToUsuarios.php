<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Agrega columna `nombre` a `usuarios` para mostrar el nombre real en la UI
 * en vez del username técnico. Nullable: usuarios existentes no quedan rotos
 * y se autocompleta a la primera edición del perfil.
 */
class AddNombreToUsuarios extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('nombre', 'usuarios')) return;

        $this->forge->addColumn('usuarios', [
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'username',
            ],
        ]);
    }

    public function down()
    {
        if (!$this->db->fieldExists('nombre', 'usuarios')) return;
        $this->forge->dropColumn('usuarios', 'nombre');
    }
}
