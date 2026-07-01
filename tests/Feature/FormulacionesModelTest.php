<?php

namespace Tests\Feature;

use App\Models\FormulacionesModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Tests del modelo `FormulacionesModel::clonarFormulacion`:
 *
 *   - Clonado básico copia todos los ingredientes con cantidades y porcentajes idénticos
 *     y activa la nueva fórmula en el producto destino
 *   - El nombre custom se respeta
 *   - Falla si from == to
 *
 * Como las fórmulas legacy de la BD productiva tienen porcentajes NULL (validación de
 * suma=100 fallaría), creamos producto origen + fórmula origen + producto destino
 * desde cero en cada test. Cleanup completo en tearDown.
 *
 * @internal
 */
final class FormulacionesModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;

    private FormulacionesModel $model;
    private array $itemsCreados = [];
    private array $formulacionesCreadas = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new FormulacionesModel();
    }

    protected function tearDown(): void
    {
        // 1. Borrar versiones + ingredientes + cabeceras de fórmulas creadas
        if (!empty($this->formulacionesCreadas)) {
            $this->db->table('item_general_formulaciones')
                ->whereIn('formulaciones_id', $this->formulacionesCreadas)
                ->delete();
            $this->db->table('formulaciones_versiones')
                ->whereIn('formulacion_id', $this->formulacionesCreadas)
                ->delete();
            $this->db->table('formulaciones')
                ->whereIn('id_formulaciones', $this->formulacionesCreadas)
                ->delete();
        }

        // 2. Borrar costos_item y items creados
        if (!empty($this->itemsCreados)) {
            $this->db->table('costos_item')
                ->whereIn('item_general_id', $this->itemsCreados)
                ->delete();
            // Limpiar fórmulas adicionales generadas con CASCADE (defensa en profundidad)
            $formsHuerfanas = $this->db->table('formulaciones')
                ->whereIn('item_general_id', $this->itemsCreados)
                ->get()->getResultArray();
            $idsForms = array_column($formsHuerfanas, 'id_formulaciones');
            if ($idsForms) {
                $this->db->table('item_general_formulaciones')
                    ->whereIn('formulaciones_id', $idsForms)
                    ->delete();
                $this->db->table('formulaciones_versiones')
                    ->whereIn('formulacion_id', $idsForms)
                    ->delete();
                $this->db->table('formulaciones')
                    ->whereIn('id_formulaciones', $idsForms)
                    ->delete();
            }
            $this->db->table('item_general')
                ->whereIn('id_item_general', $this->itemsCreados)
                ->delete();
        }

        parent::tearDown();
    }

    /**
     * Crea un producto temporal y lo registra para cleanup.
     */
    private function crearItem(string $nombre, int $tipo): int
    {
        $unidadId = (int) $this->db->table('unidad')->limit(1)->get()->getRow('id_unidad');
        $this->db->table('item_general')->insert([
            'nombre'    => $nombre,
            'tipo'      => $tipo,
            'unidad_id' => $unidadId,
            'p_kg'      => '0',
        ]);
        $id = (int) $this->db->insertID();
        $this->itemsCreados[] = $id;
        return $id;
    }

    public function testClonarFormulacionCopiaIngredientes(): void
    {
        // 1. Crear 3 materias primas
        $mp1 = $this->crearItem('TEST_MP_A_' . uniqid(), 1);
        $mp2 = $this->crearItem('TEST_MP_B_' . uniqid(), 1);
        $mp3 = $this->crearItem('TEST_MP_C_' . uniqid(), 1);

        // 2. Crear producto origen + fórmula activa con 3 ingredientes (suma 100%)
        $fromItemId = $this->crearItem('TEST_PROD_ORIGEN_' . uniqid(), 0);

        $resCrear = $this->model->crearFormulacion([
            'item_general_id' => $fromItemId,
            'nombre'          => 'FÓRMULA TEST ORIGEN',
            'descripcion'     => 'Test descripción origen',
            'materias_primas' => [
                ['materia_prima_id' => $mp1, 'cantidad' => 5,  'porcentaje' => 50],
                ['materia_prima_id' => $mp2, 'cantidad' => 3,  'porcentaje' => 30],
                ['materia_prima_id' => $mp3, 'cantidad' => 2,  'porcentaje' => 20],
            ],
            'responsable'     => 'phpunit',
        ]);
        $this->formulacionesCreadas[] = (int) $resCrear['formulacion_id'];

        // 3. Crear producto destino
        $toItemId = $this->crearItem('TEST_PROD_DESTINO_' . uniqid(), 0);

        // 4. Clonar
        $nombreCustom = 'TEST Clonada ' . uniqid();
        $resultado = $this->model->clonarFormulacion($fromItemId, $toItemId, $nombreCustom, 'phpunit');

        $this->assertTrue((bool) ($resultado['success'] ?? false), 'clonarFormulacion debe devolver success=true');
        $this->assertArrayHasKey('formulacion_id', $resultado);
        $newFormId = (int) $resultado['formulacion_id'];
        $this->formulacionesCreadas[] = $newFormId;

        // 5. Cabecera nueva activa, vinculada al destino, con el nombre custom
        $cabecera = $this->db->table('formulaciones')
            ->where('id_formulaciones', $newFormId)
            ->get()->getRowArray();
        $this->assertNotNull($cabecera);
        $this->assertSame($toItemId, (int) $cabecera['item_general_id']);
        $this->assertSame(1, (int) $cabecera['estado']);
        $this->assertSame($nombreCustom, $cabecera['nombre']);

        // 6. Mismos ingredientes (mismo set + cantidad + porcentaje)
        $ingrDestino = $this->db->query("
            SELECT item_general_id, cantidad, porcentaje
            FROM item_general_formulaciones
            WHERE formulaciones_id = ?
            ORDER BY item_general_id ASC
        ", [$newFormId])->getResultArray();

        $this->assertCount(3, $ingrDestino, 'Debe haber 3 ingredientes copiados');

        $esperados = [
            $mp1 => ['cantidad' => 5.0, 'porcentaje' => 50],
            $mp2 => ['cantidad' => 3.0, 'porcentaje' => 30],
            $mp3 => ['cantidad' => 2.0, 'porcentaje' => 20],
        ];
        foreach ($ingrDestino as $row) {
            $itemId = (int) $row['item_general_id'];
            $this->assertArrayHasKey($itemId, $esperados, "Ingrediente {$itemId} debe ser uno de los esperados");
            $this->assertEqualsWithDelta($esperados[$itemId]['cantidad'], (float) $row['cantidad'], 0.001);
            $this->assertSame($esperados[$itemId]['porcentaje'], (int) $row['porcentaje']);
        }

        // 7. La nueva fórmula tiene versión inicial
        $this->assertArrayHasKey('version_id', $resultado);
        $this->assertNotEmpty($resultado['version_id']);
    }

    public function testClonarFormulacionFallaSiOrigenIgualDestino(): void
    {
        // Solo creamos un item — el guard se dispara antes de tocar la BD
        $itemId = $this->crearItem('TEST_PROD_DUP_' . uniqid(), 0);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/no pueden ser el mismo|origen y destino/i');

        $this->model->clonarFormulacion($itemId, $itemId, null, 'phpunit');
    }
}
