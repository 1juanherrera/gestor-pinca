<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Agrega `iva_pct` a `ordenes_compra` para trazabilidad histórica del IVA
 * aplicado por cada OC.
 *
 * - `total` mantiene su semántica actual (subtotal, sin IVA).
 * - `iva_pct` se persiste al crear/actualizar; defaultea desde
 *   configuracion_sistema.iva_default si el cliente no lo provee.
 * - Backfill: OCs históricas reciben el valor actual de iva_default.
 *   Si en el futuro la DIAN cambia el IVA, las OCs viejas mantienen
 *   el % con el que se cerraron.
 */
class AddIvaPctToOrdenesCompra extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ordenes_compra', [
            'iva_pct' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'total',
                'comment'    => 'Porcentaje de IVA aplicado a la OC al momento de su cierre. NULL = legacy sin IVA.',
            ],
        ]);

        // Backfill: leer iva_default de configuracion_sistema y aplicarlo a OCs existentes.
        $cfg = $this->db->table('configuracion_sistema')
            ->where('clave', 'iva_default')
            ->get()->getRowArray();
        $ivaDefault = $cfg ? (float) trim($cfg['valor'], '"') : 19.0;

        $this->db->query('UPDATE ordenes_compra SET iva_pct = ? WHERE iva_pct IS NULL', [$ivaDefault]);
    }

    public function down()
    {
        $this->forge->dropColumn('ordenes_compra', 'iva_pct');
    }
}
