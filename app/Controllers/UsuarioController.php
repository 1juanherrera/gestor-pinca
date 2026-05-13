<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsuarioController extends BaseController
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const RATE_LIMIT_WINDOW  = 900; // 15 minutos en segundos

    public function login()
    {
        $username = trim($this->request->getVar('username') ?? '');
        $password = $this->request->getVar('password') ?? '';

        // Validación básica
        if ($username === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => 'El campo username es requerido.']);
        }
        if ($password === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => 'El campo password es requerido.']);
        }

        $ip  = $this->request->getIPAddress();
        $db  = \Config\Database::connect();

        // Rate limiting
        $cutoff   = date('Y-m-d H:i:s', time() - self::RATE_LIMIT_WINDOW);
        $attempts = $db->table('login_attempts')
            ->where('ip_address', $ip)
            ->where('created_at >', $cutoff)
            ->countAllResults();

        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            log_message('warning', "[LOGIN] IP $ip bloqueada por exceso de intentos (usuario: $username)");
            return $this->response->setStatusCode(429)
                ->setJSON(['ok' => false, 'msg' => 'Demasiados intentos fallidos. Espera 15 minutos.']);
        }

        // Registrar intento (antes de verificar credenciales, para contar cualquier intento)
        $db->table('login_attempts')->insert([
            'ip_address'       => $ip,
            'username_attempt' => $username,
        ]);

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->where('username', $username)->first();

        if (!$usuario) {
            log_message('warning', "[LOGIN_FAIL] Usuario no encontrado: $username | IP: $ip");
            return $this->response->setJSON(['ok' => false, 'msg' => 'Usuario o contraseña incorrectos.']);
        }

        if (!password_verify($password, $usuario['password'])) {
            log_message('warning', "[LOGIN_FAIL] Contraseña incorrecta para: $username | IP: $ip");
            return $this->response->setJSON(['ok' => false, 'msg' => 'Usuario o contraseña incorrectos.']);
        }

        $rol = $usuario['rol'] ?? 'operador';

        // Obtener módulos permitidos para este rol
        $modulosRows = $db->table('permisos_rol_modulo')
            ->select('modulo')
            ->where('rol', $rol)
            ->where('activo', 1)
            ->get()->getResultArray();
        $modulos = array_column($modulosRows, 'modulo');

        $secretKey = $_ENV['TOKEN_SECRET'] ?? 'miClaveSuperSecreta';

        $nombre = $usuario['nombre'] ?? null;

        $payload = [
            'iat' => time(),
            'exp' => time() + 8 * 3600,
            'data' => [
                'id'       => $usuario['id_usuarios'],
                'username' => $usuario['username'],
                'nombre'   => $nombre,
                'rol'      => $rol,
                'modulos'  => $modulos,
            ],
        ];

        $token = JWT::encode($payload, (string) $secretKey, 'HS256');

        log_message('info', "[LOGIN_OK] Usuario: $username | rol: $rol | IP: $ip");

        return $this->response->setJSON([
            'ok'      => true,
            'msg'     => 'Login exitoso',
            'token'   => $token,
            'usuario' => [
                'id'       => $usuario['id_usuarios'],
                'username' => $usuario['username'],
                'nombre'   => $nombre,
                'rol'      => $rol,
                'modulos'  => $modulos,
            ],
        ]);
    }

    public function miActividad()
    {
        if (!isset($this->request->usuario)) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false]);
        }

        $username = $this->request->usuario->username;
        $db = \Config\Database::connect();

        $intentos = $db->table('login_attempts')
            ->where('username_attempt', $username)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->response->setJSON(['ok' => true, 'data' => $intentos]);
    }

    /**
     * PATCH /api/usuarios/mi-perfil
     * Permite al usuario actualizar campos editables de su propio perfil (por ahora: nombre).
     * Body: { nombre: string }
     */
    public function actualizarPerfil()
    {
        if (!isset($this->request->usuario)) {
            return $this->response->setStatusCode(401)
                ->setJSON(['ok' => false, 'msg' => 'No autenticado.']);
        }

        $body   = $this->request->getJSON(true) ?? [];
        $nombre = isset($body['nombre']) ? trim((string) $body['nombre']) : null;

        if ($nombre !== null && strlen($nombre) > 100) {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => 'El nombre no puede superar 100 caracteres.']);
        }

        $usuarioModel = new UsuarioModel();
        $userId       = $this->request->usuario->id;

        $usuarioModel->update($userId, ['nombre' => $nombre ?: null]);

        log_message('info', "[PERFIL] Usuario {$this->request->usuario->username} actualizó su nombre a: " . ($nombre ?: '(vacío)'));

        // Re-emitir token con el nombre nuevo así no hace falta re-login
        $secretKey = $_ENV['TOKEN_SECRET'] ?? 'miClaveSuperSecreta';
        $db        = \Config\Database::connect();
        $modulosRows = $db->table('permisos_rol_modulo')
            ->select('modulo')
            ->where('rol', $this->request->usuario->rol)
            ->where('activo', 1)
            ->get()->getResultArray();
        $modulos = array_column($modulosRows, 'modulo');

        $payload = [
            'iat'  => time(),
            'exp'  => time() + 8 * 3600,
            'data' => [
                'id'       => $userId,
                'username' => $this->request->usuario->username,
                'nombre'   => $nombre ?: null,
                'rol'      => $this->request->usuario->rol,
                'modulos'  => $modulos,
            ],
        ];
        $token = JWT::encode($payload, (string) $secretKey, 'HS256');

        return $this->response->setJSON([
            'ok'      => true,
            'msg'     => 'Perfil actualizado.',
            'token'   => $token,
            'usuario' => [
                'id'       => $userId,
                'username' => $this->request->usuario->username,
                'nombre'   => $nombre ?: null,
                'rol'      => $this->request->usuario->rol,
                'modulos'  => $modulos,
            ],
        ]);
    }

    public function cambiarPassword()
    {
        if (!isset($this->request->usuario)) {
            return $this->response->setStatusCode(401)
                ->setJSON(['ok' => false, 'msg' => 'No autenticado.']);
        }

        $body            = $this->request->getJSON(true) ?? [];
        $currentPassword = $body['currentPassword'] ?? '';
        $newPassword     = $body['newPassword']     ?? '';

        if (strlen($newPassword) < 8) {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => 'La nueva contraseña debe tener al menos 8 caracteres.']);
        }

        $usuarioModel = new UsuarioModel();
        $userId  = $this->request->usuario->id;
        $usuario = $usuarioModel->find($userId);

        if (!$usuario || !password_verify($currentPassword, $usuario['password'])) {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => 'La contraseña actual es incorrecta.']);
        }

        $usuarioModel->update($userId, ['password' => $newPassword]);

        log_message('info', "[PASSWORD] Usuario {$this->request->usuario->username} actualizó su contraseña");

        return $this->response->setJSON(['ok' => true, 'msg' => 'Contraseña actualizada correctamente.']);
    }

    public function crear()
    {
        // Solo admins pueden crear usuarios vía API
        if (!isset($this->request->usuario) || $this->request->usuario->rol !== 'admin') {
            return $this->response->setStatusCode(403)
                ->setJSON(['ok' => false, 'msg' => 'Solo administradores pueden crear usuarios.']);
        }

        $username = trim($this->request->getPost('username') ?? '');
        $password = $this->request->getPost('password') ?? '';
        $rol      = trim($this->request->getPost('rol') ?? 'operador');
        $nombre   = trim($this->request->getPost('nombre') ?? '') ?: null;

        // Validación
        if (strlen($username) < 3 || strlen($username) > 50) {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => 'El username debe tener entre 3 y 50 caracteres.']);
        }
        if (strlen($password) < 8) {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => 'La contraseña debe tener al menos 8 caracteres.']);
        }
        if (!in_array($rol, ['admin', 'operador', 'visor'], true)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => 'El rol debe ser: admin, operador o visor.']);
        }

        $usuarioModel = new UsuarioModel();

        $existing = $usuarioModel->where('username', $username)->first();
        if ($existing) {
            return $this->response->setStatusCode(409)
                ->setJSON(['ok' => false, 'msg' => 'El username ya existe.']);
        }

        $usuarioModel->save([
            'username' => $username,
            'nombre'   => $nombre,
            'password' => $password,
            'rol'      => $rol,
        ]);

        log_message('info', "[USUARIO_CREADO] $username (rol: $rol) por {$this->request->usuario->username}");

        return $this->response->setJSON([
            'ok'  => true,
            'msg' => 'Usuario creado correctamente',
        ]);
    }
}
