<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Agrega el estado SUGERIDA al ENUM de requisiciones_compra.
 *
 * Las requisiciones generadas automáticamente por el MRP entran como
 * SUGERIDA. Un comprador/admin las revisa y las pasa a APROBADA antes de
 * que se puedan convertir a OC.
 */
class AddSugeridaToRequisiciones extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE requisiciones_compra
            MODIFY COLUMN estado
                ENUM('SUGERIDA','PENDIENTE','APROBADA','CONVERTIDA','CANCELADA')
                NOT NULL DEFAULT 'PENDIENTE'
        ");
    }

    public function down()
    {
        // Mover cualquier SUGERIDA a PENDIENTE antes de quitar el valor del ENUM
        $this->db->query("UPDATE requisiciones_compra SET estado = 'PENDIENTE' WHERE estado = 'SUGERIDA'");
        $this->db->query("
            ALTER TABLE requisiciones_compra
            MODIFY COLUMN estado
                ENUM('PENDIENTE','APROBADA','CONVERTIDA','CANCELADA')
                NOT NULL DEFAULT 'PENDIENTE'
        ");
    }
}
