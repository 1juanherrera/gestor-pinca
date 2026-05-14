<?php
namespace App\Models;

/**
 * NumeracionModel — control centralizado de numeración correlativa.
 *
 * Cada `tipo_doc` (factura, cotizacion, remision, orden_compra, nota_credito)
 * tiene UNA serie activa con su prefijo, padding y, opcionalmente, datos
 * de resolución DIAN (rango min/max y vigencia).
 *
 * Método principal: `reservar(string $tipoDoc): string`
 *   - Atómico vía transacción + SELECT … FOR UPDATE
 *   - Maneja reset anual automático si `reinicia_anual = 1`
 *   - Valida rango_max DIAN si está configurado (lanza Exception si excede)
 *   - Devuelve el número ya formateado: "FAC-2026-0001" / "OC-022"
 */
class NumeracionModel extends BaseModel
{
    protected $table         = 'numeracion_documentos';
    protected $primaryKey    = 'id_numeracion';
    protected $allowedFields = [
        'tipo_doc', 'prefijo', 'padding', 'proximo_numero', 'anio_actual',
        'reinicia_anual', 'resolucion_dian', 'fecha_resolucion',
        'rango_min', 'rango_max', 'fecha_vigencia_hasta', 'activo',
        'created_at', 'updated_at', 'updated_by',
    ];

    /**
     * Reserva (incrementa) y devuelve el siguiente número correlativo
     * formateado según el prefijo y padding de la serie activa.
     *
     * @throws \Exception si no hay serie activa, si excede el rango DIAN,
     *                    o si la resolución venció.
     */
    public function reservar(string $tipoDoc): string
    {
        $db   = $this->db;
        $year = (int) date('Y');
        $hoy  = date('Y-m-d');

        $db->transBegin();

        try {
            // Lock pesimista sobre la serie activa
            $row = $db->query(
                "SELECT * FROM numeracion_documentos
                 WHERE tipo_doc = ? AND activo = 1
                 LIMIT 1 FOR UPDATE",
                [$tipoDoc]
            )->getRowArray();

            if (!$row) {
                throw new \Exception("No hay serie activa para '{$tipoDoc}'.");
            }

            // Vigencia DIAN
            if (!empty($row['fecha_vigencia_hasta']) && $row['fecha_vigencia_hasta'] < $hoy) {
                throw new \Exception(
                    "La resolución DIAN del tipo '{$tipoDoc}' venció el {$row['fecha_vigencia_hasta']}. " .
                    "Cargá una nueva resolución antes de continuar."
                );
            }

            $proximoNumero = (int) $row['proximo_numero'];
            $anioActual    = $row['anio_actual'] !== null ? (int) $row['anio_actual'] : null;
            $reinicia      = (int) $row['reinicia_anual'] === 1;

            // Reset anual si corresponde
            if ($reinicia && $anioActual !== null && $year > $anioActual) {
                $proximoNumero = 1;
                $anioActual    = $year;
            }

            // Validación contra rango DIAN
            if (!empty($row['rango_max']) && $proximoNumero > (int) $row['rango_max']) {
                throw new \Exception(
                    "El próximo número ({$proximoNumero}) excede el rango DIAN autorizado ({$row['rango_max']}). " .
                    "Cargá una nueva resolución para '{$tipoDoc}'."
                );
            }

            $numeroFormateado = $this->formatear($row['prefijo'], (int) $row['padding'], $proximoNumero);

            // Persistir el incremento
            $update = [
                'proximo_numero' => $proximoNumero + 1,
                'updated_at'     => date('Y-m-d H:i:s'),
            ];
            if ($reinicia) $update['anio_actual'] = $anioActual;

            $db->table('numeracion_documentos')
               ->where('id_numeracion', $row['id_numeracion'])
               ->update($update);

            $db->transCommit();

            return $numeroFormateado;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Devuelve cuántos folios quedan en la serie DIAN activa (null si no hay rango).
     */
    public function foliosRestantes(string $tipoDoc): ?int
    {
        $row = $this->where('tipo_doc', $tipoDoc)->where('activo', 1)->first();
        if (!$row || empty($row['rango_max'])) return null;
        return max(0, (int) $row['rango_max'] - (int) $row['proximo_numero'] + 1);
    }

    /**
     * Reemplaza placeholders y arma el número final.
     */
    public function formatear(string $prefijoTpl, int $padding, int $seq): string
    {
        $prefijo = str_replace('{Y}', (string) date('Y'), $prefijoTpl);
        return $prefijo . str_pad((string) $seq, $padding, '0', STR_PAD_LEFT);
    }
}
