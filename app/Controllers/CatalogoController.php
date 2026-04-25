<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CatalogoModel;

class CatalogoController extends ResourceController
{
    protected $modelName = CatalogoModel::class;

    public function index()
    {
        $tipo       = $this->request->getGet('tipo');
        $categoria  = $this->request->getGet('categoria_id');
        $busqueda   = $this->request->getGet('q');

        $items = $this->model->listar(
            $tipo !== null && $tipo !== '' ? (int) $tipo : null,
            $categoria !== null && $categoria !== '' ? (int) $categoria : null,
            $busqueda ?: null
        );

        return $this->respond($items);
    }

    public function show($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $item = $this->model->detalle((int) $id);

        if (!$item) {
            return $this->failNotFound('Ítem no encontrado.');
        }

        return $this->respond($item);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->fail('No se recibieron datos válidos.', 400);
        }

        if (empty($data['nombre'])) {
            return $this->failValidationErrors('El nombre es obligatorio.');
        }

        try {
            $newId = $this->model->crearItem($data);

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Ítem creado en el catálogo',
                'id'      => $newId,
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function update($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $data = $this->request->getJSON(true);
        if (!$data) {
            return $this->fail('No se recibieron datos válidos.', 400);
        }

        try {
            $this->model->actualizarItem((int) $id, $data);

            return $this->respond([
                'status'  => 200,
                'message' => "Ítem {$id} actualizado correctamente",
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 400);
        }
    }

    public function delete($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $item = $this->model->find($id);
        if (!$item) {
            return $this->failNotFound("Ítem con ID {$id} no encontrado.");
        }

        $this->model->delete($id);
        return $this->respondDeleted(['message' => "Ítem {$id} eliminado del catálogo"]);
    }

    public function proveedores($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $proveedores = $this->model->proveedoresDeItem((int) $id);
        return $this->respond($proveedores);
    }
}
