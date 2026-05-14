<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabla `numeracion_documentos` — control centralizado de numeración correlativa.
 *
 * Cada `tipo_doc` tiene una serie activa con su prefijo, padding y, opcionalmente,
 * resolución DIAN con rango min/max y vigencia.
 *
 * Después de crear la tabla y los seeds, sincroniza `proximo_numero` con los
 * datos existentes para no romper la numeración actual (lee MAX del último
 * número emitido por tabla).
 */
class CreateNumeracionDocumentos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_numeracion'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tipo_doc'             => ['type' => 'VARCHAR', 'constraint' => 30],
            'prefijo'              => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => ''],
            'padding'              => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 4],
            'proximo_numero'       => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'anio_actual'          => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'reinicia_anual'       => ['type' => 'TINYINT', 'default' => 1],
            'resolucion_dian'      => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'fecha_resolucion'     => ['type' => 'DATE', 'null' => true],
            'rango_min'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'rango_max'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'fecha_vigencia_hasta' => ['type' => 'DATE', 'null' => true],
            'activo'               => ['type' => 'TINYINT', 'default' => 1],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_by'           => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id_numeracion');
        $this->forge->addKey(['tipo_doc', 'activo']);
        $this->forge->createTable('numeracion_documentos');

        // ── Seeds: una serie activa por tipo ─────────────────────────────────
        $now = date('Y-m-d H:i:s');
        $year = (int) date('Y');

        $seeds = [
            ['tipo_doc' => 'factura',       'prefijo' => 'FAC-{Y}-', 'padding' => 4, 'reinicia_anual' => 1, 'tabla_origen' => 'facturas',       'col_id' => 'id_facturas'],
            ['tipo_doc' => 'cotizacion',    'prefijo' => 'COT-{Y}-', 'padding' => 4, 'reinicia_anual' => 1, 'tabla_origen' => 'cotizaciones',   'col_id' => 'id_cotizaciones'],
            ['tipo_doc' => 'remision',      'prefijo' => 'REM-{Y}-', 'padding' => 4, 'reinicia_anual' => 1, 'tabla_origen' => 'remisiones',     'col_id' => 'id_remisiones'],
            ['tipo_doc' => 'orden_compra',  'prefijo' => 'OC-',      'padding' => 3, 'reinicia_anual' => 0, 'tabla_origen' => 'ordenes_compra', 'col_id' => 'id_orden'],
            ['tipo_doc' => 'nota_credito',  'prefijo' => 'NC-',      'padding' => 3, 'reinicia_anual' => 0, 'tabla_origen' => 'notas_credito',  'col_id' => 'id_nota_credito'],
        ];

        foreach ($seeds as $s) {
            $proximo = $this->detectarProximoNumero($s['tabla_origen'], $s['prefijo'], $s['reinicia_anual'], $year);

            $this->db->table('numeracion_documentos')->insert([
                'tipo_doc'       => $s['tipo_doc'],
                'prefijo'        => $s['prefijo'],
                'padding'        => $s['padding'],
                'proximo_numero' => $proximo,
                'anio_actual'    => $s['reinicia_anual'] ? $year : null,
                'reinicia_anual' => $s['reinicia_anual'],
                'activo'         => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
                'updated_by'     => 'migration',
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropTable('numeracion_documentos');
    }

    /**
     * Lee la tabla origen y calcula el siguiente número correlativo,
     * para que la migración no resetee la numeración existente.
     *
     * - Series con {Y}: filtra por año actual y extrae el último segmento numérico.
     * - Series sin {Y}: extrae el sufijo numérico del prefijo fijo.
     */
    private function detectarProximoNumero(string $tabla, string $prefijoTpl, int $reiniciaAnual, int $year): int
    {
        if (!$this->db->tableExists($tabla)) return 1;

        $prefijo = str_replace('{Y}', (string) $year, $prefijoTpl);

        $row = $this->db->table($tabla)
            ->select('numero')
            ->like('numero', $prefijo, 'after')
            ->orderBy('numero', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$row || empty($row['numero'])) return 1;

        // Sacar el último segmento numérico
        $partes = explode('-', $row['numero']);
        $seq    = (int) end($partes);

        return $seq > 0 ? $seq + 1 : 1;
    }
}
