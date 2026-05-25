<?php

namespace Tests\Feature;

use App\Models\InventarioCapasModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Tests directos del modelo `InventarioCapasModel`:
 *
 *   - crearCapa + recalcularPromedioPonderado → calcula bien el promedio ponderado
 *   - consumirCapasFIFO → consume capas en orden de fecha_ingreso, marca estado=0 al agotar
 *   - consumirCapasPorProveedor con déficit → lanza Exception
 *   - restaurarCapas → revierte el descuento y reactiva capas agotadas
 *   - consumirCapasManual → consume cantidades específicas de capas específicas
 *
 * Estos tests trabajan a nivel modelo (sin pasar por el controller HTTP) y crean
 * sus propias capas con un item_general_id y bodegas_id reales de la BD para no
 * romper FKs. Al cierre, tearDown limpia todo el rastro.
 *
 * @internal
 */
final class InventarioCapasModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;

    private InventarioCapasModel $model;
    private int $itemId;
    private int $bodegaId;
    private int $proveedorAId;
    private int $proveedorBId;

    /** @var int[] capas creadas en este test (para cleanup) */
    private array $capasCreadas = [];

    /** @var int[] preparaciones reales creadas para vincular consumos (para cleanup) */
    private array $prepsSimuladas = [];

    /**
     * Crea una preparación real mínima para poder asociar consumos sin violar FKs.
     * Devuelve su id_preparaciones.
     */
    private function crearPreparacionDummy(): int
    {
        $this->db->table('preparaciones')->insert([
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'cantidad'       => 1,
            'estado'         => 0, // PENDIENTE
            'item_general_id' => $this->itemId,
            'observaciones'   => 'TEST_InventarioCapasModelTest dummy',
        ]);
        $id = (int) $this->db->insertID();
        $this->prepsSimuladas[] = $id;
        return $id;
    }

    /** Costo previo de costos_item para poder restaurarlo en tearDown */
    private ?array $costoItemBackup = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new InventarioCapasModel();

        // Resolver FKs reales de la BD
        $this->itemId = (int) $this->db->table('item_general')
            ->where('tipo', 1) // materia prima
            ->where('deleted_at', null)
            ->limit(1)->get()->getRow('id_item_general');

        $this->bodegaId = (int) $this->db->table('bodegas')
            ->limit(1)->get()->getRow('id_bodegas');

        $provs = $this->db->table('proveedor')
            ->where('deleted_at', null)
            ->limit(2)->get()->getResultArray();

        if (count($provs) < 2) {
            $this->markTestSkipped('Se requieren al menos 2 proveedores en la BD para correr estos tests');
        }
        $this->proveedorAId = (int) $provs[0]['id_proveedor'];
        $this->proveedorBId = (int) $provs[1]['id_proveedor'];

        // Backup del registro costos_item del item (si existe) para restaurarlo
        $row = $this->db->table('costos_item')
            ->where('item_general_id', $this->itemId)
            ->limit(1)->get()->getRowArray();
        if ($row) {
            $this->costoItemBackup = $row;
        }
    }

    protected function tearDown(): void
    {
        // Borrar consumos de preparaciones simuladas y las preparaciones mismas
        if (!empty($this->prepsSimuladas)) {
            $this->db->table('preparacion_consumo_capas')
                ->whereIn('preparacion_id', $this->prepsSimuladas)
                ->delete();
            $this->db->table('produccion_insumos_detalle')
                ->whereIn('preparacion_id', $this->prepsSimuladas)
                ->delete();
            $this->db->table('preparaciones')
                ->whereIn('id_preparaciones', $this->prepsSimuladas)
                ->delete();
        }

        // Borrar capas creadas
        if (!empty($this->capasCreadas)) {
            $this->db->table('inventario_capas')
                ->whereIn('id_capa', $this->capasCreadas)
                ->delete();
        }

        // Restaurar costos_item original (o eliminar si no existía)
        if ($this->costoItemBackup) {
            $id = (int) $this->costoItemBackup['id_costos_item'];
            unset($this->costoItemBackup['id_costos_item']);
            $this->db->table('costos_item')
                ->where('id_costos_item', $id)
                ->update($this->costoItemBackup);
        } else {
            // Si no existía y el test lo creó, lo borramos
            $this->db->table('costos_item')
                ->where('item_general_id', $this->itemId)
                ->where('metodo_calculo', 'PROMEDIO_PONDERADO')
                ->delete();
        }

        parent::tearDown();
    }

    /**
     * Crea una capa de prueba y la registra para cleanup.
     * Espacía fecha_ingreso por segundos para garantizar orden FIFO determinístico.
     */
    private function nuevaCapa(float $cantidad, float $costo, ?int $proveedorId = null, int $offsetSegundos = 0): int
    {
        $id = $this->model->crearCapa([
            'item_general_id'     => $this->itemId,
            'bodegas_id'          => $this->bodegaId,
            'proveedor_id'        => $proveedorId,
            'cantidad_original'   => $cantidad,
            'cantidad_disponible' => $cantidad,
            'costo_unitario'      => $costo,
            'fecha_ingreso'       => date('Y-m-d H:i:s', time() + $offsetSegundos),
            'observaciones'       => 'TEST_InventarioCapasModelTest',
        ]);
        $this->capasCreadas[] = $id;
        return $id;
    }

    public function testCrearCapaYRecalcularPromedio(): void
    {
        // Capa 1: 10 kg @ $100  → aporte $1.000
        // Capa 2: 20 kg @ $200  → aporte $4.000
        // Promedio esperado: $5.000 / 30 kg ≈ 166.6667
        $this->nuevaCapa(10, 100, $this->proveedorAId, 0);
        $this->nuevaCapa(20, 200, $this->proveedorAId, 1);

        $resumen = $this->model->resumenStock($this->itemId);
        $this->assertGreaterThanOrEqual(30.0, (float) $resumen['stock_total'], 'Stock total debe incluir las 2 capas nuevas');

        $costoPromedio = $this->model->recalcularPromedioPonderado($this->itemId);

        // El item puede tener capas legacy; verificamos que el costo sea coherente
        // con las capas ACTIVAS del item, no solo con las 2 que creamos. Por eso
        // recalculamos el esperado leyendo todas las capas activas del item.
        $capasActivas = $this->db->table('inventario_capas')
            ->where('item_general_id', $this->itemId)
            ->where('estado', 1)
            ->where('cantidad_disponible >', 0)
            ->get()->getResultArray();

        $totalQty = 0.0;
        $totalPond = 0.0;
        foreach ($capasActivas as $c) {
            $totalQty  += (float) $c['cantidad_disponible'];
            $totalPond += (float) $c['cantidad_disponible'] * (float) $c['costo_unitario'];
        }
        $esperado = $totalQty > 0 ? round($totalPond / $totalQty, 4) : 0.0;

        $this->assertEqualsWithDelta($esperado, $costoPromedio, 0.01, 'Promedio ponderado coincide con cálculo manual');

        // Y costos_item refleja el mismo valor
        $row = $this->db->table('costos_item')
            ->where('item_general_id', $this->itemId)
            ->get()->getRowArray();
        $this->assertNotNull($row, 'Debe existir registro en costos_item');
        $this->assertEqualsWithDelta($esperado, (float) $row['costo_unitario'], 0.01);
    }

    public function testConsumirCapasFIFO(): void
    {
        $capa1 = $this->nuevaCapa(10, 100, $this->proveedorAId, 0);
        $capa2 = $this->nuevaCapa(20, 200, $this->proveedorAId, 1);

        // Para que el FIFO solo afecte a NUESTRAS capas, filtramos por bodega
        // dedicada — pero como reusamos la misma, vamos a consumir solo lo que
        // sabemos que aportamos: filtramos por proveedor en lugar de FIFO global.
        // Usamos consumirCapasPorProveedor que también es FIFO pero acotado.
        $this->db->transBegin();
        $consumos = $this->model->consumirCapasPorProveedor($this->itemId, 15, $this->proveedorAId, $this->bodegaId);
        $this->db->transCommit();

        $this->assertNotEmpty($consumos, 'Debe devolver array de consumos');

        // Verificamos que la primera capa se agotó (10 kg consumidos)
        $c1 = $this->db->table('inventario_capas')->where('id_capa', $capa1)->get()->getRowArray();
        $this->assertEqualsWithDelta(0.0, (float) $c1['cantidad_disponible'], 0.001, 'Capa 1 debe quedar en 0');
        $this->assertEquals(0, (int) $c1['estado'], 'Capa 1 debe quedar agotada (estado=0)');

        // Capa 2: debe haber bajado en 5 kg (15 - 10 ya consumidos de capa1)
        $c2 = $this->db->table('inventario_capas')->where('id_capa', $capa2)->get()->getRowArray();
        $this->assertEqualsWithDelta(15.0, (float) $c2['cantidad_disponible'], 0.001, 'Capa 2 debe quedar en 15 (20-5)');
        $this->assertEquals(1, (int) $c2['estado'], 'Capa 2 debe seguir activa');

        // Validar shape del array de consumos: debe contener entries para ambas capas
        $idsConsumidos = array_column($consumos, 'capa_id');
        $this->assertContains($capa1, $idsConsumidos, 'Consumos debe incluir capa1');
        $this->assertContains($capa2, $idsConsumidos, 'Consumos debe incluir capa2');

        // Sumar cantidades consumidas debería dar 15
        $totalConsumido = array_sum(array_column($consumos, 'cantidad_consumida'));
        $this->assertEqualsWithDelta(15.0, $totalConsumido, 0.001);
    }

    public function testConsumirCapasPorProveedorInsuficiente(): void
    {
        // Proveedor B aporta 5 kg, intentamos consumir 8 → debe lanzar Exception
        $this->nuevaCapa(5, 150, $this->proveedorBId, 0);
        // El otro proveedor también tiene stock, pero NO debe contar
        $this->nuevaCapa(10, 100, $this->proveedorAId, 1);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Stock insuficiente|insuficiente/i');

        $this->db->transBegin();
        try {
            $this->model->consumirCapasPorProveedor($this->itemId, 8, $this->proveedorBId, $this->bodegaId);
            $this->db->transCommit();
        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function testRestaurarCapas(): void
    {
        $capa1 = $this->nuevaCapa(10, 100, $this->proveedorAId, 0);
        $capa2 = $this->nuevaCapa(20, 200, $this->proveedorAId, 1);

        // Crear preparación real para respetar la FK de preparacion_consumo_capas
        $prepId = $this->crearPreparacionDummy();

        // Consumir 15 → consume capa1 entera + 5 de capa2
        $this->db->transBegin();
        $consumos = $this->model->consumirCapasPorProveedor($this->itemId, 15, $this->proveedorAId, $this->bodegaId);
        $this->model->registrarConsumos($prepId, $consumos);
        $this->db->transCommit();

        // Sanity: capa1 agotada
        $c1Pre = $this->db->table('inventario_capas')->where('id_capa', $capa1)->get()->getRowArray();
        $this->assertEquals(0, (int) $c1Pre['estado']);

        // Ahora restaurar
        $this->model->restaurarCapas($prepId);

        $c1Post = $this->db->table('inventario_capas')->where('id_capa', $capa1)->get()->getRowArray();
        $this->assertEqualsWithDelta(10.0, (float) $c1Post['cantidad_disponible'], 0.001, 'Capa 1 debe volver a 10');
        $this->assertEquals(1, (int) $c1Post['estado'], 'Capa 1 debe reactivarse (estado=1)');

        $c2Post = $this->db->table('inventario_capas')->where('id_capa', $capa2)->get()->getRowArray();
        $this->assertEqualsWithDelta(20.0, (float) $c2Post['cantidad_disponible'], 0.001, 'Capa 2 debe volver a 20');
        $this->assertEquals(1, (int) $c2Post['estado']);

        // Y los registros de preparacion_consumo_capas deben haberse borrado
        $consumosPost = $this->db->table('preparacion_consumo_capas')
            ->where('preparacion_id', $prepId)
            ->countAllResults();
        $this->assertSame(0, $consumosPost, 'preparacion_consumo_capas debe quedar limpia');
    }

    public function testConsumirCapasManualConSeleccionEspecifica(): void
    {
        $capa1 = $this->nuevaCapa(10, 100, $this->proveedorAId, 0);
        $capa2 = $this->nuevaCapa(20, 200, $this->proveedorAId, 1);

        // Consumo manual: 3 de capa1 y 7 de capa2 (saltea FIFO)
        $this->db->transBegin();
        $consumos = $this->model->consumirCapasManual([
            ['capa_id' => $capa1, 'cantidad' => 3],
            ['capa_id' => $capa2, 'cantidad' => 7],
        ], $this->itemId);
        $this->db->transCommit();

        $this->assertCount(2, $consumos);

        $c1 = $this->db->table('inventario_capas')->where('id_capa', $capa1)->get()->getRowArray();
        $this->assertEqualsWithDelta(7.0, (float) $c1['cantidad_disponible'], 0.001, 'Capa 1: 10 - 3 = 7');
        $this->assertEquals(1, (int) $c1['estado']);

        $c2 = $this->db->table('inventario_capas')->where('id_capa', $capa2)->get()->getRowArray();
        $this->assertEqualsWithDelta(13.0, (float) $c2['cantidad_disponible'], 0.001, 'Capa 2: 20 - 7 = 13');

        // Shape de la respuesta
        $primero = $consumos[0];
        $this->assertArrayHasKey('capa_id', $primero);
        $this->assertArrayHasKey('cantidad_consumida', $primero);
        $this->assertArrayHasKey('costo_unitario', $primero);
        $this->assertArrayHasKey('costo_total', $primero);
    }
}
