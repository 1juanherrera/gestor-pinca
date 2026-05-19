<?php

namespace App\Models;

class InventarioCapasModel extends BaseModel
{
    protected $table      = 'inventario_capas';
    protected $primaryKey = 'id_capa';

    public function __construct()
    {
        parent::__construct();
    }

    public function crearCapa(array $data): int
    {
        $data['fecha_ingreso'] = $data['fecha_ingreso'] ?? date('Y-m-d H:i:s');
        $data['estado']        = $data['estado'] ?? 1;
        $this->db->table('inventario_capas')->insert($data);
        return $this->db->insertID();
    }

    public function obtenerCapas(int $itemGeneralId, ?int $bodegaId = null, string $orden = 'ic.fecha_ingreso ASC', ?int $proveedorId = null): array
    {
        $builder = $this->db->table('inventario_capas ic')
            ->select('ic.*, p.nombre_empresa AS proveedor_nombre, b.nombre AS bodega_nombre, u.nombre AS unidad_compra_nombre')
            ->join('proveedor p', 'p.id_proveedor = ic.proveedor_id', 'left')
            ->join('bodegas b', 'b.id_bodegas = ic.bodegas_id', 'left')
            ->join('unidad u', 'u.id_unidad = ic.unidad_compra_id', 'left')
            ->where('ic.item_general_id', $itemGeneralId)
            ->where('ic.estado', 1)
            ->where('ic.cantidad_disponible >', 0);

        if ($bodegaId)    { $builder->where('ic.bodegas_id',   $bodegaId); }
        if ($proveedorId) { $builder->where('ic.proveedor_id', $proveedorId); }

        return $builder->orderBy($orden)->get()->getResult();
    }

    public function consumirCapasPorProveedor(int $itemGeneralId, float $cantidadRequerida, int $proveedorId, ?int $bodegaId = null): array
    {
        $capas = $this->obtenerCapas($itemGeneralId, $bodegaId, 'ic.fecha_ingreso ASC', $proveedorId);
        return $this->_consumirDeCapas($capas, $cantidadRequerida);
    }

    public function resumenStock(int $itemGeneralId): array
    {
        $capas = $this->obtenerCapas($itemGeneralId);
        $totalDisponible = 0;
        $totalPonderado  = 0;

        foreach ($capas as $c) {
            $qty   = (float) $c->cantidad_disponible;
            $costo = (float) $c->costo_unitario;
            $totalDisponible += $qty;
            $totalPonderado  += $qty * $costo;
        }

        return [
            'stock_total'              => round($totalDisponible, 4),
            'costo_promedio_ponderado' => $totalDisponible > 0 ? round($totalPonderado / $totalDisponible, 4) : 0,
            'total_capas'              => count($capas),
        ];
    }

    public function consumirCapasFIFO(int $itemGeneralId, float $cantidadRequerida, ?int $bodegaId = null): array
    {
        $capas = $this->obtenerCapas($itemGeneralId, $bodegaId, 'ic.fecha_ingreso ASC');
        return $this->_consumirDeCapas($capas, $cantidadRequerida);
    }

    public function consumirCapasManual(array $seleccion, ?int $expectedItemId = null): array
    {
        $consumos = [];
        foreach ($seleccion as $sel) {
            $capaId   = (int) $sel['capa_id'];
            $cantidad = (float) $sel['cantidad'];
            if ($cantidad <= 0) continue;

            $capa = $this->db->table('inventario_capas')
                ->where('id_capa', $capaId)
                ->where('estado', 1)
                ->get()->getRow();

            if (!$capa) {
                throw new \Exception("La capa #{$capaId} no existe o está agotada.");
            }
            if ($expectedItemId !== null && (int) $capa->item_general_id !== $expectedItemId) {
                throw new \Exception(
                    "La capa #{$capaId} pertenece al item #{$capa->item_general_id}, no al item #{$expectedItemId}."
                );
            }
            if ($cantidad > (float) $capa->cantidad_disponible + 0.0001) {
                throw new \Exception(
                    "La cantidad solicitada de la capa #{$capaId} ({$cantidad}) supera su disponibilidad ({$capa->cantidad_disponible})."
                );
            }

            $consumir        = min($cantidad, (float) $capa->cantidad_disponible);
            $nuevoDisponible = round((float) $capa->cantidad_disponible - $consumir, 4);

            $updates = ['cantidad_disponible' => max($nuevoDisponible, 0)];
            if ($nuevoDisponible <= 0.0001) {
                $updates['estado'] = 0;
                $updates['cantidad_disponible'] = 0;
            }

            $this->db->table('inventario_capas')
                ->where('id_capa', $capaId)
                ->update($updates);

            $consumos[] = [
                'capa_id'            => $capaId,
                'item_general_id'    => (int) $capa->item_general_id,
                'proveedor_id'       => $capa->proveedor_id ? (int) $capa->proveedor_id : null,
                'cantidad_consumida' => round($consumir, 4),
                'costo_unitario'     => (float) $capa->costo_unitario,
                'costo_total'        => round($consumir * (float) $capa->costo_unitario, 4),
                'bodegas_id'         => (int) $capa->bodegas_id,
            ];
        }
        return $consumos;
    }

    public function restaurarCapas(int $preparacionId): void
    {
        $consumos = $this->db->table('preparacion_consumo_capas')
            ->where('preparacion_id', $preparacionId)
            ->get()->getResult();

        foreach ($consumos as $c) {
            $this->db->query(
                'UPDATE inventario_capas
                    SET cantidad_disponible = cantidad_disponible + ?,
                        estado = 1
                  WHERE id_capa = ?',
                [(float) $c->cantidad_consumida, (int) $c->capa_id]
            );
        }

        $this->db->table('preparacion_consumo_capas')
            ->where('preparacion_id', $preparacionId)
            ->delete();
    }

    public function registrarConsumos(int $preparacionId, array $consumos): void
    {
        foreach ($consumos as $c) {
            $this->db->table('preparacion_consumo_capas')->insert([
                'preparacion_id'      => $preparacionId,
                'capa_id'             => $c['capa_id'],
                'item_general_id'     => $c['item_general_id'],
                'cantidad_consumida'  => $c['cantidad_consumida'],
                'costo_unitario'      => $c['costo_unitario'],
                'costo_total'         => $c['costo_total'],
            ]);
        }
    }

    /**
     * Registra el consumo de capas de una línea de remisión (audit detallado).
     */
    public function registrarConsumosRemision(int $remisionId, int $remisionDetalleId, array $consumos): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ($consumos as $c) {
            $this->db->table('remision_consumo_capas')->insert([
                'remision_id'         => $remisionId,
                'remision_detalle_id' => $remisionDetalleId,
                'capa_id'             => $c['capa_id'],
                'item_general_id'     => $c['item_general_id'],
                'cantidad_consumida'  => $c['cantidad_consumida'],
                'costo_unitario'      => $c['costo_unitario'],
                'costo_total'         => $c['costo_total'],
                'created_at'          => $now,
            ]);
        }
    }

    /**
     * Restaura las capas consumidas por una remisión (en caso de anulación).
     * Suma de vuelta las cantidades a las capas originales y reactiva si estaban agotadas.
     */
    public function restaurarCapasRemision(int $remisionId): int
    {
        $consumos = $this->db->table('remision_consumo_capas')
            ->where('remision_id', $remisionId)
            ->get()->getResult();

        foreach ($consumos as $c) {
            $this->db->query(
                'UPDATE inventario_capas
                    SET cantidad_disponible = cantidad_disponible + ?,
                        estado = 1
                  WHERE id_capa = ?',
                [(float) $c->cantidad_consumida, (int) $c->capa_id]
            );
        }

        $count = count($consumos);

        $this->db->table('remision_consumo_capas')
            ->where('remision_id', $remisionId)
            ->delete();

        return $count;
    }

    public function tieneCapas(int $itemGeneralId): bool
    {
        return $this->db->table('inventario_capas')
            ->where('item_general_id', $itemGeneralId)
            ->where('estado', 1)
            ->where('cantidad_disponible >', 0)
            ->countAllResults() > 0;
    }

    public function recalcularPromedioPonderado(int $itemGeneralId): float
    {
        $resumen = $this->resumenStock($itemGeneralId);
        $costo   = $resumen['costo_promedio_ponderado'];

        $existe = $this->db->table('costos_item')
            ->where('item_general_id', $itemGeneralId)
            ->get()->getRow();

        if ($existe) {
            $this->db->table('costos_item')
                ->where('item_general_id', $itemGeneralId)
                ->update([
                    'costo_unitario'  => $costo,
                    'metodo_calculo'  => 'PROMEDIO_PONDERADO',
                    'fecha_calculo'   => date('Y-m-d H:i:s'),
                ]);
        } else {
            $this->db->table('costos_item')->insert([
                'item_general_id' => $itemGeneralId,
                'costo_unitario'  => $costo,
                'metodo_calculo'  => 'PROMEDIO_PONDERADO',
                'fecha_calculo'   => date('Y-m-d H:i:s'),
                'volumen'         => 1,
            ]);
        }

        return $costo;
    }

    private function _consumirDeCapas(array $capas, float $cantidadRequerida): array
    {
        $consumos  = [];
        $pendiente = $cantidadRequerida;

        foreach ($capas as $capa) {
            if ($pendiente <= 0.0001) break;

            $disponible      = (float) $capa->cantidad_disponible;
            $consumir        = min($disponible, $pendiente);
            $nuevoDisponible = round($disponible - $consumir, 4);

            $updates = ['cantidad_disponible' => max($nuevoDisponible, 0)];
            if ($nuevoDisponible <= 0.0001) {
                $updates['estado'] = 0;
                $updates['cantidad_disponible'] = 0;
            }

            $this->db->table('inventario_capas')
                ->where('id_capa', $capa->id_capa)
                ->update($updates);

            $consumos[] = [
                'capa_id'            => (int) $capa->id_capa,
                'item_general_id'    => (int) $capa->item_general_id,
                'proveedor_id'       => $capa->proveedor_id ? (int) $capa->proveedor_id : null,
                'cantidad_consumida' => round($consumir, 4),
                'costo_unitario'     => (float) $capa->costo_unitario,
                'costo_total'        => round($consumir * (float) $capa->costo_unitario, 4),
                'bodegas_id'         => (int) $capa->bodegas_id,
            ];

            $pendiente -= $consumir;
        }

        if ($pendiente > 0.0001) {
            $consumido = round($cantidadRequerida - $pendiente, 4);
            throw new \Exception(
                "Stock insuficiente. Requerido: {$cantidadRequerida}, Disponible: {$consumido}, Faltante: " . round($pendiente, 4) . "."
            );
        }

        return $consumos;
    }
}
