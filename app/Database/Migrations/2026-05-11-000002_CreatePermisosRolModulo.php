<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermisosRolModulo extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'rol' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'modulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => false,
            ],
            'activo' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['rol', 'modulo'], 'uk_rol_modulo');
        $this->forge->createTable('permisos_rol_modulo', true);

        // Permisos por defecto
        $adminModulos = [
            'panel-principal', 'catalogo', 'inventario-global', 'formulaciones',
            'produccion', 'rentabilidad', 'comercial', 'compras', 'cartera',
            'clientes', 'proveedores', 'movimientos', 'pagos', 'tambores',
            'prorrateo', 'roles',
        ];

        $operadorModulos = [
            'panel-principal', 'catalogo', 'inventario-global', 'formulaciones',
            'produccion', 'compras', 'clientes', 'proveedores', 'movimientos',
            'pagos', 'tambores',
        ];

        $visorModulos = [
            'panel-principal', 'catalogo', 'inventario-global', 'formulaciones',
            'produccion', 'rentabilidad', 'comercial', 'cartera', 'movimientos',
        ];

        $rows = [];
        foreach ($adminModulos    as $m) $rows[] = ['rol' => 'admin',    'modulo' => $m, 'activo' => 1];
        foreach ($operadorModulos as $m) $rows[] = ['rol' => 'operador', 'modulo' => $m, 'activo' => 1];
        foreach ($visorModulos    as $m) $rows[] = ['rol' => 'visor',    'modulo' => $m, 'activo' => 1];

        $this->db->table('permisos_rol_modulo')->insertBatch($rows);
    }

    public function down()
    {
        $this->forge->dropTable('permisos_rol_modulo', true);
    }
}
