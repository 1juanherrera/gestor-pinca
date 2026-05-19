<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Soft-deletes en entidades críticas.
 *
 * Borrar permanentemente un cliente, proveedor o ítem rompe referencias
 * históricas en facturas, OCs y movimientos. Reemplazamos el DELETE físico
 * por un UPDATE deleted_at = NOW() — el registro queda invisible para listados
 * normales pero las relaciones FK siguen funcionando.
 *
 * Activado vía CI4 `useSoftDeletes = true` en cada Model (default field
 * `deleted_at`). Listados (findAll) filtran automáticamente; los borrados
 * pueden restaurarse con un endpoint admin.
 */
class AddSoftDeletes extends Migration
{
    private array $tablas = [
        'clientes',
        'proveedor',
        'item_general',
        'facturas',
        'ordenes_compra',
        'cotizaciones',
        'remisiones',
    ];

    public function up()
    {
        foreach ($this->tablas as $tabla) {
            if (!$this->db->tableExists($tabla)) continue;
            if ($this->db->fieldExists('deleted_at', $tabla)) continue;

            $this->forge->addColumn($tabla, [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            // Índice para que `WHERE deleted_at IS NULL` sea rápido (lo usa CI4 en cada query)
            $existe = $this->db->query(
                "SHOW INDEX FROM {$tabla} WHERE Key_name = 'idx_{$tabla}_deleted_at'"
            )->getRow();
            if (!$existe) {
                $this->db->query("CREATE INDEX idx_{$tabla}_deleted_at ON {$tabla} (deleted_at)");
            }
        }
    }

    public function down()
    {
        foreach ($this->tablas as $tabla) {
            if (!$this->db->tableExists($tabla)) continue;
            $this->db->query("DROP INDEX IF EXISTS idx_{$tabla}_deleted_at ON {$tabla}");
            if ($this->db->fieldExists('deleted_at', $tabla)) {
                $this->forge->dropColumn($tabla, 'deleted_at');
            }
        }
    }
}
