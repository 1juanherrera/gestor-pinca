<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FacturasModel;
use App\Models\NumeracionModel;

class FacturasController extends ResourceController
{
    use \App\Traits\ValidatesJson;
    use \App\Traits\JwtUserAware;
    use \App\Traits\ApiResponse;

    protected $modelName = FacturasModel::class;
    protected $request;

    // ── GET /facturas ─────────────────────────────────────────────────────
    // Sin cambios — ya funciona y devuelve JOIN con clientes
    public function index()
    {
        $db = \Config\Database::connect();

        $facturas = $db->table('facturas f')
            ->select('f.*, c.nombre_empresa, c.nombre_encargado, c.numero_documento AS nit_cliente,
                      c.tipo AS cliente_tipo, c.ciudad, c.plazo_pago')
            ->join('clientes c', 'c.id_clientes = f.cliente_id', 'left')
            ->orderBy('f.id_facturas', 'DESC')
            ->get()
            ->getResultArray();

        return $this->respond($facturas);
    }

    // ── GET /facturas/:id ─────────────────────────────────────────────────
    // Sin cambios — ya devuelve datos completos del cliente
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $db = \Config\Database::connect();

        $factura = $db->table('facturas f')
            ->select('f.*, c.nombre_empresa, c.nombre_encargado, c.numero_documento, c.email, c.telefono, c.direccion')
            ->join('clientes c', 'c.id_clientes = f.cliente_id', 'left')
            ->where('f.id_facturas', $id)
            ->get()
            ->getRowArray();

        if (!$factura) return $this->failNotFound("Factura con ID $id no encontrada.");

        return $this->respond($factura);
    }

    // ── GET /facturas/:id/detalle ─────────────────────────────────────────
    // Sin cambios
    public function detalle($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $items = $this->model->get_all('facturas_detalle', ['facturas_id' => $id]);

        return $this->respond($items);
    }

    // ── GET /facturas/:id/abonos ──────────────────────────────────────────
    // CAMBIO: ahora hace JOIN con clientes para devolver nombre_empresa
    // igual que PagosClienteController::index
    public function abonos($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $db = \Config\Database::connect();

        $abonos = $db->table('pagos_cliente p')
            ->select('p.*, c.nombre_empresa, c.nombre_encargado')
            ->join('clientes c', 'c.id_clientes = p.clientes_id', 'left')
            ->where('p.facturas_id', $id)
            ->orderBy('p.fecha_pago', 'DESC')
            ->get()
            ->getResultArray();

        return $this->respond($abonos);
    }

    // ── GET /facturas/:id/remision ────────────────────────────────────────
    // Sin cambios
    public function remision($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $db       = \Config\Database::connect();
        $remision = $db->table('remisiones')
            ->where('facturas_id', $id)
            ->get()
            ->getRowArray();

        return $this->respond($remision);
    }

    // ── POST /facturas ────────────────────────────────────────────────────
    // CAMBIO: envuelto en transacción — si falla el insert de items
    // hace rollback del encabezado también
    public function create()
    {
        $data = $this->validateJson([
            'cliente_id'             => 'required|integer|greater_than[0]',
            'fecha_emision'          => 'permit_empty|valid_date',
            'fecha_vencimiento'      => 'permit_empty|valid_date',
            'subtotal'               => 'permit_empty|decimal',
            'descuento'              => 'permit_empty|decimal|greater_than_equal_to[0]',
            'impuestos'              => 'permit_empty|decimal|greater_than_equal_to[0]',
            'retencion'              => 'permit_empty|decimal|greater_than_equal_to[0]',
            'total'                  => 'required|decimal|greater_than_equal_to[0]',
            'items'                  => 'required',
            'items.*.descripcion'    => 'required|max_length[255]',
            'items.*.cantidad'       => 'required|decimal|greater_than[0]',
            'items.*.precio_unit'    => 'required|decimal|greater_than_equal_to[0]',
        ]);
        if ($data instanceof \CodeIgniter\HTTP\ResponseInterface) return $data;

        if (!is_array($data['items']) || empty($data['items'])) {
            return $this->apiValidationError(['items' => 'La factura debe tener al menos un ítem.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Validar que el cliente exista y no esté soft-deleted antes de
            // crear la factura — el FK constraint solo rechazaría un id
            // inexistente, no uno soft-deleted.
            $clienteExiste = $db->table('clientes')
                ->where('id_clientes', $data['cliente_id'])
                ->where('deleted_at', null)
                ->countAllResults();
            if (!$clienteExiste) {
                throw new \Exception('El cliente seleccionado no existe o fue eliminado.');
            }

            $items = $data['items'] ?? [];
            unset($data['items']);

            $data['saldo_pendiente'] = $data['total'] ?? 0;
            if (empty($data['numero'])) $data['numero'] = (new NumeracionModel())->reservar('factura');

            $id = $this->model->create_table($data, 'facturas');
            if (!$id) throw new \Exception(implode(', ', $this->model->errors()));

            if (!empty($items)) {
                foreach ($items as &$item) {
                    $item['facturas_id'] = $id;
                }
                $this->model->create_table($items, 'facturas_detalle');
            }

            $db->transComplete();
            if (!$db->transStatus()) throw new \Exception('Error al confirmar la transacción');

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Factura creada exitosamente',
                'data'    => $this->model->get($id, 'facturas'),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // ── PUT /facturas/:id ─────────────────────────────────────────────────
    // Sin cambios — edición de cabecera funciona bien
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if (!$id)   return $this->apiFail('ID no proporcionado', 400);
        if (!$data) return $this->apiFail('No se recibieron datos o el JSON es inválido', 400);
        if (!$this->model->find($id)) return $this->apiNotFound("Factura con ID $id no encontrada.");

        try {
            $this->model->update_table($id, $data, 'facturas');

            // Si cambió `total`, hay que recalcular saldo para mantener cartera
            // consistente: total - pagos - NC activas. Mismo principio que en
            // PagosClienteController/NotasCreditoController.
            if (array_key_exists('total', $data)) {
                $this->model->recalcularSaldo((int) $id);
            }

            return $this->respond([
                'status'  => 200,
                'message' => "Factura $id actualizada correctamente",
                'data'    => $this->model->get($id, 'facturas'),
            ]);

        } catch (\Exception $e) {
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // ── PATCH /facturas/:id/estado ────────────────────────────────────────
    // Body: { "estado": "Pagada" | "Pendiente" | "Parcial" | "Vencida" | "Anulada" }
    //
    // Atomicidad: la lectura del estado actual + validación + UPDATE va dentro
    // de transBegin + SELECT … FOR UPDATE para evitar transiciones concurrentes
    // contradictorias (ej: dos requests intentando Pagada y Anulada a la vez).
    //
    // Anular además revierte pagos_cliente (delete) y notas_credito asociadas
    // (estado='Anulada') para que cartera quede consistente.
    public function cambiarEstado($id = null)
    {
        if (!$id) return $this->apiFail('ID no proporcionado', 400);

        $data       = $this->request->getJSON(true);
        $estado     = $data['estado'] ?? null;
        $permitidos = ['Pendiente', 'Pagada', 'Parcial', 'Vencida', 'Anulada'];

        if (!$estado || !in_array($estado, $permitidos, true)) {
            return $this->apiFail('Estado no válido. Permitidos: ' . implode(', ', $permitidos), 400);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Lock pesimista sobre la factura. FOR UPDATE garantiza que ninguna
            // otra transacción pueda leer/escribir este registro hasta el commit.
            $factura = $db->query(
                'SELECT id_facturas, estado, total FROM facturas
                 WHERE id_facturas = ? AND deleted_at IS NULL
                 FOR UPDATE',
                [$id]
            )->getRowArray();

            if (!$factura) {
                $db->transRollback();
                return $this->apiNotFound("Factura con ID $id no encontrada.");
            }

            $estadoActual = $factura['estado'];

            // Anulada es terminal. No se puede salir de ahí.
            if ($estadoActual === 'Anulada' && $estado !== 'Anulada') {
                $db->transRollback();
                return $this->apiFail('La factura ya está anulada. No se puede cambiar a otro estado.', 409);
            }

            if ($estado === 'Anulada') {
                // Revertir pagos y NC para que cartera no quede inconsistente.
                $pagosBorrados = $db->table('pagos_cliente')
                    ->where('facturas_id', $id)
                    ->countAllResults(false);
                $db->table('pagos_cliente')->where('facturas_id', $id)->delete();

                $ncAnuladas = $db->table('notas_credito')
                    ->where('facturas_id', $id)
                    ->where('estado', 'Activa')
                    ->countAllResults(false);
                $db->table('notas_credito')
                    ->where('facturas_id', $id)
                    ->where('estado', 'Activa')
                    ->update(['estado' => 'Anulada']);

                $db->table('facturas')
                    ->where('id_facturas', $id)
                    ->update([
                        'estado'          => 'Anulada',
                        'saldo_pendiente' => $factura['total'],
                    ]);

                $username = $this->request->usuario->username ?? 'sistema';
                log_message('info', "[FACTURA_ANULADA] id={$id} por {$username} — pagos revertidos={$pagosBorrados}, NC anuladas={$ncAnuladas}");

            } elseif ($estado === 'Pagada') {
                // Recalcula primero para verificar que realmente está pagada;
                // recalcularSaldo respeta el lock vigente.
                $this->model->recalcularSaldo((int) $id);
                $db->table('facturas')
                    ->where('id_facturas', $id)
                    ->update(['estado' => 'Pagada', 'saldo_pendiente' => 0]);

            } else {
                $db->table('facturas')
                    ->where('id_facturas', $id)
                    ->update(['estado' => $estado]);
            }

            $db->transCommit();

            return $this->respond([
                'status'  => 200,
                'message' => "Factura marcada como $estado",
                'data'    => $this->model->get($id, 'facturas'),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // ── DELETE /facturas/:id ──────────────────────────────────────────────
    public function delete($id = null)
    {
        if (!$this->userHasAdminAccess()) {
            return $this->apiForbidden('Solo administradores pueden eliminar facturas.');
        }
        if (!$this->model->find($id)) return $this->apiNotFound("Factura con ID $id no encontrada.");

        $this->model->delete_table($id, 'facturas');
        log_message('info', "[FACTURA_DELETE] id={$id} por {$this->getUsername()}");

        return $this->respondDeleted(['message' => "Factura $id eliminada"]);
    }
}