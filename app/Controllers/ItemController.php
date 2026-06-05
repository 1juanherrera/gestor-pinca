<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ItemModel;

class ItemController extends ResourceController
{
    use \App\Traits\ApiResponse;

    use \App\Traits\JwtUserAware;

    protected $modelName = ItemModel::class;
    protected $request;

    public function item_general()
    {
        $items = $this->model->get_all('item_general');
        return $this->respond($items);
    }

    public function materias_disponibles()
    {
        $data = $this->model->get_materias_disponibles();
        return $this->respond($data);
    }

    public function get_items_all(){
        $items = $this->model->get_items_all();
        return $this->respond($items);
    }

    public function show($id = null)
    {
        if (!$id) return $this->apiFail("ID no proporcionado", 400);

        $item = $this->model->get_full_item_details($id);

        if (!$item) {
            return $this->apiNotFound("El item no existe.");
        }

        return $this->respond($item);
    }

    public function create()
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->apiFail('No se recibieron datos o el JSON es inválido', 400);
        }

        if (empty($data['nombre'])) {
            return $this->apiFail('El nombre es obligatorio.', 422);
        }

        try {
            // Ejecutamos el modelo
            $result = $this->model->create_full_item($data);

            // Si llegó aquí es porque funcionó
            return $this->respondCreated([
                'status'  => 201,
                'message' => 'Ítem completo creado con éxito',
                'id'      => $result
            ]);

        } catch (\Exception $e) {
            // 🔥 AQUÍ ESTÁ LA MAGIA: 
            // Si el modelo falla, capturamos el mensaje real (ej. "Data too long") 
            // y lo devolvemos como un error 400 limpio en Postman.
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    /**
     * GET /api/item_general/buscar?q=texto&limit=10
     * Búsqueda fuzzy: maneja errores tipográficos mediante LIKE por token + SOUNDEX.
     */
    public function buscar(): \CodeIgniter\HTTP\ResponseInterface
    {
        $q     = trim($this->request->getGet('q') ?? '');
        $limit = min((int) ($this->request->getGet('limit') ?? 10), 30);

        if (strlen($q) < 2) {
            return $this->respond([]);
        }

        // ?tipos=1,2  → solo Materia Prima e Insumo
        $tiposRaw = trim($this->request->getGet('tipos') ?? '');
        $tipos    = $tiposRaw !== ''
            ? array_map('intval', explode(',', $tiposRaw))
            : [];

        $resultados = $this->model->buscarFuzzy($q, $limit, $tipos);
        return $this->respond($resultados);
    }

    public function update($id = null)
    {
        // 1. Obtener datos del cuerpo de la petición (soporta JSON de React)
        $data = $this->request->getJSON(true);

        if (!$id) {
            return $this->apiFail("ID no proporcionado", 400);
        }

        try {
            // 2. Llamamos a nuestra función personalizada que actualiza
            // item_general, costos_item y las formulaciones en una sola transacción
            $this->model->update_full_item($id, $data);

            return $this->respond([
                'status'  => 200,
                'message' => "Item $id y sus dependencias actualizados correctamente"
            ]);

        } catch (\Exception $e) {
            // 3. Si algo falla (el item no existe o error SQL), devolvemos el error real
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    public function updatePrecioManual($id = null)
    {
        if (!$id) return $this->apiFail('ID no proporcionado', 400);

        $data = $this->request->getJSON(true);
        if (!$data) return $this->apiFail('No se recibieron datos válidos.', 400);

        try {
            $this->model->update_precio_manual($id, $data);
            return $this->respond([
                'success' => true,
                'mensaje' => "Precio manual del item $id actualizado correctamente",
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return $this->apiFail($e->getMessage(), 400);
        }
    }

    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->apiNotFound("Item con ID $id no encontrado.");
        }

        // Chequear dependencias para devolver un 409 claro en vez de un 500 por FK.
        $db  = \Config\Database::connect();
        $iid = (int) $id;
        $bloqueos = [];

        $stock = (float) ($db->table('inventario_capas')
            ->selectSum('cantidad_disponible', 'total')
            ->where('item_general_id', $iid)->where('estado', 1)
            ->get()->getRow()->total ?? 0);
        if ($stock > 0.0001) $bloqueos[] = "tiene {$stock} de stock activo";

        if ($db->tableExists('item_general_formulaciones')) {
            $usos = $db->table('item_general_formulaciones')->where('item_general_id', $iid)->countAllResults();
            if ($usos > 0) $bloqueos[] = "se usa como ingrediente en {$usos} fórmula(s)";
        }
        $tieneFormula = $db->table('formulaciones')
            ->where('item_general_id', $iid)->where('deleted_at', null)->countAllResults();
        if ($tieneFormula > 0) $bloqueos[] = "tiene {$tieneFormula} fórmula(s) propia(s)";

        if (!empty($bloqueos)) {
            return $this->apiFail(
                "No se puede eliminar el ítem #{$id}: " . implode(', ', $bloqueos) .
                ". Quitá esas dependencias o usá Sincronización → Merge para unificarlo.",
                409
            );
        }

        try {
            $this->model->delete($id);
        } catch (\Throwable $e) {
            log_message('error', "[DELETE_ITEM] FK/constraint id={$id}: " . $e->getMessage());
            return $this->apiFail(
                "No se puede eliminar el ítem #{$id} porque está referenciado por otros registros.",
                409
            );
        }
        log_message('info', "[DELETE_ITEM] usuario={$this->getUsername()} id={$id}");
        return $this->respondDeleted(['mensaje' => "Item $id eliminado"]);
    }

    public function inventario_por_item($id = null)
    {
        if (!$id) return $this->apiFail('ID no proporcionado', 400);

        $db = \Config\Database::connect();

        $rows = $db->query("
            SELECT
                inv.id_inventario,
                inv.cantidad,
                inv.bodegas_id,
                COALESCE(b.nombre, CONCAT('Bodega #', inv.bodegas_id))  AS bodega,
                ins.nombre AS sede
            FROM inventario inv
            LEFT JOIN bodegas b         ON b.id_bodegas        = inv.bodegas_id
            LEFT JOIN instalaciones ins ON ins.id_instalaciones = b.instalaciones_id
            WHERE inv.item_general_id = ?
            ORDER BY bodega ASC
        ", [$id])->getResultArray();

        return $this->respond($rows);
    }
}
