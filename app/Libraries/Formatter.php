<?php

namespace App\Libraries;

class Formatter
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

    public static function ruleOfThree($cantidadOriginal, $volumenNuevo, $volumenBase, $decimales = 2)
    {
        $cantidad = (float) str_replace(',', '.', $cantidadOriginal);
        $volumenNuevo = (float) str_replace(',', '.', $volumenNuevo);
        $volumenBase  = (float) str_replace(',', '.', $volumenBase);

        if ($volumenBase <= 0 || $volumenNuevo <= 0) {
            return round(0, $decimales);
        }

        $factor = $volumenNuevo / $volumenBase;
        $resultado = $factor * $cantidad;

        return round($resultado, $decimales);
    }
}
