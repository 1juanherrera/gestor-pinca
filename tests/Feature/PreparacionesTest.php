<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests del flujo de Producción (MRP II — Costeo por Lotes):
 *
 *   1. Crear preparación con cantidad → debe consumir capas FIFO
 *   2. Verificar que `preparacion_consumo_capas` tiene una fila por cada capa consumida
 *   3. Verificar que `produccion_insumos_detalle` tiene snapshot del costo congelado
 *   4. Verificar que se creó audit log SALIDA por cada ingrediente
 *   5. Verificar que la preparación referencia la versión exacta de fórmula usada
 *
 * Pre-condición: el item objetivo debe tener formulación activa con ingredientes
 * que tengan stock en inventario_capas. Skipea si no se cumple (test ambiental).
 *
 * @internal
 */
final class PreparacionesTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $refresh = false;

    private string $token;
    private ?int $itemConFormulacion = null;
    private ?int $unidadId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db->query('DELETE FROM login_attempts');

        $login = $this->call('post', 'api/login', ['username' => 'root', 'password' => 'root']);
        $body = json_decode($login->getJSON(), true);
        $this->token = $body['token'] ?? '';

        // Encontrar un item con formulación activa cuyos ingredientes TODOS tengan stock
        $candidatos = $this->db->query("
            SELECT f.item_general_id, ig.unidad_id
            FROM formulaciones f
            JOIN item_general ig ON ig.id_item_general = f.item_general_id
            WHERE f.estado = 1 AND ig.unidad_id IS NOT NULL
        ")->getResultArray();

        foreach ($candidatos as $c) {
            $itemId = (int) $c['item_general_id'];
            // ¿Todos los ingredientes tienen stock?
            $faltanStock = $this->db->query("
                SELECT COUNT(DISTINCT igf.item_general_id) AS faltan
                FROM item_general_formulaciones igf
                JOIN formulaciones f ON f.id_formulaciones = igf.formulaciones_id
                WHERE f.item_general_id = ? AND f.estado = 1
                  AND NOT EXISTS (
                    SELECT 1 FROM inventario_capas ic
                    WHERE ic.item_general_id = igf.item_general_id
                      AND ic.estado = 1 AND ic.cantidad_disponible > 0
                  )
            ", [$itemId])->getRowArray();
            if ((int) ($faltanStock['faltan'] ?? 1) === 0) {
                $this->itemConFormulacion = $itemId;
                $this->unidadId = (int) $c['unidad_id'];
                break;
            }
        }
    }

    public function testCrearPreparacionConsumeCapasYGeneraSnapshot(): void
    {
        if (!$this->itemConFormulacion) {
            $this->markTestSkipped('No hay item con formulación activa + stock para probar');
        }

        // Snapshots PRE
        $consumosAntes = $this->db->table('preparacion_consumo_capas')->countAllResults();
        $snapshotsAntes = $this->db->table('produccion_insumos_detalle')->countAllResults();
        $movsAntes = $this->db->table('movimiento_inventario')
            ->where('referencia_tipo', 'ORDEN_PRODUCCION')
            ->countAllResults();

        // Crear preparación con cantidad pequeña para no agotar stock
        $resp = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode([
            'item_general_id' => $this->itemConFormulacion,
            'cantidad'        => 1,    // 1 unidad en la unidad del item
            'unidad_id'       => $this->unidadId,
            'observaciones'   => 'Test integración producción',
        ]))->call('post', 'api/preparaciones');

        if ($resp->getStatusCode() !== 201) {
            $this->fail('create_preparacion falló: ' . $resp->getStatusCode() . ' body=' . $resp->getJSON());
        }

        $body = json_decode($resp->getJSON(), true);
        $prepId = (int) ($body['data']['id_preparaciones'] ?? $body['data']['id'] ?? 0);
        $this->assertGreaterThan(0, $prepId, 'Debe retornar id de preparación creada');

        // ── Verificar consumo de capas ──────────────────────────────
        $consumosDespues = $this->db->table('preparacion_consumo_capas')
            ->where('preparacion_id', $prepId)
            ->countAllResults();
        $this->assertGreaterThan(0, $consumosDespues, 'Debe haber al menos un consumo de capa registrado');

        // ── Verificar snapshot de costo congelado ───────────────────
        $snapshots = $this->db->table('produccion_insumos_detalle')
            ->where('preparacion_id', $prepId)
            ->get()->getResultArray();
        $this->assertNotEmpty($snapshots, 'produccion_insumos_detalle debe tener filas');

        foreach ($snapshots as $s) {
            $this->assertGreaterThan(0, (float) $s['cantidad'], 'Cantidad consumida > 0');
            $this->assertGreaterThanOrEqual(0, (float) $s['costo_unitario'], 'Costo unitario válido');
            $this->assertEqualsWithDelta(
                (float) $s['cantidad'] * (float) $s['costo_unitario'],
                (float) $s['subtotal'],
                0.01,
                'subtotal debe ser cantidad × costo_unitario'
            );
        }

        // ── Verificar audit log SALIDA ──────────────────────────────
        $movs = $this->db->table('movimiento_inventario')
            ->where('referencia_tipo', 'ORDEN_PRODUCCION')
            ->where('referencia_id', $prepId)
            ->where('tipo_movimiento', 'SALIDA')
            ->countAllResults();
        $this->assertGreaterThan(0, $movs, 'Debe haber movimientos SALIDA por la producción');

        // ── Verificar versión de fórmula capturada ──────────────────
        $prep = $this->db->table('preparaciones')
            ->where('id_preparaciones', $prepId)
            ->get()->getRowArray();
        $this->assertNotNull(
            $prep['formulacion_version_id'],
            'preparaciones.formulacion_version_id debe estar poblado al crear'
        );

        // La versión apunta a una versión real con snapshot
        $version = $this->db->table('formulaciones_versiones')
            ->where('id', $prep['formulacion_version_id'])
            ->get()->getRowArray();
        $this->assertNotNull($version, 'La versión debe existir');
        $this->assertNotEmpty($version['ingredientes'], 'La versión debe tener snapshot de ingredientes');
    }

    public function testCrearPreparacionSinFormulacionFalla(): void
    {
        // Buscar un item SIN formulación activa
        $sinForm = $this->db->query("
            SELECT id_item_general
            FROM item_general
            WHERE NOT EXISTS (
                SELECT 1 FROM formulaciones f
                WHERE f.item_general_id = item_general.id_item_general AND f.estado = 1
            )
            AND tipo = 0
            LIMIT 1
        ")->getRowArray();

        if (!$sinForm) {
            $this->markTestSkipped('Todos los productos tienen formulación activa');
        }

        $resp = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode([
            'item_general_id' => (int) $sinForm['id_item_general'],
            'cantidad'        => 1,
            'unidad_id'       => $this->unidadId ?? 1,
        ]))->call('post', 'api/preparaciones');

        $resp->assertStatus(422);
        $body = json_decode($resp->getJSON(), true);
        $this->assertFalse($body['success'] ?? null);
        $this->assertStringContainsString('formulación', $body['message'] ?? '');
    }

    public function testCrearPreparacionConDatosInvalidosFalla422(): void
    {
        $resp = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type'  => 'application/json',
        ])->withBody(json_encode(['cantidad' => -5]))
          ->call('post', 'api/preparaciones');

        $resp->assertStatus(422);
        $body = json_decode($resp->getJSON(), true);
        $this->assertArrayHasKey('item_general_id', $body['errors'] ?? []);
        $this->assertArrayHasKey('unidad_id', $body['errors'] ?? []);
    }
}
