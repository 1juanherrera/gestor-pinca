<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $token = $request->getHeaderLine('Authorization'); // "Bearer token"

        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return Services::response()
                ->setJSON(['ok' => false, 'msg' => 'Token no proporcionado'])
                ->setStatusCode(401);
        }

        $token = str_replace('Bearer ', '', $token);

        // El secret DEBE venir de .env. Sin fallback débil: si no está configurado,
        // rechazamos en vez de validar tokens firmados con un secreto público
        // (que sería un bypass total de autenticación en un deploy mal configurado).
        $secretKey = $_ENV['TOKEN_SECRET'] ?? getenv('TOKEN_SECRET') ?: '';
        if (empty($secretKey) || $secretKey === 'miClaveSuperSecreta') {
            log_message('critical', '[JwtFilter] TOKEN_SECRET no configurado o usando el valor por defecto.');
            return Services::response()
                ->setJSON(['ok' => false, 'msg' => 'Error de configuración del servidor.'])
                ->setStatusCode(500);
        }

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            // Validar token_version contra la BD — invalida tokens viejos cuando
            // se cambia el rol o el password del usuario. Si el token no trae
            // el campo (tokens emitidos antes de esta feature), se asume v1.
            $tokenVersion = (int) ($decoded->data->token_version ?? 1);
            $userId       = (int) ($decoded->data->id ?? 0);
            if ($userId > 0) {
                $row = \Config\Database::connect()
                    ->table('usuarios')
                    ->select('token_version')
                    ->where('id_usuarios', $userId)
                    ->get()->getRowArray();

                if ($row && (int) $row['token_version'] !== $tokenVersion) {
                    return Services::response()
                        ->setJSON(['ok' => false, 'msg' => 'Sesión invalidada. Iniciá sesión de nuevo.'])
                        ->setStatusCode(401);
                }
            }

            // Datos del usuario disponibles en el controlador como $request->usuario
            $request->usuario = $decoded->data;
        } catch (\Firebase\JWT\ExpiredException $e) {
            return Services::response()
                ->setJSON(['ok' => false, 'msg' => 'Token expirado'])
                ->setStatusCode(401);
        } catch (Exception $e) {
            return Services::response()
                ->setJSON(['ok' => false, 'msg' => 'Token inválido'])
                ->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null){}
}
