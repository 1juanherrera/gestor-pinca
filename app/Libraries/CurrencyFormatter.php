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
        // Elimina el símbolo $ y separadores de miles, cambia coma por punto
        $numero = str_replace(['$', '.', ' '], '', $valor);
        $numero = str_replace(',', '.', $numero);
        return (float)$numero;
    }
}
