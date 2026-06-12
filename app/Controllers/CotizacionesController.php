<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CotizacionesModel;
use App\Models\FacturasModel;
use App\Models\NumeracionModel;
use App\Models\ConfiguracionModel;

class CotizacionesController extends ResourceController
{
    use \App\Traits\JwtUserAware;
    use \App\Traits\ApiResponse;

    protected $modelName = CotizacionesModel::class;
    protected $request;

    // ── GET /cotizaciones  (?cliente_id=X) ────────────────────────────────
    public function index()
    {
        $db        = \Config\Database::connect();
        $clienteId = $this->request->getGet('cliente_id');

        $query = $db->table('cotizaciones co')
            ->select('co.*, c.nombre_empresa, c.nombre_encargado, f.numero AS numero_factura')
            ->join('clientes c', 'c.id_clientes = co.cliente_id', 'left')
            ->join('facturas f', 'f.id_facturas = co.facturas_id', 'left')
            ->orderBy('co.id_cotizaciones', 'DESC');

        if ($clienteId) $query->where('co.cliente_id', $clienteId);

        return $this->respond($query->get()->getResultArray());
    }

    // ── GET /cotizaciones/:id ─────────────────────────────────────────────
    public function show($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $db = \Config\Database::connect();

        $cotizacion = $db->table('cotizaciones co')
            ->select('co.*, c.nombre_empresa, c.nombre_encargado, c.email, c.telefono')
            ->join('clientes c', 'c.id_clientes = co.cliente_id', 'left')
            ->where('co.id_cotizaciones', $id)
            ->get()
            ->getRowArray();

        if (!$cotizacion) return $this->failNotFound("Cotización con ID $id no encontrada.");

        return $this->respond($cotizacion);
    }

    // ── GET /cotizaciones/:id/detalle ─────────────────────────────────────
    public function detalle($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $items = $this->model->get_all('cotizaciones_detalle', ['cotizaciones_id' => $id]);

        return $this->respond($items);
    }

    // ── POST /cotizaciones ────────────────────────────────────────────────
    public function create()
    {
        $data = $this->request->getJSON(true);
        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        // Validación de tipos/forma sin romper el contrato actual:
        // requiere cliente_id O cliente_libre (al menos uno); fecha opcional valid_date;
        // items requerido (array, min 1); validez_dias opcional entero >= 0.
        // NOTA: cliente_libre solo cumple el "al menos uno", pero el flujo actual
        // exige cliente_id real para crear — el bloque interno valida eso luego.
        $tieneClienteId    = !empty($data['cliente_id']) && (int) $data['cliente_id'] > 0;
        $tieneClienteLibre = isset($data['cliente_libre']) && trim((string) $data['cliente_libre']) !== '';
        if (!$tieneClienteId && !$tieneClienteLibre) {
            return $this->apiValidationError(['cliente_id' => 'Se requiere cliente_id o cliente_libre']);
        }
        if (!isset($data['items']) || !is_array($data['items']) || count($data['items']) < 1) {
            return $this->apiValidationError(['items' => 'Debe enviarse un array de items con al menos 1 elemento']);
        }
        if (!empty($data['fecha'])) {
            $ts = strtotime((string) $data['fecha']);
            if ($ts === false) {
                return $this->apiValidationError(['fecha' => 'Fecha inválida']);
            }
        }
        if (isset($data['validez_dias'])) {
            $vd = $data['validez_dias'];
            if (!is_numeric($vd) || (int) $vd < 0 || (int) $vd != $vd) {
                return $this->apiValidationError(['validez_dias' => 'Debe ser entero >= 0']);
            }
        }

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            // Validar cliente existe y no está soft-deleted (FK solo cubre IDs inexistentes).
            if (empty($data['cliente_id'])) {
                throw new \Exception('cliente_id es obligatorio');
            }
            $clienteExiste = $db->table('clientes')
                ->where('id_clientes', $data['cliente_id'])
                ->where('deleted_at', null)
                ->countAllResults();
            if (!$clienteExiste) {
                throw new \Exception('El cliente seleccionado no existe o fue eliminado.');
            }

            $items = $data['items'] ?? [];
            unset($data['items'], $data['cliente_libre']);

            if (empty($data['numero'])) $data['numero'] = (new NumeracionModel())->reservar('cotizacion');
            if (empty($data['estado'])) $data['estado'] = 'Borrador';

            // Limpiar campos opcionales vacíos
            $data['fecha_vencimiento'] = $data['fecha_vencimiento'] ?? null ?: null;
            $data['observaciones']     = $data['observaciones']     ?? null ?: null;
            $data['facturas_id']       = $data['facturas_id']       ?? null ?: null;

            // Recalcular totales en el SERVIDOR — no confiar en los montos del cliente.
            // subtotal de línea = round(cantidad × precio_unit × (1 − descuento_pct/100)) a pesos
            // ENTEROS (COP), idéntico al Math.round() del frontend (CotizacionForm.jsx:191) para que
            // el total guardado coincida con el mostrado.
            // total = subtotal − descuento + impuestos − retención (misma fórmula que CotizacionForm:238;
            // descuento/impuestos/retención de cabecera son montos que define el usuario).
            $lineasCalc   = [];
            $subtotalCalc = 0.0;
            foreach ($items as $item) {
                $cantidad = (float) ($item['cantidad']      ?? 0);
                $precio   = (float) ($item['precio_unit']   ?? 0);
                $descPct  = (float) ($item['descuento_pct'] ?? 0);
                $subLinea = round($cantidad * $precio * (1 - $descPct / 100), 0);
                $subtotalCalc += $subLinea;
                $lineasCalc[] = [$item['descripcion'] ?? '', $cantidad, $precio, $descPct, $subLinea];
            }
            $descuentoHdr = (float) ($data['descuento']  ?? 0);
            $impuestosHdr = (float) ($data['impuestos']  ?? 0);
            $retencionHdr = (float) ($data['retencion']  ?? 0);
            $data['subtotal'] = $subtotalCalc;
            $data['total']    = round($subtotalCalc - $descuentoHdr + $impuestosHdr - $retencionHdr, 0);

            $id = $this->model->create_table($data, 'cotizaciones');
            if (!$id) throw new \Exception(implode(', ', $this->model->errors()));

            // Insertar ítems con query directa para evitar conflictos de allowedFields
            if (!empty($lineasCalc)) {
                foreach ($lineasCalc as $linea) {
                    $db->query(
                        "INSERT INTO cotizaciones_detalle
                            (cotizaciones_id, descripcion, cantidad, precio_unit, descuento_pct, subtotal)
                         VALUES (?, ?, ?, ?, ?, ?)",
                        [$id, $linea[0], $linea[1], $linea[2], $linea[3], $linea[4]]
                    );
                }
            }

            $db->transCommit();

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Cotización creada exitosamente',
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // ── PUT /cotizaciones/:id ─────────────────────────────────────────────
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);
        if (!$id)   return $this->fail('ID no proporcionado', 400);
        if (!$data) return $this->fail('No se recibieron datos o el JSON es inválido', 400);

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Cotización con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Convertida') {
                throw new \Exception('No se puede editar una cotización ya convertida a factura');
            }

            unset($data['items'], $data['cliente_libre']);
            $this->model->update_table($id, $data, 'cotizaciones');

            return $this->respond([
                'status'  => 200,
                'message' => "Cotización $id actualizada correctamente",
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── PATCH /cotizaciones/:id/estado ────────────────────────────────────
    public function cambiarEstado($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $data       = $this->request->getJSON(true);
        $estado     = $data['estado'] ?? null;
        $permitidos = ['Borrador', 'Enviada', 'Aceptada', 'Rechazada', 'Vencida'];

        if (!$estado || !in_array($estado, $permitidos)) {
            return $this->fail('Estado no válido. Permitidos: ' . implode(', ', $permitidos), 400);
        }

        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Cotización con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Convertida') {
                throw new \Exception('No se puede cambiar el estado de una cotización convertida');
            }

            $this->model->update_table($id, ['estado' => $estado], 'cotizaciones');

            return $this->respond([
                'status'  => 200,
                'message' => "Cotización marcada como $estado",
                'data'    => $this->model->find($id),
            ]);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── POST /cotizaciones/:id/convertir ──────────────────────────────────
    public function convertir($id = null)
    {
        if (!$id) return $this->apiFail('ID no proporcionado', 400);

        $cotizacion = $this->model->find($id);
        if (!$cotizacion) return $this->apiNotFound("Cotización con ID $id no encontrada.");

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            if ($cotizacion['estado'] === 'Convertida') {
                throw new \Exception('Esta cotización ya fue convertida a factura');
            }
            if (!in_array($cotizacion['estado'], ['Aceptada', 'Enviada'])) {
                throw new \Exception('Solo se pueden convertir cotizaciones en estado Aceptada o Enviada');
            }

            // Verificar que el cliente sigue vigente: pudo eliminarse entre
            // la creación de la cotización y la conversión.
            $clienteExiste = $db->table('clientes')
                ->where('id_clientes', $cotizacion['cliente_id'])
                ->where('deleted_at', null)
                ->countAllResults();
            if (!$clienteExiste) {
                throw new \Exception('El cliente de la cotización fue eliminado. No se puede generar la factura.');
            }

            $facturaModel = new FacturasModel();
            $numeroFac    = (new NumeracionModel())->reservar('factura');

            $facturaData = [
                'numero'            => $numeroFac,
                'cliente_id'        => $cotizacion['cliente_id'],
                'fecha_emision'     => date('Y-m-d'),
                'fecha_vencimiento' => date('Y-m-d', strtotime('+' . ((int) (new ConfiguracionModel())->obtener('dias_vencimiento_factura', 30)) . ' days')),
                'subtotal'          => $cotizacion['subtotal'],
                'descuento'         => $cotizacion['descuento'],
                'impuestos'         => $cotizacion['impuestos'],
                'retencion'         => $cotizacion['retencion'],
                'total'             => $cotizacion['total'],
                'saldo_pendiente'   => $cotizacion['total'],
                'estado'            => 'Pendiente',
                'observaciones'     => "Generada desde cotización {$cotizacion['numero']}",
            ];

            $facturaId = $facturaModel->create_table($facturaData, 'facturas');
            if (!$facturaId) throw new \Exception('Error al generar la factura');

            // Copiar ítems con query directa
            $items = $this->model->get_all('cotizaciones_detalle', ['cotizaciones_id' => $id]);
            if (!empty($items)) {
                foreach ($items as $item) {
                    $db->query(
                        "INSERT INTO facturas_detalle
                            (facturas_id, descripcion, cantidad, precio_unit, descuento_pct, subtotal)
                         VALUES (?, ?, ?, ?, ?, ?)",
                        [
                            $facturaId,
                            $item['descripcion'],
                            $item['cantidad'],
                            $item['precio_unit'],
                            $item['descuento_pct'] ?? 0,
                            $item['subtotal'],
                        ]
                    );
                }
            }

            $this->model->update_table($id, [
                'estado'      => 'Convertida',
                'facturas_id' => $facturaId,
            ], 'cotizaciones');

            $db->transCommit();

            return $this->respondCreated([
                'status'  => 201,
                'message' => "Cotización convertida. Factura {$numeroFac} creada.",
                'data'    => $facturaModel->find($facturaId),
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    // ── DELETE /cotizaciones/:id ──────────────────────────────────────────
    public function delete($id = null)
    {
        // Acceso por módulo (política 2026-05-30): sin guard por rol.
        $existing = $this->model->find($id);
        if (!$existing) return $this->failNotFound("Cotización con ID $id no encontrada.");

        try {
            if ($existing['estado'] === 'Convertida') {
                throw new \Exception('No se puede eliminar una cotización ya convertida a factura');
            }
            $this->model->delete_table($id, 'cotizaciones');
            log_message('info', "[COTIZACION_DELETE] id={$id} por {$this->getUsername()}");
            return $this->respondDeleted(['message' => "Cotización $id eliminada"]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    // ── Número correlativo: ahora delegado a NumeracionModel::reservar() ──
}