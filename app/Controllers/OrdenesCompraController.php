<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\OrdenesCompraModel;
use App\Models\InventarioModel;
use App\Models\InventarioCapasModel;

class OrdenesCompraController extends ResourceController
{
    use \App\Traits\JwtUserAware;
    use \App\Traits\ValidatesJson;

    protected $modelName = OrdenesCompraModel::class;

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
            'lineas'                    => 'required',
            'lineas.*.item_proveedor_id'=> 'required|integer|greater_than[0]',
            'lineas.*.cantidad'         => 'required|decimal|greater_than[0]',
            'lineas.*.precio_unit'      => 'required|decimal|greater_than_equal_to[0]',
        ]);
        if ($data instanceof \CodeIgniter\HTTP\ResponseInterface) return $data;

        if (!is_array($data['lineas']) || empty($data['lineas'])) {
            return $this->failValidationErrors('La orden debe tener al menos una línea.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Crear cabecera
            $lineas = $data['lineas'] ?? [];
            unset($data['lineas']);

            $data['numero'] = $this->model->generarNumero();
            $data['estado'] = 'Borrador';

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
            return $this->fail($e->getMessage(), 400);
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
        $orden = $this->model->detalle((int) $id);
        if (!$orden) return $this->failNotFound("Orden con ID $id no encontrada.");

        $data        = $this->request->getJSON(true) ?? $this->request->getPost();
        $nuevoEstado = $data['estado'] ?? null;

        $transiciones = [
            'Borrador' => ['Enviada', 'Cancelada'],
            'Enviada'  => ['Cancelada'],
        ];

        $permitidos = $transiciones[$orden['estado']] ?? [];
        if (!in_array($nuevoEstado, $permitidos)) {
            return $this->fail("No se puede cambiar de {$orden['estado']} a $nuevoEstado.", 400);
        }

        $this->model->update((int) $id, ['estado' => $nuevoEstado]);

        return $this->respond(['mensaje' => "Estado actualizado a $nuevoEstado"]);
    }

    // POST api/ordenes_compra/{id}/recibir/{detalle_id}
    // Body: { cantidad_recibida }
    // La bodega viene de la cabecera de la orden
    public function recibirLinea($idOrden = null, $idDetalle = null)
    {
        $orden = $this->model->detalle((int) $idOrden);
        if (!$orden) return $this->failNotFound("Orden con ID $idOrden no encontrada.");
        if ($orden['estado'] !== 'Enviada') {
            return $this->fail('Solo se pueden recibir líneas de órdenes Enviadas.', 400);
        }

        // Buscar la línea
        $linea = null;
        foreach ($orden['lineas'] as $l) {
            if ((int)$l['id_detalle'] === (int)$idDetalle) {
                $linea = $l;
                break;
            }
        }
        if (!$linea) return $this->failNotFound("Línea con ID $idDetalle no encontrada.");
        if ($linea['recibido_en']) return $this->fail('Esta línea ya fue recibida.', 400);

        $data              = json_decode($this->request->getBody(), true);
        $cantidadRecibida  = (float)($data['cantidad_recibida'] ?? $linea['cantidad']);
        $bodegaId          = (int) $orden['bodegas_id'];

        if ($cantidadRecibida <= 0) {
            return $this->failValidationErrors('La cantidad recibida debe ser mayor a 0.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Marcar línea como recibida
            $db->table('ordenes_compra_detalle')
                ->where('id_detalle', $idDetalle)
                ->update([
                    'cantidad_recibida' => $cantidadRecibida,
                    'recibido_en'       => date('Y-m-d H:i:s'),
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
                    'unidad_compra_id'    => $itemProv->unidad_compra_id ?? null,
                    'factor_conversion'   => $factorConversion,
                    'precio_compra'       => (float) $linea['precio_unit'],
                    'lote_proveedor'      => $data['lote_proveedor'] ?? null,
                ]);

                // Inventario agregado (compatibilidad)
                $inventarioModel = new InventarioModel();
                $ok = $inventarioModel->ingresarABodega($itemGeneralId, $bodegaId, $cantidadBase);
                if (!$ok) throw new \Exception('Error al ingresar al inventario.');

                // Recalcular promedio ponderado
                $capasModel->recalcularPromedioPonderado($itemGeneralId);

                $db->table('item_general')
                    ->where('id_item_general', $itemGeneralId)
                    ->update(['costo_produccion' => $costoUnitarioKg]);

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
                        'item_proveedor_nombre' => $itemProv->nombre ?? null,
                        'cantidad_recibida_unidad_compra' => $cantidadRecibida,
                        'unidad_compra'       => $itemProv->unidad_compra_id ?? null,
                        'factor_conversion'   => $factorConversion,
                        'precio_unit_compra'  => (float) $linea['precio_unit'],
                        'lote_proveedor'      => $data['lote_proveedor'] ?? null,
                    ],
                ]);
            }

            $db->transComplete();
            if (!$db->transStatus()) throw new \Exception('Error al confirmar la transacción.');

            // Si todas las líneas están recibidas → Recibida
            if ($this->model->todasRecibidas((int) $idOrden)) {
                $this->model->update((int) $idOrden, ['estado' => 'Recibida']);
            }

            return $this->respond(['mensaje' => 'Línea recibida correctamente']);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage(), 400);
        }
    }

    // DELETE api/ordenes_compra/{id} — solo Borrador
    public function delete($id = null)
    {
        $orden = $this->model->detalle((int) $id);
        if (!$orden) return $this->failNotFound("Orden con ID $id no encontrada.");
        if ($orden['estado'] !== 'Borrador') {
            return $this->fail('Solo se pueden eliminar órdenes en estado Borrador.', 400);
        }

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