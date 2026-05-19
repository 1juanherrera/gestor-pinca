<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Setea un logo default si la empresa todavía no tiene `logo_path` definido.
 * Asume que existe el archivo en `public/uploads/empresa/logo_default.png`
 * (se copia manualmente desde `pinca_frontend/src/assets/pincaicono.png`).
 */
class SeedDefaultLogo extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('empresa')) return;

        $row = $this->db->table('empresa')->where('id_empresa', 1)->get()->getRowArray();
        if (!$row) return;

        if (empty($row['logo_path'])) {
            $this->db->table('empresa')
                ->where('id_empresa', 1)
                ->update(['logo_path' => '/uploads/empresa/logo_default.png']);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('empresa')) return;
        $this->db->table('empresa')
            ->where('id_empresa', 1)
            ->where('logo_path', '/uploads/empresa/logo_default.png')
            ->update(['logo_path' => null]);
    }
}
