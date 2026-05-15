<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ItemProveedorModel;
use App\Models\BaseModel;

class ItemProveedorController extends ResourceController
{
    protected $modelName = ItemProveedorModel::class;

    /**
     * Valida factor_conversion > 0 cuando viene en el payload.
     * Un factor 0 o negativo causa división por cero al recibir OC
     * (cantidad_base = qty * factor; costo_kg = precio / factor).
     */
    private function validarFactorConversion(array $data): void
    {
        if (!array_key_exists('factor_conversion', $data)) return;
        if ($data['factor_conversion'] === null || $data['factor_conversion'] === '') return;
        $f = (float) $data['factor_conversion'];
        if ($f <= 0) {
            throw new \InvalidArgumentException('El factor de conversión debe ser mayor a 0. Recibido: ' . $data['factor_conversion']);
        }
    }

    public function item_proveedores()
    {
        $proveedores = $this->model->get_all('item_proveedor');
        return $this->respond($proveedores);
    }

    public function get_item_proveedores()
    {
        $data = $this->model->get_item_proveedores();
        if (!$data) {
            return $this->failNotFound("No se encontraron ítems de proveedores.");
        }
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $item_proveedor = $this->model->get($id, 'item_proveedor');
        if (!$item_proveedor) {
            return $this->failNotFound("Item Proveedor con ID $id no encontrado.");
        }
        return $this->respond($item_proveedor);
    }

    public function create()
    {
        $data = json_decode($this->request->getBody(), true);
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }

        try {
            $this->validarFactorConversion($data);
            $itemGeneralId = $this->model->resolverItemGeneral($data);
            $insertId      = $this->model->create_table($data, 'item_proveedor');

            if (!$insertId) {
                return $this->fail('Error al crear el Item Proveedor');
            }

            return $this->respondCreated([
                'mensaje'         => 'Item Proveedor creado correctamente',
                'id'              => $insertId,
                'item_general_id' => $itemGeneralId,
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function update($id = null)
    {
        $data = json_decode($this->request->getBody(), true);
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        if (!$this->model->get($id, 'item_proveedor')) {
            return $this->failNotFound("Item Proveedor con ID $id no encontrado.");
        }

        try {
            $this->validarFactorConversion($data);
            $this->model->resolverItemGeneral($data);
            $updated = $this->model->update_table($id, $data, 'item_proveedor');

            if ($updated === false || (is_array($updated) && isset($updated['error']))) {
                return $this->fail('No se pudo actualizar el Item Proveedor.');
            }

            return $this->respond([
                'mensaje'         => "Item Proveedor con ID $id actualizado correctamente",
                'item_general_id' => $data['item_general_id'],
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function delete($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        if (!$this->model->get($id, 'item_proveedor')) {
            return $this->failNotFound("Item Proveedor con ID $id no encontrado.");
        }
        $deleted = $this->model->delete_table($id, 'item_proveedor');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar el Item Proveedor con ID $id.");
        }
        return $this->respondDeleted([
            'mensaje' => "Item Proveedor con ID $id eliminada correctamente"
        ]);
    }

    // ── PATCH api/item_proveedores/{id}/vincular ──────────────────────────
    // Casos:
    //   { item_general_id: 5 }                      → vincula a ítem existente
    //   { crear: true, nombre, tipo, ... }           → crea ítem nuevo y vincula
    //   { item_general_id: null }                    → desvincula
    //   + { bodegas_id, cantidad } en cualquier caso → ingresa al inventario
    public function vincular($id = null)
    {
        $data = json_decode($this->request->getBody(), true);

        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }

        $itemProveedor = $this->model->get($id, 'item_proveedor');
        if (!$itemProveedor) {
            return $this->failNotFound("Item Proveedor con ID $id no encontrado.");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $itemGeneralId = null;

            // Caso A: crear ítem nuevo en item_general
            if (!empty($data['crear']) && $data['crear'] === true) {
                $baseModel     = new BaseModel();
                $itemGeneralId = $baseModel->create_table([
                    'nombre'       => $data['nombre']       ?? $itemProveedor->nombre,
                    'codigo'       => $data['codigo']       ?? $itemProveedor->codigo,
                    'tipo'         => $data['tipo']         ?? 2,
                    'unidad_id'    => $data['unidad_id']    ?? null,
                    'categoria_id' => $data['categoria_id'] ?? null,
                ], 'item_general');

                if (!$itemGeneralId) {
                    throw new \Exception('No se pudo crear el ítem general.');
                }

            // Caso B: vincular a ítem existente o desvincular (null)
            } else {
                $itemGeneralId = isset($data['item_general_id'])
                    ? ($data['item_general_id'] === null ? null : (int) $data['item_general_id'])
                    : null;
            }

            // Actualizar vínculo + unidad de compra + factor de conversión
            $unidadCompraId   = isset($data['unidad_compra_id'])   ? (int)   $data['unidad_compra_id']   : null;
            $factorConversion = isset($data['factor_conversion'])  ? (float) $data['factor_conversion']  : 1.0;
            if ($factorConversion <= 0) {
                throw new \InvalidArgumentException('El factor de conversión debe ser mayor a 0.');
            }
            $this->model->vincular((int) $id, $itemGeneralId, $unidadCompraId, $factorConversion);

            // Inventario NO se crea al vincular — stock solo ingresa por OC o Producción

            $db->transComplete();

            if (!$db->transStatus()) {
                throw new \Exception('Error al confirmar la transacción.');
            }

            return $this->respond([
                'mensaje'         => 'Ítem vinculado correctamente',
                'item_general_id' => $itemGeneralId,
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail($e->getMessage(), 400);
        }
    }
}