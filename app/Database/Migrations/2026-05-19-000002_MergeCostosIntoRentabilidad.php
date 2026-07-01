<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Unifica el módulo 'costos' dentro de 'rentabilidad'.
 *
 * Razón: Costos era un subset puro de Rentabilidad (mismos tabs:
 * Producción, Compras, Indirectos). Rentabilidad añade Resumen + Ganancias.
 * Tener ambos era duplicación.
 *
 * Estrategia: para cualquier rol que tenía 'costos' activo, garantizar
 * que también tenga 'rentabilidad' activo. Luego eliminar todas las
 * filas con modulo='costos'.
 */
class MergeCostosIntoRentabilidad extends Migration
{
    public function up()
    {
        // 1. Por cada rol que tenía 'costos' activo, activar 'rentabilidad'
        //    si no la tenía (o crearla si no existía la fila).
        $rolesConCostos = $this->db->table('permisos_rol_modulo')
            ->select('rol')
            ->where('modulo', 'costos')
            ->where('activo', 1)
            ->get()->getResultArray();

        foreach ($rolesConCostos as $r) {
            $rol = $r['rol'];

            $existeRentabilidad = $this->db->table('permisos_rol_modulo')
                ->where('rol', $rol)
                ->where('modulo', 'rentabilidad')
                ->countAllResults();

            if ($existeRentabilidad > 0) {
                $this->db->table('permisos_rol_modulo')
                    ->where('rol', $rol)
                    ->where('modulo', 'rentabilidad')
                    ->update(['activo' => 1]);
            } else {
                $this->db->table('permisos_rol_modulo')->insert([
                    'rol'    => $rol,
                    'modulo' => 'rentabilidad',
                    'activo' => 1,
                ]);
            }
        }

        // 2. Eliminar todas las filas con modulo='costos' (activas o no).
        $this->db->table('permisos_rol_modulo')
            ->where('modulo', 'costos')
            ->delete();
    }

    public function down()
    {
        // Restituir filas 'costos' (inactivas por defecto — no recuperamos el estado original).
        $roles = $this->db->table('permisos_rol_modulo')
            ->select('rol')
            ->distinct()
            ->get()->getResultArray();

        foreach ($roles as $r) {
            $exists = $this->db->table('permisos_rol_modulo')
                ->where('rol', $r['rol'])
                ->where('modulo', 'costos')
                ->countAllResults();
            if ($exists === 0) {
                $this->db->table('permisos_rol_modulo')->insert([
                    'rol'    => $r['rol'],
                    'modulo' => 'costos',
                    'activo' => 0,
                ]);
            }
        }
    }
}
