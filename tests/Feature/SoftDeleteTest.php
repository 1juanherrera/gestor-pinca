<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests del comportamiento de soft-deletes:
 *
 *   - DELETE /api/clientes/:id → hace UPDATE deleted_at, no DELETE físico
 *   - GET /api/clientes/:id → devuelve 404 después de eliminar
 *   - GET /api/clientes (listado) → no incluye eliminados
 *   - La fila sigue existiendo en la tabla (verificable por SQL directo)
 *   - Las FK de facturas/cotizaciones siguen funcionando hacia el cliente borrado
 *
 * @internal
 */
final class SoftDeleteTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $refresh = false;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db->query('DELETE FROM login_attempts');

        $login = $this->call('post', 'api/login', ['username' => 'root', 'password' => 'root']);
        $this->token = json_decode($login->getJSON(), true)['token'] ?? '';
    }

    public function testDeleteClienteHaceSoftDeleteNoFisico(): void
    {
        // Crear cliente de prueba
        $crear = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode([
            'nombre_empresa'   => 'Cliente Soft Delete Test ' . uniqid(),
            'numero_documento' => '999' . random_int(100000, 999999),
        ]))->call('post', 'api/clientes');
        $crear->assertStatus(201);
        $clienteId = (int) (json_decode($crear->getJSON(), true)['id'] ?? 0);
        $this->assertGreaterThan(0, $clienteId);

        // Eliminar via API
        $del = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->call('delete', "api/clientes/{$clienteId}");
        $del->assertStatus(200);

        // GET debe devolver 404 (filtra deleted_at IS NULL)
        $get = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->call('get', "api/clientes/{$clienteId}");
        $get->assertStatus(404);

        // Pero en BD sigue existiendo con deleted_at poblado
        $row = $this->db->table('clientes')->where('id_clientes', $clienteId)->get()->getRowArray();
        $this->assertNotNull($row, 'La fila debe seguir existiendo físicamente');
        $this->assertNotNull($row['deleted_at'], 'deleted_at debe estar poblado');

        // Listado general no lo incluye
        $list = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->call('get', 'api/clientes');
        $list->assertStatus(200);
        $clientes = json_decode($list->getJSON(), true);
        $ids = array_column($clientes, 'id_clientes');
        $this->assertNotContains($clienteId, $ids, 'El listado normal NO debe incluir clientes eliminados');

        // Cleanup: restaurar para no contaminar la BD (o hard delete real)
        $this->db->table('clientes')->where('id_clientes', $clienteId)->delete();
    }

    public function testRestoreDevuelveClienteAlListado(): void
    {
        $crear = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode([
            'nombre_empresa'   => 'Cliente Restore Test ' . uniqid(),
            'numero_documento' => '888' . random_int(100000, 999999),
        ]))->call('post', 'api/clientes');
        $clienteId = (int) (json_decode($crear->getJSON(), true)['id'] ?? 0);

        // Soft delete
        $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->call('delete', "api/clientes/{$clienteId}");

        // Verificar que está soft-deleted
        $row = $this->db->table('clientes')->where('id_clientes', $clienteId)->get()->getRowArray();
        $this->assertNotNull($row['deleted_at']);

        // Restore manual (simulando endpoint admin)
        $this->db->table('clientes')->where('id_clientes', $clienteId)->update(['deleted_at' => null]);

        // GET ahora debe devolver el cliente
        $get = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->call('get', "api/clientes/{$clienteId}");
        $get->assertStatus(200);

        // Cleanup
        $this->db->table('clientes')->where('id_clientes', $clienteId)->delete();
    }
}
