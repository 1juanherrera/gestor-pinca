<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Amplía `costos_item.metodo_calculo` de varchar(6) a varchar(20).
 *
 * Motivo: `InventarioCapasModel::recalcularPromedioPonderado` guarda
 * `metodo_calculo = 'PROMEDIO_PONDERADO'` (18 chars) y `CatalogoModel::crearItem`
 * guarda `'Catálogo'` (8), pero la columna era varchar(6). En MySQL no-estricto
 * CI4 truncaba en silencio ('PROMED', 'Catálo'); en modo estricto (mysql2/Nest)
 * el INSERT falla. Ampliar deja que ambos backends guarden el valor completo.
 *
 * Idempotente: solo altera si la longitud actual es menor a 20.
 */
class WidenCostosItemMetodoCalculo extends Migration
{
    public function up()
    {
        $len = $this->db->query(
            "SELECT CHARACTER_MAXIMUM_LENGTH AS len
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'costos_item'
                AND COLUMN_NAME = 'metodo_calculo'"
        )->getRow();

        if ($len && (int) $len->len < 20) {
            $this->db->query("ALTER TABLE `costos_item` MODIFY `metodo_calculo` VARCHAR(20) DEFAULT NULL");
        }
    }

    public function down()
    {
        // No revertimos: reintroduciría el truncamiento silencioso.
    }
}
