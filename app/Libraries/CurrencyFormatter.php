<?php

namespace App\Libraries;

class CurrencyFormatter
{
    /**
     * Formatea un número como moneda COP (pesos colombianos)
     */
    public static function toCOP($value, $conSimbolo = true)
    {
        if (!is_numeric($value)) {
            return $conSimbolo ? '$0' : '0';
        }

        $formateado = number_format((float) $value, 2, ',', '.');
        return $conSimbolo ? '$' . $formateado : $formateado;
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
}
