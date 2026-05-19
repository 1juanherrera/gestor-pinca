<?php

namespace App\Helpers;

use App\Models\ConfiguracionModel;

/**
 * Helper estático con cache por request para leer valores de
 * `configuracion_sistema` sin pegarle a la DB cada vez.
 *
 * Uso típico:
 *   use App\Helpers\Cfg;
 *   $margen = Cfg::n('margen_utilidad_default_pct', 50);
 *   $diasVenc = Cfg::n('dias_vencimiento_factura', 30);
 */
class Cfg
{
    private static array $cache = [];
    private static ?ConfiguracionModel $model = null;

    /** Lee como número (cast int o float según el default). */
    public static function n(string $clave, $default = 0)
    {
        if (!array_key_exists($clave, self::$cache)) {
            self::$cache[$clave] = self::model()->obtener($clave, $default);
        }
        $v = self::$cache[$clave];
        return is_int($default) ? (int) $v : (float) $v;
    }

    /** Lee como string. */
    public static function s(string $clave, string $default = ''): string
    {
        if (!array_key_exists($clave, self::$cache)) {
            self::$cache[$clave] = self::model()->obtener($clave, $default);
        }
        return (string) self::$cache[$clave];
    }

    /** Lee como booleano. */
    public static function b(string $clave, bool $default = false): bool
    {
        if (!array_key_exists($clave, self::$cache)) {
            self::$cache[$clave] = self::model()->obtener($clave, $default);
        }
        return (bool) self::$cache[$clave];
    }

    /** Invalida el cache (llamar tras un cambio de configuración). */
    public static function flush(): void { self::$cache = []; }

    private static function model(): ConfiguracionModel
    {
        return self::$model ??= new ConfiguracionModel();
    }
}
