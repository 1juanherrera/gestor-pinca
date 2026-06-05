<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\CatalogoModel;

class CatalogoController extends ResourceController
{
    use \App\Traits\ValidatesJson;
    use \App\Traits\JwtUserAware;
    use \App\Traits\ApiResponse;

    protected $modelName = CatalogoModel::class;

    private const RULES_BASE = [
        'nombre'        => 'required|max_length[100]',
        'codigo'        => 'permit_empty|max_length[10]',
        'tipo'          => 'permit_empty',
        'categoria_id'  => 'permit_empty|integer',
        'unidad_id'     => 'permit_empty|integer',
        'unidad_almacenaje_id' => 'permit_empty|integer',
    ];

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
        $data = $this->validateJson(self::RULES_BASE);
        if ($data instanceof ResponseInterface) return $data;

        try {
            $newId = $this->model->crearItem($data);

            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Ítem creado en el catálogo',
                'id'      => $newId,
            ]);
        } catch (\Exception $e) {
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    public function update($id = null)
    {
        if (!$id) return $this->apiFail('ID no proporcionado', 400);

        $data = $this->validateJson(self::RULES_BASE);
        if ($data instanceof ResponseInterface) return $data;

        try {
            $this->model->actualizarItem((int) $id, $data);

            return $this->respond([
                'status'  => 200,
                'message' => "Ítem {$id} actualizado correctamente",
            ]);
        } catch (\Exception $e) {
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    public function delete($id = null)
    {
        if (!$id) return $this->apiFail('ID no proporcionado', 400);

        $item = $this->model->find($id);
        if (!$item) {
            return $this->apiNotFound("Ítem con ID {$id} no encontrado.");
        }

        // Rechazar si el ítem tiene stock activo: si lo archivamos, los rows
        // de inventario_capas quedan huérfanos (nombre = NULL en queries con
        // LEFT JOIN). El usuario debe ajustar el stock a 0 o usar Merge.
        $stock = (float) (db_connect()->table('inventario_capas')
            ->selectSum('cantidad_disponible', 'total')
            ->where('item_general_id', (int) $id)
            ->where('estado', 1)
            ->get()->getRow()->total ?? 0);
        if ($stock > 0.0001) {
            return $this->apiFail(
                "No se puede archivar el ítem #{$id}: tiene {$stock} unidades de stock activo. " .
                "Ajustá el stock a 0 (Inventario → AjusteManual) o usá Sincronización → Merge.",
                409
            );
        }

        // useSoftDeletes activo → delete() hace UPDATE deleted_at, no DELETE físico
        $this->model->delete($id);
        log_message('info', "[DELETE_CATALOGO] usuario={$this->getUsername()} id={$id}");
        return $this->respondDeleted(['message' => "Ítem {$id} archivado del catálogo"]);
    }

    /**
     * POST /api/catalogo/:id/restore — restaura un ítem soft-deleted.
     */
    public function restore($id = null)
    {
        if (!$id) return $this->failValidationErrors('No se proporcionó un ID válido.');

        // withDeleted() incluye los borrados; find() devolvería null para ellos
        $item = $this->model->withDeleted()->find($id);
        if (!$item) {
            return $this->failNotFound("Ítem con ID {$id} no encontrado.");
        }
        if (empty($item['deleted_at'])) {
            return $this->fail('El ítem no está archivado.');
        }
        $this->model->update($id, ['deleted_at' => null]);
        return $this->respond(['message' => "Ítem {$id} restaurado."]);
    }

    public function proveedores($id = null)
    {
        if (!$id) return $this->fail('ID no proporcionado', 400);

        $proveedores = $this->model->proveedoresDeItem((int) $id);
        return $this->respond($proveedores);
    }
}
