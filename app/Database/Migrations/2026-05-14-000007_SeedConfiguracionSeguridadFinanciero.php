<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use App\Models\ConfiguracionModel;

/**
 * Seeds para los grupos `seguridad`, `financiero` y `comercial` —
 * sacando del código valores que estaban hardcodeados.
 */
class SeedConfiguracionSeguridadFinanciero extends Migration
{
    public function up()
    {
        $cfg = new ConfiguracionModel();

        // ── Seguridad ───────────────────────────────────────────────────────
        $cfg->seedIfMissing('seguridad', 'jwt_expiracion_horas',         8,    'number',
            'Horas de validez del JWT desde su emisión. Tras este tiempo el usuario debe re-loguearse.');
        $cfg->seedIfMissing('seguridad', 'max_intentos_login',           5,    'number',
            'Cantidad máxima de intentos de login fallidos antes de bloquear la IP temporalmente.');
        $cfg->seedIfMissing('seguridad', 'ventana_intentos_segundos',    900,  'number',
            'Ventana en segundos durante la cual se cuentan los intentos fallidos (default 900 = 15 min).');
        $cfg->seedIfMissing('seguridad', 'password_min_caracteres',      8,    'number',
            'Longitud mínima requerida para contraseñas nuevas.');

        // ── Financiero ──────────────────────────────────────────────────────
        $cfg->seedIfMissing('financiero', 'margen_utilidad_default_pct', 50,   'number',
            'Margen de utilidad por defecto (%) cuando un costo no tiene `porcentaje_utilidad` explícito.');

        // ── Comercial ───────────────────────────────────────────────────────
        $cfg->seedIfMissing('comercial', 'dias_vencimiento_factura',     30,   'number',
            'Días desde la emisión hasta el vencimiento por default al crear/convertir una factura.');
        $cfg->seedIfMissing('comercial', 'dias_credito_default',         30,   'number',
            'Plazo de pago por default sugerido al crear un cliente nuevo.');

        // ── Notificaciones ──────────────────────────────────────────────────
        $cfg->seedIfMissing('notificaciones', 'limit_default',           30,   'number',
            'Cantidad de notificaciones a devolver por defecto en la query.');
        $cfg->seedIfMissing('notificaciones', 'limit_maximo',            100,  'number',
            'Tope superior absoluto para evitar payloads excesivos.');
        $cfg->seedIfMissing('notificaciones', 'dias_alerta_vencimiento', 3,    'number',
            'Días previos al vencimiento de una factura para empezar a notificar.');

        // ── Paginación general ──────────────────────────────────────────────
        $cfg->seedIfMissing('paginacion', 'page_size_default',           25,   'number',
            'Cantidad de filas por página default en tablas listables.');
        $cfg->seedIfMissing('paginacion', 'max_per_page',                200,  'number',
            'Tope máximo permitido para `?per_page=` en endpoints paginados.');
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->table('configuracion_sistema')
           ->whereIn('grupo', ['seguridad', 'financiero', 'comercial', 'notificaciones', 'paginacion'])
           ->delete();
    }
}
