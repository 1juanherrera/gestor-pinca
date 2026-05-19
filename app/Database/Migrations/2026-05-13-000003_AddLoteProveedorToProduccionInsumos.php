<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Agrega `lote_proveedor` al snapshot `produccion_insumos_detalle` para
 * trazabilidad rápida MP → lote consumido.
 *
 * Cuando una preparación consume capas con un único lote, se copia ese lote.
 * Cuando consume capas de varios lotes del mismo proveedor, el campo queda
 * NULL y la trazabilidad granular se obtiene vía JOIN a
 * `preparacion_consumo_capas → inventario_capas`.
 *
 * Index agregado para filtrar por lote en queries de trazabilidad inversa
 * ("¿qué preparaciones consumieron este lote?").
 */
class AddLoteProveedorToProduccionInsumos extends Migration
{
    public function up()
    {
        $tabla = 'produccion_insumos_detalle';
        if (!$this->db->tableExists($tabla)) return;

        if (!$this->db->fieldExists('lote_proveedor', $tabla)) {
            $this->forge->addColumn($tabla, [
                'lote_proveedor' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'proveedor_id',
                ],
            ]);
        }

        // Index para búsqueda inversa por lote
        $exists = $this->db->query(
            "SHOW INDEX FROM {$tabla} WHERE Key_name = 'idx_pid_lote'"
        )->getRow();
        if (!$exists) {
            $this->db->query("CREATE INDEX idx_pid_lote ON {$tabla} (lote_proveedor)");
        }
    }

    public function down()
    {
        $tabla = 'produccion_insumos_detalle';
        $this->db->query("DROP INDEX IF EXISTS idx_pid_lote ON {$tabla}");
        if ($this->db->fieldExists('lote_proveedor', $tabla)) {
            $this->forge->dropColumn($tabla, 'lote_proveedor');
        }
    }
}
