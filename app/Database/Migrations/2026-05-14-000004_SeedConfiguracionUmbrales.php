<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\ConfiguracionModel;

/**
 * Seeds del grupo `umbrales` — días/porcentajes que disparan alertas en
 * dashboard, inventario y cartera. Hoy están hardcodeados; admin podrá
 * ajustarlos sin tocar código.
 */
class SeedConfiguracionUmbrales extends Migration
{
    public function up()
    {
        $cfg = new ConfiguracionModel();

        // Stock
        $cfg->seedIfMissing('umbrales', 'stock_critico_dias', 7,  'number',
            'Días restantes de stock para considerar una MP "crítica" (rojo en dashboard / inventario).');
        $cfg->seedIfMissing('umbrales', 'stock_warning_dias', 30, 'number',
            'Días restantes para "advertencia" (amarillo). Por encima → "ok" (verde).');

        // Cartera
        $cfg->seedIfMissing('umbrales', 'mora_warning_dias', 30, 'number',
            'Días de mora desde los cuales una factura entra en alerta amarilla.');
        $cfg->seedIfMissing('umbrales', 'mora_critica_dias', 60, 'number',
            'Días de mora desde los cuales una factura entra en alerta roja (crítica).');

        // Rentabilidad
        $cfg->seedIfMissing('umbrales', 'margen_minimo_pct',   10, 'number',
            'Margen (%) por debajo del cual el dashboard marca rentabilidad en rojo.');
        $cfg->seedIfMissing('umbrales', 'margen_objetivo_pct', 20, 'number',
            'Margen (%) objetivo: por encima la rentabilidad se muestra en verde.');
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('configuracion_sistema')
           ->where('grupo', 'umbrales')
           ->whereIn('clave', [
               'stock_critico_dias', 'stock_warning_dias',
               'mora_warning_dias',  'mora_critica_dias',
               'margen_minimo_pct',  'margen_objetivo_pct',
           ])
           ->delete();
    }
}
