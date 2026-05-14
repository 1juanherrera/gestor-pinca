<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\ConfiguracionModel;

/**
 * Seed inicial del grupo `tributaria`.
 *
 * Idempotente: usa `seedIfMissing()` — si la clave ya existe (admin la modificó)
 * NO la sobreescribe.
 */
class SeedConfiguracionTributaria extends Migration
{
    public function up()
    {
        $cfg = new ConfiguracionModel();

        $cfg->seedIfMissing('tributaria', 'iva_default',             19,    'number',  'Porcentaje IVA general (%) aplicado por defecto en facturas y compras.');
        $cfg->seedIfMissing('tributaria', 'retencion_fuente_pct',    2.5,   'number',  'Retención en la fuente por compra (%) — varía por concepto y régimen.');
        $cfg->seedIfMissing('tributaria', 'retencion_iva_pct',       15,    'number',  'ReteIVA: porcentaje del IVA pagado que se retiene al proveedor (%).');
        $cfg->seedIfMissing('tributaria', 'retencion_ica_default',   11.04, 'number',  'ReteICA por mil — default Barranquilla. Ajustar por ciudad/actividad.');
        $cfg->seedIfMissing('tributaria', 'aplicar_iva_por_default', true,  'boolean', 'Si true, los formularios de compra activan el toggle IVA al abrirse.');
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('configuracion_sistema')
           ->where('grupo', 'tributaria')
           ->whereIn('clave', [
               'iva_default',
               'retencion_fuente_pct',
               'retencion_iva_pct',
               'retencion_ica_default',
               'aplicar_iva_por_default',
           ])
           ->delete();
    }
}
