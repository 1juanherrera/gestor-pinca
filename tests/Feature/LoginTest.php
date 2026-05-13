<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests de integración del flujo de login.
 *
 *   - login con credenciales válidas → token JWT válido + usuario serializado
 *   - login con password incorrecta → 200 OK con ok=false (no expone si user existe)
 *   - rate-limit: 5 intentos fallidos por IP en 15 min → 429
 *
 * Usa DatabaseTestTrait con `$refresh = false` — operamos sobre la BD real (root)
 * y limpiamos `login_attempts` antes de cada test para no arrastrar contadores.
 *
 * @internal
 */
final class LoginTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    /**
     * No usar transactions — la tabla login_attempts debe persistir entre requests
     * dentro del mismo test (rate-limit se chequea con SELECT real, no en memoria).
     */
    protected $refresh = false;

    protected function setUp(): void
    {
        parent::setUp();
        // Limpia intentos para que cada test arranque con cero
        $this->db->query('DELETE FROM login_attempts');
    }

    public function testLoginConCredencialesValidasDevuelveToken(): void
    {
        $result = $this->call('post', 'api/login', [
            'username' => 'root',
            'password' => 'root',
        ]);

        $result->assertStatus(200);
        $result->assertJSONFragment(['ok' => true]);

        $body = json_decode($result->getJSON(), true);
        $this->assertNotEmpty($body['token'] ?? null, 'El login debe retornar un token JWT');
        $this->assertSame('root', $body['usuario']['username'] ?? null);
        $this->assertSame('admin', $body['usuario']['rol'] ?? null);
        $this->assertIsArray($body['usuario']['modulos'] ?? null);
    }

    public function testLoginConPasswordIncorrectaFalla(): void
    {
        $result = $this->call('post', 'api/login', [
            'username' => 'root',
            'password' => 'password-equivocada-xxx',
        ]);

        $result->assertStatus(200);
        $body = json_decode($result->getJSON(), true);

        $this->assertFalse($body['ok'] ?? null);
        $this->assertArrayNotHasKey('token', $body, 'El login fallido NO debe devolver token');
        // El mensaje no debe revelar si el usuario existe (timing-safe)
        $this->assertSame('Usuario o contraseña incorrectos.', $body['msg']);
    }

    public function testLoginConUsuarioInexistenteFalla(): void
    {
        $result = $this->call('post', 'api/login', [
            'username' => 'usuario-que-no-existe-' . uniqid(),
            'password' => 'cualquiera',
        ]);

        $result->assertStatus(200);
        $body = json_decode($result->getJSON(), true);

        $this->assertFalse($body['ok'] ?? null);
        $this->assertSame('Usuario o contraseña incorrectos.', $body['msg']);
    }

    public function testRateLimitBloqueaTrasQuintoIntentoFallido(): void
    {
        // 5 intentos fallidos seguidos
        for ($i = 0; $i < 5; $i++) {
            $this->call('post', 'api/login', [
                'username' => 'root',
                'password' => 'mal-password-' . $i,
            ]);
        }

        // El 6to debe ser bloqueado con 429
        $result = $this->call('post', 'api/login', [
            'username' => 'root',
            'password' => 'mal-password-final',
        ]);

        $result->assertStatus(429);
        $body = json_decode($result->getJSON(), true);
        $this->assertFalse($body['ok'] ?? null);
        $this->assertStringContainsString('Demasiados intentos', $body['msg']);
    }

    public function testLoginRequiereUsername(): void
    {
        $result = $this->call('post', 'api/login', ['password' => 'algo']);
        $result->assertStatus(400);
        $body = json_decode($result->getJSON(), true);
        $this->assertFalse($body['ok'] ?? null);
        $this->assertStringContainsString('username', $body['msg']);
    }
}
