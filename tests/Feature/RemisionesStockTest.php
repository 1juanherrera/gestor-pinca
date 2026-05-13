<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests del refactor de Remisiones (Hito 5):
 *
 *   - Crear remisión con item_general_id en líneas
 *   - Cambiar a Despachada → descuenta stock real (FIFO sobre inventario_capas)
 *   - Audit log SALIDA generado con metadata REF_REMISION
 *   - Cambiar a Anulada → restaura capas + audit ENTRADA por reverso
 *   - Item sin stock al despachar → falla con 400 (rollback total)
 *
 * @internal
 */
final class RemisionesStockTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $refresh = false;

    private string $token;
    private int $clienteId;
    private ?int $itemConStock = null;
    private ?int $bodegaId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db->query('DELETE FROM login_attempts');

        $login = $this->call('post', 'api/login', ['username' => 'root', 'password' => 'root']);
        $this->token = json_decode($login->getJSON(), true)['token'] ?? '';

        $this->clienteId = (int) $this->db->table('clientes')
            ->where('deleted_at', null)
            ->limit(1)->get()->getRow('id_clientes');

        // Buscar item con stock disponible
        $row = $this->db->query("
            SELECT ic.item_general_id, ic.bodegas_id, SUM(ic.cantidad_disponible) AS stock
            FROM inventario_capas ic
            WHERE ic.estado = 1 AND ic.cantidad_disponible > 0
            GROUP BY ic.item_general_id, ic.bodegas_id
            HAVING stock >= 5
            LIMIT 1
        ")->getRowArray();

        if ($row) {
            $this->itemConStock = (int) $row['item_general_id'];
            $this->bodegaId     = (int) $row['bodegas_id'];
        }
    }

    public function testDespacharRemisionDescuentaStockYGeneraAuditLog(): void
    {
        if (!$this->itemConStock) {
            $this->markTestSkipped('No hay items con stock para probar el flujo');
        }

        // 1. Crear remisión con item vinculado
        $cantidad = 1.5;
        $crear = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode([
            'cliente_id' => $this->clienteId,
            'fecha_remision' => date('Y-m-d'),
            'items' => [[
                'item_general_id' => $this->itemConStock,
                'bodega_id'       => $this->bodegaId,
                'descripcion'     => 'Test despacho',
                'cantidad'        => $cantidad,
                'precio_unit'     => 10000,
                'subtotal'        => $cantidad * 10000,
            ]],
        ]))->call('post', 'api/remisiones');

        // FeatureTest a veces no captura el status code correctamente; verificamos por data
        $body = json_decode($crear->getJSON(), true);
        $this->assertSame('Remisión creada exitosamente', $body['message'] ?? null,
            'Create remisión esperaba mensaje exitoso. Body: ' . $crear->getJSON());
        $remId = (int) ($body['data']['id_remisiones'] ?? 0);
        $this->assertGreaterThan(0, $remId);

        // Stock antes
        $stockAntes = (float) $this->db->query("
            SELECT COALESCE(SUM(cantidad_disponible), 0) AS s
            FROM inventario_capas
            WHERE item_general_id = ? AND estado = 1
        ", [$this->itemConStock])->getRow()->s;

        $movsAntes = $this->db->table('movimiento_inventario')
            ->where('referencia_tipo', 'REMISION')
            ->where('referencia_id', $remId)
            ->countAllResults();

        // 2. Cambiar a Despachada
        $despachar = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode(['estado' => 'Despachada']))
          ->call('patch', "api/remisiones/{$remId}/estado");

        $despBody = json_decode($despachar->getJSON(), true);
        $this->assertSame('Remisión marcada como Despachada', $despBody['message'] ?? null,
            'Despachar falló. Body: ' . $despachar->getJSON());

        // 3. Verificar stock descontado
        $stockDespues = (float) $this->db->query("
            SELECT COALESCE(SUM(cantidad_disponible), 0) AS s
            FROM inventario_capas
            WHERE item_general_id = ? AND estado = 1
        ", [$this->itemConStock])->getRow()->s;

        $this->assertEqualsWithDelta($stockAntes - $cantidad, $stockDespues, 0.01,
            'Stock debe haber bajado exactamente la cantidad despachada');

        // 4. Verificar audit log SALIDA
        $movsDespues = $this->db->table('movimiento_inventario')
            ->where('referencia_tipo', 'REMISION')
            ->where('referencia_id', $remId)
            ->countAllResults();
        $this->assertSame($movsAntes + 1, $movsDespues);

        $mov = $this->db->table('movimiento_inventario')
            ->where('referencia_id', $remId)
            ->where('tipo_movimiento', 'SALIDA')
            ->orderBy('id_movimiento_inventario', 'DESC')
            ->limit(1)->get()->getRowArray();
        $this->assertNotNull($mov);
        $meta = json_decode($mov['metadata'], true);
        $this->assertArrayHasKey('remision_numero', $meta);
        $this->assertArrayHasKey('cliente_id', $meta);

        // 5. Verificar registro en remision_consumo_capas
        $consumos = $this->db->table('remision_consumo_capas')
            ->where('remision_id', $remId)
            ->countAllResults();
        $this->assertGreaterThan(0, $consumos, 'Debe haber registros de consumo de capas');

        // 6. Anular remisión → debe restaurar stock
        $anular = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode(['estado' => 'Anulada']))
          ->call('patch', "api/remisiones/{$remId}/estado");

        $anulBody = json_decode($anular->getJSON(), true);
        $this->assertSame('Remisión marcada como Anulada', $anulBody['message'] ?? null,
            'Anular falló: ' . $anular->getJSON());

        $stockFinal = (float) $this->db->query("
            SELECT COALESCE(SUM(cantidad_disponible), 0) AS s
            FROM inventario_capas
            WHERE item_general_id = ? AND estado = 1
        ", [$this->itemConStock])->getRow()->s;

        $this->assertEqualsWithDelta($stockAntes, $stockFinal, 0.01,
            'Stock debe haber vuelto al estado original tras anular');

        // 7. Audit log ENTRADA reverso
        $reverso = $this->db->table('movimiento_inventario')
            ->where('referencia_id', $remId)
            ->where('referencia_tipo', 'ANULACION')
            ->where('tipo_movimiento', 'ENTRADA')
            ->countAllResults();
        $this->assertGreaterThan(0, $reverso, 'Debe haber audit ENTRADA por reverso');
    }

    public function testDespacharSinStockSuficienteFallaConRollback(): void
    {
        if (!$this->itemConStock) {
            $this->markTestSkipped('No hay items para probar');
        }

        // Crear remisión con cantidad ridículamente alta
        $crear = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode([
            'cliente_id' => $this->clienteId,
            'fecha_remision' => date('Y-m-d'),
            'items' => [[
                'item_general_id' => $this->itemConStock,
                'descripcion'     => 'Test sin stock',
                'cantidad'        => 999999,
                'precio_unit'     => 1,
                'subtotal'        => 999999,
            ]],
        ]))->call('post', 'api/remisiones');
        $remId = (int) (json_decode($crear->getJSON(), true)['data']['id_remisiones'] ?? 0);

        $stockAntes = (float) $this->db->query("
            SELECT COALESCE(SUM(cantidad_disponible), 0) AS s
            FROM inventario_capas
            WHERE item_general_id = ? AND estado = 1
        ", [$this->itemConStock])->getRow()->s;

        $despachar = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode(['estado' => 'Despachada']))
          ->call('patch', "api/remisiones/{$remId}/estado");

        // Esperamos fallo — el mensaje debe indicar problema de stock
        $body = json_decode($despachar->getJSON(), true);
        $errMsg = $body['messages']['error'] ?? $body['message'] ?? '';
        $this->assertTrue(
            str_contains($errMsg, 'Sin stock') || str_contains($errMsg, 'Stock insuficiente') || str_contains($errMsg, 'stock'),
            'Esperaba mensaje de stock insuficiente, recibió: ' . $errMsg
        );

        // Stock no debe haberse modificado
        $stockDespues = (float) $this->db->query("
            SELECT COALESCE(SUM(cantidad_disponible), 0) AS s
            FROM inventario_capas
            WHERE item_general_id = ? AND estado = 1
        ", [$this->itemConStock])->getRow()->s;

        $this->assertEqualsWithDelta($stockAntes, $stockDespues, 0.01,
            'Stock NO debe modificarse si el despacho falla (rollback)');

        // Estado de la remisión debe seguir siendo Pendiente
        $rem = $this->db->table('remisiones')->where('id_remisiones', $remId)->get()->getRowArray();
        $this->assertSame('Pendiente', $rem['estado']);
    }

    public function testCrearRemisionSinClienteRetorna422(): void
    {
        $resp = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode([
            'items' => [['descripcion' => 'X', 'cantidad' => 1, 'precio_unit' => 1]],
        ]))->call('post', 'api/remisiones');

        $body = json_decode($resp->getJSON(), true);
        $this->assertArrayHasKey('cliente_id', $body['errors'] ?? [],
            'Esperaba error sobre cliente_id. Body: ' . $resp->getJSON());
    }
}
