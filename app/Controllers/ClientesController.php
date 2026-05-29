<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\ClientesModel;

class ClientesController extends ResourceController
{
    use \App\Traits\ApiResponse;

    use \App\Traits\ValidatesJson;
    use \App\Traits\JwtUserAware;

    protected $modelName = ClientesModel::class;

    /**
     * Reglas reutilizables. En `update` se hacen permit_empty para soportar
     * actualizaciones parciales sin obligar a re-enviar todos los campos.
     */
    private const RULES_BASE = [
        'nombre_empresa'   => 'permit_empty|max_length[50]',
        'nombre_encargado' => 'permit_empty|max_length[50]',
        'numero_documento' => 'permit_empty|max_length[20]',
        'direccion'        => 'permit_empty|max_length[50]',
        'ciudad'           => 'permit_empty|max_length[100]',
        'telefono'         => 'permit_empty|max_length[20]',
        'email'            => 'permit_empty|valid_email|max_length[50]',
        'plazo_pago'       => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[365]',
        'tipo'             => 'permit_empty|integer|in_list[1,2,3]',
        'limite_credito'   => 'permit_empty|decimal|greater_than_equal_to[0]',
    ];

    public function clientes()
    {
        $clientes = $this->model->get_all('clientes');
        return $this->respond($clientes);
    }

    public function get_item_clientes($id = null)
    {
        $data = $this->model->get_item_clientes($id);

        if ($id !== null && !$data) {
            return $this->apiNotFound("Cliente con ID $id no encontrado.");
        }

        return $this->respond($data);
    }

    public function show($id = null)
    {
        $cliente = $this->model->get($id, 'clientes');
        if (!$cliente) {
            return $this->apiNotFound("Cliente con ID $id no encontrado.");
        }
        return $this->respond($cliente);
    }

    public function create()
    {
        // En create exigimos al menos uno de los nombres + documento
        $rules = array_merge(self::RULES_BASE, [
            'nombre_empresa'   => 'required_without[nombre_encargado]|max_length[50]',
            'nombre_encargado' => 'required_without[nombre_empresa]|max_length[50]',
            'numero_documento' => 'required|max_length[20]',
        ]);

        $data = $this->validateJson($rules);
        if ($data instanceof ResponseInterface) return $data;

        $insert_id = $this->model->create_table($data, 'clientes');
        if ($insert_id) {
            return $this->respondCreated([
                'mensaje' => 'Cliente creado correctamente',
                'id'      => $insert_id,
            ]);
        }
        return $this->apiFail('Error al crear el cliente');
    }

    public function update($id = null)
    {
        if (!$this->model->get($id, 'clientes')) {
            return $this->apiNotFound("Cliente con ID $id no encontrado.");
        }

        $data = $this->validateJson(self::RULES_BASE);
        if ($data instanceof ResponseInterface) return $data;

        $updated = $this->model->update_table($id, $data, 'clientes');
        if ($updated === false || (is_array($updated) && isset($updated['error']))) {
            return $this->apiFail('No se pudo actualizar el cliente.');
        }
        return $this->respond([
            'mensaje' => "Cliente con ID $id actualizado correctamente",
            'data'    => $data
        ]);
    }

    public function delete($id = null)
    {
        if (!$this->userHasAdminAccess()) {
            return $this->apiForbidden('Solo administradores pueden eliminar clientes.');
        }
        if ($id === null) {
            return $this->apiFail('No se proporcionó un ID válido.', 422);
        }
        if (!$this->model->get($id, 'clientes')) {
            return $this->apiNotFound("Cliente con ID $id no encontrado.");
        }
        log_message('info', "[CLIENTE_DELETE] id={$id} por {$this->getUsername()}");
        $deleted = $this->model->delete_table($id, 'clientes');
        if ($deleted === false || (is_array($deleted) && isset($deleted['error']))) {
            return $this->apiFail("No se pudo eliminar el cliente con ID $id.");
        }
        return $this->respondDeleted([
            'mensaje' => "Cliente con ID $id archivado correctamente",
        ]);
    }

    /**
     * POST /api/clientes/:id/restore — restaura un cliente soft-deleted.
     */
    public function restore($id = null)
    {
        if ($id === null) {
            return $this->apiFail('No se proporcionó un ID válido.', 422);
        }
        $db  = \Config\Database::connect();
        $row = $db->table('clientes')->where('id_clientes', $id)->get()->getRowArray();
        if (!$row) {
            return $this->apiNotFound("Cliente con ID $id no encontrado.");
        }
        if ($row['deleted_at'] === null) {
            return $this->apiFail("El cliente no está archivado.");
        }
        $this->model->restore_table($id, 'clientes');
        return $this->respond([
            'mensaje' => "Cliente con ID $id restaurado correctamente",
        ]);
    }
}