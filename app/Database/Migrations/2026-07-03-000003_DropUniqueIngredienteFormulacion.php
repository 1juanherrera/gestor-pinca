<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Fase 3 — Permitir el MISMO ingrediente varias veces en una fórmula.
 *
 * En la libreta muchas materias primas se agregan en dos (o más) pasos distintos
 * (ej. Resina 60 al inicio + 40 al final; Varsol/Agua/Xilol 2×). El
 * UNIQUE(formulaciones_id, item_general_id) lo impedía y forzaba consolidar. Se
 * quita para poder re-cargar las fórmulas partidas y en orden. Idempotente.
 *
 * El costeo funciona igual con repetidos: la cantidad es por línea y el precio es
 * por material (mismo material → mismo proveedor/precio). El merge de dedup sigue
 * consolidando manualmente en PHP (no dependía de este índice).
 */
class DropUniqueIngredienteFormulacion extends Migration
{
    private function indexExists(string $name): bool
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'item_general_formulaciones'
               AND INDEX_NAME = ?",
            [$name]
        )->getRow()->c > 0;
    }

    public function up()
    {
        if ($this->indexExists('uq_formula_ingrediente')) {
            $this->db->query('ALTER TABLE item_general_formulaciones DROP INDEX uq_formula_ingrediente');
        }
    }

    public function down()
    {
        if ($this->indexExists('uq_formula_ingrediente')) {
            return;
        }
        // Re-crear el UNIQUE solo si NO hay ingredientes repetidos (si los hay, no se puede volver atrás).
        $dups = (int) $this->db->query(
            "SELECT COUNT(*) AS c FROM (
                SELECT formulaciones_id, item_general_id
                FROM item_general_formulaciones
                WHERE item_general_id IS NOT NULL
                GROUP BY formulaciones_id, item_general_id
                HAVING COUNT(*) > 1
             ) t"
        )->getRow()->c;

        if ($dups === 0) {
            $this->db->query('ALTER TABLE item_general_formulaciones
                ADD UNIQUE KEY uq_formula_ingrediente (formulaciones_id, item_general_id)');
        } else {
            log_message('warning', "[migration down] No se re-creó uq_formula_ingrediente: existen {$dups} ingredientes repetidos.");
        }
    }
}
