<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\ProveedorModel;

class ProveedorController extends ResourceController
{
    use \App\Traits\ValidatesJson;
    use \App\Traits\JwtUserAware;

    protected $modelName = ProveedorModel::class;

    private const RULES_BASE = [
        'nombre_empresa'   => 'permit_empty|max_length[27]',
        'nombre_encargado' => 'permit_empty|max_length[40]',
        'numero_documento' => 'permit_empty|max_length[11]',
        'direccion'        => 'permit_empty|max_length[45]',
        'telefono'         => 'permit_empty|max_length[14]',
        'email'            => 'permit_empty|valid_email|max_length[34]',
    ];

    public function proveedores()
    {
        $proveedores = $this->model->get_all('proveedor');
        return $this->respond($proveedores);
    }

    public function get_item_proveedores($id = null)
    {
        $data = $this->model->get_item_proveedores($id);

        if ($id !== null && !$data) {
            return $this->failNotFound("Proveedor con ID $id no encontrado.");
        }

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $proveedor = $this->model->get($id, 'proveedor');
        if (!$proveedor) {
            return $this->failNotFound("Proveedor con ID $id no encontrado.");
        }
        return $this->respond($proveedor);
    }

    public function create()
    {
        $rules = array_merge(self::RULES_BASE, [
            'nombre_empresa'   => 'required_without[nombre_encargado]|max_length[27]',
            'nombre_encargado' => 'required_without[nombre_empresa]|max_length[40]',
            'numero_documento' => 'required|max_length[11]',
        ]);

        $data = $this->validateJson($rules);
        if ($data instanceof ResponseInterface) return $data;

        $insert_id = $this->model->create_table($data, 'proveedor');
        if ($insert_id) {
            return $this->respondCreated([
                'mensaje' => 'Proveedor creado correctamente',
                'id'      => $insert_id,
            ]);
        }
        return $this->fail('Error al crear el proveedor');
    }

    public function update($id = null)
    {
        if (!$this->model->get($id, 'proveedor')) {
            return $this->failNotFound("Proveedor con ID $id no encontrado.");
        }

        $data = $this->validateJson(self::RULES_BASE);
        if ($data instanceof ResponseInterface) return $data;

        $updated = $this->model->update_table($id, $data, 'proveedor');
        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->fail('No se pudo actualizar el proveedor.');
        }
        return $this->respond([
            'mensaje' => "Proveedor con ID $id actualizado correctamente",
            'data'    => $data
        ]);
    }

    public function delete($id = null)
    {
        // Validar que se envió un ID
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        // Verificar que la proveedor exista
        if (!$this->model->get($id, 'proveedor')) {
            return $this->failNotFound("proveedor con ID $id no encontrada.");
        }
        // Intentar eliminar usando BaseModel
        $deleted = $this->model->delete_table($id, 'proveedor');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->fail("No se pudo eliminar la proveedor con ID $id.");
        }
        log_message('info', "[DELETE_PROVEEDOR] usuario={$this->getUsername()} id={$id}");
        return $this->respondDeleted([
            'mensaje' => "Proveedor con ID $id archivado correctamente"
        ]);
    }

    /**
     * POST /api/proveedores/:id/restore — restaura un proveedor soft-deleted.
     */
    public function restore($id = null)
    {
        if ($id === null) {
            return $this->failValidationErrors('No se proporcionó un ID válido.');
        }
        $db  = \Config\Database::connect();
        $row = $db->table('proveedor')->where('id_proveedor', $id)->get()->getRowArray();
        if (!$row) {
            return $this->failNotFound("Proveedor con ID $id no encontrado.");
        }
        if ($row['deleted_at'] === null) {
            return $this->fail("El proveedor no está archivado.");
        }
        $this->model->restore_table($id, 'proveedor');
        return $this->respond([
            'mensaje' => "Proveedor con ID $id restaurado correctamente",
        ]);
    }
}