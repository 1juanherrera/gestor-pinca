<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Agrega las FKs que faltaban en dos tablas clave (verificado: 0 huérfanos al aplicar):
 *
 * - `item_proveedor` no tenía NINGUNA FK → permitía referencias colgadas a item_general
 *   o proveedor (raíz de los huérfanos históricos del catálogo).
 * - `item_general_formulaciones` (la BOM/receta) tampoco tenía FKs → renglones de receta
 *   podían apuntar a ingredientes o fórmulas inexistentes.
 *
 * Reglas elegidas:
 * - item_proveedor.item_general_id → ON DELETE SET NULL (columna nullable = "pendiente";
 *   si se borra el ítem, la referencia del proveedor queda pendiente, no rota).
 * - item_proveedor.proveedor_id    → ON DELETE RESTRICT (no borrar un proveedor con ítems;
 *   además proveedor usa soft-delete, así que un DELETE físico no ocurre en el flujo normal).
 * - igf.formulaciones_id → ON DELETE CASCADE (al borrar la fórmula, sus renglones se van).
 * - igf.item_general_id  → ON DELETE RESTRICT (no se puede borrar un ítem usado como
 *   ingrediente — complementa el chequeo de ItemController::delete).
 */
class AddMissingForeignKeys extends Migration
{
    private array $fks = [
        ['fk_ip_item_general',  'item_proveedor',              'item_general_id',  'item_general', 'id_item_general',  'SET NULL', 'CASCADE'],
        ['fk_ip_proveedor',     'item_proveedor',              'proveedor_id',     'proveedor',    'id_proveedor',     'RESTRICT', 'CASCADE'],
        ['fk_igf_formulaciones','item_general_formulaciones',  'formulaciones_id', 'formulaciones','id_formulaciones', 'CASCADE',  'CASCADE'],
        ['fk_igf_item_general', 'item_general_formulaciones',  'item_general_id',  'item_general', 'id_item_general',  'RESTRICT', 'CASCADE'],
    ];

    public function up()
    {
        foreach ($this->fks as [$name, $table, $col, $refTable, $refCol, $onDel, $onUpd]) {
            // Idempotente: agregar solo si no existe ya una FK con ese nombre.
            $exists = $this->db->query("
                SELECT COUNT(*) AS n FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$table, $name])->getRow()->n ?? 0;
            if ((int) $exists > 0) continue;

            $this->db->query(
                "ALTER TABLE `{$table}` ADD CONSTRAINT `{$name}` FOREIGN KEY (`{$col}`) " .
                "REFERENCES `{$refTable}`(`{$refCol}`) ON DELETE {$onDel} ON UPDATE {$onUpd}"
            );
        }
    }

    public function down()
    {
        foreach (array_reverse($this->fks) as [$name, $table]) {
            $exists = $this->db->query("
                SELECT COUNT(*) AS n FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$table, $name])->getRow()->n ?? 0;
            if ((int) $exists > 0) {
                $this->db->query("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
            }
        }
    }
}
