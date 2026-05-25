<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\IncomingRequest;
use App\Models\BodegasModel;
use Exception;

class BodegasController extends ResourceController
{
    use \App\Traits\JwtUserAware;

    protected $modelName = BodegasModel::class;
    protected IncomingRequest $req;

    public function __construct()
    {
        $this->req = service('request'); // ✅
    }

    public function bodegas()
    {
        $db = \Config\Database::connect();
        $bodegas = $db->query(
            'SELECT b.*, i.nombre AS sede_nombre
             FROM bodegas b
             LEFT JOIN instalaciones i ON i.id_instalaciones = b.instalaciones_id
             ORDER BY i.nombre, b.nombre'
        )->getResultArray();
        return $this->respond($bodegas);
    }

    public function bodega_inventario($id = null)
    {
        try {
            if ($id === null) {
                return $this->failValidationErrors('El ID de bodega es obligatorio.');
            }

            $page    = $this->req->getGet('page')    ?? 1;
            $perPage = $this->req->getGet('perPage') ?? 10;
            $search  = $this->req->getGet('search')  ?? '';
            $tipo    = $this->req->getGet('tipo')    ?? '';

            $data = $this->model->bodega_inventario($id, $page, $perPage, $search, $tipo);

            if (!$data) {
                return $this->failNotFound("Bodega con ID $id no encontrada.");
            }

            return $this->respond([
                'status' => 'success',
                'data'   => $data
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }

    public function show($id = null)
    {
        $bodega = $this->model->get($id, 'bodegas');
        if (!$bodega) {
            return $this->failNotFound("Bodega con ID $id no encontrada.");
        }
        return $this->respond($bodega);
    }

    public function create_item_bodega()
    {
        try {
            $data = $this->req->getJSON(true);

            if (empty($data)) {
                return $this->failValidationErrors('No se recibieron datos válidos.');
            }

            $result = $this->model->create_item_bodega($data);

            return $this->respondCreated([
                'status'  => 'success',
                'message' => $result['message'],
                'id'      => $result['id']
            ]);
        } catch (Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
}

    public function create(){
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        $insert_id = $this->model->create_table($data, 'bodegas');
        if ($insert_id) {
            return $this->respondCreated([
                'mensaje' => 'Bodega creada correctamente',
                'id'      => $insert_id,
            ]);
        }
        return $this->fail('Error al crear la bodega');
    }

    // update_item_bodega ELIMINADO — bypaseaba capas y audit log.
    // Stock solo se modifica vía OC, Producción, Traspaso o AjusteManual.
    // Atributos del catálogo se editan vía CatalogoController::update.

    public function update($id = null)
    {
        $json = $this->request->getBody();
        $data = json_decode($json, true);
        // Validar que haya data
        if (!$data) {
            return $this->failValidationErrors('No se recibieron datos válidos.');
        }
        // Verificar que el registro exista antes de actualizar
        if (!$this->model->get($id, 'bodegas')) {
            return $this->failNotFound("Bodega con ID $id no encontrada.");
        }
        // Intentar actualizar
        $updated = $this->model->update_table($id, $data, 'bodegas');

        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->fail('No se pudo actualizar la bodega.');
        }
        return $this->respond([
            'mensaje' => "Bodega con ID $id actualizada correctamente",
            'data'    => $data
        ]);
    }

    // patch_cantidad ELIMINADO (2026-05-15) — bypass de audit log.
    // Stock solo se modifica vía OC, Producción, Traspaso o AjusteManual,
    // todos pasando por MovimientoInventarioModel::registrar().

    public function delete($id = null)
    {
        // Validar que se envió un ID
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        // Verificar que la bodega exista
        if (!$this->model->get($id, 'bodegas')) {
            return $this->failNotFound("Bodega con ID $id no encontrada.");
        }
        // Intentar eliminar usando BaseModel
        $deleted = $this->model->delete_table($id, 'bodegas');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar la bodega con ID $id.");
        }
        log_message('info', "[DELETE_BODEGA] usuario={$this->getUsername()} id={$id}");
        return $this->respondDeleted([
            'mensaje' => "Bodega con ID $id eliminada correctamente"
        ]);
    }
}
