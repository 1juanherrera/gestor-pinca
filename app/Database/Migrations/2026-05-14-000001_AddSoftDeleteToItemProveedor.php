<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Soft-delete para `item_proveedor`.
 *
 * El DELETE físico falla por FK desde `historial_precios` y
 * `ordenes_compra_detalle`. Marcamos `deleted_at` para conservar referencias
 * históricas y ocultar el ítem de los listados normales (BaseModel.get_all
 * y BaseModel.get filtran automáticamente).
 *
 * Complementa la migración 2026-05-13-000007_AddSoftDeletes que ya cubre
 * clientes, proveedor, item_general, facturas, ordenes_compra, cotizaciones,
 * remisiones — pero no incluyó item_proveedor.
 */
class AddSoftDeleteToItemProveedor extends Migration
{
    private string $tabla = 'item_proveedor';

    public function up()
    {
        if (!$this->db->tableExists($this->tabla)) return;
        if ($this->db->fieldExists('deleted_at', $this->tabla)) return;

        $this->forge->addColumn($this->tabla, [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $existe = $this->db->query(
            "SHOW INDEX FROM {$this->tabla} WHERE Key_name = 'idx_{$this->tabla}_deleted_at'"
        )->getRow();
        if (!$existe) {
            $this->db->query("CREATE INDEX idx_{$this->tabla}_deleted_at ON {$this->tabla} (deleted_at)");
        }
    }

    public function down()
    {
        if (!$this->db->tableExists($this->tabla)) return;
        $this->db->query("DROP INDEX IF EXISTS idx_{$this->tabla}_deleted_at ON {$this->tabla}");
        if ($this->db->fieldExists('deleted_at', $this->tabla)) {
            $this->forge->dropColumn($this->tabla, 'deleted_at');
        }
    }
}
