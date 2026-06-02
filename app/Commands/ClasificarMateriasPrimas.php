<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SincronizacionModel;
use App\Services\ClasificadorQuimicoService;

/**
 * Clasificación química de materias primas/insumos para deduplicación.
 *
 * MODO ONLINE (requiere ANTHROPIC_API_KEY en .env):
 *   php spark sync:clasificar               # MP + Insumos
 *   php spark sync:clasificar --tipo=1      # solo Materias Primas
 *
 * MODO OFFLINE (sin API key — export/import manual):
 *   php spark sync:clasificar --offline --out=writable/sync/dataset.json
 *   ... (clasificás el JSON por fuera con una IA y armás el archivo de clusters) ...
 *   php spark sync:clasificar --in=writable/sync/clusters.json
 *
 * El JSON de clusters (--in) debe tener: {"clusters":[{identidad_quimica, nombre_base,
 * confianza, razonamiento, tipo, keep_id, items:[{item_general_id, confianza, motivo}]}]}
 */
class ClasificarMateriasPrimas extends BaseCommand
{
    protected $group       = 'Sincronizacion';
    protected $name        = 'sync:clasificar';
    protected $description = 'Clasifica MP/insumos por identidad química (IA) y guarda clusters de deduplicación.';

    public function run(array $params)
    {
        $tipo    = isset($params['tipo']) && $params['tipo'] !== '' ? (int) $params['tipo'] : null;
        $offline = array_key_exists('offline', $params);
        $out     = $params['out'] ?? ('writable/sync/dataset_' . date('YmdHis') . '.json');
        $in      = $params['in']  ?? null;

        $model = new SincronizacionModel();

        // ── Importar clusters ya clasificados (offline) ────────────────────
        if ($in !== null) {
            if (!is_file($in)) {
                CLI::error("No existe el archivo: {$in}");
                return;
            }
            $data = json_decode(file_get_contents($in), true);
            $clusters = $data['clusters'] ?? $data;
            if (!is_array($clusters)) {
                CLI::error('El archivo no contiene un array "clusters" válido.');
                return;
            }
            $res = $model->guardarSugerencias($clusters, 'OFFLINE-' . date('YmdHis'), 'offline');
            CLI::write("✓ Importados {$res['clusters_creados']} cluster(s) desde {$in}", 'green');
            return;
        }

        // ── Dataset ─────────────────────────────────────────────────────────
        $dataset = $model->datasetParaClasificacion($tipo);
        if (empty($dataset)) {
            CLI::write('No hay materias primas/insumos para clasificar.', 'yellow');
            return;
        }
        CLI::write(sprintf('Dataset: %d ítem(s) tipo %s.', count($dataset), $tipo ?? '1+2'));

        // ── Modo offline: exportar dataset y terminar ────────────────────────
        if ($offline) {
            $dir = dirname($out);
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            file_put_contents($out, json_encode($dataset, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            CLI::write("✓ Dataset exportado a {$out}", 'green');
            CLI::write('Clasificá ese JSON con una IA y reimportá con: php spark sync:clasificar --in=archivo_clusters.json', 'cyan');
            return;
        }

        // ── Modo online: clasificar con Claude y guardar ─────────────────────
        try {
            $service  = new ClasificadorQuimicoService();
            CLI::write('Clasificando con ' . $service->modelo() . ' …');
            $lote     = 'IA-' . date('YmdHis');
            $clusters = $service->clasificar($dataset);
            $res      = $model->guardarSugerencias($clusters, $lote, $service->modelo());
            CLI::write(sprintf('✓ %d cluster(s) propuesto(s) (lote %s).', $res['clusters_creados'], $lote), 'green');
        } catch (\Throwable $e) {
            CLI::error($e->getMessage());
            CLI::write('Tip: sin API key, usá el modo offline: php spark sync:clasificar --offline', 'yellow');
        }
    }
}
