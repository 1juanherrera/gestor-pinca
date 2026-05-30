<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\RemisionesModel;
use App\Models\FacturasModel;
use App\Models\InventarioCapasModel;
use App\Models\InventarioModel;
use App\Models\MovimientoInventarioModel;
use App\Models\NumeracionModel;
use App\Helpers\Cfg;

class RemisionesController extends ResourceController
{
    use \App\Traits\JwtUserAware;
    use \App\Traits\ValidatesJson;
    use \App\Traits\ApiResponse;

    protected $modelName = RemisionesModel::class;
    protected $request;

    // ── GET /remisiones  (?cliente_id=X | ?factura_id=X) ─────────────────
    public function index()
    {
        $clienteId = $this->request->getGet('cliente_id');
        $facturaId = $this->request->getGet('factura_id');

        return $this->respond(
            $this->model->get_remisiones(
                $clienteId ? (int) $clienteId : null,
                $facturaId ? (int) $facturaId : null,
            )
        );
    }

    // ── GET /remisiones/:id ───────────────────────────────────────────────
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        try {
            return $this->respond($this->model->get_remision_by_id((int) $id));
        } catch (\Exception $e) {
            return $this->failNotFound($e->getMessage());
        }
    }

    // ── GET /remisiones/:id/detalle ───────────────────────────────────────
    public function detalle($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        return $this->respond($this->model->get_detalle((int) $id));
    }

    // ── POST /remisiones ──────────────────────────────────────────────────
    public function create()
    {
        $data = $this->validateJson([
            'cliente_id'                 => 'required|integer|greater_than[0]',
            'fecha_remision'             => 'permit_empty|valid_date',
            'direccion_entrega'          => 'permit_empty|max_length[255]',
            'observaciones'              => 'permit_empty',
            'items'                      => 'required',
            'items.*.descripcion'        => 'required|max_length[255]',
            'items.*.cantidad'           => 'required|decimal|greater_than[0]',
            'items.*.precio_unit'        => 'required|decimal|greater_than_equal_to[0]',
            // item_general_id es opcional para soportar texto libre legacy.
            // Si está presente, debe ser entero válido — y al pasar a Despachada se exige.
            'items.*.item_general_id'    => 'permit_empty|integer|greater_than[0]',
            'items.*.bodega_id'          => 'permit_empty|integer|greater_than[0]',
        ]);
        if ($data instanceof ResponseInterface) return $data;

        if (!is_array($data['items']) || empty($data['items'])) {
            return $this->apiValidationError(['items' => 'La remisión debe tener al menos un ítem.']);
        }

        $db = \Config\Database::connect();

        // Validar FK: cliente debe existir y no estar soft-deleted.
        $clienteExiste = $db->table('clientes')
            ->where('id_clientes', (int) $data['cliente_id'])
            ->where('deleted_at', null)
            ->countAllResults();
        if (!$clienteExiste) {
            return $this->apiValidationError(['cliente_id' => "El cliente #{$data['cliente_id']} no existe o está archivado."]);
        }

        $db->transBegin();
        try {
            $items = $data['items'];
            unset($data['items']);

            if (empty($data['numero'])) $data['numero'] = (new NumeracionModel())->reservar('remision');
            if (empty($data['estado'])) $data['estado'] = 'Pendiente';

            $id = $this->model->create_table($data, 'remisiones');
            if (!$id) throw new \Exception(implode(', ', $this->model->errors()));

            foreach ($items as $item) {
                $db->query(
                    "INSERT INTO remisiones_detalle
                        (remisiones_id, item_general_id, bodega_id, descripcion, cantidad, precio_unit, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $id,
                        isset($item['item_general_id']) ? (int) $item['item_general_id'] : null,
                        isset($item['bodega_id'])       ? (int) $item['bodega_id']       : null,
                        $item['descripcion'] ?? '',
                        (float) ($item['cantidad']    ?? 0),
                        (float) ($item['precio_unit'] ?? 0),
                        (float) ($item['subtotal']    ?? 0),
                    ]
                );
            }

            $db->transCommit();

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Remisión creada exitosamente',
                'data'    => $this->model->get_remision_by_id($id),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // ── PUT /remisiones/:id ───────────────────────────────────────────────
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!$id)   return $this->fail('ID no proporcionado', 400);
        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Remisión con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Anulada') {
                throw new \Exception('No se puede editar una remisión anulada');
            }

            $this->model->update_table($id, $data, 'remisiones');

            return $this->respond([
                'status'  => 200,
                'message' => "Remisión $id actualizada correctamente",
                'data'    => $this->model->get_remision_by_id((int) $id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PATCH /remisiones/:id/estado ──────────────────────────────────────
    public function cambiarEstado($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $data       = $this->request->getJSON(true) ?? $this->request->getPost();
        $estado     = $data['estado'] ?? null;
        $permitidos = ['Pendiente', 'Despachada', 'Facturada', 'Anulada'];

        if (!$estado || !in_array($estado, $permitidos)) {
            return $this->fail('Estado no válido. Permitidos: ' . implode(', ', $permitidos), 400);
        }

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Remisión con ID $id no encontrada.");

        // Anuladas no pueden cambiar (terminal)
        if ($existing['estado'] === 'Anulada') {
            return $this->fail('No se puede cambiar el estado de una remisión anulada', 400);
        }

        try {
            // ── Pendiente → Despachada: descontar stock por cada línea ──────
            if ($estado === 'Despachada' && $existing['estado'] !== 'Despachada') {
                $this->descontarStockDespacho((int) $id);
            }

            // ── Despachada → Anulada: restaurar capas ───────────────────────
            if ($estado === 'Anulada' && in_array($existing['estado'], ['Despachada', 'Facturada'])) {
                $this->restaurarStockAnulacion((int) $id);
            }

            $this->model->update_table($id, ['estado' => $estado], 'remisiones');

            return $this->respond([
                'status'  => 200,
                'message' => "Remisión marcada como $estado",
                'data'    => $this->model->get_remision_by_id((int) $id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    /**
     * Descuenta stock por cada línea de la remisión que tiene `item_general_id`.
     * Líneas con texto libre (sin item) se ignoran (legacy compat).
     * Usa FIFO sobre `inventario_capas`. Genera audit log SALIDA por cada movimiento.
     * Atómico: si una línea no tiene stock suficiente, rollback total.
     */
    private function descontarStockDespacho(int $remisionId): void
    {
        $db        = \Config\Database::connect();
        $capasMod  = new InventarioCapasModel();
        $movMod    = new MovimientoInventarioModel();
        $invMod    = new InventarioModel();

        $lineas = $db->table('remisiones_detalle')
            ->where('remisiones_id', $remisionId)
            ->where('item_general_id IS NOT NULL')
            ->get()->getResultArray();

        if (empty($lineas)) return;

        $remision = $db->table('remisiones')->where('id_remisiones', $remisionId)->get()->getRowArray();
        $responsable = $this->getUsername();

        $db->transBegin();
        try {
            foreach ($lineas as $linea) {
                $itemId   = (int) $linea['item_general_id'];
                $cantidad = (float) $linea['cantidad'];
                $bodegaId = $linea['bodega_id'] ? (int) $linea['bodega_id'] : null;

                if ($cantidad <= 0) continue;

                // Verificar stock disponible
                if (!$capasMod->tieneCapas($itemId)) {
                    throw new \Exception(
                        "Sin stock para despachar item #{$itemId} «{$linea['descripcion']}» (cantidad solicitada: {$cantidad})"
                    );
                }

                $consumos = $capasMod->consumirCapasFIFO($itemId, $cantidad, $bodegaId);
                $consumido = array_sum(array_column($consumos, 'cantidad_consumida'));

                if ($consumido < $cantidad - 0.0001) {
                    throw new \Exception(
                        "Stock insuficiente para item #{$itemId}. Disponible: {$consumido}, Requerido: {$cantidad}"
                    );
                }

                // Registrar audit detallado por capa
                $capasMod->registrarConsumosRemision($remisionId, (int) $linea['id_detalle'], $consumos);

                // Actualizar inventario legacy (compatibilidad — no romper queries antiguas)
                $stockRow = $db->table('inventario')
                    ->where('item_general_id', $itemId)
                    ->orderBy('cantidad', 'DESC')->limit(1)
                    ->get()->getRowArray();
                $saldoAntes = $stockRow ? (float) $stockRow['cantidad'] : 0;
                if ($stockRow) {
                    $db->query(
                        'UPDATE inventario SET cantidad = GREATEST(cantidad - ?, 0)
                          WHERE id_inventario = ?',
                        [(float) $cantidad, (int) $stockRow['id_inventario']]
                    );
                }

                // Costo unitario promedio del consumo
                $costoTotal = array_sum(array_column($consumos, 'costo_total'));
                $costoUnit  = $consumido > 0 ? $costoTotal / $consumido : 0;

                // Audit log SALIDA
                $movMod->registrar([
                    'tipo'             => MovimientoInventarioModel::TIPO_SALIDA,
                    'item_general_id'  => $itemId,
                    'bodega_id'        => $bodegaId ?? ($stockRow['bodegas_id'] ?? null),
                    'cantidad'         => $consumido,
                    'referencia_tipo'  => MovimientoInventarioModel::REF_REMISION,
                    'referencia_id'    => $remisionId,
                    'descripcion'      => "Despacho remisión {$remision['numero']} línea {$linea['id_detalle']}",
                    'costo_unitario'   => $costoUnit,
                    'saldo_anterior'   => $saldoAntes,
                    'saldo_nuevo'      => max($saldoAntes - $consumido, 0),
                    'responsable'      => $responsable,
                    'metadata'         => [
                        'remision_id'     => $remisionId,
                        'remision_numero' => $remision['numero'],
                        'cliente_id'      => $remision['cliente_id'],
                        'detalle_id'      => (int) $linea['id_detalle'],
                        'descripcion'     => $linea['descripcion'],
                        'capas_consumidas'=> count($consumos),
                    ],
                ]);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            // Limpiar consumos parciales si quedaron registrados antes del fallo
            $capasMod->restaurarCapasRemision($remisionId);
            throw $e;
        }
    }

    /**
     * Restaura las capas consumidas y registra audit log de la anulación.
     */
    private function restaurarStockAnulacion(int $remisionId): void
    {
        $db        = \Config\Database::connect();
        $capasMod  = new InventarioCapasModel();
        $movMod    = new MovimientoInventarioModel();

        $remision = $db->table('remisiones')->where('id_remisiones', $remisionId)->get()->getRowArray();

        // Restaurar capas en BD + obtener movimientos para el audit log de reverso
        $consumosAntes = $db->table('remision_consumo_capas')
            ->where('remision_id', $remisionId)
            ->get()->getResultArray();

        if (empty($consumosAntes)) return;  // nada que restaurar

        // Agrupar por item para el audit log (1 ENTRADA por item)
        $porItem = [];
        foreach ($consumosAntes as $c) {
            $key = (int) $c['item_general_id'];
            if (!isset($porItem[$key])) {
                $porItem[$key] = ['cantidad' => 0, 'costo_total' => 0];
            }
            $porItem[$key]['cantidad']    += (float) $c['cantidad_consumida'];
            $porItem[$key]['costo_total'] += (float) $c['costo_total'];
        }

        $count = $capasMod->restaurarCapasRemision($remisionId);

        $responsable = $this->getUsername();

        // Audit log ENTRADA por reverso
        foreach ($porItem as $itemId => $info) {
            $costoUnit = $info['cantidad'] > 0 ? $info['costo_total'] / $info['cantidad'] : 0;
            $movMod->registrar([
                'tipo'             => MovimientoInventarioModel::TIPO_ENTRADA,
                'item_general_id'  => $itemId,
                'cantidad'         => $info['cantidad'],
                'referencia_tipo'  => MovimientoInventarioModel::REF_ANULACION,
                'referencia_id'    => $remisionId,
                'descripcion'      => "Anulación remisión {$remision['numero']} (reintegro de stock)",
                'costo_unitario'   => $costoUnit,
                'responsable'      => $responsable,
                'metadata'         => [
                    'remision_id'     => $remisionId,
                    'remision_numero' => $remision['numero'],
                    'origen_estado'   => $remision['estado'],
                    'capas_restauradas' => $count,
                ],
            ]);
        }
    }

    // ── POST /remisiones/:id/convertir ────────────────────────────────────
    public function convertir($id = null)
    {
        if (!$id) return $this->apiFail('ID no proporcionado', 400);

        $remision = $this->model->find($id);
        if (!$remision) return $this->apiNotFound("Remisión con ID $id no encontrada.");

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            if ($remision['estado'] === 'Facturada') {
                throw new \Exception('Esta remisión ya fue convertida a factura');
            }
            if ($remision['estado'] === 'Anulada') {
                throw new \Exception('No se puede convertir una remisión anulada');
            }

            $items    = $this->model->get_detalle((int) $id);
            $subtotal = array_sum(array_column($items, 'subtotal'));
            $ivaPct   = (float) Cfg::n('iva_default', 19);
            $iva      = round($subtotal * $ivaPct / 100, 2);
            $total    = $subtotal + $iva;

            $facturaModel = new FacturasModel();
            $numeroFac    = (new NumeracionModel())->reservar('factura');

            $facturaData = [
                'numero'            => $numeroFac,
                'cliente_id'        => $remision['cliente_id'],
                'fecha_emision'     => date('Y-m-d'),
                'fecha_vencimiento' => date('Y-m-d', strtotime('+30 days')),
                'subtotal'          => $subtotal,
                'descuento'         => 0,
                'impuestos'         => $iva,
                'retencion'         => 0,
                'total'             => $total,
                'saldo_pendiente'   => $total,
                'estado'            => 'Pendiente',
                'observaciones'     => "Generada desde remisión {$remision['numero']}",
            ];

            $facturaId = $facturaModel->create_table($facturaData, 'facturas');
            if (!$facturaId) throw new \Exception('Error al generar la factura');

            if (!empty($items)) {
                $itemsFactura = array_map(fn($item) => [
                    'facturas_id'   => $facturaId,
                    'descripcion'   => $item['descripcion'],
                    'cantidad'      => $item['cantidad'],
                    'precio_unit'   => $item['precio_unit'],
                    'descuento_pct' => 0,
                    'subtotal'      => $item['subtotal'],
                ], $items);
                $this->model->create_table($itemsFactura, 'facturas_detalle');
            }

            $this->model->update_table($id, [
                'estado'      => 'Facturada',
                'facturas_id' => $facturaId,
            ], 'remisiones');

            $db->transCommit();

            return $this->respondCreated([
                'status'  => 201,
                'message' => "Remisión convertida. Factura $numeroFac creada.",
                'data'    => $facturaModel->find($facturaId),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // ── DELETE /remisiones/:id ────────────────────────────────────────────
    public function delete($id = null)
    {
        // Acceso por módulo (política 2026-05-30): si el usuario tiene el módulo, puede ejecutar la acción. Sin guard por rol.

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Remisión con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Facturada') {
                throw new \Exception('No se puede eliminar una remisión que ya tiene factura generada');
            }

            $this->model->delete_table($id, 'remisiones');

            return $this->respondDeleted(['message' => "Remisión $id eliminada"]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── Numeración correlativa: ahora delegada a NumeracionModel::reservar()
}