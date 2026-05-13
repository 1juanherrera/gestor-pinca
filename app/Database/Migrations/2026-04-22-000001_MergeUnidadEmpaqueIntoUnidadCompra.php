<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MergeUnidadEmpaqueIntoUnidadCompra extends Migration
{
    public function up()
    {
        // 1. Agregar unidades comerciales faltantes a la tabla unidad
        $nuevas = ['UNIDAD', 'CAJA', 'BULTO', 'CANECA', 'LITRO'];
        foreach ($nuevas as $nombre) {
            $existe = $this->db->query(
                "SELECT id_unidad FROM unidad WHERE nombre = ? LIMIT 1", [$nombre]
            )->getRowArray();
            if (!$existe) {
                $this->db->query(
                    "INSERT INTO unidad (nombre, escala) VALUES (?, 1.000000)", [$nombre]
                );
            }
        }

        // 2. Migrar unidad_empaque → unidad_compra_id (solo si la columna aún existe)
        $columns = array_column(
            $this->db->query("SHOW COLUMNS FROM item_proveedor")->getResultArray(),
            'Field'
        );

        if (in_array('unidad_empaque', $columns)) {
            $this->db->query("
                UPDATE item_proveedor ip
                JOIN unidad u ON UPPER(TRIM(ip.unidad_empaque)) = UPPER(TRIM(u.nombre))
                SET ip.unidad_compra_id = u.id_unidad
                WHERE ip.unidad_compra_id IS NULL
                  AND ip.unidad_empaque IS NOT NULL
                  AND ip.unidad_empaque != ''
            ");

            // 3. Eliminar columna unidad_empaque
            $this->forge->dropColumn('item_proveedor', 'unidad_empaque');
        }
    }

    public function down()
    {
        // Re-agregar columna unidad_empaque
        $this->forge->addColumn('item_proveedor', [
            'unidad_empaque' => [
                'type'       => 'VARCHAR',
                'constraint' => 13,
                'null'       => true,
                'default'    => null,
                'after'      => 'tipo',
            ],
        ]);

        // Repoblar desde unidad_compra_id (capitalizar: 'GALON' → 'Galon')
        $this->db->query("
            UPDATE item_proveedor ip
            JOIN unidad u ON u.id_unidad = ip.unidad_compra_id
            SET ip.unidad_empaque = CONCAT(UPPER(LEFT(u.nombre, 1)), LOWER(SUBSTRING(u.nombre, 2)))
            WHERE ip.unidad_compra_id IS NOT NULL
        ");
    }
}
