<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tablas para la deduplicación de materias primas asistida por IA.
 *
 * - `item_sync_clusters`: un grupo de ítems que la IA (o el humano) considera el
 *   MISMO material químico. Guarda la identidad química propuesta, el nombre base
 *   sugerido/aprobado, confianza, razonamiento y el estado del flujo de revisión.
 * - `item_sync_cluster_items`: los miembros de cada cluster, con su rol (cuál se
 *   conserva = keep, cuáles se fusionan = merge, cuáles se excluyen) y la confianza
 *   por ítem (un cluster puede tener un miembro dudoso que conviene verificar).
 *
 * Nada se fusiona automáticamente: estas tablas son la "bandeja de propuestas" que
 * el usuario revisa antes de ejecutar el merge (ver SincronizacionModel::fusionarCluster).
 */
class CreateItemSyncSugerencias extends Migration
{
    public function up()
    {
        // ── item_sync_clusters ──────────────────────────────────────────────
        $this->forge->addField([
            'id_cluster' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'clave_grupo' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'comment'    => 'Slug de identidad química (ej. dioxido-titanio-rutilo).',
            ],
            'identidad_quimica' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Nombre químico canónico ("Dióxido de titanio rutilo").',
            ],
            'nombre_base_propuesto' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'comment'    => 'Nombre base limpio que sugiere la IA para el keep.',
            ],
            'nombre_base_aprobado' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'comment'    => 'Nombre base que fija el humano (gana sobre el propuesto).',
            ],
            'confianza' => [
                'type'       => 'ENUM',
                'constraint' => ['alta', 'media', 'baja'],
                'null'       => false,
                'default'    => 'media',
            ],
            'razonamiento' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Explicación de la IA: por qué son el mismo material.',
            ],
            'tipo' => [
                'type'    => 'TINYINT',
                'null'    => false,
                'comment' => '1=Materia Prima, 2=Insumo. Un cluster no mezcla tipos.',
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['propuesto', 'revisado', 'aprobado', 'fusionado', 'descartado'],
                'null'       => false,
                'default'    => 'propuesto',
            ],
            'keep_id_sugerido' => [
                'type' => 'INT',
                'null' => true,
                'comment' => 'item_general que la IA sugiere conservar.',
            ],
            'keep_id_aprobado' => [
                'type' => 'INT',
                'null' => true,
                'comment' => 'item_general que el humano confirma conservar.',
            ],
            'lote_ia' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
                'comment'    => 'Id de la corrida de clasificación (re-correr sin pisar).',
            ],
            'modelo_ia' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'aprobado_por' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'fusionado_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_cluster', true);
        $this->forge->addKey('estado');
        $this->forge->addKey('lote_ia');
        $this->forge->createTable('item_sync_clusters', true);

        // ── item_sync_cluster_items ─────────────────────────────────────────
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'cluster_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => false,
            ],
            'item_general_id' => [
                // INT con signo, sin FK dura: item_general usa soft-delete (no se
                // borra físico) y evitamos fallos de mismatch de tipo en la FK.
                'type' => 'INT',
                'null' => false,
            ],
            'rol' => [
                'type'       => 'ENUM',
                'constraint' => ['keep', 'merge', 'excluido'],
                'null'       => false,
                'default'    => 'merge',
            ],
            'confianza_item' => [
                'type'       => 'ENUM',
                'constraint' => ['alta', 'media', 'baja'],
                'null'       => false,
                'default'    => 'media',
            ],
            'motivo_revision' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Ej. "verificar con ficha técnica" cuando baja confianza.',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['cluster_id', 'item_general_id']);
        $this->forge->addKey('item_general_id');
        $this->forge->addForeignKey('cluster_id', 'item_sync_clusters', 'id_cluster', 'CASCADE', 'CASCADE');
        $this->forge->createTable('item_sync_cluster_items', true);
    }

    public function down()
    {
        // Dropear hijo primero por la FK; dropTable entero evita DROP INDEX IF EXISTS.
        $this->forge->dropTable('item_sync_cluster_items', true);
        $this->forge->dropTable('item_sync_clusters', true);
    }
}
