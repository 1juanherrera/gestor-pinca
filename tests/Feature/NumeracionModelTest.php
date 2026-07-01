<?php

namespace Tests\Feature;

use App\Models\NumeracionModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Tests del modelo `NumeracionModel::reservar`:
 *
 *   - Reserva secuencial básica: 2 llamadas consecutivas devuelven N y N+1, ambos formateados
 *   - Reset anual: si anio_actual difiere del año actual y reinicia_anual=1, vuelve a 1
 *   - Rango DIAN agotado: cuando proximo_numero supera rango_max → lanza Exception
 *
 * Para no afectar las series productivas (factura, cotizacion, orden_compra, etc.),
 * usamos un tipo_doc inventado ("test_<random>") por cada test y desactivamos al final.
 *
 * @internal
 */
final class NumeracionModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = false;

    private NumeracionModel $model;
    private array $tiposCreados = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new NumeracionModel();

        // Defensa: si otra corrida de tests rompió migraciones, la tabla puede no existir.
        // Skip antes de cualquier insert.
        if (!$this->db->tableExists('numeracion_documentos')) {
            $this->markTestSkipped('Tabla numeracion_documentos no existe (correr php spark migrate)');
        }
    }

    protected function tearDown(): void
    {
        if (!empty($this->tiposCreados)) {
            $this->db->table('numeracion_documentos')
                ->whereIn('tipo_doc', $this->tiposCreados)
                ->delete();
        }
        parent::tearDown();
    }

    /**
     * Crea una serie de prueba con tipo_doc único y la marca para cleanup.
     */
    private function seedSerie(array $overrides = []): string
    {
        $tipo = 'test_' . bin2hex(random_bytes(4));
        $this->tiposCreados[] = $tipo;

        $base = [
            'tipo_doc'       => $tipo,
            'prefijo'        => 'TST-{Y}-',
            'padding'        => 4,
            'proximo_numero' => 1,
            'anio_actual'    => (int) date('Y'),
            'reinicia_anual' => 1,
            'activo'         => 1,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
            'updated_by'     => 'phpunit',
        ];

        $this->db->table('numeracion_documentos')->insert(array_merge($base, $overrides));
        return $tipo;
    }

    public function testReservarSecuencialBasico(): void
    {
        $tipo = $this->seedSerie([
            'prefijo'        => 'TST-{Y}-',
            'padding'        => 4,
            'proximo_numero' => 1,
        ]);
        $year = date('Y');

        $primero  = $this->model->reservar($tipo);
        $segundo  = $this->model->reservar($tipo);

        $this->assertSame("TST-{$year}-0001", $primero, 'Primera reserva devuelve 0001');
        $this->assertSame("TST-{$year}-0002", $segundo, 'Segunda reserva devuelve 0002');

        // proximo_numero en BD debe ser 3
        $row = $this->db->table('numeracion_documentos')
            ->where('tipo_doc', $tipo)->get()->getRowArray();
        $this->assertSame(3, (int) $row['proximo_numero']);
    }

    public function testReservarResetAnual(): void
    {
        // Setup con anio_actual del año pasado y proximo_numero=50.
        // Como NumeracionModel::reservar lee date('Y') del sistema (no inyectable),
        // simulamos el reset poniendo anio_actual = año actual - 1 → al llamar
        // reservar() detectará year > anioActual y reseteará a 1.
        $anioPasado = (int) date('Y') - 1;
        $tipo = $this->seedSerie([
            'prefijo'        => 'TST-{Y}-',
            'padding'        => 4,
            'proximo_numero' => 50,
            'anio_actual'    => $anioPasado,
            'reinicia_anual' => 1,
        ]);

        $year = date('Y');
        $reservado = $this->model->reservar($tipo);

        $this->assertSame("TST-{$year}-0001", $reservado, 'Debe resetear a 0001 al detectar año nuevo');

        // BD: anio_actual debe haberse actualizado al año actual y proximo_numero=2
        $row = $this->db->table('numeracion_documentos')
            ->where('tipo_doc', $tipo)->get()->getRowArray();
        $this->assertSame($year, (string) $row['anio_actual']);
        $this->assertSame(2, (int) $row['proximo_numero']);
    }

    public function testReservarRangoDianAgotado(): void
    {
        // rango_max=100, proximo_numero=100 → la primera reserva devuelve 100,
        // la segunda debería lanzar Exception (101 > 100).
        $tipo = $this->seedSerie([
            'prefijo'        => 'TST-{Y}-',
            'padding'        => 4,
            'proximo_numero' => 100,
            'rango_min'      => 1,
            'rango_max'      => 100,
        ]);
        $year = date('Y');

        $primero = $this->model->reservar($tipo);
        $this->assertSame("TST-{$year}-0100", $primero, 'La reserva 100 debe estar permitida');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/rango DIAN|excede/i');
        $this->model->reservar($tipo);
    }
}
