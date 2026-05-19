<?php

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Validación de payloads JSON (o POST como fallback) usando las reglas de CI4.
 *
 * Uso típico en un controller:
 *
 *   class ClientesController extends ResourceController {
 *       use \App\Traits\ValidatesJson;
 *
 *       public function create() {
 *           $data = $this->validateJson([
 *               'nombre'  => 'required|max_length[100]',
 *               'email'   => 'permit_empty|valid_email',
 *           ]);
 *           if ($data instanceof ResponseInterface) return $data;  // 422 con errores
 *
 *           // $data ya está validado y limpio
 *           $id = $this->model->create_table($data, 'clientes');
 *           ...
 *       }
 *   }
 *
 * Si la validación falla, retorna directamente una respuesta 422 con shape:
 *   { success: false, message: "Datos inválidos", errors: { campo: "msg", ... } }
 */
trait ValidatesJson
{
    /**
     * @param array       $rules     Reglas de validación CI4
     * @param array|null  $messages  Mensajes custom (opcional)
     * @return array|ResponseInterface  Array validado o respuesta 422 con errores
     */
    protected function validateJson(array $rules, ?array $messages = null)
    {
        $data = $this->request->getJSON(true)
            ?? $this->request->getPost()
            ?? [];

        $validation = \Config\Services::validation();
        $validation->setRules($rules, $messages ?? []);

        if (!$validation->run($data)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors'  => $validation->getErrors(),
            ]);
        }

        return $data;
    }
}
