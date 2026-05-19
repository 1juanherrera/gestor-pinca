<?php

namespace App\Traits;

/**
 * Trait para acceder a los datos del usuario autenticado desde cualquier controller.
 *
 * El `JwtFilter::before` guarda el payload decodificado en `$request->usuario` con
 * estructura {id, username, rol, modulos}. Este trait expone helpers tipados sobre
 * eso para que los controllers no toquen `$this->request->usuario` directamente.
 *
 * Uso:
 *   class MiController extends ResourceController {
 *       use \App\Traits\JwtUserAware;
 *
 *       public function metodo() {
 *           $username = $this->getUsername();          // 'jperez' o 'sistema' si no hay token
 *           $rol      = $this->getUserRol();           // 'admin' | 'operador' | 'visor'
 *           if (!$this->userHasRole('admin')) { ... }
 *       }
 *   }
 *
 * Rutas publicas (login, crear) NO tienen `$request->usuario` poblado — los helpers
 * retornan valores por defecto sensatos en ese caso.
 */
trait JwtUserAware
{
    /**
     * Retorna el objeto `data` del JWT decodificado, o null si no hay sesión.
     */
    protected function getJwtUser(): ?object
    {
        return $this->request->usuario ?? null;
    }

    /**
     * Username del usuario autenticado, o 'sistema' si no hay sesión (jobs, cron, etc.).
     */
    protected function getUsername(): string
    {
        return $this->getJwtUser()->username ?? 'sistema';
    }

    /**
     * ID del usuario autenticado o null.
     */
    protected function getUserId(): ?int
    {
        $u = $this->getJwtUser();
        return $u && isset($u->id) ? (int) $u->id : null;
    }

    /**
     * Rol del usuario. Default 'visor' si no hay sesión (mínimo privilegio).
     */
    protected function getUserRol(): string
    {
        return $this->getJwtUser()->rol ?? 'visor';
    }

    /**
     * Lista de keys de módulos a los que el usuario tiene acceso.
     */
    protected function getUserModulos(): array
    {
        $modulos = $this->getJwtUser()->modulos ?? [];
        return is_array($modulos) ? $modulos : [];
    }

    /**
     * Chequeo rápido de rol. Acepta uno o varios roles aceptables.
     *   $this->userHasRole('admin')
     *   $this->userHasRole(['admin', 'operador'])
     */
    protected function userHasRole(string|array $roles): bool
    {
        $current = $this->getUserRol();
        $accepted = is_array($roles) ? $roles : [$roles];
        return in_array($current, $accepted, true);
    }

    /**
     * Chequeo de acceso a módulo.
     */
    protected function userHasModule(string $modulo): bool
    {
        // Admin tiene acceso a todo
        if ($this->getUserRol() === 'admin') return true;
        return in_array($modulo, $this->getUserModulos(), true);
    }
}
