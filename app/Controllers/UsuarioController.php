<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\ConfiguracionModel;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsuarioController extends BaseController
{
    /**
     * Lee el secret JWT del .env. Sin fallback inseguro:
     * si TOKEN_SECRET no está definido o está vacío lanza InvalidArgumentException.
     */
    private function getJwtSecret(): string
    {
        $secret = $_ENV['TOKEN_SECRET'] ?? '';
        if ($secret === '' || $secret === 'miClaveSuperSecreta') {
            log_message('critical', '[SECURITY] TOKEN_SECRET no está configurado en .env o usa el fallback inseguro.');
            throw new \InvalidArgumentException('Configuración de seguridad inválida (TOKEN_SECRET).');
        }
        return $secret;
    }

    private function cfg(): ConfiguracionModel
    {
        return new ConfiguracionModel();
    }

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

        // Rate limiting (parámetros configurables desde Configuración → Seguridad)
        $cfg            = $this->cfg();
        $maxIntentos    = (int) $cfg->obtener('max_intentos_login',         5);
        $ventanaSeg     = (int) $cfg->obtener('ventana_intentos_segundos',  900);
        $cutoff         = date('Y-m-d H:i:s', time() - $ventanaSeg);
        $attempts = $db->table('login_attempts')
            ->where('ip_address', $ip)
            ->where('created_at >', $cutoff)
            ->countAllResults();

        if ($attempts >= $maxIntentos) {
            $minutos = (int) ceil($ventanaSeg / 60);
            log_message('warning', "[LOGIN] IP $ip bloqueada por exceso de intentos (usuario: $username)");
            return $this->response->setStatusCode(429)
                ->setJSON(['ok' => false, 'msg' => "Demasiados intentos fallidos. Espera {$minutos} minutos."]);
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->where('username', $username)->first();

        $registrarFallo = function () use ($db, $ip, $username) {
            $db->table('login_attempts')->insert([
                'ip_address'       => $ip,
                'username_attempt' => $username,
            ]);
        };

        if (!$usuario) {
            $registrarFallo();
            log_message('warning', "[LOGIN_FAIL] Usuario no encontrado: $username | IP: $ip");
            return $this->response->setJSON(['ok' => false, 'msg' => 'Usuario o contraseña incorrectos.']);
        }

        if (!password_verify($password, $usuario['password'])) {
            $registrarFallo();
            log_message('warning', "[LOGIN_FAIL] Contraseña incorrecta para: $username | IP: $ip");
            return $this->response->setJSON(['ok' => false, 'msg' => 'Usuario o contraseña incorrectos.']);
        }

        // Login exitoso → limpiar intentos previos de esta IP para no
        // autobloquear al usuario después de re-logins legítimos.
        $db->table('login_attempts')->where('ip_address', $ip)->delete();

        $rol = $usuario['rol'] ?? 'operador';

        // Obtener módulos permitidos para este rol
        $modulosRows = $db->table('permisos_rol_modulo')
            ->select('modulo')
            ->where('rol', $rol)
            ->where('activo', 1)
            ->get()->getResultArray();
        $modulos = array_column($modulosRows, 'modulo');

        try {
            $secretKey = $this->getJwtSecret();
        } catch (\InvalidArgumentException $e) {
            return $this->response->setStatusCode(500)
                ->setJSON(['ok' => false, 'msg' => 'Error de configuración del servidor.']);
        }
        $jwtHoras = (int) $this->cfg()->obtener('jwt_expiracion_horas', 8);

        $nombre = $usuario['nombre'] ?? null;

        $payload = [
            'iat' => time(),
            'exp' => time() + $jwtHoras * 3600,
            'data' => [
                'id'            => $usuario['id_usuarios'],
                'username'      => $usuario['username'],
                'nombre'        => $nombre,
                'rol'           => $rol,
                'modulos'       => $modulos,
                'token_version' => (int) ($usuario['token_version'] ?? 1),
            ],
        ];

        $token = JWT::encode($payload, (string) $secretKey, 'HS256');

        log_message('info', "[LOGIN_OK] Usuario: $username | rol: $rol | IP: $ip");

        return $this->response->setJSON([
            'ok'      => true,
            'msg'     => 'Login exitoso',
            'token'   => $token,
            'usuario' => [
                'id'                   => $usuario['id_usuarios'],
                'username'             => $usuario['username'],
                'nombre'               => $nombre,
                'rol'                  => $rol,
                'modulos'              => $modulos,
                'password_must_change' => (int) ($usuario['password_must_change'] ?? 0),
            ],
        ]);
    }

    /**
     * GET /api/auth/me
     * Devuelve el usuario actual (datos frescos de BD, no del payload del JWT).
     * El frontend la usa al cargar la app para verificar que el token sigue válido
     * antes de renderizar rutas protegidas — evita el flash al panel cuando hay
     * un token expirado.
     */
    public function me()
    {
        if (!isset($this->request->usuario)) {
            return $this->response->setStatusCode(401)
                ->setJSON(['ok' => false, 'msg' => 'No autenticado.']);
        }

        $userId = $this->request->usuario->id;
        $db     = \Config\Database::connect();

        $usuario = $db->table('usuarios')
            ->where('id_usuarios', $userId)
            ->get()->getRowArray();

        if (!$usuario) {
            return $this->response->setStatusCode(401)
                ->setJSON(['ok' => false, 'msg' => 'Usuario inexistente.']);
        }

        // Recuperar módulos activos del rol actual (puede haber cambiado desde el login).
        $rol     = $usuario['rol'];
        $modulos = array_column(
            $db->table('permisos_rol_modulo')
                ->where('rol', $rol)
                ->where('activo', 1)
                ->get()->getResultArray(),
            'modulo'
        );

        return $this->response->setJSON([
            'ok'      => true,
            'usuario' => [
                'id'                   => (int) $usuario['id_usuarios'],
                'username'             => $usuario['username'],
                'nombre'               => $usuario['nombre'] ?? null,
                'rol'                  => $rol,
                'modulos'              => $modulos,
                'password_must_change' => (int) ($usuario['password_must_change'] ?? 0),
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
        try {
            $secretKey = $this->getJwtSecret();
        } catch (\InvalidArgumentException $e) {
            return $this->response->setStatusCode(500)
                ->setJSON(['ok' => false, 'msg' => 'Error de configuración del servidor.']);
        }
        $jwtHoras    = (int) $this->cfg()->obtener('jwt_expiracion_horas', 8);
        $db          = \Config\Database::connect();
        $modulosRows = $db->table('permisos_rol_modulo')
            ->select('modulo')
            ->where('rol', $this->request->usuario->rol)
            ->where('activo', 1)
            ->get()->getResultArray();
        $modulos = array_column($modulosRows, 'modulo');

        $payload = [
            'iat'  => time(),
            'exp'  => time() + $jwtHoras * 3600,
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

        $minPwd = (int) $this->cfg()->obtener('password_min_caracteres', 8);
        if (strlen($newPassword) < $minPwd) {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => "La nueva contraseña debe tener al menos {$minPwd} caracteres."]);
        }

        $usuarioModel = new UsuarioModel();
        $userId  = $this->request->usuario->id;
        $usuario = $usuarioModel->find($userId);

        if (!$usuario || !password_verify($currentPassword, $usuario['password'])) {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => 'La contraseña actual es incorrecta.']);
        }

        // Al cambiar el password, limpiamos el flag de "debe cambiar" si lo tenía.
        $usuarioModel->update($userId, [
            'password'             => $newPassword,
            'password_must_change' => 0,
        ]);

        // Incrementar token_version: invalida cualquier OTRA sesión activa
        // del usuario (otras pestañas, otra máquina). Devolvemos un token
        // nuevo en la respuesta para que esta sesión siga viva sin tener
        // que volver a loguearse.
        $db = \Config\Database::connect();
        $db->table('usuarios')
            ->where('id_usuarios', $userId)
            ->set('token_version', 'token_version + 1', false)
            ->update();
        $nuevoTokenVersion = (int) $db->table('usuarios')
            ->select('token_version')
            ->where('id_usuarios', $userId)
            ->get()->getRow()->token_version;

        try {
            $secretKey = $this->getJwtSecret();
            $jwtHoras  = (int) $this->cfg()->obtener('jwt_expiracion_horas', 8);
            $modulosRows = $db->table('permisos_rol_modulo')
                ->select('modulo')
                ->where('rol', $usuario['rol'])
                ->where('activo', 1)
                ->get()->getResultArray();
            $modulos = array_column($modulosRows, 'modulo');

            $payload = [
                'iat'  => time(),
                'exp'  => time() + $jwtHoras * 3600,
                'data' => [
                    'id'            => (int) $usuario['id_usuarios'],
                    'username'      => $usuario['username'],
                    'nombre'        => $usuario['nombre'] ?? null,
                    'rol'           => $usuario['rol'],
                    'modulos'       => $modulos,
                    'token_version' => $nuevoTokenVersion,
                ],
            ];
            $nuevoToken = JWT::encode($payload, (string) $secretKey, 'HS256');
        } catch (\Exception $e) {
            // Si falla la generación del token, igual el password ya cambió.
            // El usuario verá un 401 en el próximo request y tendrá que loguearse.
            $nuevoToken = null;
        }

        log_message('info', "[PASSWORD] Usuario {$this->request->usuario->username} actualizó su contraseña");

        return $this->response->setJSON([
            'ok'    => true,
            'msg'   => 'Contraseña actualizada correctamente.',
            'token' => $nuevoToken,
        ]);
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
        $minPwd = (int) $this->cfg()->obtener('password_min_caracteres', 8);
        if (strlen($password) < $minPwd) {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'msg' => "La contraseña debe tener al menos {$minPwd} caracteres."]);
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
