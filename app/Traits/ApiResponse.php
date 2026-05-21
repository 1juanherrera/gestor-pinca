<?php

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Helpers para devolver respuestas JSON con un shape consistente:
 *
 *   { ok: bool, msg: string, data?: any, errors?: object }
 *
 * Objetivo: unificar los 3 formatos que coexisten hoy en el codebase
 *   - `{ ok, msg }`            (UsuarioController, EmpresaController, …)
 *   - `{ success, message }`   (BaseController, varios)
 *   - `{ status, message, data }` (FacturasController::create, …)
 *
 * El frontend ya tolera todos, pero estandarizar simplifica el manejo de
 * errores y testing. Aplicación incremental: cada controller migra cuando
 * se lo toca.
 *
 * Ejemplo de uso:
 *
 *   class FooController extends ResourceController {
 *       use \App\Traits\ApiResponse;
 *
 *       public function show($id) {
 *           $foo = $this->model->find($id);
 *           if (!$foo) return $this->apiNotFound("Foo $id no encontrado.");
 *           return $this->apiSuccess($foo);
 *       }
 *   }
 */
trait ApiResponse
{
    protected function apiSuccess($data = null, string $msg = 'OK', int $status = 200): ResponseInterface
    {
        $payload = ['ok' => true, 'msg' => $msg];
        if ($data !== null) $payload['data'] = $data;
        return $this->response->setStatusCode($status)->setJSON($payload);
    }

    protected function apiCreated($data = null, string $msg = 'Creado correctamente'): ResponseInterface
    {
        return $this->apiSuccess($data, $msg, 201);
    }

    protected function apiFail(string $msg, int $status = 400, ?array $errors = null): ResponseInterface
    {
        $payload = ['ok' => false, 'msg' => $msg];
        if ($errors) $payload['errors'] = $errors;
        return $this->response->setStatusCode($status)->setJSON($payload);
    }

    protected function apiNotFound(string $msg = 'Recurso no encontrado.'): ResponseInterface
    {
        return $this->apiFail($msg, 404);
    }

    protected function apiForbidden(string $msg = 'Acceso denegado.'): ResponseInterface
    {
        return $this->apiFail($msg, 403);
    }

    protected function apiValidationError(array $errors, string $msg = 'Datos inválidos'): ResponseInterface
    {
        return $this->apiFail($msg, 422, $errors);
    }
}
