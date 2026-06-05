<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Amplía las columnas de "ficha técnica" de item_general, que estaban con
 * tamaños diminutos heredados (ph varchar(1), color varchar(3), molienda
 * varchar(5)...). Con MySQL en modo estricto, editar/crear un ítem con valores
 * más largos ("8.5 - 9.0", "Blanco Nieve", "6 Hegman") lanzaba
 * "Data too long for column" — el error que el usuario veía al editar una
 * materia prima desde Catálogo (y confundía con un error de constraint/cascade).
 *
 * Las amplía a varchar(50), suficiente para cualquier especificación textual.
 */
class WidenItemGeneralFichaTecnica extends Migration
{
    /** columna => tamaño viejo (para el down) */
    private array $cols = [
        'viscosidad'     => 10,
        'p_g'            => 14,
        'color'          => 3,
        'brillo_60'      => 6,
        'secado'         => 8,
        'cubrimiento'    => 9,
        'molienda'       => 5,
        'ph'             => 1,
        'poder_tintoreo' => 13,
    ];

    public function up()
    {
        $fields = [];
        foreach (array_keys($this->cols) as $col) {
            $fields[$col] = ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true];
        }
        $this->forge->modifyColumn('item_general', $fields);
    }

    public function down()
    {
        $fields = [];
        foreach ($this->cols as $col => $size) {
            $fields[$col] = ['type' => 'VARCHAR', 'constraint' => $size, 'null' => true];
        }
        $this->forge->modifyColumn('item_general', $fields);
    }
}
