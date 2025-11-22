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
        $secretKey = $_ENV['TOKEN_SECRET'] ?? 'miClaveSuperSecreta';

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            // Puedes guardar los datos del usuario para usarlos en tu controlador
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
