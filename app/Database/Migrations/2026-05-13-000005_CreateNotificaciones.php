<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Sistema de notificaciones in-app.
 *
 * Cada notificación puede dirigirse a un usuario específico (`user_id`),
 * a todos los usuarios de un rol (`rol_target`), o a ambos NULL para
 * notificaciones globales (admin).
 *
 *  link  → ruta del frontend a navegar al hacer click (ej: '/cartera?factura=123')
 *  meta  → JSON libre con datos del recurso (factura_numero, monto, etc.)
 *  tipo  → categoría: factura_vencimiento, oc_retrasada, mp_critica,
 *          requisicion_nueva, item_huerfano, info, etc.
 */
class CreateNotificaciones extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('notificaciones')) return;

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'null' => true],
            'rol_target' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'tipo'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'titulo'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'mensaje'    => ['type' => 'TEXT', 'null' => true],
            'link'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'leida'      => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'leida_at'   => ['type' => 'DATETIME', 'null' => true],
            'metadata'   => ['type' => 'JSON', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'leida'], false, false, 'idx_notif_user_leida');
        $this->forge->addKey(['rol_target', 'leida'], false, false, 'idx_notif_rol_leida');
        $this->forge->addKey('tipo', false, false, 'idx_notif_tipo');
        $this->forge->addKey('created_at', false, false, 'idx_notif_created');

        $this->forge->createTable('notificaciones');
    }

    public function down()
    {
        $this->forge->dropTable('notificaciones', true);
    }
}
