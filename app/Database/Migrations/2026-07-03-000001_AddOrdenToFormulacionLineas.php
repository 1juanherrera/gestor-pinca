<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fase 1 — Orden de proceso en las líneas de formulación.
 *
 * Agrega `orden` a `item_general_formulaciones` para poder mostrar y guardar los
 * ingredientes en la secuencia real de la libreta (hoy se ordenan por nombre).
 * Idempotente. No toca datos existentes (default 0); el backfill del orden inicial
 * se hace por separado tras la migración.
 */
class AddOrdenToFormulacionLineas extends Migration
{
    public function up()
    {
        $col = $this->db->query(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'item_general_formulaciones'
               AND COLUMN_NAME = 'orden'"
        )->getRow()->c;

        if ((int) $col === 0) {
            $this->forge->addColumn('item_general_formulaciones', [
                'orden' => [
                    'type'       => 'SMALLINT',
                    'null'       => false,
                    'default'    => 0,
                    'after'      => 'item_general_id',
                    'comment'    => 'Secuencia de proceso (orden en que se agrega el ingrediente)',
                ],
            ]);
        }
    }

    public function down()
    {
        $col = $this->db->query(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'item_general_formulaciones'
               AND COLUMN_NAME = 'orden'"
        )->getRow()->c;

        if ((int) $col > 0) {
            $this->forge->dropColumn('item_general_formulaciones', 'orden');
        }
    }
}
