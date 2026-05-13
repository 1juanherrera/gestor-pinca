<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Convierte Remisiones en un evento que descuenta stock real (gap heredado).
 *
 * Cambios:
 *   1. `remisiones_detalle.item_general_id` (nullable) — vincula la línea al item del catálogo.
 *      Nullable para no romper data legacy (descripcion como texto libre).
 *   2. `remisiones_detalle.bodega_id` (nullable) — bodega desde la que se despacha.
 *   3. `remisiones.estado` ENUM extendido con `'Despachada'` (entre Pendiente y Facturada).
 *      Es el estado en el que se descuenta stock.
 *   4. Tabla nueva `remision_consumo_capas` — audit detallado de qué capas consumió
 *      cada línea de remisión. Permite restaurar exacto al anular.
 */
class RemisionesStockTracking extends Migration
{
    public function up()
    {
        // 1. Columnas en remisiones_detalle
        if (!$this->db->fieldExists('item_general_id', 'remisiones_detalle')) {
            $this->forge->addColumn('remisiones_detalle', [
                'item_general_id' => ['type' => 'INT', 'null' => true, 'after' => 'remisiones_id'],
                'bodega_id'       => ['type' => 'INT', 'null' => true, 'after' => 'item_general_id'],
            ]);
            $this->db->query("CREATE INDEX idx_remdet_item ON remisiones_detalle (item_general_id)");
        }

        // 2. Extender ENUM remisiones.estado
        $this->db->query("
            ALTER TABLE remisiones
            MODIFY COLUMN estado
                ENUM('Pendiente','Despachada','Facturada','Anulada')
                NOT NULL DEFAULT 'Pendiente'
        ");

        // 3. Tabla de auditoría de capas consumidas por remisión
        if (!$this->db->tableExists('remision_consumo_capas')) {
            $this->forge->addField([
                'id'                  => ['type' => 'INT', 'auto_increment' => true],
                'remision_id'         => ['type' => 'INT', 'null' => false],
                'remision_detalle_id' => ['type' => 'INT', 'null' => false],
                'capa_id'             => ['type' => 'INT', 'null' => false],
                'item_general_id'     => ['type' => 'INT', 'null' => false],
                'cantidad_consumida'  => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
                'costo_unitario'      => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
                'costo_total'         => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
                'created_at'          => ['type' => 'DATETIME', 'null' => false],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('remision_id', false, false, 'idx_rcc_rem');
            $this->forge->addKey('remision_detalle_id', false, false, 'idx_rcc_det');
            $this->forge->addKey('capa_id', false, false, 'idx_rcc_capa');
            $this->forge->createTable('remision_consumo_capas');
        }
    }

    public function down()
    {
        $this->forge->dropTable('remision_consumo_capas', true);
        $this->db->query("
            ALTER TABLE remisiones
            MODIFY COLUMN estado
                ENUM('Pendiente','Facturada','Anulada')
                NOT NULL DEFAULT 'Pendiente'
        ");
        if ($this->db->fieldExists('item_general_id', 'remisiones_detalle')) {
            $this->db->query("DROP INDEX IF EXISTS idx_remdet_item ON remisiones_detalle");
            $this->forge->dropColumn('remisiones_detalle', 'item_general_id');
            $this->forge->dropColumn('remisiones_detalle', 'bodega_id');
        }
    }
}
