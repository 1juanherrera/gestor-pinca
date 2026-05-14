<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabla `configuracion_sistema` — almacenamiento key/value tipado para
 * parámetros globales del sistema (IVA, umbrales de alertas, etc.).
 *
 * Diseño:
 * - `clave` único: lookup directo por nombre.
 * - `grupo` para agrupar visualmente en UI (tab "Tributaria", "Umbrales").
 * - `valor` JSON: soporta string, number, boolean, array, object — un solo schema.
 * - `tipo` discrimina cómo deserializar al leer.
 * - `updated_by` queda para auditoría.
 */
class CreateConfiguracionSistema extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_configuracion' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'grupo'            => ['type' => 'VARCHAR', 'constraint' => 40],
            'clave'            => ['type' => 'VARCHAR', 'constraint' => 80],
            'valor'            => ['type' => 'JSON', 'null' => true],
            'tipo'             => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'string'],
            'descripcion'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_by'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id_configuracion');
        $this->forge->addUniqueKey('clave');
        $this->forge->addKey('grupo');
        $this->forge->createTable('configuracion_sistema');
    }

    public function down()
    {
        $this->forge->dropTable('configuracion_sistema');
    }
}
