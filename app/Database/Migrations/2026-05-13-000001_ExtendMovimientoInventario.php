<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Extiende movimiento_inventario con todas las columnas necesarias para
 * funcionar como audit log de inventario:
 *  - FK a item_general y bodega
 *  - Snapshot de saldos antes/después
 *  - Costo unitario al momento del movimiento
 *  - Referencia a documento origen (id + tipo)
 *  - Responsable (usuario que originó el evento)
 *  - metadata JSON para diff completo (precios, factores, lote, etc.)
 *  - timestamps de creación
 *
 * Convierte fecha_movimiento de DATE a DATETIME para registrar hora exacta.
 */
class ExtendMovimientoInventario extends Migration
{
    public function up()
    {
        $tabla   = 'movimiento_inventario';
        $exists  = fn($col) => $this->db->fieldExists($col, $tabla);

        $fields = [];

        if (!$exists('item_general_id')) {
            $fields['item_general_id'] = ['type' => 'INT', 'null' => true, 'after' => 'referencia_tipo'];
        }
        if (!$exists('bodega_id')) {
            $fields['bodega_id'] = ['type' => 'INT', 'null' => true];
        }
        if (!$exists('referencia_id')) {
            $fields['referencia_id'] = ['type' => 'INT', 'null' => true];
        }
        if (!$exists('costo_unitario')) {
            $fields['costo_unitario'] = ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => true];
        }
        if (!$exists('saldo_anterior')) {
            $fields['saldo_anterior'] = ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => true];
        }
        if (!$exists('saldo_nuevo')) {
            $fields['saldo_nuevo'] = ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => true];
        }
        if (!$exists('responsable')) {
            $fields['responsable'] = ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true];
        }
        if (!$exists('metadata')) {
            // JSON con detalle adicional del evento (precios antes/después, lote, factor, etc.)
            $fields['metadata'] = ['type' => 'JSON', 'null' => true];
        }
        if (!$exists('created_at')) {
            $fields['created_at'] = ['type' => 'DATETIME', 'null' => true];
        }

        if (!empty($fields)) {
            $this->forge->addColumn($tabla, $fields);
        }

        // Cambiar fecha_movimiento DATE → DATETIME (preserva valores)
        $this->forge->modifyColumn($tabla, [
            'fecha_movimiento' => ['type' => 'DATETIME', 'null' => true],
        ]);

        // Ampliar descripcion (100 → 255) por si los logs traen detalle largo
        $this->forge->modifyColumn($tabla, [
            'descripcion' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);

        // Índices para queries frecuentes (MySQL 8 no soporta CREATE INDEX IF NOT EXISTS)
        $this->crearIndiceSiNoExiste($tabla, 'idx_mov_item',   'item_general_id');
        $this->crearIndiceSiNoExiste($tabla, 'idx_mov_bodega', 'bodega_id');
        $this->crearIndiceSiNoExiste($tabla, 'idx_mov_ref',    'referencia_tipo, referencia_id');
        $this->crearIndiceSiNoExiste($tabla, 'idx_mov_fecha',  'fecha_movimiento');
        $this->crearIndiceSiNoExiste($tabla, 'idx_mov_tipo',   'tipo_movimiento');
    }

    private function crearIndiceSiNoExiste(string $tabla, string $nombre, string $columnas): void
    {
        $existe = $this->db->query("SHOW INDEX FROM {$tabla} WHERE Key_name = ?", [$nombre])->getRow();
        if (!$existe) {
            $this->db->query("CREATE INDEX {$nombre} ON {$tabla} ({$columnas})");
        }
    }

    public function down()
    {
        $tabla = 'movimiento_inventario';

        $this->dropIndiceSiExiste($tabla, 'idx_mov_item');
        $this->dropIndiceSiExiste($tabla, 'idx_mov_bodega');
        $this->dropIndiceSiExiste($tabla, 'idx_mov_ref');
        $this->dropIndiceSiExiste($tabla, 'idx_mov_fecha');
        $this->dropIndiceSiExiste($tabla, 'idx_mov_tipo');

        $cols = ['item_general_id','bodega_id','referencia_id','costo_unitario',
                 'saldo_anterior','saldo_nuevo','responsable','metadata','created_at'];
        foreach ($cols as $c) {
            if ($this->db->fieldExists($c, $tabla)) {
                $this->forge->dropColumn($tabla, $c);
            }
        }
    }

    private function dropIndiceSiExiste(string $tabla, string $indexName): void
    {
        $existsResult = $this->db->query(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
            [$tabla, $indexName]
        )->getRow();
        if ($existsResult && (int) $existsResult->c > 0) {
            $this->db->query("ALTER TABLE {$tabla} DROP INDEX {$indexName}");
        }
    }
}
