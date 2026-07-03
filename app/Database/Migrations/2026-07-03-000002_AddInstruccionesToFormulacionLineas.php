<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fase 2 — Instrucciones, notas y fases en las líneas de formulación.
 *
 * Una línea de `item_general_formulaciones` puede ser:
 *   - 'ingrediente' (default): una materia prima (item_general_id NOT NULL).
 *   - 'instruccion': un paso de proceso (item_general_id NULL, texto = "Dispersar x 5 min…").
 *   - 'fase': un separador/encabezado de fase (item_general_id NULL, texto = etiqueta).
 *
 * `nota` = anotación corta por ingrediente (ej. "pH", "asociativo", "pino").
 * `item_general_id` pasa a NULLABLE para las filas de instrucción/fase.
 * El UNIQUE(formulaciones_id, item_general_id) NO estorba: MySQL permite múltiples
 * NULL en índices únicos (los repetidos de ingrediente son Fase 3). Idempotente.
 */
class AddInstruccionesToFormulacionLineas extends Migration
{
    private function hasColumn(string $col): bool
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'item_general_formulaciones'
               AND COLUMN_NAME = ?",
            [$col]
        )->getRow()->c > 0;
    }

    public function up()
    {
        $add = [];
        if (! $this->hasColumn('tipo')) {
            $add['tipo'] = [
                'type'       => 'ENUM',
                'constraint' => ['ingrediente', 'instruccion', 'fase'],
                'null'       => false,
                'default'    => 'ingrediente',
                'after'      => 'orden',
            ];
        }
        if (! $this->hasColumn('texto')) {
            $add['texto'] = ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'tipo'];
        }
        if (! $this->hasColumn('nota')) {
            $add['nota'] = ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'texto'];
        }
        if (! empty($add)) {
            $this->forge->addColumn('item_general_formulaciones', $add);
        }

        // item_general_id → NULLABLE (las filas de instrucción/fase no tienen ingrediente).
        // ALTER MODIFY directo: no toca la FK (fk_igf_item_general sigue válida; NULL = sin referencia).
        $nullable = (string) $this->db->query(
            "SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'item_general_formulaciones'
               AND COLUMN_NAME = 'item_general_id'"
        )->getRow()->IS_NULLABLE;

        if ($nullable === 'NO') {
            $this->db->query('ALTER TABLE item_general_formulaciones MODIFY item_general_id INT NULL');
        }
    }

    public function down()
    {
        foreach (['nota', 'texto', 'tipo'] as $col) {
            if ($this->hasColumn($col)) {
                $this->forge->dropColumn('item_general_formulaciones', $col);
            }
        }
        // No se revierte item_general_id a NOT NULL (podría haber filas de instrucción con NULL).
    }
}
