# Auditoría backend — 2026-06-12

Sesión de auditoría + fixes, validada en Docker (`php spark validar:fixes` 53/53, `php -l` en todos los
archivos tocados, smoke test en vivo de los 219 endpoints, tests de modelo/Login/RemisionesStock).

## ✅ Fixes aplicados (validados)

### Endpoints rotos (daban 500 en vivo) — CORREGIDOS
| Endpoint | Causa | Fix |
|---|---|---|
| `GET cotizaciones/:id/detalle` | `findAll()` aplicaba soft-delete a `cotizaciones_detalle` (sin `deleted_at`) | `BaseModel::get_all/get` usan `withDeleted()` |
| `GET facturas/:id/detalle` | idem con `facturas_detalle` | idem |
| `GET costos_indirectos/item/:id` | tabla `costos_indirectos_item` no existía | migración `2026-06-12-000001` la crea |

### Bugs / seguridad / integridad
- **`OrdenesCompraController::update`**: whitelist de campos editables — `estado`/`numero`/`total` ya no
  por PUT (evita saltar a "Recibida" sin generar capas, o folios/total corruptos). *(verificado)*
- **`PagosClienteController` create/update**: lock `SELECT … FOR UPDATE` de la factura (race de sobrepago) +
  recálculo de la factura **original** al reasignar un pago + rechaza factura Anulada. *(verificado)*
- **`NotasCreditoController::create`**: lock `FOR UPDATE` (race de NC que excede saldo) + rechaza Anulada.
- **`RemisionesController`**: `update()` con whitelist (no marcar "Despachada" sin descontar stock) +
  `cambiarEstado()` **atómico** con lock `FOR UPDATE` (descuento de stock y cambio de estado en una sola
  transacción; `descontarStockDespacho` ya no abre su propia transacción → 1 nivel). *(RemisionesStockTest 3/3)*
- **`InventarioModel::traspaso` CAPAS-AWARE**: mueve las capas de costo origen→destino (split parcial,
  preservando costo/lote/proveedor). Antes solo tocaba la tabla legacy → stock-por-bodega y FIFO mal.
- **`RequisicionesCompraModel::convertirAOC`**: honra el precio APROBADO de la requisición, no el actual.
- **`UsuarioController::actualizarPerfil`**: re-emite el JWT con `token_version` (antes expulsaba la sesión).
- **`ConfiguracionController::bulkUpdate`**: usaba `set()` (CI4 Model::set — no persiste) → `guardar()`.
- **`NumeracionController::update`**: valida que `proximo_numero` no retroceda (folios fiscales duplicados).
- **`CostosIndirectosController`**: valida `valor_mensual` numérico ≥ 0 (no corrompe el `SUM`).
- **`EmpresaController::update`**: quita `logo_path` de los campos editables (path traversal).
- **`ItemModel`**: `ph` ya no se trunca a 1 carácter; `tipo` se mapea en update (MP no se vuelve producto).
- **`Formatter::parseCOP`**: delega en `fromCOP` (no corrompe importes numéricos).
- **`CorsFilter`**: fail-closed (no cae a `*`) + lee `getenv()`.
- **`RbacFilter`**: whitelist por límite de segmento (no `str_contains` — cierra bypass del visor).
- **`GestionesCobroController`**: `in_array(..., true)`.
- **`PreparacionesController`**: umbral stock crítico desde `Cfg::n()` + `floor()`.
- **`CotizacionesController::create`**: recalcula subtotal/total en servidor (redondeo a pesos enteros,
  igual que el frontend) — no confía en los montos del cliente.

### Infra
- **BD de tests separada**: `phpunit.xml.dist` → `gestorpincadb_test` (copia). Los tests ya NO corrompen
  la BD real. Instrucciones para recrear la copia están en el propio XML.
- **Migración `2026-06-12-000001_CreateCostosIndirectosItem`**: crea la tabla faltante (idempotente).

## ❌ Descartados como falsos positivos
- `BaseModel` mass assignment → ya endurecido en 2026-05-25 (lanza RuntimeException).
- `BodegasModel::create_item_bodega` y `create_preparaciones_lote` → código muerto (sin ruta/caller).

## 🟡 Lo que falta (backend)
- **`ajusteManual`** deja stale la tabla legacy `inventario` (cosmético; capas es la fuente de verdad).
- **`ItemController::create`** inserta inventario saltando capas → path **legacy** (usar `CatalogoController`).
- **IDOR** en lecturas/mutaciones de Cotizaciones/Remisiones/Instalaciones/Cartera (decisión de negocio:
  hoy RBAC es por módulo; no hay scope por cliente/empresa).
- **Unicidad de NIT** en Proveedor (tiene aristas: ya hay 1 duplicado + soft-delete; requiere validación custom).
- **Landmines de rutas** (no afectan la UI hoy): `GET ordenes_compra/:id` (501, no usado),
  `PUT bodegas/item/:id` (deshabilitada, hook sin uso), `DELETE preparaciones/:id` (no existe — se cancela
  con estado=3).
- **Duplicación** de mapeo `item_general` (ItemModel vs CatalogoModel ×4) y de stock crítico
  (Dashboard vs Notificaciones) — refactors de alto valor.
- **Migraciones `down()` rotas** (`DROP INDEX IF EXISTS`, `DropTamboresTable`) → impiden `migrate:refresh`.
  Arreglarlas permitiría usar `$refresh=true` en tests con migraciones limpias.

> Detalle completo (con frontend) en `../AUDITORIA_2026-06-12.md` (raíz del monorepo).
