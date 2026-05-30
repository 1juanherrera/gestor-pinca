<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\OrdenesCompraModel;
use App\Models\InventarioModel;
use App\Models\InventarioCapasModel;
use App\Models\NumeracionModel;
use App\Helpers\Cfg;

class OrdenesCompraController extends ResourceController
{
    use \App\Traits\JwtUserAware;
    use \App\Traits\ValidatesJson;
    use \App\Traits\ApiResponse;

    protected $modelName = OrdenesCompraModel::class;

    /**
     * Resuelve el código de lote para una recepción de OC.
     *
     * - Si el usuario proveyó uno (no vacío), lo respeta.
     * - Si ya existen capas con `orden_compra_id = $idOrden` y `lote_proveedor`
     *   no nulo, reusa ese mismo código → todas las líneas de la OC comparten lote.
     * - Si no existe ninguno, genera `LOT-OC{idOrden}-{Ymd}`.
     */
    private function resolverLoteProveedor(int $idOrden, ?string $loteInput): string
    {
        $manual = trim((string) ($loteInput ?? ''));
        if ($manual !== '') return $manual;

        $db = \Config\Database::connect();
        $existente = $db->table('inventario_capas')
            ->select('lote_proveedor')
            ->where('orden_compra_id', $idOrden)
            ->where('lote_proveedor IS NOT NULL')
            ->where("TRIM(lote_proveedor) != ''", null, false)
            ->orderBy('fecha_ingreso', 'ASC')
            ->limit(1)
            ->get()->getRowArray();

        if ($existente && !empty($existente['lote_proveedor'])) {
            return $existente['lote_proveedor'];
        }

        return 'LOT-OC' . $idOrden . '-' . date('Ymd');
    }

    // GET api/ordenes_compra/{id}/lote-sugerido
    // Devuelve el código de lote que se usará al recibir mercancía de esta OC.
    // El frontend lo muestra en el input antes de confirmar.
    public function loteSugerido($idOrden = null)
    {
        if (!$idOrden) return $this->fail('ID no proporcionado', 400);
        $lote = $this->resolverLoteProveedor((int) $idOrden, null);
        return $this->respond(['lote' => $lote]);
    }

    // GET api/ordenes_compra
    public function index()
    {
        return $this->respond($this->model->listar());
    }

    // GET api/ordenes_compra/{id}/detalle
    public function detalle($id = null)
    {
        $data = $this->model->detalle((int) $id);
        if (!$data) return $this->failNotFound("Orden con ID $id no encontrada.");
        return $this->respond($data);
    }

    // POST api/ordenes_compra
    public function create()
    {
        $data = $this->validateJson([
            'proveedor_id'              => 'required|integer|greater_than[0]',
            'bodegas_id'                => 'permit_empty|integer|greater_than[0]',
            'fecha'                     => 'permit_empty|valid_date',
            'fecha_esperada'            => 'permit_empty|valid_date',
            'observaciones'             => 'permit_empty|max_length[500]',
            'iva_pct'                   => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'lineas'                    => 'required',
            'lineas.*.item_proveedor_id'=> 'required|integer|greater_than[0]',
            'lineas.*.cantidad'         => 'required|decimal|greater_than[0]',
            'lineas.*.precio_unit'      => 'required|decimal|greater_than_equal_to[0]',
        ]);
        if ($data instanceof \CodeIgniter\HTTP\ResponseInterface) return $data;

        if (!is_array($data['lineas']) || empty($data['lineas'])) {
            return $this->apiValidationError(['lineas' => 'La orden debe tener al menos una línea.']);
        }

        $db = \Config\Database::connect();

        // Validar que el proveedor exista y no esté archivado.
        $proveedorActivo = $db->table('proveedor')
            ->where('id_proveedor', (int) $data['proveedor_id'])
            ->where('deleted_at', null)
            ->countAllResults();
        if ($proveedorActivo === 0) {
            return $this->apiValidationError(['proveedor_id' => "El proveedor #{$data['proveedor_id']} no existe o está archivado."]);
        }

        $db->transStart();

        try {
            // Crear cabecera
            $lineas = $data['lineas'] ?? [];
            unset($data['lineas']);

            $data['numero'] = (new NumeracionModel())->reservar('orden_compra');
            $data['estado'] = 'Borrador';
            // iva_pct persistido: respeta el override del cliente; si no, default global.
            $data['iva_pct'] = isset($data['iva_pct'])
                ? (float) $data['iva_pct']
                : (float) Cfg::n('iva_default', 19);

            $db->table('ordenes_compra')->insert($data);
            if (!$db->affectedRows()) throw new \Exception('Error al crear la orden.');
            $idOrden = $db->insertID();

            // Insertar líneas
            $total = 0;
            foreach ($lineas as $linea) {
                $subtotal = (float)$linea['cantidad'] * (float)$linea['precio_unit'];
                $total   += $subtotal;

                $db->table('ordenes_compra_detalle')->insert([
                    'ordenes_compra_id'  => $idOrden,
                    'item_proveedor_id'  => $linea['item_proveedor_id'],
                    'item_general_id'    => $linea['item_general_id'] ?? null,
                    'descripcion'        => $linea['descripcion']     ?? null,
                    'cantidad'           => $linea['cantidad'],
                    'precio_unit'        => $linea['precio_unit'],
                    'subtotal'           => $subtotal,
                ]);
            }

            // Actualizar total
            $db->table('ordenes_compra')
                ->where('id_orden', $idOrden)
                ->update(['total' => $total]);

            $db->transComplete();
            if (!$db->transStatus()) throw new \Exception('Error al confirmar la transacción.');

            return $this->respondCreated([
                'mensaje'  => 'Orden creada correctamente',
                'id'       => $idOrden,
                'numero'   => $data['numero'],
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // PUT api/ordenes_compra/{id} — solo Borrador
    public function update($id = null)
    {
        $orden = $this->model->detalle((int) $id);
        if (!$orden) return $this->failNotFound("Orden con ID $id no encontrada.");
        if ($orden['estado'] !== 'Borrador') {
            return $this->fail('Solo se pueden editar órdenes en estado Borrador.', 400);
        }

        $data = json_decode($this->request->getBody(), true);
        if (!$data) return $this->failValidationErrors('No se recibieron datos válidos.');

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $lineas = $data['lineas'] ?? null;
            unset($data['lineas']);

            // Actualizar cabecera
            if (!empty($data)) {
                $this->model->update((int) $id, $data);
            }

            // Reemplazar líneas si se envían
            if ($lineas !== null) {
                $db->table('ordenes_compra_detalle')
                    ->where('ordenes_compra_id', $id)
                    ->delete();

                $total = 0;
                foreach ($lineas as $linea) {
                    $subtotal = (float)$linea['cantidad'] * (float)$linea['precio_unit'];
                    $total   += $subtotal;

                    $db->table('ordenes_compra_detalle')->insert([
                        'ordenes_compra_id'  => $id,
                        'item_proveedor_id'  => $linea['item_proveedor_id'],
                        'item_general_id'    => $linea['item_general_id'] ?? null,
                        'descripcion'        => $linea['descripcion']     ?? null,
                        'cantidad'           => $linea['cantidad'],
                        'precio_unit'        => $linea['precio_unit'],
                        'subtotal'           => $subtotal,
                    ]);
                }

                $db->table('ordenes_compra')
                    ->where('id_orden', $id)
                    ->update(['total' => $total]);
            }

            $db->transComplete();
            if (!$db->transStatus()) throw new \Exception('Error al confirmar la transacción.');

            return $this->respond(['mensaje' => "Orden $id actualizada correctamente"]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage(), 400);
        }
    }

    // PATCH api/ordenes_compra/{id}/estado
    // Body: { estado: 'Enviada' | 'Cancelada' }
    public function cambiarEstado($id = null)
    {
        $data        = $this->request->getJSON(true) ?? $this->request->getPost();
        $nuevoEstado = $data['estado'] ?? null;

        $transiciones = [
            'Borrador' => ['Enviada', 'Cancelada'],
            'Enviada'  => ['Cancelada'],
        ];

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // Lock pesimista del row para evitar transiciones concurrentes.
            $orden = $db->table('ordenes_compra')
                ->where('id_orden', (int) $id)
                ->where('deleted_at', null)
                ->orderBy('id_orden')
                ->getCompiledSelect();
            $orden = $db->query($orden . ' FOR UPDATE')->getRowArray();

            if (!$orden) {
                $db->transRollback();
                return $this->apiNotFound("Orden con ID $id no encontrada.");
            }

            $permitidos = $transiciones[$orden['estado']] ?? [];
            if (!in_array($nuevoEstado, $permitidos)) {
                $db->transRollback();
                return $this->apiFail("No se puede cambiar de {$orden['estado']} a $nuevoEstado.", 400);
            }

            $db->table('ordenes_compra')
                ->where('id_orden', (int) $id)
                ->update(['estado' => $nuevoEstado]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->apiFail($e->getMessage(), 400);
        }

        return $this->respond(['mensaje' => "Estado actualizado a $nuevoEstado"]);
    }

    // POST api/ordenes_compra/{id}/recibir/{detalle_id}
    // Body: { cantidad_recibida }
    // La bodega viene de la cabecera de la orden
    public function recibirLinea($idOrden = null, $idDetalle = null)
    {
        $orden = $this->model->detalle((int) $idOrden);
        if (!$orden) return $this->apiNotFound("Orden con ID $idOrden no encontrada.");
        if ($orden['estado'] !== 'Enviada') {
            return $this->apiFail('Solo se pueden recibir líneas de órdenes Enviadas.', 400);
        }

        // Buscar la línea
        $linea = null;
        foreach ($orden['lineas'] as $l) {
            if ((int)$l['id_detalle'] === (int)$idDetalle) {
                $linea = $l;
                break;
            }
        }
        if (!$linea) return $this->apiNotFound("Línea con ID $idDetalle no encontrada.");
        if ($linea['recibido_en']) return $this->apiFail('Esta línea ya fue recibida completamente.', 400);

        // Validación del body (cantidad_recibida y lote_proveedor opcional).
        // Si no se envió body, cantidad_recibida se asume = pendiente (legacy).
        $validated = $this->validateJson([
            'cantidad_recibida' => 'permit_empty|decimal|greater_than[0]',
            'lote_proveedor'    => 'permit_empty|max_length[100]',
        ]);
        if ($validated instanceof \CodeIgniter\HTTP\ResponseInterface) return $validated;

        $data              = $validated;
        $cantidadPedida    = (float) $linea['cantidad'];
        $recibidoPrev      = (float) ($linea['cantidad_recibida'] ?? 0);
        $pendiente         = max(0, $cantidadPedida - $recibidoPrev);
        $cantidadRecibida  = (float)($data['cantidad_recibida'] ?? $pendiente);
        $bodegaId          = (int) $orden['bodegas_id'];

        if ($cantidadRecibida <= 0) {
            return $this->apiValidationError(['cantidad_recibida' => 'La cantidad recibida debe ser mayor a 0.']);
        }
        if ($cantidadRecibida > $pendiente + 0.0001) {
            return $this->apiValidationError([
                'cantidad_recibida' => "La cantidad recibida ({$cantidadRecibida}) supera el pendiente de la línea ({$pendiente}).",
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Lock pesimista contra recepciones simultáneas: si dos usuarios
            // intentan recibir la misma línea en paralelo, el segundo espera
            // al commit del primero y luego ve cantidad_recibida actualizada.
            $lockRow = $db->query(
                'SELECT recibido_en, cantidad, cantidad_recibida FROM ordenes_compra_detalle WHERE id_detalle = ? FOR UPDATE',
                [$idDetalle]
            )->getRow();

            if (!$lockRow) {
                throw new \Exception('Línea no encontrada al iniciar la transacción.');
            }
            if ($lockRow->recibido_en) {
                throw new \Exception('Esta línea ya fue recibida completamente.');
            }

            $recibidoActual = (float) ($lockRow->cantidad_recibida ?? 0);
            $pedidoActual   = (float) $lockRow->cantidad;
            $cantidadAcumulada = $recibidoActual + $cantidadRecibida;
            $completa = $cantidadAcumulada >= $pedidoActual - 0.0001;

            // Re-check con el valor lockeado para evitar over-receiving en concurrencia
            if ($cantidadAcumulada > $pedidoActual + 0.0001) {
                throw new \Exception(
                    "Otro usuario adelantó la recepción de esta línea. "
                    . "Pendiente actual: " . max(0, $pedidoActual - $recibidoActual) . " — recargá la orden."
                );
            }

            // Acumular cantidad recibida; marcar recibido_en solo si la línea quedó cubierta
            $db->table('ordenes_compra_detalle')
                ->where('id_detalle', $idDetalle)
                ->update([
                    'cantidad_recibida' => $cantidadAcumulada,
                    'recibido_en'       => $completa ? date('Y-m-d H:i:s') : null,
                ]);

            // Ingresar al inventario
            if ($linea['item_general_id']) {
                $itemGeneralId = (int) $linea['item_general_id'];

                // Obtener datos del item_proveedor para conversión de unidades
                $itemProv = $linea['item_proveedor_id']
                    ? $db->table('item_proveedor')
                        ->where('id_item_proveedor', $linea['item_proveedor_id'])
                        ->get()->getRow()
                    : null;

                $factorConversion = $itemProv ? max((float) ($itemProv->factor_conversion ?: 1), 0.001) : 1;
                $cantidadBase     = $cantidadRecibida * $factorConversion;
                $costoUnitarioKg  = (float) $linea['precio_unit'] / $factorConversion;

                // Lote: el helper reusa el código si ya hay capas de esta misma
                // OC, o genera uno nuevo si esta es la primera línea recibida.
                $loteProveedor = $this->resolverLoteProveedor((int) $idOrden, $data['lote_proveedor'] ?? null);

                // Crear capa de inventario
                $capasModel = new InventarioCapasModel();
                $capasModel->crearCapa([
                    'item_general_id'     => $itemGeneralId,
                    'bodegas_id'          => $bodegaId,
                    'proveedor_id'        => $orden['proveedor_id'] ? (int) $orden['proveedor_id'] : null,
                    'item_proveedor_id'   => $linea['item_proveedor_id'] ? (int) $linea['item_proveedor_id'] : null,
                    'orden_compra_id'     => (int) $idOrden,
                    'cantidad_original'   => $cantidadBase,
                    'cantidad_disponible' => $cantidadBase,
                    'costo_unitario'      => $costoUnitarioKg,
                    'unidad_compra_id'    => $itemProv?->unidad_compra_id ?? null,
                    'factor_conversion'   => $factorConversion,
                    'precio_compra'       => (float) $linea['precio_unit'],
                    'lote_proveedor'      => $loteProveedor,
                ]);

                // Inventario agregado (compatibilidad)
                $inventarioModel = new InventarioModel();
                $ok = $inventarioModel->ingresarABodega($itemGeneralId, $bodegaId, $cantidadBase);
                if (!$ok) throw new \Exception('Error al ingresar al inventario.');

                // Recalcular promedio ponderado (devuelve el costo promedio recalculado).
                $promedio = $capasModel->recalcularPromedioPonderado($itemGeneralId);

                // item_general.costo_produccion debe reflejar el promedio, no
                // el costo de la última OC (de lo contrario subestima/sobrestima
                // tras varias recepciones).
                $db->table('item_general')
                    ->where('id_item_general', $itemGeneralId)
                    ->update(['costo_produccion' => $promedio]);

                // ── Audit log: ENTRADA por recepción de OC ─────────────
                $movModel = new \App\Models\MovimientoInventarioModel();
                $movModel->registrar([
                    'tipo'             => \App\Models\MovimientoInventarioModel::TIPO_ENTRADA,
                    'item_general_id'  => $itemGeneralId,
                    'bodega_id'        => $bodegaId,
                    'cantidad'         => $cantidadBase,
                    'referencia_tipo'  => \App\Models\MovimientoInventarioModel::REF_OC,
                    'referencia_id'    => (int) $idOrden,
                    'descripcion'      => "Recepción OC #{$orden['numero']} línea {$idDetalle}",
                    'costo_unitario'   => $costoUnitarioKg,
                    'responsable'      => $this->getUsername(),
                    'metadata'         => [
                        'numero_oc'           => $orden['numero'] ?? null,
                        'proveedor_id'        => $orden['proveedor_id'] ?? null,
                        'item_proveedor_id'   => $linea['item_proveedor_id'] ?? null,
                        'item_proveedor_nombre' => $itemProv?->nombre ?? null,
                        'cantidad_recibida_unidad_compra' => $cantidadRecibida,
                        'unidad_compra'       => $itemProv?->unidad_compra_id ?? null,
                        'factor_conversion'   => $factorConversion,
                        'precio_unit_compra'  => (float) $linea['precio_unit'],
                        'lote_proveedor'      => $loteProveedor,
                    ],
                ]);
            }

            // Verificar si TODAS las líneas están recibidas (dentro de la misma conexión transaccional)
            $pendientes = (int) $db->query('
                SELECT COUNT(*) as total
                FROM ordenes_compra_detalle
                WHERE ordenes_compra_id = ?
                  AND recibido_en IS NULL
            ', [$idOrden])->getRow()->total;

            // Marcar la OC como Recibida DENTRO de la transacción (atómico).
            // Antes este UPDATE corría tras transComplete(): si el proceso moría
            // entre el commit de la línea y el update de estado, la OC quedaba con
            // todas las líneas recibidas pero en estado Enviada. Al moverlo aquí,
            // el cambio de estado se commitea junto con la recepción de la línea.
            if ($pendientes === 0) {
                $db->table('ordenes_compra')
                    ->where('id_orden', (int) $idOrden)
                    ->update(['estado' => 'Recibida']);
            }

            $db->transComplete();
            if (!$db->transStatus()) throw new \Exception('Error al confirmar la transacción.');

            return $this->respond(['mensaje' => 'Línea recibida correctamente']);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // POST api/ordenes_compra/{id}/recibir-prorrateado
    // Recibe varias líneas en un solo lote con un precio total negociado
    // (típicamente menor a la suma de precios originales por descuento por volumen).
    //
    // Body:
    //   {
    //     precio_total_pagado: number,
    //     lote_proveedor?: string,                // se aplica a todas las capas
    //     lineas: [
    //       { id_detalle: int, cantidad_recibida: number }
    //     ]
    //   }
    //
    // Cálculo:
    //   valor_lista_total  = Σ (cantidad_recibida × precio_unit_oc)
    //   factor             = precio_total_pagado / valor_lista_total
    //   por cada línea:
    //     precio_unit_real = precio_unit_oc × factor       (en unidad_compra)
    //     costo_unit_kg    = precio_unit_real / factor_conversion   (en unidad base)
    //
    // Todo va en una sola transacción: si una línea falla, ninguna se aplica.
    public function recibirLoteProrrateado($idOrden = null)
    {
        $orden = $this->model->detalle((int) $idOrden);
        if (!$orden) return $this->apiNotFound("Orden con ID $idOrden no encontrada.");
        if ($orden['estado'] !== 'Enviada') {
            return $this->apiFail('Solo se pueden recibir líneas de órdenes Enviadas.', 400);
        }

        $body            = json_decode($this->request->getBody(), true) ?? [];
        $precioPagado    = (float) ($body['precio_total_pagado'] ?? 0);
        $loteProveedor   = $body['lote_proveedor'] ?? null;
        $lineasPayload   = $body['lineas'] ?? [];

        if ($precioPagado <= 0) {
            return $this->apiValidationError(['precio_total_pagado' => 'El precio total pagado debe ser mayor a 0.']);
        }
        if (!is_array($lineasPayload) || count($lineasPayload) < 2) {
            return $this->apiValidationError(['lineas' => 'El prorrateo necesita al menos 2 líneas.']);
        }

        // Mapeo id_detalle → línea original (para validar y calcular).
        $lineasPorId = [];
        foreach ($orden['lineas'] as $l) {
            $lineasPorId[(int) $l['id_detalle']] = $l;
        }

        // Validar cada línea del payload + calcular valor lista total.
        $valorListaTotal = 0;
        $lineasPreparadas = [];
        foreach ($lineasPayload as $lp) {
            $idDetalle = (int) ($lp['id_detalle'] ?? 0);
            $cantRec   = (float) ($lp['cantidad_recibida'] ?? 0);
            $linea     = $lineasPorId[$idDetalle] ?? null;

            if (!$linea) {
                return $this->apiValidationError(['lineas' => "Línea {$idDetalle} no pertenece a la OC."]);
            }
            if ($linea['recibido_en']) {
                return $this->apiValidationError(['lineas' => "La línea {$idDetalle} ya fue recibida."]);
            }
            if ($cantRec <= 0) {
                return $this->apiValidationError(['lineas' => "La cantidad recibida de la línea {$idDetalle} debe ser mayor a 0."]);
            }
            $pendiente = max(0, (float) $linea['cantidad'] - (float) ($linea['cantidad_recibida'] ?? 0));
            if ($cantRec > $pendiente + 0.0001) {
                return $this->apiValidationError([
                    'lineas' => "La cantidad recibida de la línea {$idDetalle} ({$cantRec}) supera el pendiente ({$pendiente}).",
                ]);
            }

            $valorLista = $cantRec * (float) $linea['precio_unit'];
            $valorListaTotal += $valorLista;

            $lineasPreparadas[] = [
                'idDetalle'   => $idDetalle,
                'linea'       => $linea,
                'cantRec'     => $cantRec,
                'valorLista'  => $valorLista,
            ];
        }

        if ($valorListaTotal <= 0) {
            return $this->apiValidationError(['lineas' => 'La suma del valor de lista debe ser mayor a 0.']);
        }

        $factor   = $precioPagado / $valorListaTotal;
        $bodegaId = (int) $orden['bodegas_id'];

        // Resolver código de lote (un único código para todo el lote prorrateado).
        $loteProveedor = $this->resolverLoteProveedor((int) $idOrden, $loteProveedor);

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $capasModel      = new InventarioCapasModel();
            $inventarioModel = new InventarioModel();
            $movModel        = new \App\Models\MovimientoInventarioModel();
            $username        = $this->getUsername();

            foreach ($lineasPreparadas as $lp) {
                $idDetalle = $lp['idDetalle'];
                $linea     = $lp['linea'];
                $cantRec   = $lp['cantRec'];

                // Lock optimista por línea: si dos usuarios procesan el mismo
                // lote a la vez, el segundo falla aquí con stale read.
                $lockRow = $db->query(
                    'SELECT recibido_en, cantidad, cantidad_recibida FROM ordenes_compra_detalle
                     WHERE id_detalle = ? FOR UPDATE',
                    [$idDetalle]
                )->getRow();
                if (!$lockRow) throw new \Exception("Línea {$idDetalle} desapareció durante la transacción.");
                if ($lockRow->recibido_en) {
                    throw new \Exception("Otro usuario recibió la línea {$idDetalle} antes — recargá la orden.");
                }

                $recibidoActual    = (float) ($lockRow->cantidad_recibida ?? 0);
                $pedidoActual      = (float) $lockRow->cantidad;
                $cantidadAcumulada = $recibidoActual + $cantRec;
                if ($cantidadAcumulada > $pedidoActual + 0.0001) {
                    throw new \Exception("Línea {$idDetalle}: la cantidad acumulada supera el pedido tras lock.");
                }
                $completa = $cantidadAcumulada >= $pedidoActual - 0.0001;

                $db->table('ordenes_compra_detalle')
                    ->where('id_detalle', $idDetalle)
                    ->update([
                        'cantidad_recibida' => $cantidadAcumulada,
                        'recibido_en'       => $completa ? date('Y-m-d H:i:s') : null,
                    ]);

                if (!$linea['item_general_id']) continue;
                $itemGeneralId = (int) $linea['item_general_id'];

                // Item proveedor → factor de conversión a unidad base.
                $itemProv = $linea['item_proveedor_id']
                    ? $db->table('item_proveedor')
                        ->where('id_item_proveedor', $linea['item_proveedor_id'])
                        ->get()->getRow()
                    : null;

                $factorConversion = $itemProv ? max((float) ($itemProv->factor_conversion ?: 1), 0.001) : 1;
                $cantidadBase     = $cantRec * $factorConversion;

                // Precio prorrateado: aplico el factor al precio_unit de la OC.
                $precioUnitProrrateado = (float) $linea['precio_unit'] * $factor;
                $costoUnitarioKg       = $precioUnitProrrateado / $factorConversion;

                $capasModel->crearCapa([
                    'item_general_id'     => $itemGeneralId,
                    'bodegas_id'          => $bodegaId,
                    'proveedor_id'        => $orden['proveedor_id'] ? (int) $orden['proveedor_id'] : null,
                    'item_proveedor_id'   => $linea['item_proveedor_id'] ? (int) $linea['item_proveedor_id'] : null,
                    'orden_compra_id'     => (int) $idOrden,
                    'cantidad_original'   => $cantidadBase,
                    'cantidad_disponible' => $cantidadBase,
                    'costo_unitario'      => $costoUnitarioKg,
                    'unidad_compra_id'    => $itemProv?->unidad_compra_id ?? null,
                    'factor_conversion'   => $factorConversion,
                    'precio_compra'       => $precioUnitProrrateado,
                    'lote_proveedor'      => $loteProveedor,
                ]);

                $ok = $inventarioModel->ingresarABodega($itemGeneralId, $bodegaId, $cantidadBase);
                if (!$ok) throw new \Exception("Error al ingresar al inventario el item {$itemGeneralId}.");

                $promedio = $capasModel->recalcularPromedioPonderado($itemGeneralId);
                $db->table('item_general')
                    ->where('id_item_general', $itemGeneralId)
                    ->update(['costo_produccion' => $promedio]);

                $movModel->registrar([
                    'tipo'             => \App\Models\MovimientoInventarioModel::TIPO_ENTRADA,
                    'item_general_id'  => $itemGeneralId,
                    'bodega_id'        => $bodegaId,
                    'cantidad'         => $cantidadBase,
                    'referencia_tipo'  => \App\Models\MovimientoInventarioModel::REF_OC,
                    'referencia_id'    => (int) $idOrden,
                    'descripcion'      => "Recepción lote prorrateado OC #{$orden['numero']} línea {$idDetalle}",
                    'costo_unitario'   => $costoUnitarioKg,
                    'responsable'      => $username,
                    'metadata'         => [
                        'numero_oc'              => $orden['numero'] ?? null,
                        'proveedor_id'           => $orden['proveedor_id'] ?? null,
                        'item_proveedor_id'      => $linea['item_proveedor_id'] ?? null,
                        'cantidad_recibida'      => $cantRec,
                        'unidad_compra'          => $itemProv?->unidad_compra_id ?? null,
                        'factor_conversion'      => $factorConversion,
                        'precio_unit_original'   => (float) $linea['precio_unit'],
                        'precio_unit_prorrateado'=> $precioUnitProrrateado,
                        'factor_prorrateo'       => $factor,
                        'valor_lista_total_lote' => $valorListaTotal,
                        'precio_pagado_lote'     => $precioPagado,
                        'lote_proveedor'         => $loteProveedor,
                    ],
                ]);
            }

            // Marcar OC como Recibida si no quedan líneas pendientes.
            $pendientes = (int) $db->query(
                'SELECT COUNT(*) as total FROM ordenes_compra_detalle
                 WHERE ordenes_compra_id = ? AND recibido_en IS NULL',
                [$idOrden]
            )->getRow()->total;

            if ($pendientes === 0) {
                $db->table('ordenes_compra')
                    ->where('id_orden', $idOrden)
                    ->update(['estado' => 'Recibida']);
            }

            $db->transCommit();

            log_message('info', "[OC_PRORRATEO] id={$idOrden} factor=" . round($factor, 4) . " líneas=" . count($lineasPreparadas) . " por {$username}");

            return $this->apiSuccessFlat([
                'mensaje' => 'Lote prorrateado y recibido correctamente.',
                'factor'  => round($factor, 6),
                'lineas'  => count($lineasPreparadas),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // DELETE api/ordenes_compra/{id} — solo Borrador
    public function delete($id = null)
    {
        // Acceso por módulo (política 2026-05-30): sin guard por rol.
        $orden = $this->model->detalle((int) $id);
        if (!$orden) return $this->failNotFound("Orden con ID $id no encontrada.");
        if ($orden['estado'] !== 'Borrador') {
            return $this->fail('Solo se pueden eliminar órdenes en estado Borrador.', 400);
        }
        log_message('info', "[OC_DELETE] id={$id} por {$this->getUsername()}");

        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('ordenes_compra_detalle')->where('ordenes_compra_id', $id)->delete();
        $db->table('ordenes_compra')->where('id_orden', $id)->delete();

        $db->transComplete();
        if (!$db->transStatus()) {
            return $this->fail('Error al eliminar la orden.');
        }

        return $this->respondDeleted(['mensaje' => "Orden $id eliminada correctamente"]);
    }
}