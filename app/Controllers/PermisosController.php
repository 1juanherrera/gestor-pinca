<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class PermisosController extends BaseController
{
    private function requireAdmin(): bool
    {
        return isset($this->request->usuario->rol)
            && $this->request->usuario->rol === 'admin';
    }

    /**
     * GET /api/roles/permisos
     * Retorna todos los roles con sus módulos activos.
     */
    public function index(): ResponseInterface
    {
        $db = \Config\Database::connect();
        $rows = $db->table('permisos_rol_modulo')
            ->where('activo', 1)
            ->orderBy('rol')
            ->get()->getResultArray();

        $result = ['admin' => [], 'operador' => [], 'visor' => []];
        foreach ($rows as $row) {
            if (isset($result[$row['rol']])) {
                $result[$row['rol']][] = $row['modulo'];
            }
        }

        return $this->success($result);
    }

    /**
     * GET /api/roles/permisos/(:alpha)
     * Módulos activos de un rol específico.
     */
    public function show(string $rol): ResponseInterface
    {
        $validRoles = ['admin', 'operador', 'visor'];
        if (!in_array($rol, $validRoles)) {
            return $this->error('Rol inválido.', 400);
        }

        $db = \Config\Database::connect();
        $rows = $db->table('permisos_rol_modulo')
            ->select('modulo')
            ->where('rol', $rol)
            ->where('activo', 1)
            ->get()->getResultArray();

        return $this->success(array_column($rows, 'modulo'));
    }

    /**
     * PUT /api/roles/(:alpha)/permisos
     * Reemplaza los módulos de un rol. Solo admin.
     */
    public function update(string $rol): ResponseInterface
    {
        if (!$this->requireAdmin()) {
            return $this->error('Acceso denegado. Se requiere rol administrador.', 403);
        }

        $validRoles = ['admin', 'operador', 'visor'];
        if (!in_array($rol, $validRoles)) {
            return $this->error('Rol inválido.', 400);
        }

        $body = $this->request->getJSON(true);
        $modulos = $body['modulos'] ?? null;

        if (!is_array($modulos)) {
            return $this->error('El campo modulos debe ser un array.', 400);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $db->table('permisos_rol_modulo')->where('rol', $rol)->delete();

        if (!empty($modulos)) {
            $inserts = array_map(
                fn($m) => ['rol' => $rol, 'modulo' => trim($m), 'activo' => 1],
                array_unique($modulos)
            );
            $db->table('permisos_rol_modulo')->insertBatch($inserts);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->serverError(new \Exception("transComplete falló para rol $rol"));
        }

        log_message('info', "[PERMISOS] Rol $rol actualizado por {$this->request->usuario->username}: " . implode(', ', $modulos));

        return $this->success(['rol' => $rol, 'modulos' => $modulos]);
    }

    /**
     * GET /api/roles/usuarios
     * Lista todos los usuarios con su rol. Solo admin.
     */
    public function listarUsuarios(): ResponseInterface
    {
        if (!$this->requireAdmin()) {
            return $this->error('Acceso denegado.', 403);
        }

        $db = \Config\Database::connect();
        $usuarios = $db->table('usuarios')
            ->select('id_usuarios, username, nombre, rol')
            ->get()->getResultArray();

        return $this->success($usuarios);
    }

    /**
     * PATCH /api/roles/usuarios/(:num)/rol
     * Cambia el rol de un usuario. Solo admin.
     */
    public function cambiarRol(int $userId): ResponseInterface
    {
        if (!$this->requireAdmin()) {
            return $this->error('Acceso denegado.', 403);
        }

        $body = $this->request->getJSON(true);
        $nuevoRol = $body['rol'] ?? '';
        $validRoles = ['admin', 'operador', 'visor'];

        if (!in_array($nuevoRol, $validRoles)) {
            return $this->error('Rol inválido. Debe ser: admin, operador o visor.', 400);
        }

        $db = \Config\Database::connect();
        $usuario = $db->table('usuarios')->where('id_usuarios', $userId)->get()->getRowArray();

        if (!$usuario) {
            return $this->error('Usuario no encontrado.', 404);
        }

        $db->table('usuarios')->where('id_usuarios', $userId)->update(['rol' => $nuevoRol]);

        log_message('info', "[ROLES] Usuario $userId ({$usuario['username']}) cambió de {$usuario['rol']} a $nuevoRol por {$this->request->usuario->username}");

        return $this->success(['id' => $userId, 'rol' => $nuevoRol]);
    }
}
