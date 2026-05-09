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

        $this->db->transBegin();

        $stockOrigen = $this->db->table('inventario')
            ->where(['item_general_id' => $itemId, 'bodegas_id' => $origen])
            ->get()->getRow();

        if (!$stockOrigen || $stockOrigen->cantidad < $cantidad) {
            $this->db->transRollback();
            return false;
        }

        $this->db->table('inventario')
            ->where(['item_general_id' => $itemId, 'bodegas_id' => $origen])
            ->set('cantidad', "cantidad - $cantidad", false)
            ->update();

        $this->db->table('inventario')
            ->where(['item_general_id' => $itemId, 'bodegas_id' => $origen])
            ->where('cantidad', 0)
            ->delete();

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

        $this->db->transCommit();
        return $this->db->transStatus();
    }

    public function removeFromBodega(int $itemId, int $bodegaId): bool
    {
        $affected = $this->db->table('inventario')
            ->where([
                'item_general_id' => $itemId,
                'bodegas_id'      => $bodegaId,
            ])
            ->delete();

        return $affected > 0;
    }

    public function ingresarABodega(int $itemGeneralId, int $bodegaId, float $cantidad): bool
    {
        $this->db->transStart();

        $existe = $this->db->table('inventario')
            ->where(['item_general_id' => $itemGeneralId, 'bodegas_id' => $bodegaId])
            ->get()->getRow();

        if ($existe) {
            $this->db->table('inventario')
                ->where(['item_general_id' => $itemGeneralId, 'bodegas_id' => $bodegaId])
                ->set('cantidad', "cantidad + $cantidad", false)
                ->update();
        } else {
            $this->db->table('inventario')->insert([
                'item_general_id' => $itemGeneralId,
                'bodegas_id'      => $bodegaId,
                'cantidad'        => $cantidad,
                'estado'          => 1,
                'tipo'            => 1,
                'fecha_update'    => date('Y-m-d'),
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }
}