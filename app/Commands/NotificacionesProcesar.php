<?php

namespace App\Commands;

use App\Models\NotificacionModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Comando programado: genera notificaciones que requieren agendamiento
 * (no las que se disparan en runtime con un evento).
 *
 * Ejecutar:
 *   php spark notificaciones:procesar
 *
 * Ideal: agregar al crontab del container 1 vez al día (ej: 06:00).
 *   0 6 * * * cd /var/www/html && php spark notificaciones:procesar >> /var/log/notif.log 2>&1
 *
 * Cubre:
 *   - Facturas que vencen en los próximos 3 días (rol admin)
 *   - OCs en estado 'Enviada' con fecha_esperada < hoy (rol admin)
 *
 * Idempotente: usa `dedup_key` con la fecha del día, así no duplica si
 * el cron corre 2 veces.
 */
class NotificacionesProcesar extends BaseCommand
{
    protected $group       = 'Notificaciones';
    protected $name        = 'notificaciones:procesar';
    protected $description = 'Genera notificaciones agendadas (vencimientos, OCs retrasadas).';

    public function run(array $params)
    {
        $db    = \Config\Database::connect();
        $notif = new NotificacionModel();
        $hoy   = date('Y-m-d');

        // ─── 1. Facturas próximas a vencer (1-3 días) ──────────────────────
        $facturas = $db->query("
            SELECT id_facturas, numero, fecha_vencimiento, total, saldo_pendiente,
                   DATEDIFF(fecha_vencimiento, CURDATE()) AS dias
            FROM facturas
            WHERE estado IN ('Pendiente', 'Parcial')
              AND saldo_pendiente > 0
              AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
        ")->getResultArray();

        $contFact = 0;
        foreach ($facturas as $f) {
            $dias = (int) $f['dias'];
            $msg  = $dias === 0
                ? "Vence HOY ({$f['numero']}, saldo $" . number_format($f['saldo_pendiente'], 0, ',', '.') . ')'
                : "Vence en {$dias} día(s): {$f['numero']}, saldo $" . number_format($f['saldo_pendiente'], 0, ',', '.');

            $created = $notif->crear([
                'tipo'       => NotificacionModel::TIPO_FACTURA_VENCIMIENTO,
                'titulo'     => 'Factura próxima a vencer',
                'mensaje'    => $msg,
                'rol_target' => 'admin',
                'link'       => '/cartera',
                'metadata'   => [
                    'factura_id'        => (int) $f['id_facturas'],
                    'numero'            => $f['numero'],
                    'fecha_vencimiento' => $f['fecha_vencimiento'],
                    'saldo_pendiente'   => (float) $f['saldo_pendiente'],
                    'dias'              => $dias,
                ],
                'dedup_key'  => "factura-venc-{$f['id_facturas']}-{$hoy}",
            ]);
            if ($created) $contFact++;
        }

        // ─── 2. OCs retrasadas vs fecha_esperada ───────────────────────────
        $ocs = $db->query("
            SELECT id_orden, numero, fecha_esperada, total, proveedor_id,
                   DATEDIFF(CURDATE(), fecha_esperada) AS dias_retraso
            FROM ordenes_compra
            WHERE estado = 'Enviada'
              AND fecha_esperada IS NOT NULL
              AND fecha_esperada < CURDATE()
        ")->getResultArray();

        $contOc = 0;
        foreach ($ocs as $oc) {
            $dias = (int) $oc['dias_retraso'];
            $created = $notif->crear([
                'tipo'       => NotificacionModel::TIPO_OC_RETRASADA,
                'titulo'     => "OC retrasada {$dias} día(s)",
                'mensaje'    => "{$oc['numero']} no fue recibida (esperada {$oc['fecha_esperada']}).",
                'rol_target' => 'admin',
                'link'       => '/compras',
                'metadata'   => [
                    'oc_id'          => (int) $oc['id_orden'],
                    'numero'         => $oc['numero'],
                    'fecha_esperada' => $oc['fecha_esperada'],
                    'dias_retraso'   => $dias,
                    'total'          => (float) $oc['total'],
                ],
                'dedup_key'  => "oc-retrasada-{$oc['id_orden']}-{$hoy}",
            ]);
            if ($created) $contOc++;
        }

        CLI::write("✓ Facturas próximas a vencer: {$contFact} notificaciones nuevas", 'green');
        CLI::write("✓ OCs retrasadas: {$contOc} notificaciones nuevas", 'green');
        CLI::write("Procesado: " . date('Y-m-d H:i:s'), 'light_gray');
    }
}
