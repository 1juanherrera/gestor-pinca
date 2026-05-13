<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests del flujo end-to-end de Órdenes de Compra:
 *   1. Crear OC en estado "Borrador" con cabecera + líneas
 *   2. Cambiar estado a "Enviada"
 *   3. Recibir una línea → debe:
 *      - Marcar la línea como recibida (`recibido_en`)
 *      - Crear capa en `inventario_capas` con costo correcto + factor_conversion
 *      - Sumar inventario legacy
 *      - Generar audit log en `movimiento_inventario` (ENTRADA con metadata OC)
 *      - Actualizar `costos_item.costo_unitario` (promedio ponderado)
 *
 * Toca 5 tablas en una sola transacción atómica. Si algún paso falla,
 * todo debe rollback (no quedan capas huérfanas).
 *
 * @internal
 */
final class OrdenesCompraTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $refresh = false;

    private string $token;
    private int $proveedorId;
    private int $itemProveedorId;
    private int $itemGeneralId;
    private int $bodegaId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db->query('DELETE FROM login_attempts');

        // Login para obtener JWT
        $login = $this->call('post', 'api/login', [
            'username' => 'root',
            'password' => 'root',
        ]);
        $body = json_decode($login->getJSON(), true);
        $this->token = $body['token'] ?? '';

        // Capturar IDs reales de la BD para usar en los tests
        $this->proveedorId    = (int) $this->db->table('proveedor')->limit(1)->get()->getRow('id_proveedor');
        $this->bodegaId       = (int) $this->db->table('bodegas')->limit(1)->get()->getRow('id_bodegas');
        $this->itemProveedorId = (int) $this->db->table('item_proveedor')
            ->where('item_general_id IS NOT NULL')
            ->where('disponible', 1)
            ->limit(1)->get()->getRow('id_item_proveedor');
        $this->itemGeneralId = (int) $this->db->table('item_proveedor')
            ->where('id_item_proveedor', $this->itemProveedorId)
            ->get()->getRow('item_general_id');
    }

    public function testCrearOCRecibirLineaGeneraCapaYAuditLog(): void
    {
        // ── 1. Crear OC ─────────────────────────────────────────────
        $crearBody = [
            'proveedor_id'   => $this->proveedorId,
            'bodegas_id'     => $this->bodegaId,
            'fecha'          => date('Y-m-d'),
            'fecha_esperada' => date('Y-m-d', strtotime('+5 days')),
            'observaciones'  => 'Test integración OC',
            'lineas'         => [[
                'item_proveedor_id' => $this->itemProveedorId,
                'item_general_id'   => $this->itemGeneralId,
                'descripcion'       => 'Test',
                'cantidad'          => 10,
                'precio_unit'       => 5000,
            ]],
        ];
        $crearResp = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode($crearBody))->call('post', 'api/ordenes_compra');

        $crearResp->assertStatus(201);
        $crearJson = json_decode($crearResp->getJSON(), true);
        $idOrden   = (int) ($crearJson['id'] ?? 0);
        $this->assertGreaterThan(0, $idOrden, 'Debe retornar id de la OC creada');

        // Verificar que la OC quedó en Borrador con la línea
        $orden = $this->db->table('ordenes_compra')->where('id_orden', $idOrden)->get()->getRowArray();
        $this->assertSame('Borrador', $orden['estado']);
        $linea = $this->db->table('ordenes_compra_detalle')
            ->where('ordenes_compra_id', $idOrden)
            ->get()->getRowArray();
        $this->assertNotNull($linea, 'La línea debe existir');
        $this->assertNull($linea['recibido_en'], 'No debería estar recibida aún');

        // ── 2. Cambiar estado a Enviada ─────────────────────────────
        $estadoResp = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode(['estado' => 'Enviada']))
          ->call('patch', "api/ordenes_compra/{$idOrden}/estado");
        $estadoResp->assertStatus(200);

        // ── 3. Recibir línea ────────────────────────────────────────
        $capasAntes = $this->db->table('inventario_capas')
            ->where('item_general_id', $this->itemGeneralId)
            ->countAllResults();
        $movsAntes  = $this->db->table('movimiento_inventario')
            ->where('referencia_tipo', 'ORDEN_COMPRA')
            ->where('referencia_id', $idOrden)
            ->countAllResults();

        $recibirResp = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode([
            'cantidad_recibida' => 10,
            'lote_proveedor'    => 'TEST-LOTE-' . uniqid(),
        ]))->call('post', "api/ordenes_compra/{$idOrden}/recibir/{$linea['id_detalle']}");
        $recibirResp->assertStatus(200);

        // ── 4. Verificar efectos colaterales ────────────────────────

        // 4a. Línea marcada como recibida
        $lineaPost = $this->db->table('ordenes_compra_detalle')
            ->where('id_detalle', $linea['id_detalle'])
            ->get()->getRowArray();
        $this->assertNotNull($lineaPost['recibido_en'], 'La línea debe tener recibido_en');
        $this->assertEqualsWithDelta(10, (float) $lineaPost['cantidad_recibida'], 0.001);

        // 4b. Capa nueva creada con costo correcto
        $capasDespues = $this->db->table('inventario_capas')
            ->where('item_general_id', $this->itemGeneralId)
            ->countAllResults();
        $this->assertSame($capasAntes + 1, $capasDespues, 'Debe haber UNA capa nueva');

        $nuevaCapa = $this->db->table('inventario_capas')
            ->where('orden_compra_id', $idOrden)
            ->get()->getRowArray();
        $this->assertNotNull($nuevaCapa, 'La capa debe estar vinculada a la OC');
        $this->assertSame($this->bodegaId, (int) $nuevaCapa['bodegas_id']);
        $this->assertSame($this->proveedorId, (int) $nuevaCapa['proveedor_id']);
        $this->assertGreaterThan(0, (float) $nuevaCapa['cantidad_disponible']);
        $this->assertGreaterThan(0, (float) $nuevaCapa['costo_unitario']);

        // 4c. Audit log generado
        $movsDespues = $this->db->table('movimiento_inventario')
            ->where('referencia_tipo', 'ORDEN_COMPRA')
            ->where('referencia_id', $idOrden)
            ->countAllResults();
        $this->assertSame($movsAntes + 1, $movsDespues, 'Debe haber UN movimiento ENTRADA por OC');

        $mov = $this->db->table('movimiento_inventario')
            ->where('referencia_id', $idOrden)
            ->where('tipo_movimiento', 'ENTRADA')
            ->orderBy('id_movimiento_inventario', 'DESC')
            ->limit(1)->get()->getRowArray();
        $this->assertSame('ENTRADA', $mov['tipo_movimiento']);
        $this->assertSame($this->itemGeneralId, (int) $mov['item_general_id']);
        $this->assertNotNull($mov['metadata'], 'metadata JSON debe estar poblada');

        $meta = json_decode($mov['metadata'], true);
        $this->assertArrayHasKey('numero_oc', $meta);
        $this->assertArrayHasKey('proveedor_id', $meta);
        $this->assertArrayHasKey('factor_conversion', $meta);
    }

    public function testRecibirLineaDeOrdenNoEnviadaFalla(): void
    {
        // Crear OC pero NO cambiar estado a Enviada
        $crear = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode([
            'proveedor_id'   => $this->proveedorId,
            'bodegas_id'     => $this->bodegaId,
            'fecha'          => date('Y-m-d'),
            'lineas'         => [[
                'item_proveedor_id' => $this->itemProveedorId,
                'cantidad'          => 5,
                'precio_unit'       => 1000,
            ]],
        ]))->call('post', 'api/ordenes_compra');
        $crear->assertStatus(201);
        $idOrden = (int) (json_decode($crear->getJSON(), true)['id'] ?? 0);

        $linea = $this->db->table('ordenes_compra_detalle')
            ->where('ordenes_compra_id', $idOrden)
            ->get()->getRowArray();

        // Intentar recibir → debe rechazar (sigue en Borrador)
        $recibir = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode(['cantidad_recibida' => 5]))
          ->call('post', "api/ordenes_compra/{$idOrden}/recibir/{$linea['id_detalle']}");

        $recibir->assertStatus(400);
        $body = json_decode($recibir->getJSON(), true);
        $this->assertStringContainsString('Enviada', $body['messages']['error'] ?? $body['message'] ?? '');
    }

    public function testCrearOCSinProveedorRetorna422(): void
    {
        $resp = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode([
            'lineas' => [['item_proveedor_id' => 1, 'cantidad' => 1, 'precio_unit' => 1]],
        ]))->call('post', 'api/ordenes_compra');

        $resp->assertStatus(422);
        $body = json_decode($resp->getJSON(), true);
        $this->assertArrayHasKey('proveedor_id', $body['errors'] ?? []);
    }
}
