<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

/**
 * RBAC: el rol `visor` es de SOLO LECTURA.
 *
 * Corre DESPUÉS de JwtFilter (que setea $request->usuario con el JWT decodificado).
 * Bloquea cualquier mutación (POST/PUT/PATCH/DELETE) para el rol `visor`, excepto
 * las acciones sobre su propia cuenta (cambiar password / cerrar sesión).
 *
 * operador / admin / superadmin no se ven afectados (mantienen su acceso actual).
 * Las rutas públicas (login, crear, refresh, health) no tienen $request->usuario,
 * así que pasan sin restricción.
 */
class RbacFilter implements FilterInterface
{
    /** Métodos de solo lectura — siempre permitidos. */
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    /** Rutas que el visor SÍ puede mutar (su propia cuenta). */
    private const WHITELIST = ['usuarios/mi-password', 'auth/logout'];

    public function before(RequestInterface $request, $arguments = null)
    {
        if (in_array(strtoupper($request->getMethod()), self::SAFE_METHODS, true)) {
            return; // lecturas: permitidas para todos
        }

        $usuario = $request->usuario ?? null;          // lo setea JwtFilter
        $rol     = $usuario->rol ?? null;
        if ($rol !== 'visor') {
            return; // solo restringimos al visor
        }

        $path = $request->getUri()->getPath();
        foreach (self::WHITELIST as $allowed) {
            if (str_contains($path, $allowed)) return;
        }

        return Services::response()
            ->setJSON([
                'ok'  => false,
                'msg' => 'Tu rol (visor) es de solo lectura: no tenés permiso para esta acción.',
            ])
            ->setStatusCode(403);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
