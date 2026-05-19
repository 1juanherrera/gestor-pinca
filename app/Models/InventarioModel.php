<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioModel extends Model
{
    protected $table      = 'inventario';
    protected $primaryKey = 'id_inventario';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Traspaso entre bodegas. Registra audit log TRASPASO en transacción.
     *
     * @param array $data {
     *     item_id, bodega_origen_id, bodega_destino_id, cantidad,
     *     responsable (opcional), observaciones (opcional)
     * }
     */
    public function traspaso($data)
    {
        $itemId   = (int)   $data['item_id'];
        $origen   = (int)   $data['bodega_origen_id'];
        $destino  = (int)   $data['bodega_destino_id'];
        $cantidad = (float) $data['cantidad'];

        if ($origen === $destino) {
            return false;
        }

        $this->db->transBegin();

        try {
            $stockOrigen = $this->db->table('inventario')
                ->where(['item_general_id' => $itemId, 'bodegas_id' => $origen])
                ->get()->getRow();

            if (!$stockOrigen || $stockOrigen->cantidad < $cantidad) {
                $this->db->transRollback();
                return false;
            }

            $saldoOrigenAntes  = (float) $stockOrigen->cantidad;
            $saldoOrigenDespues = $saldoOrigenAntes - $cantidad;

            // Descontar en origen
            $this->db->table('inventario')
                ->where(['item_general_id' => $itemId, 'bodegas_id' => $origen])
                ->set('cantidad', "cantidad - $cantidad", false)
                ->update();

            $this->db->table('inventario')
                ->where(['item_general_id' => $itemId, 'bodegas_id' => $origen])
                ->where('cantidad', 0)
                ->delete();

            // Sumar en destino
            $checkDestino = $this->db->table('inventario')
                ->where(['item_general_id' => $itemId, 'bodegas_id' => $destino])
                ->get()->getRow();

            $saldoDestinoAntes = $checkDestino ? (float) $checkDestino->cantidad : 0;

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
                    'tipo'            => 1,
                ]);
            }

            $saldoDestinoDespues = $saldoDestinoAntes + $cantidad;

            // Datos auxiliares para metadata
            $bodegaOrigen  = $this->db->table('bodegas')->where('id_bodegas', $origen)->get()->getRow();
            $bodegaDestino = $this->db->table('bodegas')->where('id_bodegas', $destino)->get()->getRow();

            // ── Audit log: 1 movimiento TRASPASO con metadata de ambos lados ──
            $movModel = new MovimientoInventarioModel();
            $movModel->registrar([
                'tipo'            => MovimientoInventarioModel::TIPO_TRASPASO,
                'item_general_id' => $itemId,
                'bodega_id'       => $origen,            // bodega "principal" del registro = origen
                'cantidad'        => $cantidad,
                'referencia_tipo' => MovimientoInventarioModel::REF_TRASPASO,
                'referencia_id'   => null,
                'descripcion'     => "Traspaso de bodega {$bodegaOrigen?->nombre} → {$bodegaDestino?->nombre}",
                'saldo_anterior'  => $saldoOrigenAntes,
                'saldo_nuevo'     => $saldoOrigenDespues,
                'responsable'     => $data['responsable'] ?? 'sistema',
                'metadata'        => [
                    'bodega_origen_id'      => $origen,
                    'bodega_origen_nombre'  => $bodegaOrigen?->nombre,
                    'bodega_destino_id'     => $destino,
                    'bodega_destino_nombre' => $bodegaDestino?->nombre,
                    'saldo_origen_antes'    => $saldoOrigenAntes,
                    'saldo_origen_despues'  => $saldoOrigenDespues,
                    'saldo_destino_antes'   => $saldoDestinoAntes,
                    'saldo_destino_despues' => $saldoDestinoDespues,
                    'observaciones'         => $data['observaciones'] ?? null,
                ],
            ]);

            $this->db->transCommit();
            return $this->db->transStatus();

        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', '[InventarioModel::traspaso] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina la fila de inventario para un item en una bodega (ajuste manual).
     * Registra audit log AJUSTE con el saldo eliminado.
     */
    public function removeFromBodega(int $itemId, int $bodegaId, ?string $responsable = null, ?string $motivo = null): bool
    {
        $row = $this->db->table('inventario')
            ->where(['item_general_id' => $itemId, 'bodegas_id' => $bodegaId])
            ->get()->getRow();

        if (!$row) return false;

        $saldoAntes = (float) $row->cantidad;

        $affected = $this->db->table('inventario')
            ->where(['item_general_id' => $itemId, 'bodegas_id' => $bodegaId])
            ->delete();

        if ($affected > 0) {
            $bodega = $this->db->table('bodegas')->where('id_bodegas', $bodegaId)->get()->getRow();

            $movModel = new MovimientoInventarioModel();
            $movModel->registrar([
                'tipo'            => MovimientoInventarioModel::TIPO_AJUSTE,
                'item_general_id' => $itemId,
                'bodega_id'       => $bodegaId,
                'cantidad'        => $saldoAntes,
                'referencia_tipo' => MovimientoInventarioModel::REF_AJUSTE,
                'descripcion'     => "Ajuste manual: removido de bodega {$bodega?->nombre}" . ($motivo ? " — {$motivo}" : ''),
                'saldo_anterior'  => $saldoAntes,
                'saldo_nuevo'     => 0,
                'responsable'     => $responsable ?? 'sistema',
                'metadata'        => [
                    'accion'        => 'remove_from_bodega',
                    'bodega_id'     => $bodegaId,
                    'bodega_nombre' => $bodega?->nombre,
                    'cantidad_removida' => $saldoAntes,
                    'motivo'        => $motivo,
                ],
            ]);
        }

        return $affected > 0;
    }

    /**
     * Suma stock a una bodega (compatibilidad). Llamado por OC al recibir.
     * El audit log lo emite el caller (OrdenesCompraController) porque tiene
     * más contexto (proveedor, OC, factor, lote).
     */
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
