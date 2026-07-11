<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Amplía `facturas.numero` de varchar(10) a varchar(20).
 *
 * Motivo: la serie de numeración de facturas usa el formato `FAC-{Y}-####`
 * (ej. "FAC-2026-0001" = 13 caracteres), pero la columna era varchar(10). En
 * MySQL no-estricto el INSERT truncaba el número en silencio (guardando
 * "FAC-2026-0"), corrompiendo la numeración fiscal. Las otras series ya usan
 * varchar(20) (cotizaciones.numero, remisiones.numero) — esto las alinea.
 *
 * Idempotente: solo altera si la longitud actual es menor a 20.
 */
class WidenFacturasNumero extends Migration
{
    public function up()
    {
        $len = $this->db->query(
            "SELECT CHARACTER_MAXIMUM_LENGTH AS len
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'facturas'
                AND COLUMN_NAME = 'numero'"
        )->getRow();

        if ($len && (int) $len->len < 20) {
            $this->db->query("ALTER TABLE `facturas` MODIFY `numero` VARCHAR(20) NULL");
        }
    }

    public function down()
    {
        // No revertimos a varchar(10): reintroduciría el bug de truncamiento.
        // (down intencionalmente vacío.)
    }
}
