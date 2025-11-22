<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsuarioController extends BaseController
{
    public function login()
    {
        $usuarioModel = new UsuarioModel();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $usuario = $usuarioModel->where('username', $username)->first();

        if (!$usuario) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Usuario no encontrado']);
        }

        if (!password_verify($password, $usuario['password'])) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Contraseña incorrecta']);
        }

        $secretKey = $_ENV['TOKEN_SECRET'] ?? 'miClaveSuperSecreta';

        // 🔥 Crear token JWT
        $payload = [
            'iat' => time(),
            'exp' => time() + 8*3600, // Expira en 8 horas
            'data' => [
                'id' => $usuario['id_usuarios'],
                'username' => $usuario['username']
            ]
        ];

        $token = JWT::encode($payload, (string)$secretKey, 'HS256');

        return $this->response->setJSON([
            'ok' => true,
            'msg' => 'Login exitoso',
            'token' => $token,
            'usuario' => [
                'id' => $usuario['id_usuarios'],
                'username' => $usuario['username']
            ]
        ]);
    }

    public function crear()
    {
        $usuarioModel = new UsuarioModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (!$username || !$password) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'Username y password son requeridos'
            ]);
        }

        $usuarioModel->save([
            'username' => $username,
            'password' => $password, // Se encripta automáticamente desde el modelo
        ]);

        return $this->response->setJSON([
            'ok' => true,
            'msg' => 'Usuario creado correctamente'
        ]);
    }
}
