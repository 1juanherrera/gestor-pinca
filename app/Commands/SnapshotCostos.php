<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\FormulacionesModel;

/**
 * Captura un snapshot del costo actual de cada producto.
 *
 * Uso:
 *   docker exec gestor-pinca-app php spark snapshot:costos
 *
 * Idempotente: si ya hay snapshot para el día actual, lo actualiza
 * (no genera duplicados gracias al UNIQUE en (item_general_id, fecha)).
 *
 * Para automatizar — agregar al cron:
 *   0 6 1 * *  docker exec gestor-pinca-app php spark snapshot:costos
 *   (primer día de cada mes a las 6:00)
 */
class SnapshotCostos extends BaseCommand
{
    protected $group       = 'Costos';
    protected $name        = 'snapshot:costos';
    protected $description = 'Captura snapshot del costo actual de todos los productos con fórmula activa.';

    public function run(array $params)
    {
        $model = new FormulacionesModel();
        $batch = $model->get_costos_produccion_batch();
        $productos = $batch['productos'] ?? [];

        if (empty($productos)) {
            CLI::write('No hay productos con fórmula activa para snapshotear.', 'yellow');
            return;
        }

        $db     = \Config\Database::connect();
        $fecha  = date('Y-m-d');
        $insertados = 0;
        $actualizados = 0;

        foreach ($productos as $p) {
            $row = [
                'item_general_id'      => $p['id_item_general'],
                'fecha'                => $fecha,
                'estado'               => $p['estado'],
                'volumen_base'         => $p['volumen_base'],
                'costo_mp_total'       => $p['costo_mp_total'],
                'costo_mp_por_unidad'  => $p['costo_mp_por_unidad'],
                'costo_empaque_mod'    => $p['costo_empaque_mod'],
                'costo_total'          => $p['costo_total'],
                'porcentaje_utilidad'  => $p['porcentaje_utilidad'],
                'precio_venta_calc'    => $p['precio_venta_calc'],
                'mps_total'            => $p['mps_total'],
                'mps_cubiertas'        => $p['mps_total'] - count($p['mps_faltantes'] ?? []),
            ];

            // Upsert: si existe el (item, fecha) → actualizar; sino → insertar
            $existe = $db->table('costos_snapshot')
                ->where('item_general_id', $row['item_general_id'])
                ->where('fecha', $fecha)
                ->countAllResults();

            if ($existe > 0) {
                $db->table('costos_snapshot')
                    ->where('item_general_id', $row['item_general_id'])
                    ->where('fecha', $fecha)
                    ->update($row);
                $actualizados++;
            } else {
                $db->table('costos_snapshot')->insert($row);
                $insertados++;
            }
        }

        CLI::write(
            sprintf('✓ Snapshot %s — %d insertado(s), %d actualizado(s), total %d producto(s)',
                $fecha, $insertados, $actualizados, count($productos)),
            'green'
        );
    }
}
