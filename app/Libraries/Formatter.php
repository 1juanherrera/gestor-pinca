<?php

namespace App\Libraries;

class Formatter
{
    /**
     * Formatea un número como moneda COP (pesos colombianos)
     */
    public static function toCOP($value)
    {
        if (!is_numeric($value)) {
            return '0';
        }

        $formateado = number_format((float) $value, 0, ',', '.');
        return $formateado;
    }

    public static function parseCOP($valor) 
    {
        $numero = str_replace(['$', '.', ' '], '', $valor);
        $numero = str_replace(',', '.', $numero);
        return (float)$numero;
    }

    public static function toThousands($number, $decimals = 2, $dec_point = '.', $thousands_sep = ',')
    {

        $number = (float) $number;
        
        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }

    public static function fromCOP($valor)
    {
        if (is_numeric($valor)) {
            return (float) $valor;
        }

        $limpio = str_replace(['$', 'COP', 'cop', ' ', '.', "\xc2\xa0"], '', trim($valor));

        $limpio = str_replace(',', '.', $limpio);

        $numero = (float) $limpio;

        return $numero;
    }
}
