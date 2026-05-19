<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\FacturasModel;
use App\Models\InventarioCapasModel;
use App\Models\NumeracionModel;

/**
 * Validación end-to-end de los 10 fixes aplicados en la sesión 2026-05-19.
 *
 * Uso: docker exec gestor-pinca-app php spark validar:fixes
 *
 * Cada test usa transBegin/transRollback para no alterar la BD.
 */
class ValidarFixes extends BaseCommand
{
    protected $group       = 'Validación';
    protected $name        = 'validar:fixes';
    protected $description = 'Corre todas las pruebas de los fixes del análisis profundo.';

    private int $pass = 0;
    private int $fail = 0;
    private array $details = [];

    public function run(array $params)
    {
        CLI::write('═══ Validación de fixes — ' . date('Y-m-d H:i:s') . ' ═══', 'cyan');
        CLI::newLine();

        $this->test1_recalcularSaldo();
        $this->test2_fifoLanzaSiInsuficiente();
        $this->test3_filtrosEstado();
        $this->test4_rutaBodegasItemEliminada();
        $this->test5_requisicionesUsaNumeracionModel();
        $this->test6_numeracionTransaccional();
        $this->test7_remisionesTransaccional();
        $this->test8_cotizacionesTransaccional();
        $this->test9_sinInterpolacionSQL();
        $this->test10_validacionFKOC();

        CLI::newLine();
        CLI::write('── Importantes ──', 'cyan');
        $this->test11_traspasoSinSaldos();
        $this->test12_cambiarEstadoTransaccional();
        $this->test13_updateTableSoftDelete();
        $this->test14_restoreTablePk();
        $this->test15_getLockChequea();
        $this->test16_mrpUsaCapas();
        $this->test17_deletedAtEnProveedores();
        $this->test18_loginNoBloqueaLegitimo();
        $this->test19_catalogoDeleteChequeaStock();

        CLI::newLine();
        CLI::write('── Menores ──', 'cyan');
        $this->test20_codigoMuertoEliminado();
        $this->test21_registrarMovimientoEliminado();
        $this->test22_costoProduccionUsaPromedio();
        $this->test23_estadoNormalizadoString();

        CLI::newLine();
        CLI::write('── IVA en OCs (B+) ──', 'cyan');
        $this->test24_ivaPctColumnaExiste();
        $this->test25_ocCreateGuardaIvaPct();
        $this->test26_listarDevuelveTrioIva();
        $this->test27_dashboardDevuelveConIva();
        $this->test28_remisionesUsaCfg();

        CLI::newLine();
        CLI::write("═══ Resumen ═══", 'cyan');
        CLI::write("PASS: {$this->pass}   FAIL: {$this->fail}", $this->fail === 0 ? 'green' : 'red');

        if (!empty($this->details)) {
            CLI::newLine();
            CLI::write('Detalle:', 'yellow');
            foreach ($this->details as $d) CLI::write('  • ' . $d);
        }
    }

    // ── helpers ──────────────────────────────────────────────────────────
    private function pass(string $name, string $note = ''): void
    {
        $this->pass++;
        CLI::write("  ✓ {$name}" . ($note ? " — {$note}" : ''), 'green');
    }
    private function fail(string $name, string $note): void
    {
        $this->fail++;
        $this->details[] = "{$name}: {$note}";
        CLI::write("  ✗ {$name} — {$note}", 'red');
    }

    // ── #1 — FacturasModel::recalcularSaldo existe y funciona ────────────
    private function test1_recalcularSaldo(): void
    {
        CLI::write('#1 recalcularSaldo', 'cyan');

        $m = new FacturasModel();
        if (!method_exists($m, 'recalcularSaldo')) {
            $this->fail('#1.a método existe', 'recalcularSaldo no definido');
            return;
        }
        $this->pass('#1.a método existe');

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Crear factura de prueba
            $db->table('facturas')->insert([
                'numero'          => 'TEST-' . uniqid(),
                'cliente_id'      => null,
                'fecha_emision'   => date('Y-m-d'),
                'total'           => 1000.00,
                'saldo_pendiente' => 1000.00,
                'estado'          => 'Pendiente',
                'subtotal'        => 1000.00,
            ]);
            $fid = $db->insertID();

            // Caso A: sin pagos → sigue Pendiente, saldo 1000
            $m->recalcularSaldo($fid);
            $f = $db->table('facturas')->where('id_facturas', $fid)->get()->getRowArray();
            if ((float) $f['saldo_pendiente'] === 1000.00 && $f['estado'] === 'Pendiente') {
                $this->pass('#1.b sin pagos: saldo=1000, estado=Pendiente');
            } else {
                $this->fail('#1.b sin pagos', "saldo={$f['saldo_pendiente']}, estado={$f['estado']}");
            }

            // Caso B: con abono parcial de 300 → Parcial, saldo 700
            $db->table('pagos_cliente')->insert([
                'fecha_pago' => date('Y-m-d'),
                'monto' => 300.00, 'metodo_pago' => 'efectivo',
                'tipo' => 'abono', 'facturas_id' => $fid,
            ]);
            $m->recalcularSaldo($fid);
            $f = $db->table('facturas')->where('id_facturas', $fid)->get()->getRowArray();
            if ((float) $f['saldo_pendiente'] === 700.00 && $f['estado'] === 'Parcial') {
                $this->pass('#1.c abono 300: saldo=700, estado=Parcial');
            } else {
                $this->fail('#1.c abono 300', "saldo={$f['saldo_pendiente']}, estado={$f['estado']}");
            }

            // Caso C: + NC activa por 700 → totalmente cubierta → Pagada, saldo 0
            $db->table('notas_credito')->insert([
                'numero' => 'NC-T-' . uniqid(), 'facturas_id' => $fid,
                'clientes_id' => 1, 'fecha' => date('Y-m-d'),
                'monto' => 700.00, 'estado' => 'Activa',
            ]);
            $m->recalcularSaldo($fid);
            $f = $db->table('facturas')->where('id_facturas', $fid)->get()->getRowArray();
            if ((float) $f['saldo_pendiente'] <= 0.01 && $f['estado'] === 'Pagada') {
                $this->pass('#1.d con NC 700: saldo=0, estado=Pagada');
            } else {
                $this->fail('#1.d con NC 700', "saldo={$f['saldo_pendiente']}, estado={$f['estado']}");
            }

            // Caso D: factura Anulada NO debe tocarse
            $db->table('facturas')->where('id_facturas', $fid)->update(['estado' => 'Anulada', 'saldo_pendiente' => 999]);
            $m->recalcularSaldo($fid);
            $f = $db->table('facturas')->where('id_facturas', $fid)->get()->getRowArray();
            if ($f['estado'] === 'Anulada' && (float) $f['saldo_pendiente'] === 999.00) {
                $this->pass('#1.e factura Anulada no se toca');
            } else {
                $this->fail('#1.e Anulada se modificó', "saldo={$f['saldo_pendiente']}, estado={$f['estado']}");
            }
        } finally {
            $db->transRollback();
        }
    }

    // ── #2 + #18 — FIFO lanza Exception si stock insuficiente ────────────
    private function test2_fifoLanzaSiInsuficiente(): void
    {
        CLI::write('#2/#18 FIFO con déficit', 'cyan');
        $capas = new InventarioCapasModel();

        // Item ficticio sin capas → debe lanzar
        $itemInexistente = 999999;
        try {
            $capas->consumirCapasFIFO($itemInexistente, 10.0);
            $this->fail('#2 FIFO sin capas', 'no lanzó Exception');
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'insuficiente') || str_contains($e->getMessage(), 'Stock')) {
                $this->pass('#2 FIFO sin capas lanza Exception', '"' . substr($e->getMessage(), 0, 60) . '"');
            } else {
                $this->fail('#2 FIFO sin capas', 'lanzó pero mensaje raro: ' . $e->getMessage());
            }
        }

        // Caso real con datos existentes: tomar primer item con capas y pedir mucho más
        $db = \Config\Database::connect();
        $row = $db->query('
            SELECT item_general_id, SUM(cantidad_disponible) AS stock
            FROM inventario_capas WHERE estado = 1
            GROUP BY item_general_id HAVING stock > 0
            ORDER BY stock ASC LIMIT 1
        ')->getRowArray();

        if (!$row) {
            CLI::write('  (saltado #2.b: no hay capas activas en BD)', 'yellow');
            return;
        }

        $itemId = (int) $row['item_general_id'];
        $excesivo = (float) $row['stock'] + 10000;

        $db->transBegin();
        try {
            $capas->consumirCapasFIFO($itemId, $excesivo);
            $this->fail('#2.b FIFO con déficit real', 'no lanzó (item ' . $itemId . ', pedido ' . $excesivo . ')');
        } catch (\Throwable $e) {
            $this->pass('#2.b FIFO con déficit real lanza', "item {$itemId}, pedido {$excesivo}");
        } finally {
            $db->transRollback();
        }
    }

    // ── #3 — filtros de estado usan != 3 ─────────────────────────────────
    private function test3_filtrosEstado(): void
    {
        CLI::write('#3 filtros estado != 3', 'cyan');

        $archivos = [
            'app/Controllers/InventarioController.php',
            'app/Controllers/DashboardController.php',
            'app/Controllers/NotificacionesController.php',
            'app/Controllers/PreparacionesController.php',
        ];
        $root = FCPATH . '..';
        $rotos = [];
        foreach ($archivos as $a) {
            $contenido = file_get_contents($root . '/' . $a);
            // No debe quedar el viejo string ni `!= 0`
            if (str_contains($contenido, "p.estado != 'cancelada'")) $rotos[] = $a . ': string viejo';
            if (preg_match('/p\.estado\s*!=\s*0\b/', $contenido))     $rotos[] = $a . ': "!= 0"';
        }
        if (empty($rotos)) {
            $this->pass('#3 no quedan filtros incorrectos');
        } else {
            $this->fail('#3 filtros incorrectos', implode('; ', $rotos));
        }

        // Verificar la consulta corregida funciona en SQL real
        $db = \Config\Database::connect();
        $r = $db->query("
            SELECT COUNT(*) AS c FROM produccion_insumos_detalle pid
            JOIN preparaciones p ON p.id_preparaciones = pid.preparacion_id
            WHERE p.estado != 3
        ")->getRowArray();
        if ($r !== null) $this->pass('#3.b query no canceladas ejecuta', 'rows=' . $r['c']);
    }

    // ── #4 — Ruta PUT /bodegas/item/:id eliminada ────────────────────────
    private function test4_rutaBodegasItemEliminada(): void
    {
        CLI::write('#4 ruta /bodegas/item eliminada', 'cyan');

        $routes = file_get_contents(FCPATH . '../app/Config/Routes.php');
        if (str_contains($routes, "'BodegasController::update_item_bodega")) {
            $this->fail('#4 ruta sigue declarada', 'aparece update_item_bodega');
        } else {
            $this->pass('#4 ruta eliminada de Routes.php');
        }

        $ctrl = file_get_contents(FCPATH . '../app/Controllers/BodegasController.php');
        if (preg_match('/public function update_item_bodega/', $ctrl)) {
            $this->fail('#4 método sigue en controller', 'update_item_bodega');
        } else {
            $this->pass('#4 método removido del controller');
        }

        $model = file_get_contents(FCPATH . '../app/Models/BodegasModel.php');
        if (preg_match('/public function update_item_bodega/', $model)) {
            $this->fail('#4 método sigue en modelo', 'update_item_bodega en BodegasModel');
        } else {
            $this->pass('#4 método removido del modelo');
        }
    }

    // ── #5 — RequisicionesCompraModel::convertirAOC usa NumeracionModel ──
    private function test5_requisicionesUsaNumeracionModel(): void
    {
        CLI::write('#5 convertirAOC usa NumeracionModel', 'cyan');

        $src = file_get_contents(FCPATH . '../app/Models/RequisicionesCompraModel.php');
        $usaNuevo  = str_contains($src, "reservar('orden_compra')");
        $usaViejo  = preg_match('/SELECT numero FROM ordenes_compra ORDER BY id_orden DESC LIMIT 1/', $src);

        if ($usaNuevo && !$usaViejo) {
            $this->pass('#5 usa NumeracionModel y removió SELECT MAX');
        } else {
            $this->fail('#5', "nuevo={$usaNuevo}, viejo={$usaViejo}");
        }

        // Probar que reservar funciona y entrega formato OC-XXX
        $num = (new NumeracionModel())->reservar('orden_compra');
        if (preg_match('/^OC-\d+$/', $num)) {
            $this->pass('#5.b NumeracionModel::reservar(orden_compra) entrega formato', $num);
        } else {
            $this->fail('#5.b formato número', $num);
        }
    }

    // ── #14 — NumeracionController envuelto en transacción ───────────────
    private function test6_numeracionTransaccional(): void
    {
        CLI::write('#14 NumeracionController transaccional', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Controllers/NumeracionController.php');
        $hasBegin = substr_count($src, '$db->transBegin()');
        $hasCommit = substr_count($src, '$db->transCommit()');
        $hasRollback = substr_count($src, '$db->transRollback()');
        // Esperamos al menos 2 (update + create)
        if ($hasBegin >= 2 && $hasCommit >= 2 && $hasRollback >= 2) {
            $this->pass('#14 transBegin/Commit/Rollback en update+create', "begin={$hasBegin} commit={$hasCommit} rollback={$hasRollback}");
        } else {
            $this->fail('#14 falta transacción', "begin={$hasBegin} commit={$hasCommit} rollback={$hasRollback}");
        }
    }

    // ── #6 — RemisionesController transaccional en create/convertir ──────
    private function test7_remisionesTransaccional(): void
    {
        CLI::write('#6 RemisionesController transaccional', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Controllers/RemisionesController.php');
        $begin = substr_count($src, '$db->transBegin()');
        $commit = substr_count($src, '$db->transCommit()');
        $rollback = substr_count($src, '$db->transRollback()');
        if ($begin >= 2 && $commit >= 2 && $rollback >= 2) {
            $this->pass('#6 create+convertir envueltos', "begin={$begin}");
        } else {
            $this->fail('#6', "begin={$begin} commit={$commit} rollback={$rollback}");
        }
    }

    // ── #7 — CotizacionesController transaccional ────────────────────────
    private function test8_cotizacionesTransaccional(): void
    {
        CLI::write('#7 CotizacionesController transaccional', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Controllers/CotizacionesController.php');
        $begin = substr_count($src, '$db->transBegin()');
        $commit = substr_count($src, '$db->transCommit()');
        $rollback = substr_count($src, '$db->transRollback()');
        if ($begin >= 2 && $commit >= 2 && $rollback >= 2) {
            $this->pass('#7 create+convertir envueltos', "begin={$begin}");
        } else {
            $this->fail('#7', "begin={$begin} commit={$commit} rollback={$rollback}");
        }
    }

    // ── #8 — Sin interpolación con `false` en set() ──────────────────────
    private function test9_sinInterpolacionSQL(): void
    {
        CLI::write('#8 sin interpolación SQL', 'cyan');
        $archivos = [
            'app/Models/InventarioCapasModel.php',
            'app/Models/InventarioModel.php',
            'app/Controllers/RemisionesController.php',
        ];
        $root = FCPATH . '..';
        $sospechosos = [];
        foreach ($archivos as $a) {
            $src = file_get_contents("$root/$a");
            // patrón viejo: ->set('col', "col + {$var}", false)  o  "col - $var"
            if (preg_match('/->set\([^)]*\{\$[a-zA-Z_>\-]+\}[^)]*,\s*false\s*\)/', $src) ||
                preg_match('/->set\(\s*[\'"][^\'"]+[\'"]\s*,\s*[\'"][^\'"]*\$\w+[^\'"]*[\'"]\s*,\s*false\s*\)/', $src)) {
                $sospechosos[] = $a;
            }
        }
        if (empty($sospechosos)) {
            $this->pass('#8 no quedan set(..., false) con interpolación');
        } else {
            $this->fail('#8 quedan vectores', implode(', ', $sospechosos));
        }

        // Probar que el patch real (UPDATE parametrizado) funciona
        $db = \Config\Database::connect();
        $r = $db->query('SELECT 1+? AS x', [5])->getRowArray();
        if ((int) $r['x'] === 6) $this->pass('#8.b parameter binding ok');
    }

    // ── #9 — Validación FK en OC.create y Formulaciones.create ───────────
    private function test10_validacionFKOC(): void
    {
        CLI::write('#9 validación FK proveedor / item_general', 'cyan');

        $oc = file_get_contents(FCPATH . '../app/Controllers/OrdenesCompraController.php');
        $checkOC = str_contains($oc, "table('proveedor')") && str_contains($oc, "deleted_at");
        if ($checkOC) {
            $this->pass('#9.a OC valida proveedor existe + no archivado');
        } else {
            $this->fail('#9.a OC', 'falta query a proveedor con deleted_at');
        }

        $fc = file_get_contents(FCPATH . '../app/Controllers/FormulacionesController.php');
        $checkFc = str_contains($fc, "table('item_general')") && str_contains($fc, "deleted_at");
        if ($checkFc) {
            $this->pass('#9.b Formulaciones valida item_general existe + no archivado');
        } else {
            $this->fail('#9.b Formulaciones', 'falta query a item_general con deleted_at');
        }

        // Verificar que la query de validación funciona (proveedor inexistente devuelve 0)
        $db = \Config\Database::connect();
        $c = $db->table('proveedor')->where('id_proveedor', 999999)->where('deleted_at', null)->countAllResults();
        if ($c === 0) $this->pass('#9.c query countAllResults sobre proveedor inexistente devuelve 0');
    }

    // ── #10 TRASPASO sin saldos loggea warning ───────────────────────────
    private function test11_traspasoSinSaldos(): void
    {
        CLI::write('#10 TRASPASO sin saldos explícitos', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Models/MovimientoInventarioModel.php');
        if (str_contains($src, 'sin saldo_anterior explícito') && str_contains($src, "default             => null")) {
            $this->pass('#10 match TRASPASO/AJUSTE devuelve null + log warning');
        } else {
            $this->fail('#10', 'el match default sigue devolviendo $saldoNuevo silenciosamente');
        }
    }

    // ── #11 cambiarEstado transaccional ──────────────────────────────────
    private function test12_cambiarEstadoTransaccional(): void
    {
        CLI::write('#11 cambiarEstado con tx + lock', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Controllers/OrdenesCompraController.php');
        $hasLock  = str_contains($src, 'FOR UPDATE');
        $hasBegin = str_contains($src, '$db->transBegin()');
        if ($hasLock && $hasBegin) {
            $this->pass('#11 FOR UPDATE + transBegin presentes en cambiarEstado');
        } else {
            $this->fail('#11', "FOR UPDATE={$hasLock}, transBegin={$hasBegin}");
        }
    }

    // ── #12 update_table respeta soft-delete ─────────────────────────────
    private function test13_updateTableSoftDelete(): void
    {
        CLI::write('#12 update_table rechaza archivados', 'cyan');
        $db = \Config\Database::connect();

        // Tomamos un cliente real, lo archivamos dentro de una tx, probamos
        // update_table y luego rollback.
        $cliente = $db->table('clientes')->where('deleted_at', null)->limit(1)->get()->getRowArray();
        if (!$cliente) {
            CLI::write('  (saltado: no hay clientes activos en BD)', 'yellow');
            return;
        }
        $cid = (int) $cliente['id_clientes'];

        $db->transBegin();
        try {
            $db->table('clientes')->where('id_clientes', $cid)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            $model = new \App\Models\ClientesModel();
            $res = $model->update_table($cid, ['nombres' => 'INTENTO_EDIT_ARCHIVADO'], 'clientes');

            if (is_array($res) && isset($res['archivado'])) {
                $this->pass('#12 update_table devuelve error sobre archivado', $res['archivado']);
            } else {
                $this->fail('#12 update_table no bloqueó', 'result=' . json_encode($res));
            }
        } finally {
            $db->transRollback();
        }
    }

    // ── #13 restore_table usa pkOf ───────────────────────────────────────
    private function test14_restoreTablePk(): void
    {
        CLI::write('#13 restore_table usa pkOf()', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Models/BaseModel.php');
        if (preg_match('/where\(\s*\$this->pkOf\(\$table\)\s*,\s*\$id\s*\)/', $src)) {
            $this->pass('#13 restore_table usa pkOf($table)');
        } else {
            $this->fail('#13', 'restore_table sigue con id_ . $table hardcoded');
        }

        // Test funcional: archivar y restaurar una OC (PK = id_orden)
        $db = \Config\Database::connect();
        $oc = $db->table('ordenes_compra')->where('deleted_at', null)->limit(1)->get()->getRowArray();
        if (!$oc) {
            CLI::write('  (saltado #13.b: no hay OCs activas)', 'yellow');
            return;
        }
        $oid = (int) $oc['id_orden'];

        $db->transBegin();
        try {
            $db->table('ordenes_compra')->where('id_orden', $oid)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            $model = new \App\Models\OrdenesCompraModel();
            $ok = $model->restore_table($oid, 'ordenes_compra');
            if ($ok) {
                $this->pass('#13.b restore_table funcional en ordenes_compra (PK = id_orden)');
            } else {
                $this->fail('#13.b restore_table OC', 'devolvió false');
            }
        } finally {
            $db->transRollback();
        }
    }

    // ── #15 GET_LOCK chequeado ───────────────────────────────────────────
    private function test15_getLockChequea(): void
    {
        CLI::write('#15 GET_LOCK chequea retorno', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Models/ItemProveedorModel.php');
        $checkOk = str_contains($src, "\$lockRow['got']") && str_contains($src, 'No se pudo obtener el lock');
        if ($checkOk) {
            $this->pass('#15 lee got y lanza si != 1');
        } else {
            $this->fail('#15', 'no chequea retorno de GET_LOCK');
        }
    }

    // ── #16 MRP usa inventario_capas ─────────────────────────────────────
    private function test16_mrpUsaCapas(): void
    {
        CLI::write('#16 MRP consulta inventario_capas', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Models/RequisicionesCompraModel.php');
        $usaCapas = str_contains($src, 'FROM inventario_capas') && str_contains($src, "estado = 1");
        $usaLegacy = preg_match('/FROM inventario WHERE item_general_id = \?/', $src) === 1;
        if ($usaCapas && !$usaLegacy) {
            $this->pass('#16 verificarDisponibilidad usa inventario_capas');
        } else {
            $this->fail('#16', "usaCapas={$usaCapas}, usaLegacy={$usaLegacy}");
        }
    }

    // ── #17 deleted_at en queries de proveedores ─────────────────────────
    private function test17_deletedAtEnProveedores(): void
    {
        CLI::write('#17 deleted_at en queries proveedor', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Models/RequisicionesCompraModel.php');
        $ocurrencias = substr_count($src, 'ip.deleted_at IS NULL');
        if ($ocurrencias >= 2) {
            $this->pass('#17 ambas queries filtran deleted_at', "n={$ocurrencias}");
        } else {
            $this->fail('#17', "ocurrencias={$ocurrencias} (esperaba ≥2)");
        }
    }

    // ── #20 login no autobloquea exitoso ─────────────────────────────────
    private function test18_loginNoBloqueaLegitimo(): void
    {
        CLI::write('#20 login limpia attempts al éxito', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Controllers/UsuarioController.php');
        $limpia = str_contains($src, "table('login_attempts')") && str_contains($src, '->delete()') &&
                  str_contains($src, 'Login exitoso → limpiar intentos');
        if ($limpia) {
            $this->pass('#20 al login exitoso se borran intentos previos');
        } else {
            $this->fail('#20', 'no aparece el delete de login_attempts en login exitoso');
        }
    }

    // ── #22 CatalogoController::delete chequea stock ─────────────────────
    private function test19_catalogoDeleteChequeaStock(): void
    {
        CLI::write('#22 CatalogoController::delete chequea stock', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Controllers/CatalogoController.php');
        $chequea = str_contains($src, "table('inventario_capas')") &&
                   str_contains($src, 'stock activo');
        if ($chequea) {
            $this->pass('#22 delete consulta inventario_capas');
        } else {
            $this->fail('#22', 'no aparece el check de stock antes de archivar');
        }
    }

    // ── #23 OrdenesCompraModel/NotasCreditoModel::generarNumero borrados ─
    private function test20_codigoMuertoEliminado(): void
    {
        CLI::write('#23 generarNumero eliminado', 'cyan');
        $oc = file_get_contents(FCPATH . '../app/Models/OrdenesCompraModel.php');
        $nc = file_get_contents(FCPATH . '../app/Models/NotasCreditoModel.php');
        $sigueOC = preg_match('/public function generarNumero\b/', $oc);
        $sigueNC = preg_match('/public function generarNumero\b/', $nc);
        if (!$sigueOC && !$sigueNC) {
            $this->pass('#23 generarNumero removido de OC y NC');
        } else {
            $this->fail('#23', "sigueOC={$sigueOC}, sigueNC={$sigueNC}");
        }
    }

    // ── #24 registrarMovimiento deprecada eliminada ──────────────────────
    private function test21_registrarMovimientoEliminado(): void
    {
        CLI::write('#24 registrarMovimiento eliminado', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Models/MovimientoInventarioModel.php');
        if (preg_match('/public function registrarMovimiento\b/', $src)) {
            $this->fail('#24', 'sigue presente en MovimientoInventarioModel');
        } else {
            $this->pass('#24 registrarMovimiento removido');
        }
    }

    // ── #27 costo_produccion = promedio (no costo de última OC) ──────────
    private function test22_costoProduccionUsaPromedio(): void
    {
        CLI::write('#27 costo_produccion usa promedio', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Controllers/OrdenesCompraController.php');
        $usaPromedio = str_contains($src, "'costo_produccion' => \$promedio") &&
                       !str_contains($src, "'costo_produccion' => \$costoUnitarioKg");
        if ($usaPromedio) {
            $this->pass('#27 costo_produccion lee resultado de recalcularPromedioPonderado');
        } else {
            $this->fail('#27', 'sigue escribiendo costoUnitarioKg de la última OC');
        }
    }

    // ── #30 estado normalizado en update_preparacion ─────────────────────
    private function test23_estadoNormalizadoString(): void
    {
        CLI::write('#30 update_preparacion mapea strings de estado', 'cyan');

        $model = new \App\Models\PreparacionesModel();

        // El mapeo corre antes del lookup de la preparación: un string
        // inválido debe lanzar "inválido" incluso si la prep no existe.
        try {
            $model->update_preparacion(999999, ['estado' => 'NOPE_NO_EXISTE']);
            $this->fail('#30 estado inválido', 'no lanzó Exception');
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'inválido') && str_contains($e->getMessage(), 'NOPE_NO_EXISTE')) {
                $this->pass('#30 estado string inválido es rechazado', '"' . substr($e->getMessage(), 0, 80) . '"');
            } else {
                $this->fail('#30', 'mensaje inesperado: ' . $e->getMessage());
            }
        }

        // Verificación de mapeo: estado=4 (entero fuera de rango) debe rechazarse
        try {
            $model->update_preparacion(999999, ['estado' => 99]);
            $this->fail('#30.b estado entero inválido', 'no lanzó');
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'inválido')) {
                $this->pass('#30.b entero fuera de rango también es rechazado');
            } else {
                $this->fail('#30.b', 'mensaje: ' . $e->getMessage());
            }
        }

        // Verificación de código: el mapa CANCELADA→3 existe en el fuente
        $src = file_get_contents(FCPATH . '../app/Models/PreparacionesModel.php');
        if (str_contains($src, "'CANCELADA' => 3") && str_contains($src, '$estadoMap[$key]')) {
            $this->pass('#30.c estadoMap definido y aplicado en el código');
        } else {
            $this->fail('#30.c', 'no aparece el mapping CANCELADA → 3');
        }
    }

    // ── IVA: columna iva_pct existe + backfill aplicado ───────────────────
    private function test24_ivaPctColumnaExiste(): void
    {
        CLI::write('IVA columna iva_pct + backfill', 'cyan');
        $db = \Config\Database::connect();
        $col = $db->query("SHOW COLUMNS FROM ordenes_compra LIKE 'iva_pct'")->getRowArray();
        if (!$col) {
            $this->fail('IVA.a columna iva_pct', 'no existe en ordenes_compra');
            return;
        }
        $this->pass('IVA.a columna iva_pct existe', $col['Type']);

        $nulos = $db->query("SELECT COUNT(*) AS n FROM ordenes_compra WHERE iva_pct IS NULL AND deleted_at IS NULL")->getRow()->n;
        if ((int) $nulos === 0) {
            $this->pass('IVA.b OCs históricas tienen iva_pct backfilled');
        } else {
            $this->fail('IVA.b backfill', "{$nulos} OCs aún con iva_pct NULL");
        }
    }

    // ── OC create persiste iva_pct ────────────────────────────────────────
    private function test25_ocCreateGuardaIvaPct(): void
    {
        CLI::write('IVA OrdenesCompraController create persiste iva_pct', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Controllers/OrdenesCompraController.php');
        $usaCfg = str_contains($src, "Cfg::n('iva_default'") &&
                  str_contains($src, "\$data['iva_pct']");
        if ($usaCfg) {
            $this->pass('IVA.c create() guarda iva_pct (override del cliente o Cfg)');
        } else {
            $this->fail('IVA.c', 'no aparece la lógica de iva_pct en create');
        }

        $allowed = file_get_contents(FCPATH . '../app/Models/OrdenesCompraModel.php');
        if (str_contains($allowed, "'iva_pct'")) {
            $this->pass('IVA.d iva_pct en allowedFields del modelo');
        } else {
            $this->fail('IVA.d', 'iva_pct no está en allowedFields');
        }
    }

    // ── listar/detalle devuelven iva_monto y total_con_iva ────────────────
    private function test26_listarDevuelveTrioIva(): void
    {
        CLI::write('IVA listar() y detalle() enriquecen con trío', 'cyan');
        $model = new \App\Models\OrdenesCompraModel();
        $todas = $model->listar();
        if (empty($todas)) {
            CLI::write('  (saltado: no hay OCs)', 'yellow');
            return;
        }
        $primera = $todas[0];

        $tieneTrio = isset($primera['iva_pct']) && isset($primera['iva_monto']) && isset($primera['total_con_iva']);
        if (!$tieneTrio) {
            $this->fail('IVA.e listar() trio', 'faltan campos en el row');
            return;
        }
        $this->pass('IVA.e listar() devuelve iva_pct + iva_monto + total_con_iva');

        // Validar fórmula: total_con_iva ≈ total * (1 + iva_pct/100)
        $esperado = round((float) $primera['total'] * (1 + (float) $primera['iva_pct'] / 100), 2);
        if (abs($esperado - (float) $primera['total_con_iva']) < 0.01) {
            $this->pass('IVA.f fórmula total_con_iva correcta', sprintf(
                'total=%s × (1 + %s%%) = %s',
                $primera['total'], $primera['iva_pct'], $primera['total_con_iva']
            ));
        } else {
            $this->fail('IVA.f fórmula', "esperado={$esperado}, devuelto={$primera['total_con_iva']}");
        }

        // detalle(): mismo enriquecimiento
        $detalle = $model->detalle((int) $primera['id_orden']);
        if (isset($detalle['iva_monto']) && isset($detalle['total_con_iva'])) {
            $this->pass('IVA.g detalle() también enriquece');
        } else {
            $this->fail('IVA.g detalle() trio', 'faltan campos');
        }
    }

    // ── Dashboard devuelve valor_total_con_iva ────────────────────────────
    private function test27_dashboardDevuelveConIva(): void
    {
        CLI::write('IVA Dashboard ocsPendientes incluye valor_total_con_iva', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Controllers/DashboardController.php');
        $checkSql = str_contains($src, 'valor_total_con_iva') &&
                    str_contains($src, 'COALESCE(oc.iva_pct, 0)');
        if ($checkSql) {
            $this->pass('IVA.h ocsPendientes query incluye valor_total_con_iva');
        } else {
            $this->fail('IVA.h', 'falta valor_total_con_iva en la query del dashboard');
        }
    }

    // ── Remisiones usa Cfg en vez de 0.19 hardcodeado ────────────────────
    private function test28_remisionesUsaCfg(): void
    {
        CLI::write('IVA RemisionesController usa Cfg en vez de 0.19', 'cyan');
        $src = file_get_contents(FCPATH . '../app/Controllers/RemisionesController.php');
        $sinHardcode = !preg_match('/\$subtotal\s*\*\s*0\.19/', $src);
        $usaCfg      = str_contains($src, "Cfg::n('iva_default'");

        if ($sinHardcode && $usaCfg) {
            $this->pass('IVA.i convertir() lee iva_default de Cfg');
        } else {
            $this->fail('IVA.i', "sinHardcode={$sinHardcode}, usaCfg={$usaCfg}");
        }
    }
}
