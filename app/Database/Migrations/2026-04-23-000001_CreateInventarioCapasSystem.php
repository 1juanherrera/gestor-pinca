<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventarioCapasSystem extends Migration
{
    public function up()
    {
        // 1. Tabla de capas de inventario (si no existe — pudo haberse creado manualmente)
        if (!$this->db->tableExists('inventario_capas')) {
            $this->forge->addField([
                'id_capa'             => ['type' => 'INT', 'auto_increment' => true],
                'item_general_id'     => ['type' => 'INT', 'null' => false],
                'bodegas_id'          => ['type' => 'INT', 'null' => false],
                'proveedor_id'        => ['type' => 'INT', 'null' => true],
                'item_proveedor_id'   => ['type' => 'INT', 'null' => true],
                'orden_compra_id'     => ['type' => 'INT', 'null' => true],
                'cantidad_original'   => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
                'cantidad_disponible' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
                'costo_unitario'      => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
                'unidad_compra_id'    => ['type' => 'INT', 'null' => true],
                'factor_conversion'   => ['type' => 'DECIMAL', 'constraint' => '15,6', 'default' => 1],
                'precio_compra'       => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => true],
                'fecha_ingreso'       => ['type' => 'DATETIME', 'null' => false],
                'lote_proveedor'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'observaciones'       => ['type' => 'TEXT', 'null' => true],
                'estado'              => ['type' => 'TINYINT', 'default' => 1],
            ]);
            $this->forge->addKey('id_capa', true);
            $this->forge->addKey(['item_general_id', 'bodegas_id', 'estado'], false, false, 'idx_item_bodega');
            $this->forge->addKey('proveedor_id', false, false, 'idx_proveedor');
            $this->forge->addKey('fecha_ingreso', false, false, 'idx_fecha');
            $this->forge->addForeignKey('item_general_id', 'item_general', 'id_item_general');
            $this->forge->addForeignKey('bodegas_id', 'bodegas', 'id_bodegas');
            $this->forge->createTable('inventario_capas');
        }

        // 2. Tabla de consumo por producción (si no existe)
        if (!$this->db->tableExists('preparacion_consumo_capas')) {
            $this->forge->addField([
                'id'                  => ['type' => 'INT', 'auto_increment' => true],
                'preparacion_id'      => ['type' => 'INT', 'null' => false],
                'capa_id'             => ['type' => 'INT', 'null' => false],
                'item_general_id'     => ['type' => 'INT', 'null' => false],
                'cantidad_consumida'  => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
                'costo_unitario'      => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
                'costo_total'         => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => false],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('preparacion_id', false, false, 'idx_preparacion');
            $this->forge->addKey('capa_id', false, false, 'idx_capa');
            $this->forge->addForeignKey('preparacion_id', 'preparaciones', 'id_preparaciones');
            $this->forge->addForeignKey('capa_id', 'inventario_capas', 'id_capa');
            $this->forge->createTable('preparacion_consumo_capas');
        }

        // 3. Migrar saldos actuales como capas "legacy"
        $this->db->query("
            INSERT INTO inventario_capas
                (item_general_id, bodegas_id, cantidad_original, cantidad_disponible,
                 costo_unitario, fecha_ingreso, observaciones, estado)
            SELECT
                i.item_general_id, i.bodegas_id, i.cantidad, i.cantidad,
                COALESCE(ci.costo_unitario, 0), NOW(),
                'Migración: saldo existente sin proveedor identificado', 1
            FROM inventario i
            LEFT JOIN costos_item ci ON ci.item_general_id = i.item_general_id
            WHERE i.cantidad > 0
            AND NOT EXISTS (
                SELECT 1 FROM inventario_capas ic
                WHERE ic.item_general_id = i.item_general_id
                AND ic.bodegas_id = i.bodegas_id
                AND ic.observaciones LIKE 'Migración:%'
            )
        ");
    }

    public function down()
    {
        $this->forge->dropTable('preparacion_consumo_capas', true);
        $this->forge->dropTable('inventario_capas', true);
    }
}
