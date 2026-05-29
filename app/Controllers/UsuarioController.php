<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\ConfiguracionModel;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsuarioController extends BaseController
{
    use \App\Traits\ApiResponse;

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

    /**
     * Arma y firma el JWT del usuario. Punto único de generación de tokens —
     * usado por login() y refresh(). El `token_version` SIEMPRE se pasa fresco
     * (leído de BD por el caller), no se asume.
     *
     * @param array $usuario  Fila de `usuarios` (debe traer id_usuarios, username, nombre, rol, token_version).
     * @param array $modulos  Módulos activos del rol.
     * @throws \InvalidArgumentException si TOKEN_SECRET no está configurado.
     */
    private function generarJwt(array $usuario, array $modulos): string
    {
        $secretKey = $this->getJwtSecret();
        $jwtHoras  = (int) $this->cfg()->obtener('jwt_expiracion_horas', 8);

        $payload = [
            'iat'  => time(),
            'exp'  => time() + $jwtHoras * 3600,
            'data' => [
                'id'            => (int) $usuario['id_usuarios'],
                'username'      => $usuario['username'],
                'nombre'        => $usuario['nombre'] ?? null,
                'rol'           => $usuario['rol'] ?? 'operador',
                'modulos'       => $modulos,
                'token_version' => (int) ($usuario['token_version'] ?? 1),
            ],
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }

    /**
     * Resuelve los módulos activos del rol indicado.
     */
    private function modulosDeRol(string $rol): array
    {
        $db = \Config\Database::connect();
        return array_column(
            $db->table('permisos_rol_modulo')
                ->select('modulo')
                ->where('rol', $rol)
                ->where('activo', 1)
                ->get()->getResultArray(),
            'modulo'
        );
    }

    /**
     * Crea y persiste un refresh token para el usuario.
     *
     * Genera un string aleatorio (256 bits), guarda SOLO su hash SHA-256 en
     * `refresh_tokens` con expiración a 7 días, y devuelve el token PLANO
     * (para mandárselo al cliente una única vez — nunca se vuelve a poder leer).
     */
    private function crearRefreshToken(int $usuarioId): string
    {
        $plain = bin2hex(random_bytes(32));
        $db    = \Config\Database::connect();

        $db->table('refresh_tokens')->insert([
            'usuario_id' => $usuarioId,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => date('Y-m-d H:i:s', time() + 7 * 24 * 3600),
            'created_at' => date('Y-m-d H:i:s'),
            'revoked'    => 0,
        ]);

        return $plain;
    }

    public function login()
    {
        $username = trim($this->request->getVar('username') ?? '');
        $password = $this->request->getVar('password') ?? '';

        // Validación básica
        if ($username === '') {
            return $this->apiFail('El campo username es requerido.', 400);
        }
        if ($password === '') {
            return $this->apiFail('El campo password es requerido.', 400);
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
            return $this->apiFail("Demasiados intentos fallidos. Espera {$minutos} minutos.", 429);
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
            return $this->apiFail('Usuario o contraseña incorrectos.', 200);
        }

        if (!password_verify($password, $usuario['password'])) {
            $registrarFallo();
            log_message('warning', "[LOGIN_FAIL] Contraseña incorrecta para: $username | IP: $ip");
            return $this->apiFail('Usuario o contraseña incorrectos.', 200);
        }

        // Login exitoso → limpiar intentos previos de esta IP para no
        // autobloquear al usuario después de re-logins legítimos.
        $db->table('login_attempts')->where('ip_address', $ip)->delete();

        $rol = $usuario['rol'] ?? 'operador';

        // Obtener módulos permitidos para este rol
        $modulos = $this->modulosDeRol($rol);

        try {
            $token = $this->generarJwt($usuario, $modulos);
        } catch (\InvalidArgumentException $e) {
            return $this->apiFail('Error de configuración del servidor.', 500);
        }

        // Refresh token de larga vida (7 días). Solo el hash queda en BD.
        $refreshToken = $this->crearRefreshToken((int) $usuario['id_usuarios']);

        $nombre = $usuario['nombre'] ?? null;

        log_message('info', "[LOGIN_OK] Usuario: $username | rol: $rol | IP: $ip");

        return $this->apiSuccessFlat([
            'token'         => $token,
            'refresh_token' => $refreshToken,
            'usuario' => [
                'id'                   => $usuario['id_usuarios'],
                'username'             => $usuario['username'],
                'nombre'               => $nombre,
                'rol'                  => $rol,
                'modulos'              => $modulos,
                'password_must_change' => (int) ($usuario['password_must_change'] ?? 0),
            ],
        ], 'Login exitoso');
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
            return $this->apiFail('No autenticado.', 401);
        }

        $userId = $this->request->usuario->id;
        $db     = \Config\Database::connect();

        $usuario = $db->table('usuarios')
            ->where('id_usuarios', $userId)
            ->get()->getRowArray();

        if (!$usuario) {
            return $this->apiFail('Usuario inexistente.', 401);
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

        return $this->apiSuccessFlat([
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
            return $this->apiFail('No autenticado.', 401);
        }

        $username = $this->request->usuario->username;
        $db = \Config\Database::connect();

        $intentos = $db->table('login_attempts')
            ->where('username_attempt', $username)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->apiSuccessFlat(['data' => $intentos]);
    }

    /**
     * PATCH /api/usuarios/mi-perfil
     * Permite al usuario actualizar campos editables de su propio perfil (por ahora: nombre).
     * Body: { nombre: string }
     */
    public function actualizarPerfil()
    {
        if (!isset($this->request->usuario)) {
            return $this->apiFail('No autenticado.', 401);
        }

        $body   = $this->request->getJSON(true) ?? [];
        $nombre = isset($body['nombre']) ? trim((string) $body['nombre']) : null;

        if ($nombre !== null && strlen($nombre) > 100) {
            return $this->apiFail('El nombre no puede superar 100 caracteres.', 400);
        }

        $usuarioModel = new UsuarioModel();
        $userId       = $this->request->usuario->id;

        $usuarioModel->update($userId, ['nombre' => $nombre ?: null]);

        log_message('info', "[PERFIL] Usuario {$this->request->usuario->username} actualizó su nombre a: " . ($nombre ?: '(vacío)'));

        // Re-emitir token con el nombre nuevo así no hace falta re-login
        try {
            $secretKey = $this->getJwtSecret();
        } catch (\InvalidArgumentException $e) {
            return $this->apiFail('Error de configuración del servidor.', 500);
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

        return $this->apiSuccessFlat([
            'token'   => $token,
            'usuario' => [
                'id'       => $userId,
                'username' => $this->request->usuario->username,
                'nombre'   => $nombre ?: null,
                'rol'      => $this->request->usuario->rol,
                'modulos'  => $modulos,
            ],
        ], 'Perfil actualizado.');
    }

    public function cambiarPassword()
    {
        if (!isset($this->request->usuario)) {
            return $this->apiFail('No autenticado.', 401);
        }

        $body            = $this->request->getJSON(true) ?? [];
        $currentPassword = $body['currentPassword'] ?? '';
        $newPassword     = $body['newPassword']     ?? '';

        $minPwd = (int) $this->cfg()->obtener('password_min_caracteres', 8);
        if (strlen($newPassword) < $minPwd) {
            return $this->apiFail("La nueva contraseña debe tener al menos {$minPwd} caracteres.", 400);
        }

        $usuarioModel = new UsuarioModel();
        $userId  = $this->request->usuario->id;
        $usuario = $usuarioModel->find($userId);

        if (!$usuario || !password_verify($currentPassword, $usuario['password'])) {
            return $this->apiFail('La contraseña actual es incorrecta.', 400);
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

        return $this->apiSuccessFlat([
            'token' => $nuevoToken,
        ], 'Contraseña actualizada correctamente.');
    }

    /**
     * POST /api/auth/logout
     * Cierra la sesión del usuario actual incrementando `usuarios.token_version`.
     * Esto invalida cualquier JWT vigente (incluido el que vino en este request)
     * en su próximo uso, gracias al chequeo de `JwtFilter::before`.
     * El cliente debe descartar el token de su almacenamiento local.
     */
    public function logout()
    {
        if (!isset($this->request->usuario)) {
            return $this->apiFail('No autenticado.', 401);
        }

        $userId   = (int) $this->request->usuario->id;
        $username = $this->request->usuario->username ?? 'desconocido';

        $db = \Config\Database::connect();
        $db->table('usuarios')
            ->where('id_usuarios', $userId)
            ->set('token_version', 'token_version + 1', false)
            ->update();

        // Revocar todos los refresh tokens del usuario — la sesión queda muerta
        // tanto para el JWT como para el refresh.
        $db->table('refresh_tokens')
            ->where('usuario_id', $userId)
            ->where('revoked', 0)
            ->update(['revoked' => 1]);

        log_message('info', "[LOGOUT] Usuario: {$username} | id={$userId}");

        return $this->apiSuccess(null, 'Sesión cerrada correctamente');
    }

    /**
     * POST /api/auth/refresh
     * Público (excluido del filtro JWT) — el refresh token ES la credencial.
     *
     * Body: { refresh_token: "..." }
     *
     * Valida el refresh token (hash, no revocado, no expirado), emite un JWT
     * nuevo con el `token_version` ACTUAL del usuario, ROTA el refresh token
     * (revoca el viejo, crea uno nuevo) y devuelve { ok, token, refresh_token }.
     */
    public function refresh()
    {
        $body         = $this->request->getJSON(true) ?? [];
        $refreshToken = $body['refresh_token'] ?? $this->request->getVar('refresh_token') ?? '';

        if (!is_string($refreshToken) || $refreshToken === '') {
            return $this->apiFail('Refresh token inválido o expirado', 401);
        }

        $db   = \Config\Database::connect();
        $hash = hash('sha256', $refreshToken);

        $row = $db->table('refresh_tokens')
            ->where('token_hash', $hash)
            ->where('revoked', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowArray();

        if (!$row) {
            return $this->apiFail('Refresh token inválido o expirado', 401);
        }

        $usuario = $db->table('usuarios')
            ->where('id_usuarios', (int) $row['usuario_id'])
            ->get()->getRowArray();

        if (!$usuario) {
            return $this->apiFail('Refresh token inválido o expirado', 401);
        }

        $modulos = $this->modulosDeRol($usuario['rol'] ?? 'operador');

        try {
            // token_version ACTUAL leído de BD (no del payload viejo).
            $token = $this->generarJwt($usuario, $modulos);
        } catch (\InvalidArgumentException $e) {
            return $this->apiFail('Error de configuración del servidor.', 500);
        }

        // Rotación: revoca el refresh viejo y emite uno nuevo.
        $db->table('refresh_tokens')
            ->where('id', (int) $row['id'])
            ->update(['revoked' => 1]);

        $nuevoRefresh = $this->crearRefreshToken((int) $row['usuario_id']);

        log_message('info', "[REFRESH] Usuario id={$row['usuario_id']} renovó su sesión");

        return $this->apiSuccessFlat([
            'token'         => $token,
            'refresh_token' => $nuevoRefresh,
        ]);
    }

    public function crear()
    {
        // Solo admins pueden crear usuarios vía API
        if (!isset($this->request->usuario) || $this->request->usuario->rol !== 'admin') {
            return $this->apiForbidden('Solo administradores pueden crear usuarios.');
        }

        // Validación con CI4 nativo — payload puede venir como POST (form) o JSON.
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[' . (int) $this->cfg()->obtener('password_min_caracteres', 8) . ']',
            'nombre'   => 'required|max_length[100]',
            'rol'      => 'permit_empty|in_list[admin,operador,visor]',
        ];
        if (!$this->validate($rules)) {
            return $this->apiValidationError($this->validator->getErrors());
        }

        $username = trim($this->request->getPost('username') ?? '');
        $password = $this->request->getPost('password') ?? '';
        $rol      = trim($this->request->getPost('rol') ?? 'operador') ?: 'operador';
        $nombre   = trim($this->request->getPost('nombre') ?? '') ?: null;

        $usuarioModel = new UsuarioModel();

        $existing = $usuarioModel->where('username', $username)->first();
        if ($existing) {
            return $this->apiFail('El username ya existe.', 409);
        }

        $usuarioModel->save([
            'username' => $username,
            'nombre'   => $nombre,
            'password' => $password,
            'rol'      => $rol,
        ]);

        log_message('info', "[USUARIO_CREADO] $username (rol: $rol) por {$this->request->usuario->username}");

        // Mantener shape original `{ok, msg}` para no romper consumidores.
        return $this->apiSuccessFlat([], 'Usuario creado correctamente');
    }
}
