<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioModel extends Model
{
    protected $table      = 'inventario';
    protected $primaryKey = 'id_inventario';

    public function __construct(){
        parent::__construct();
    }

    public function traspaso($data)
    {
        $itemId   = (int)$data['item_id'];
        $origen   = (int)$data['bodega_origen_id'];
        $destino  = (int)$data['bodega_destino_id'];
        $cantidad = (float)$data['cantidad'];

        $this->db->transStart();

        // 1. VALIDACIÓN: Verificar que el origen tenga suficiente stock
        $stockOrigen = $this->db->table('inventario')
            ->where(['item_general_id' => $itemId, 'bodegas_id' => $origen])
            ->get()->getRow();

        if (!$stockOrigen || $stockOrigen->cantidad < $cantidad) {
            $this->db->transRollback();
            return false; 
        }

        // 2. RESTAR de la bodega origen
        $this->db->table('inventario')
            ->where(['item_general_id' => $itemId, 'bodegas_id' => $origen])
            ->set('cantidad', "cantidad - $cantidad", false)
            ->update();

        // 3. SUMAR o INSERTAR en la bodega destino
        $checkDestino = $this->db->table('inventario')
            ->where(['item_general_id' => $itemId, 'bodegas_id' => $destino])
            ->get()->getRow();

        if ($checkDestino) {
            $this->db->table('inventario')
                ->where(['item_general_id' => $itemId, 'bodegas_id' => $destino])
                ->set('cantidad', "cantidad + $cantidad", false)
                ->update();
        } else {
            $this->db->table('inventario')->insert([
                'item_general_id' => $itemId,
                'bodegas_id'      => $destino,
                'cantidad'        => $cantidad,
                'estado'          => 1,
                'tipo'            => 1
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }
}