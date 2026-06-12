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

        if ($origen === $destino || $cantidad <= 0) {
            return false;
        }

        $this->db->transBegin();

        try {
            // ── Validación + movimiento sobre inventario_capas (fuente de verdad de stock/costo) ──
            // Capas activas del origen en orden FIFO, lockeadas (FOR UPDATE) para evitar consumo concurrente.
            $capasOrigen = $this->db->query(
                'SELECT * FROM inventario_capas
                  WHERE item_general_id = ? AND bodegas_id = ? AND estado = 1 AND cantidad_disponible > 0
                  ORDER BY fecha_ingreso ASC, id_capa ASC FOR UPDATE',
                [$itemId, $origen]
            )->getResultArray();

            $saldoOrigenAntes = array_sum(array_map(static fn($c) => (float) $c['cantidad_disponible'], $capasOrigen));

            if ($saldoOrigenAntes + 0.0001 < $cantidad) {
                $this->db->transRollback();
                return false; // stock insuficiente en el origen (según capas, la fuente de verdad)
            }

            // Stock del destino ANTES (solo para metadata del audit).
            $saldoDestinoAntes = (float) ($this->db->query(
                'SELECT COALESCE(SUM(cantidad_disponible), 0) AS s FROM inventario_capas
                  WHERE item_general_id = ? AND bodegas_id = ? AND estado = 1',
                [$itemId, $destino]
            )->getRow()->s ?? 0);

            // Mover capas FIFO origen → destino, preservando costo/lote/proveedor/fecha.
            $restante = $cantidad;
            foreach ($capasOrigen as $capa) {
                if ($restante <= 0.0001) break;
                $disp  = (float) $capa['cantidad_disponible'];
                $mover = min($restante, $disp);

                if ($mover >= $disp - 0.0001) {
                    // Capa completa: solo cambia de bodega.
                    $this->db->query('UPDATE inventario_capas SET bodegas_id = ? WHERE id_capa = ?',
                        [(int) $destino, (int) $capa['id_capa']]);
                } else {
                    // Parcial: reduce la del origen y crea una NUEVA en destino con el mismo costo/lote.
                    $this->db->query('UPDATE inventario_capas SET cantidad_disponible = cantidad_disponible - ? WHERE id_capa = ?',
                        [$mover, (int) $capa['id_capa']]);
                    $this->db->table('inventario_capas')->insert([
                        'item_general_id'     => $itemId,
                        'bodegas_id'          => $destino,
                        'proveedor_id'        => $capa['proveedor_id'],
                        'item_proveedor_id'   => $capa['item_proveedor_id'],
                        'orden_compra_id'     => $capa['orden_compra_id'],
                        'cantidad_original'   => $mover,
                        'cantidad_disponible' => $mover,
                        'costo_unitario'      => $capa['costo_unitario'],
                        'unidad_compra_id'    => $capa['unidad_compra_id'],
                        'factor_conversion'   => $capa['factor_conversion'],
                        'precio_compra'       => $capa['precio_compra'],
                        'fecha_ingreso'       => $capa['fecha_ingreso'],
                        'lote_proveedor'      => $capa['lote_proveedor'],
                        'observaciones'       => $capa['observaciones'],
                        'estado'              => 1,
                    ]);
                }
                $restante -= $mover;
            }

            $saldoOrigenDespues  = $saldoOrigenAntes - $cantidad;
            $saldoDestinoDespues = $saldoDestinoAntes + $cantidad;

            // ── Mantener la tabla legacy `inventario` en sync (best-effort, backward-compat) ──
            $this->db->query(
                'UPDATE inventario SET cantidad = GREATEST(cantidad - ?, 0)
                  WHERE item_general_id = ? AND bodegas_id = ?',
                [(float) $cantidad, (int) $itemId, (int) $origen]
            );
            $this->db->table('inventario')
                ->where(['item_general_id' => $itemId, 'bodegas_id' => $origen])
                ->where('cantidad', 0)
                ->delete();

            $checkDestino = $this->db->table('inventario')
                ->where(['item_general_id' => $itemId, 'bodegas_id' => $destino])
                ->get()->getRow();
            if ($checkDestino) {
                $this->db->query(
                    'UPDATE inventario SET cantidad = cantidad + ?
                      WHERE item_general_id = ? AND bodegas_id = ?',
                    [(float) $cantidad, (int) $itemId, (int) $destino]
                );
            } else {
                $this->db->table('inventario')->insert([
                    'item_general_id' => $itemId,
                    'bodegas_id'      => $destino,
                    'cantidad'        => $cantidad,
                    'estado'          => 1,
                    'tipo'            => 1,
                ]);
            }

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
            $this->db->query(
                'UPDATE inventario SET cantidad = cantidad + ?
                  WHERE item_general_id = ? AND bodegas_id = ?',
                [(float) $cantidad, (int) $itemGeneralId, (int) $bodegaId]
            );
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
