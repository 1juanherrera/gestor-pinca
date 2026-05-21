# CLAUDE.md

> **Última actualización**: 2026-05-20.
> Backend en estado funcional con seguridad activa (JWT global + RBAC + rol superadmin) + análisis profundo aplicado (23 fixes). Ver §"Sesión 2026-05-20 — Costos de Producción + Salud + Superadmin + Backup" para el snapshot más reciente.

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

**Start the full stack (app + MySQL + phpMyAdmin):**
```bash
docker-compose up -d
```
- App: `http://localhost:8080`
- phpMyAdmin: `http://localhost:8081`
- DB: MySQL 8.0, database `gestorpincadb`, user `user`, password `password`

**Run tests:**
```bash
composer test
# or directly:
vendor/bin/phpunit
```

**CodeIgniter Spark CLI (run inside container or with PHP 8.1+):**
```bash
php spark migrate        # run migrations
php spark db:seed        # seed database
php spark serve          # dev server (port 8080)
```

**Install dependencies:**
```bash
composer install
```

## Architecture Overview

PINCA is a **manufacturing and procurement management REST API** built on CodeIgniter 4. It has no views — all responses are JSON. The frontend is a separate project.

### Request Lifecycle

```
HTTP Request → CorsFilter (all routes) → [JwtFilter (protected routes)] → Controller → Model → DB
```

- **CORS**: `CorsFilter` applied via aliases in `app/Config/Filters.php`. Origen restringido a `http://localhost:5173` desde `.env`.
- **JWT Auth**: **Aplicado globalmente** en `app/Config/Filters.php` como `'jwt' => ['except' => ['api/login', 'api/crear']]`. TODAS las rutas requieren `Authorization: Bearer <token>` excepto login y registro. Tokens expiran en 8 horas. Secret leído de `TOKEN_SECRET` en `.env` (64 chars hex). Mensaje de error: `{"ok": false, "msg": "Token no proporcionado|expirado|inválido"}`.
- **RBAC**: Tabla `permisos_rol_modulo` controla acceso por módulo. Roles: `admin` (15 módulos), `operador` (11), `visor` (9). Frontend filtra el sidebar; backend valida en endpoints sensibles via `rol` en el JWT.
- All routes are prefixed with `/api` (see `app/Config/Routes.php`).

### Controllers (32 total)

All controllers live in `app/Controllers/`. They either extend `BaseController` or CodeIgniter's `ResourceController`. Controllers never return HTML — they use `$this->response->setJSON(...)` or `$this->respond(...)`.

| Controller | Domain |
|---|---|
| `UsuarioController` | Login endpoint, JWT generation |
| `CatalogoController` | **Maestro de ítems (Catálogo)**: list/detail/CRUD of item_general with stock totals + proveedores |
| `ItemController` | Products/materials with cost data via JOINs (legacy — use CatalogoController for new features) |
| `FacturasController` | Sales invoices with state management |
| `CotizacionesController` | Sales quotations with state management |
| `RemisionesController` | Delivery notes / dispatch orders |
| `OrdenesCompraController` | Purchase orders with state management |
| `InventarioController` | Stock queries by warehouse |
| `BodegasController` | Warehouse (bodega) management |
| `CarteraController` | Receivables / aging analysis |
| `PagosClienteController` | Client payment registration |
| `NotasCreditoController` | Credit notes |
| `GestionesCobroController` | Collection management |
| `FormulacionesController` | Paint formulations (recipes) |
| `PreparacionesController` | Production preparation orders |
| `RequisicionesCompraController` | Purchase requisitions + MRP availability check |
| `TamborController` | Drum/container management |
| `ClientesController` | Client CRUD |
| `ProveedorController` | Supplier CRUD |
| `ItemProveedorController` | Item-supplier price mapping (auto-links item_general on save) |
| `CostosItemController` | Item cost tracking |
| `CostosIndirectosController` | Indirect cost management |
| `MovimientoInventarioController` | Inventory movement log |
| `ComparadorController` | Price comparison tool |
| `CategoriaController` | Product categories |
| `UnidadController` | Units of measure |
| `EmpresaController` | Company profile |
| `InstalacionesController` | Installations / locations |
| `CapasInventarioController` | Cost layers (capas): stock by item, bodegas with layers, consumption history |
| `SincronizacionController` | **(2026-05-13)** Auditoría catálogo↔proveedores: stats, maestro, pendientes, duplicados, huérfanos, merge |
| `PermisosController` | **(2026-05-12)** RBAC: lista/aplica permisos por rol, listado de usuarios, cambio de rol |

### Models (31 total)

All models extend `BaseModel` (`app/Models/BaseModel.php`) which extends CI4's `Model`. `BaseModel` provides generic helpers:
- `get_all()` — fetch all records
- `create_table($data)` — insert
- `update_table($id, $data)` — update by primary key
- `delete_table($id)` — delete by primary key

Primary key naming convention: `id_[tablename]` (e.g., `id_item_general`, `id_usuarios`, `id_facturas`).

Models use `allowedFields` for mass assignment protection. Complex queries (JOINs, aggregates) are implemented as custom methods directly on the model.

### Database Schema Patterns

- Detail tables for line items: `detalle_facturas`, `remisiones_detalle`, `ordenes_compra_detalle`
- Cost tracking: `costos_item`, `item_proveedor`, `costos_indirectos`
- State fields (`estado`) on documents: controls workflow transitions (cotizaciones, facturas, ordenes_compra, remisiones)
- FK naming: `cliente_id`, `proveedor_id`, `id_item_general`, etc.
- Migrations live in `app/Database/Migrations/`

**Applied migrations:**
1. `2026-04-17-000001_CreateTamboresTable` — tambores table
2. `2026-04-21-000001_CreateRequisicionesCompraTable` — requisiciones_compra table
3. `2026-04-21-000002_AddUnidadBaseAndItemProveedorCompra` — adds KILO to unidad table; adds `unidad_compra_id` (FK→unidad) and `factor_conversion DECIMAL(15,6)` to `item_proveedor`
4. `2026-04-22-000001_MergeUnidadEmpaqueIntoUnidadCompra` — consolida columnas de unidad de empaque
5. `2026-04-23-000001_CreateInventarioCapasSystem` — creates `inventario_capas` and `preparacion_consumo_capas` tables; migrates existing inventory saldos into legacy layers
6. `2026-04-24-000001_CreateProduccionInsumosDetalle` — snapshot de costo congelado por preparación
7. `2026-05-11-000001_AddRolToUsuarios` — agrega `rol ENUM('admin','operador','visor')` con default `'operador'` a `usuarios`
8. `2026-05-11-000002_CreatePermisosRolModulo` — tabla `permisos_rol_modulo (id, rol, modulo, activo)` con 15 módulos para admin, 11 operador, 9 visor
9. `2026-05-11-000003_CreateLoginAttempts` — tabla `login_attempts (id, ip_address, username_attempt, created_at)` para auditoría de intentos
10. `2026-05-13-000001_ExtendMovimientoInventario` — agrega 9 columnas a `movimiento_inventario` (item_general_id, bodega_id, referencia_id, costo_unitario, saldo_anterior, saldo_nuevo, responsable, metadata JSON, created_at), cambia `fecha_movimiento` a DATETIME, amplía `descripcion` a 255, agrega 5 índices

SQL dumps in `/initdb/` are auto-loaded by Docker on first run.

### Route Organization

Routes follow this pattern per domain:
1. Specific sub-resource routes come **before** generic `/:id` routes
2. RESTful verbs: GET (list/detail), POST (create), PUT (update), DELETE, PATCH (state changes)

Domain groups in `app/Config/Routes.php`: empresa, usuarios, items, instalaciones, bodegas, formulaciones, proveedores, item_proveedores, clientes, facturas, inventario, capas_inventario, costos_item, costos_indirectos, unidades, categorias, preparaciones, requisiciones, pagos_cliente, cartera, gestiones_cobro, notas_credito, cotizaciones, remisiones, comparador, movimientos_inventario, tambores, ordenes_compra.

### JWT Authentication

`app/Filters/JwtFilter.php` validates the `Authorization: Bearer` header on protected routes. On failure it returns 401.

## Key Models — Recent Changes

### ItemModel (`app/Models/ItemModel.php`)
- `buscarFuzzy(string $query, int $limit, array $tipos)` — fuzzy search combining multi-token LIKE + SOUNDEX. Optional `$tipos` array filters by `item_general.tipo` (1=Materia Prima, 2=Insumo, 0=Producto). Returns: `id_item_general`, `nombre`, `codigo`, `tipo`, `costo_unitario` (from costos_item JOIN), `total_proveedores`, `precio_min`, `precio_max`, `proveedores_lista` (GROUP_CONCAT: "NombreProv|precio;;;...").
- Route: `GET /api/item_general/buscar?q=texto&tipos=1,2&limit=10`

### ItemProveedorModel (`app/Models/ItemProveedorModel.php`)
- `allowedFields` includes: `unidad_compra_id`, `factor_conversion`
- `resolverItemGeneral(array &$data)` — called automatically on create/update. If `item_general_id` is missing: searches item_general by nombre (case-insensitive); if not found, creates new item_general with tipo derived from item_proveedor.tipo and `unidad_almacenaje_id = KILO`. Mutates `$data['item_general_id']` in place.
- `vincular(int $id, ?int $itemGeneralId, ?int $unidadCompraId, float $factorConversion)` — links item_proveedor to item_general with unit conversion data.
- `get_item_proveedores()` — JOINs proveedor + item_general + unidad (for unidad_compra_nombre).

### ItemProveedorController (`app/Controllers/ItemProveedorController.php`)
- `create()` and `update()` both call `$this->model->resolverItemGeneral($data)` before saving — item_general is always auto-created/linked.
- `vincular()` — extracts `unidad_compra_id` and `factor_conversion` from request body.

### RequisicionesCompraModel/Controller
- `verificarDisponibilidad()` — explodes formulation BOM, checks inventory per ingredient, returns deficit items with available suppliers.
- `crearRequisiciones()` — batch insert.
- `convertirAOC()` — groups by proveedor, creates one OC per supplier.

### InventarioCapasModel (`app/Models/InventarioCapasModel.php`)

Manages cost layers for inventory tracking by provider/lot/date. Each inventory entry (OC receipt) creates a separate layer preserving its origin cost.

**Key methods:**
- `crearCapa(array $data)` — creates a new cost layer on OC receipt (provider, cost, qty, lot, bodega, OC reference)
- `obtenerCapas(int $itemId, ?int $bodegaId, string $orden, ?int $proveedorId)` — returns active layers with provider/bodega details. Supports `proveedor_id` filter for directed stock queries
- `resumenStock(int $itemId)` — returns `stock_total` and `costo_promedio_ponderado`
- `consumirCapasFIFO(int $itemId, float $cantidad, ?int $bodegaId)` — FIFO from oldest layers first
- `consumirCapasPorProveedor(int $itemId, float $cantidad, int $proveedorId, ?int $bodegaId)` — **NEW**: FIFO restricted to a specific proveedor's layers only. Throws if stock is insufficient.
- `consumirCapasManual(array $seleccion)` — consumes specific amounts from specific layers: `[{capa_id, cantidad}]`
- `restaurarCapas(int $prepId)` — reverses consumption when production order is cancelled
- `registrarConsumos(int $prepId, array $consumos)` — writes to `preparacion_consumo_capas` table
- `recalcularPromedioPonderado(int $itemId)` — updates `costos_item.costo_unitario` with weighted average from active layers
- All consumo arrays include `proveedor_id` for traceability in `produccion_insumos_detalle`

**Tables:**
- `inventario_capas` — one row per cost layer: `id_capa`, `item_general_id`, `bodega_id`, `cantidad_original`, `cantidad_disponible`, `costo_unitario`, `proveedor_id`, `lote_proveedor`, `orden_compra_id`, `fecha_ingreso`, `estado` (activa/agotada)
- `preparacion_consumo_capas` — per-layer consumption detail: `capa_id`, `preparacion_id`, `cantidad_consumida`, `costo_unitario`, `costo_total`
- `produccion_insumos_detalle` — **NEW**: frozen cost snapshot per ingredient per production order: `preparacion_id`, `item_general_id`, `proveedor_id`, `bodega_id`, `cantidad`, `costo_unitario` (frozen), `subtotal`, `created_at`. Cost here NEVER changes even if supplier raises prices later.

### CapasInventarioController (`app/Controllers/CapasInventarioController.php`)

**Routes:**
- `GET /api/inventario/{id}/capas?bodega_id=X` — all active layers for an item, with provider/bodega details (includes `proveedor_id` per capa)
- `GET /api/inventario/capas/bodegas` — distinct bodegas with active layers
- `GET /api/inventario/capas/preparacion/{id}` — consumption history for a production order

### PreparacionesModel — Provider-Directed Stock Selection

`_ajustarInventarioPorPreparacion` supports three consumption modes per ingredient:
1. **MANUAL** (capas specified): `consumirCapasManual()` — exact capa_ids with quantities
2. **By proveedor** (`proveedor_id` in detalle): `consumirCapasPorProveedor()` — FIFO restricted to that supplier. Throws if stock insufficient (transaction rolls back all prior consumptions)
3. **FIFO global** (default): `consumirCapasFIFO()` with optional bodega filter

- All creates use `transBegin()`/`transCommit()`/`transRollback()` for proper atomic rollback on PHP exceptions (not just SQL errors)
- On consumption: writes frozen cost record to `produccion_insumos_detalle`
- On cancellation: calls `restaurarCapas()` + deletes `produccion_insumos_detalle` records for the prep
- Factor de conversión is applied at OC receipt time, NOT at production time — formulation quantities are always in base unit (kg)

### OrdenesCompraController — Layer Creation on Receipt

`recibirLinea` was modified to:
- Fetch `item_proveedor` data for `factor_conversion`
- Calculate `cantidadBase = cantidadRecibida × factorConversion` and `costoUnitarioKg = precio_unit / factorConversion`
- Create a cost layer via `crearCapa()` with provider, OC, unit conversion, lot info
- Call `recalcularPromedioPonderado()` to update `costos_item`

### SincronizacionModel + SincronizacionController (2026-05-13)

Centro de auditoría de la relación `item_general` ↔ `item_proveedor`. Endpoints:

| Verbo | Ruta | Descripción |
|-------|------|-------------|
| GET | `/api/sincronizacion/stats` | KPIs: total MP, con/sin proveedor, 1 vs 2+ proveedores, items pendientes, duplicados, **ahorro_potencial** (stock × (costo_actual − precio_min_kg)) |
| GET | `/api/sincronizacion/maestro?search=&cobertura=&tipo=` | Tabla MP con `precio_min_kg`, `precio_max_kg`, `spread_pct` calculados; subarray `proveedores` por item (sin N+1 — query secundaria agrupada) |
| GET | `/api/sincronizacion/pendientes` | `item_proveedor` con `item_general_id IS NULL` + top 3 sugerencias vía `ItemModel::buscarFuzzy` con score `similar_text` |
| GET | `/api/sincronizacion/duplicados?threshold=70` | Pares `tipo=1` similares por Levenshtein normalizado + bonus categoría compartida (+10). Threshold 50-100 |
| GET | `/api/sincronizacion/huerfanos` | MP sin proveedores activos + última compra (JOIN `ordenes_compra`) + stock residual |
| POST | `/api/sincronizacion/merge` | Body `{keep_id, remove_id}`. Traslada en transacción: `item_proveedor`, `item_general_formulaciones` (consolida cantidades si el ingrediente estaba duplicado), `costos_indirectos_item`, `inventario`, `inventario_capas`, `movimiento_inventario`, `produccion_insumos_detalle`. Elimina `costos_item` del remove. Marca el item removido con prefijo `[MERGED→keepId]`. Rechaza si stock activo > 0 o si los tipos no coinciden |

### MovimientoInventarioModel — Audit Log de Inventario (2026-05-13)

Reescrito para servir como audit log centralizado. Cada cambio de stock (recepción OC, traspaso, ajuste, producción) lo registra.

**Helper centralizado `registrar(array)`** — único punto de entrada estandarizado:
- Auto-calcula `saldo_anterior` desde el saldo actual (vía `inventario_capas`) si no se provee.
- Serializa `metadata` array → JSON con `JSON_UNESCAPED_UNICODE`.
- Constantes canónicas: `TIPO_ENTRADA|SALIDA|TRASPASO|AJUSTE`, `REF_OC|FACTURA_VENTA|REMISION|PRODUCCION|TRASPASO_BODEGA|AJUSTE_MANUAL|ANULACION`.
- `registrarReverso(array $original, string $motivo)` — atajo para anulaciones que invierte el tipo.

**Schema actualizado de `movimiento_inventario`** (15 columnas):
- `tipo_movimiento`, `cantidad` (siempre positiva), `fecha_movimiento` **DATETIME**, `descripcion` VARCHAR(255)
- `referencia_tipo`, `referencia_id` — origen del evento
- `item_general_id`, `bodega_id` — FK
- `costo_unitario`, `saldo_anterior`, `saldo_nuevo` DECIMAL(15,4) — snapshot
- `responsable` VARCHAR(100), `metadata` JSON, `created_at` DATETIME
- Índices: `idx_mov_item`, `idx_mov_bodega`, `idx_mov_ref (referencia_tipo, referencia_id)`, `idx_mov_fecha`, `idx_mov_tipo`

**Puntos instrumentados** (todas las mutaciones reales de stock):
- `OrdenesCompraController::recibirLinea` → ENTRADA con metadata `{numero_oc, proveedor_id, factor_conversion, precio_unit_compra, lote_proveedor, item_proveedor_nombre}`
- `InventarioModel::traspaso` → TRASPASO con metadata `{bodega_origen, bodega_destino, saldo_origen/destino_antes/despues}`
- `InventarioModel::removeFromBodega` → AJUSTE con metadata `{accion, bodega_nombre, cantidad_removida, motivo}`
- `PreparacionesModel::_ajustarInventarioPorPreparacion` → SALIDA (consumo) o ENTRADA (reintegro por cancelación) con metadata `{preparacion_id, multiplicador, subtotal}`

**NO instrumentados (por diseño)**: `FacturasController` (cobranza pura, no mueve stock) ni `RemisionesController::create` (su detalle es texto libre sin FK a `item_general` — gap del schema heredado).

### Sistema RBAC (2026-05-11)

- **`usuarios.rol`**: ENUM `('admin','operador','visor')` con default `'operador'`. Incluido en payload JWT.
- **`permisos_rol_modulo`**: tabla `(id, rol, modulo, activo)`. 15 módulos para admin, 11 operador, 9 visor. Módulos: `panel-principal, catalogo, inventario-global, formulaciones, produccion, rentabilidad, comercial, compras, cartera, clientes, proveedores, movimientos, pagos, tambores, prorrateo, roles, sincronizacion`.
- **`PermisosController`** expone: `GET /api/roles/permisos`, `GET /api/roles/permisos/:rol`, `PUT /api/roles/:rol/permisos`, `GET /api/roles/usuarios`, `PATCH /api/roles/usuarios/:id/rol`. Requiere `rol=admin` para mutaciones.
- **`login_attempts`** tabla creada para rate limiting (aún NO instrumentado en `UsuarioController::login` — pendiente).

### FormulacionesController — Per-ingredient Provider Options

- `GET /api/formulaciones/{id}/opciones-ingredientes` → `opciones_proveedor_ingrediente()` — returns per-ingredient supplier options with `precio_por_kg`, sorted by cheapest. Uses `FormulacionesModel::get_opciones_proveedor_formulacion()`
- `GET /api/formulaciones/{id}/proveedores` → `proveedores_formulacion()` — returns providers that cover the formulation's raw materials (used by global provider simulation)
- `GET /api/formulaciones/costos/{id}/proveedor/{provId}` → `calcular_costos_por_proveedor()` — simulates cost using one specific provider for ALL ingredients

## Unit of Measure Design

- `item_general.unidad_id` = sales/presentation unit (GALON, TAMBOR, CUÑETE — all in `unidad` table with `escala` = gallons factor)
- `item_general.unidad_almacenaje_id` = storage base unit (KILO for raw materials)
- `item_proveedor.unidad_compra_id` = unit the supplier sells in
- `item_proveedor.factor_conversion` = multiplier to convert purchase unit → base unit (e.g., 1 BULTO = 25 KG → factor=25)
- **Rule**: inventory always stored in the base unit. Conversion happens at OC receipt time.
- **Costing strategy**: Promedio Ponderado Móvil (moving weighted average) — implemented via `InventarioCapasModel::recalcularPromedioPonderado()` on OC receipt. Updates `costos_item.costo_unitario` from active cost layers.

## Protocolo de Gestión de Capas de Costo

Este protocolo describe el ciclo de vida completo de una unidad de materia prima desde su ingreso por OC hasta su consumo en producción, garantizando trazabilidad y costo histórico inmutable.

### 1. Ingreso — Conversión de UOM al recibir OC

Cuando `OrdenesCompraController::recibirLinea` procesa una línea de OC:

1. Recupera `factor_conversion` del registro `item_proveedor` correspondiente.
2. Calcula la **cantidad en unidad base**: `cantidadBase = cantidadRecibida × factorConversion` (e.g., 2 BULTOS × 25 = 50 kg).
3. Calcula el **costo unitario en unidad base**: `costoUnitarioBase = precioUnitario / factorConversion` (e.g., $50.000/BULTO ÷ 25 = $2.000/kg).
4. Llama a `InventarioCapasModel::crearCapa()` con `cantidad_original = cantidadBase` y `costo_unitario = costoUnitarioBase`.
5. Llama a `recalcularPromedioPonderado(itemId)` para actualizar `costos_item.costo_unitario` con el nuevo promedio ponderado móvil.

**Invariante**: `inventario_capas` siempre almacena cantidades y costos en unidad base (kg). La conversión ocurre una sola vez, en la recepción.

### 2. Consumo en Producción — Cascada por Proveedor

`PreparacionesModel::_ajustarInventarioPorPreparacion` enruta el consumo en tres modos según el payload recibido:

| Modo | Condición en `capasConfig[itemId]` | Método llamado |
|------|------------------------------------|----------------|
| **MANUAL** | `modo = 'MANUAL'` y `capas: [{capa_id, cantidad}]` | `consumirCapasManual(capas)` |
| **Por proveedor** | `proveedor_id` presente (y modo ≠ MANUAL) | `consumirCapasPorProveedor(itemId, cantidad, proveedorId, bodegaId)` |
| **FIFO global** | Sin proveedor ni capas manuales | `consumirCapasFIFO(itemId, cantidad, bodegaId)` |

**Cascada por proveedor** (`consumirCapasPorProveedor`):
- Filtra `inventario_capas` por `item_general_id` + `proveedor_id` + `estado = 'activa'`.
- Ordena por `fecha_ingreso ASC` (FIFO estricto dentro del proveedor).
- Descuenta de la capa más antigua hasta cubrir la cantidad requerida; si la capa se agota, continúa con la siguiente del mismo proveedor.
- Si la suma de todas las capas del proveedor es insuficiente, lanza `Exception` con detalle de déficit → el `catch` en `create_preparacion` llama a `transRollback()` y nada se persiste.

**Garantía de atomicidad**: toda la secuencia (INSERT en `preparaciones`, consumo de capas, INSERT en `produccion_insumos_detalle`) ocurre dentro de un bloque `transBegin()` / `transCommit()`. Cualquier `Exception` PHP o error SQL dispara `transRollback()` completo.

### 3. Costo Congelado — `produccion_insumos_detalle`

Inmediatamente después de consumir las capas, `_ajustarInventarioPorPreparacion` escribe una fila por ingrediente en `produccion_insumos_detalle`:

```
preparacion_id  → FK a preparaciones (CASCADE DELETE)
item_general_id → ingrediente consumido
proveedor_id    → proveedor de las capas consumidas (nullable si FIFO global)
bodega_id       → bodega de origen (nullable)
cantidad        → kg consumidos (unidad base)
costo_unitario  → promedio ponderado de las capas efectivamente consumidas (snapshot)
subtotal        → cantidad × costo_unitario
created_at      → timestamp de la operación
```

`costo_unitario` es un **snapshot inmutable**: aunque `costos_item.costo_unitario` cambie por recepciones futuras, el registro histórico de esta producción refleja el costo real de las capas que se descontaron. Esto permite:
- Comparar costo teórico (promedio vigente en `costos_item`) vs. costo real (capas realmente usadas).
- Auditorías de rentabilidad por lote de producción.
- El widget de **Variación de Costo** en el frontend (`ConfirmSubForm`) usa `onCostoChange(itemId, {real, teorico})` para mostrar Δ% en tiempo real antes de confirmar la producción.

**Cancelación**: si una preparación pasa a estado cancelado, `restaurarCapas(prepId)` revierte los descuentos en `inventario_capas` y se eliminan las filas correspondientes en `produccion_insumos_detalle`.

## Key Configuration Files

| File | Purpose |
|------|---------|
| `app/Config/Routes.php` | All API route definitions |
| `app/Config/Database.php` | MySQL connection |
| `.env` | DB credentials override (hostname=db, database=gestorpincadb) |
| `app/Config/Filters.php` | JWT + CORS filter aliases and assignment |
| `app/Filters/JwtFilter.php` | Token validation logic |
| `app/Filters/CorsFilter.php` | CORS headers |
| `docker-compose.yml` | Full stack: app (:8080), MySQL (:3306), phpMyAdmin (:8081) |

## Dependencies

- **Runtime**: `codeigniter4/framework ^4.0`, `firebase/php-jwt ^6.11`
- **Dev**: `phpunit/phpunit ^10.5`, `fakerphp/faker ^1.9`, `mikey179/vfsstream ^1.6`

## Catálogo (Maestro de Ítems) — Added 2026-04-24

### CatalogoController (`app/Controllers/CatalogoController.php`)

Separates the technical definition of products from their physical inventory. Items are created ONLY through the Catálogo — the Inventario module is now read-only for stock visualization.

**Routes:**

| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/api/catalogo?tipo=0&categoria_id=1&q=texto` | List all items with stock totals (from inventario_capas) and proveedores count |
| `show($id)` | GET `/api/catalogo/{id}` | Full detail: item + proveedores array + stock per bodega |
| `create()` | POST `/api/catalogo` | Create item_general + costos_item (cost=0, no inventory entry) |
| `update($id)` | PUT `/api/catalogo/{id}` | Update item attributes only (no inventory/cost changes) |
| `delete($id)` | DELETE `/api/catalogo/{id}` | Delete item |
| `proveedores($id)` | GET `/api/catalogo/{id}/proveedores` | List item_proveedor linked to this item |

### CatalogoModel (`app/Models/CatalogoModel.php`)

- `listar(?tipo, ?categoriaId, ?busqueda)` — single query with LEFT JOINs to categoria, unidad, costos_item + subqueries for stock_total (SUM inventario_capas) and total_proveedores (COUNT item_proveedor)
- `detalle(id)` — full item with proveedores array and stock_por_bodega breakdown
- `crearItem(data)` — transactional: INSERT item_general + INSERT costos_item (costo=0). NO inventory entry — stock enters only via OC receipt
- `actualizarItem(id, data)` — updates item_general attributes only
- `proveedoresDeItem(id)` — item_proveedor with proveedor + unidad JOINs

### Design Decision: Catálogo vs ItemController

`CatalogoController` replaces `ItemController` as the primary interface for item management. Key difference: `CatalogoController::create()` does NOT create inventory records (no bodega, no cantidad). Stock enters the system exclusively through OC receipt → `InventarioCapasModel::crearCapa()`. The old `ItemController` remains for backward compatibility with existing endpoints but should not be used for new item creation.

## Inventory Write Restriction (2026-04-24)

Inventory records can ONLY be created/incremented through two transactional sources:

1. **OC Receipt** (`OrdenesCompraController::recibirLinea`) — creates cost layers + updates `inventario` table for raw materials
2. **Production Order closure** (`PreparacionesModel`) — creates finished product inventory

**Disabled routes:**
- `POST /api/bodegas/item` — manual item creation in bodega (removed from Routes.php)
- `POST /api/inventario/ingresar` — direct inventory insertion (removed from Routes.php)
- `PATCH /api/inventario/{id}/cantidad` — manual quantity update (removed from Routes.php)

**Still allowed:**
- `POST /api/inventario/traspaso` — transfers between bodegas (no net stock increase)
- `DELETE /api/inventario/{id}/bodega/{id}` — remove item from bodega

**Model method `InventarioModel::ingresarABodega()`** is kept as an internal method called by `OrdenesCompraController` during OC receipt — it is NOT exposed via HTTP.

**`ItemProveedorController::vincular()`** no longer creates inventory entries when linking a provider to an item — stock only enters via OC receipt.

## Pending / Next Steps

- **Requisiciones management page**: frontend page in Compras module to list, approve, and convert requisitions to OC.
- **"Sin vincular" badge**: visual indicator on item_proveedor table rows with no `item_general_id`.
- **Instrumentar `login_attempts`** en `UsuarioController::login` (rate limit por IP+usuario).
- **Logging de seguridad** en `UsuarioController::login` y deletes críticos.
- **Refactor `RemisionesController`**: agregar `item_general_id` al detalle para que las salidas descuenten stock real (hoy el detalle es texto libre).

---

> **Estado del sistema (2026-05-13):** Backend funcional con JWT global activo, RBAC implementado, CORS restringido y audit log de inventario robusto. Producción cumple **MRP II — Costeo por Lotes**: selección de proveedor dirigida, consumo FIFO por capa, costo unitario congelado en `produccion_insumos_detalle` al momento de la producción, atomicidad transaccional completa. **Catálogo** es la fuente única de creación de ítems. **Inventario** es de solo lectura — todo stock ingresa exclusivamente por Recepción de OC o Cierre de Producción. **Sincronización** centraliza la auditoría catálogo↔proveedores con detección de duplicados, huérfanos y merge robusto. Cada cambio de stock genera fila en `movimiento_inventario` con metadata JSON via `MovimientoInventarioModel::registrar()`.

---

## Estado real de la auditoría — actualizado 2026-05-13

> Snapshot honesto del estado actual contrastando contra la auditoría histórica del 2026-05-09. **Resueltos 6 de 10 críticos**. Lo que sigue abierto está marcado claramente.

### ✅ RESUELTOS

| # | Crítico original | Estado actual |
|---|------------------|---------------|
| 1 | JWT no aplicado | **Aplicado globalmente** vía `'jwt' => ['except' => ['api/login', 'api/crear']]` en `app/Config/Filters.php`. Confirmado por mensaje real `{"ok": false, "msg": "Token no proporcionado"}` que retorna `JwtFilter::before` |
| 2 | Secret JWT débil hardcodeado | **TOKEN_SECRET en .env** con 64 chars hex. El fallback `'miClaveSuperSecreta'` sigue en código pero ya no se usa |
| 3 | `display_errors = 1` | Cambiado a `0` en `app/Config/Boot/production.php` |
| 7 | CORS abierto `'*'` | **Restringido a `http://localhost:5173`** vía `.env`. Listo para cambiar al dominio real al deploy |
| 8 | Sin RBAC | **Implementado**: `usuarios.rol` ENUM + tabla `permisos_rol_modulo` + `PermisosController`. Ver §Sistema RBAC |
| 10 | Password no re-hasheada en update | `$beforeUpdate = ['hashPassword']` agregado a `UsuarioModel` |

### ⚠️ PARCIALMENTE RESUELTOS

| # | Crítico | Falta |
|---|---------|-------|
| 4 | Credenciales DB | Movidas a `.env` ✅, pero las credenciales son las de DEV (`user`/`password`). **Cambiar antes de deploy** |
| 6 | Rate limit login | ✅ Instrumentado en `UsuarioController::login` (max 5 intentos por IP en ventana de 15 min, retorna 429). Falta solo: extender a otros endpoints de mutación crítica si se quiere |
| 9 | Logging de seguridad | ✅ Activo en `UsuarioController::login` (`[LOGIN_OK]`, `[LOGIN_FAIL]`), `[PASSWORD]`, `[USUARIO_CREADO]`, `[MERGE_ITEMS]`. Pendiente: extender a deletes críticos (DELETE clientes, facturas, OCs) |

### ❌ ABIERTOS (sin cambios)

- **#5 Validación de input** — sin `$this->validate([...])` en endpoints críticos. Solo chequeos de "no vacío"
- **#11 BaseModel auto-genera allowedFields** — sigue siendo riesgo de mass assignment en modelos genéricos
- **#12 HTTPS no forzado** — `force_https()` comentado en `production.php`
- **#13 `DBDebug: true`** — sigue true en `Database.php`. Cambiar a `(ENVIRONMENT !== 'production')`
- **#14 Sin tope max en paginación** — algunos controllers aceptan `?limit=∞`
- **#16 Sin soft-deletes** — DELETE permanentes, sin `deleted_at`
- **#17 Sin health check** — agregar `GET /api/health`
- **#18 Formatos de error inconsistentes** — coexisten `{ok}`, `{success}`, `{status}`
- **#19 Sin validación de FKs** antes de INSERT
- **#20 Sin tests** — `tests/` vacío

### 🔵 Deuda técnica todavía pendiente

- Sin `.env.example`
- Sin versionado de API (`/api/v1/`)
- Sin OpenAPI/Swagger
- Sin security headers (`X-Frame-Options`, `Strict-Transport-Security`, etc.)

### Checklist actualizado para deploy real

```
✅ JWT aplicado a todas las rutas protegidas
✅ TOKEN_SECRET fuerte y en .env
✅ display_errors = 0 en production.php
✅ RBAC funcional con permisos por módulo
✅ CORS restringido al frontend
✅ Rate limiting instrumentado en login (5 intentos / 15 min / IP)
✅ Logs de intentos de login habilitados
✅ Audit log de cambios de stock con responsable real (vía JwtUserAware trait)
□ Credenciales DB de PRODUCCIÓN (no las de dev)
□ HTTPS forzado
□ DBDebug = false en producción
□ Security headers activados
□ Health check endpoint disponible
□ Validación de input con $this->validate()
□ Soft-deletes en entidades críticas
□ Tests de integración para OC, producción y login
```

### Trait `JwtUserAware` (2026-05-13)

Archivo: `app/Traits/JwtUserAware.php`. Trait reutilizable que expone los datos del JWT decodificado (que `JwtFilter::before` guarda en `$request->usuario`) como métodos tipados:

```php
class MiController extends ResourceController {
    use \App\Traits\JwtUserAware;

    public function metodo() {
        $username = $this->getUsername();        // 'jperez' o 'sistema' si no hay sesión
        $rol      = $this->getUserRol();         // 'admin' | 'operador' | 'visor'
        if (!$this->userHasRole('admin')) { ... }
    }
}
```

Aplicado en: `OrdenesCompraController`, `InventarioController`, `PreparacionesController`, `SincronizacionController`. Reemplaza el viejo header `X-User` que el frontend mandaba como workaround. Cuando un controller registra un movimiento de inventario, el `responsable` viene del trait, no del cliente — el usuario no puede falsificar su identidad.

---

## Sesión 2026-05-14 — Configuración del Sistema + PDFs (snapshot)

### Tabla nueva: `configuracion_sistema` (key/value/JSON)

Migración `2026-05-14-000002_CreateConfiguracionSistema`. Esquema:
- `id_configuracion`, `grupo` VARCHAR(40), `clave` VARCHAR(80) UNIQUE, `valor` JSON, `tipo` (`string|number|boolean|json`), `descripcion`, `updated_at`, `updated_by`

Grupos seedeados:
| Grupo | Migración | Claves |
|---|---|---|
| `tributaria` | `_000003` | iva_default(19), retencion_fuente_pct(2.5), retencion_iva_pct(15), retencion_ica_default(11.04), aplicar_iva_por_default(true) |
| `umbrales` | `_000004` | stock_critico_dias(7), stock_warning_dias(30), mora_warning_dias(30), mora_critica_dias(60), margen_minimo_pct(10), margen_objetivo_pct(20) |
| `seguridad`, `financiero`, `comercial`, `notificaciones`, `paginacion` | `_000007` | jwt_expiracion_horas(8), max_intentos_login(5), ventana_intentos_segundos(900), password_min_caracteres(8), margen_utilidad_default_pct(50), dias_vencimiento_factura(30), dias_credito_default(30), limit_default(30), limit_maximo(100), dias_alerta_vencimiento(3), page_size_default(25), max_per_page(200) |
| `apariencia` | `_000008` | avatar_palette (json array con 8 gradientes) |

### Tabla nueva: `numeracion_documentos`

Migración `2026-05-14-000005`. Control centralizado de numeración correlativa para los 5 tipos de documento. Schema completo: `id_numeracion`, `tipo_doc`, `prefijo` (con placeholder `{Y}`), `padding`, `proximo_numero`, `anio_actual`, `reinicia_anual`, `resolucion_dian`, `fecha_resolucion`, `rango_min/max`, `fecha_vigencia_hasta`, `activo`, audit cols.

Seeds que detectan el próximo número desde data existente (no resetean):
- `factura` → FAC-{Y}-, 4 dígitos
- `cotizacion` → COT-{Y}-, 4 dígitos
- `remision` → REM-{Y}-, 4 dígitos
- `orden_compra` → OC-, 3 dígitos (no se reinicia anual)
- `nota_credito` → NC-, 3 dígitos (no se reinicia anual)

### Modelos nuevos

**`ConfiguracionModel`** — métodos `obtener(clave, default)`, `guardar(clave, valor, usuario)`, `getGrupo(grupo)`, `getAllGrouped()`, `seedIfMissing(grupo, clave, valor, tipo, desc)`. **Importante**: usa `obtener`/`guardar` y NO `get`/`set` para no chocar con `BaseModel::get($id, $table)` y `CI4 Model::set($key, $value, $escape)`.

**`NumeracionModel`** — método estrella `reservar(string $tipoDoc): string` con transacción + `SELECT … FOR UPDATE`. Maneja reset anual automático si `reinicia_anual=1` y validación de `rango_max` DIAN. Devuelve número formateado: `"FAC-2026-0001"` o `"OC-022"`.

### Controllers nuevos

| Controller | Endpoints | Notas |
|---|---|---|
| `ConfiguracionController` | `GET /configuracion`, `GET /configuracion/grupo/:g`, `GET /configuracion/:clave`, `PUT /configuracion/:clave`, `PUT /configuracion/bulk`, `GET /configuracion/tipos-movimiento` | Mutaciones gated por `rol=admin` (vía `JwtUserAware`) |
| `NumeracionController` | `GET /numeracion`, `POST /numeracion`, `PUT /numeracion/:id` | El consumo (reservar) es interno desde Facturas/OC/etc. Crear nueva activa desactiva la anterior automáticamente. |
| `AuditoriaController` | `GET /auditoria/login-attempts?...`, `GET /auditoria/movimientos?...` | Solo `rol=admin`. Paginado con `page` + `per_page`. Movimientos hace JOIN a `item_general` + `bodegas` y decodifica `metadata` JSON. |

### Helper PHP nuevo: `App\Helpers\Cfg`

Cache estático per-request para evitar leer DB cada vez. API:
```php
Cfg::n('clave', $default = 0)   // number (int o float según default)
Cfg::s('clave', $default = '')  // string
Cfg::b('clave', $default = false) // boolean
Cfg::flush()                    // invalida cache (después de mutar config)
```

Usado en: `UsuarioController` (JWT, password, rate limit), `BodegasModel` + `FormulacionesModel` (margen utilidad default 50), `CotizacionesController` (vencimiento factura), `AuditoriaController` (page_size), `NotificacionModel` (limits).

### EmpresaController extendido

Migración `_000006_ExtendEmpresa` agrega: `direccion`, `email`, `celular`, `locale` (default `es-CO`), `moneda` (default `COP`), `logo_path`. Backfill con valores que estaban hardcodeados en frontend.

Endpoints nuevos:
- `GET /empresa` — devuelve OBJETO único (antes devolvía array)
- `PUT /empresa` — admin only, acepta locale/moneda/logo_path además de los originales
- `POST /empresa/logo` — multipart con campo `logo`. Valida tipo (PNG/JPG/WEBP) y tamaño (≤2MB). Guarda en `public/uploads/empresa/logo_TIMESTAMP.ext`, borra el anterior, actualiza `logo_path`.
- `DELETE /empresa/logo` — quita el logo actual y borra archivo del disco.
- `GET /empresa/logo-base64` — devuelve `data:image/png;base64,...` (lo usa el frontend para incrustar el logo en jsPDF, evita CORS al fetch directo de `/uploads/`).

Migración `_000009_SeedDefaultLogo` setea `logo_path = '/uploads/empresa/logo_default.png'` si está vacío. El archivo se copia manualmente desde `pinca_frontend/src/assets/pincaicono.png`.

### Soft-delete en `item_proveedor`

Migración `_000001_AddSoftDeleteToItemProveedor` agrega `deleted_at` + índice. **No requirió tocar el controller** — `BaseModel::delete_table()` ya detecta la columna y hace UPDATE en vez de DELETE. Modelo declara `useSoftDeletes = true`. El `get_item_proveedores()` (query raw) tuvo que sumar `WHERE ip.deleted_at IS NULL` manualmente.

Resuelve el FK constraint con `historial_precios` y `ordenes_compra_detalle`.

### Refactors críticos

- **`UsuarioController`**: JWT secret sin fallback (lanza excepción si `TOKEN_SECRET` está vacío o usa `'miClaveSuperSecreta'`); JWT expiración + max intentos + ventana + password mín leídos de `Cfg`.
- **`BodegasModel` + `FormulacionesModel`**: 5 ocurrencias de `COALESCE(ci.porcentaje_utilidad, 50)` → `Cfg::n('margen_utilidad_default_pct', 50)`.
- **`CotizacionesController::convertir`**: `+30 days` → `dias_vencimiento_factura` config.
- **`AuditoriaController`**: `min(200, ...)` → `Cfg::n('max_per_page', 200)`.
- **`NotificacionModel::listarPara`**: limit default/máximo configurables.
- **5 controllers de documentos** (Facturas, Cotizaciones, Remisiones, OrdenesCompra, NotasCredito): reemplazaron sus `generarNumero()` legacy por `(new NumeracionModel())->reservar('factura' | 'cotizacion' | 'remision' | 'orden_compra' | 'nota_credito')`. Las funciones legacy quedaron eliminadas.

### Migraciones aplicadas en esta sesión

```
2026-05-14-000001 AddSoftDeleteToItemProveedor
2026-05-14-000002 CreateConfiguracionSistema
2026-05-14-000003 SeedConfiguracionTributaria
2026-05-14-000004 SeedConfiguracionUmbrales
2026-05-14-000005 CreateNumeracionDocumentos
2026-05-14-000006 ExtendEmpresa
2026-05-14-000007 SeedConfiguracionSeguridadFinanciero
2026-05-14-000008 SeedAvatarPalette
2026-05-14-000009 SeedDefaultLogo
```

### Estado de la auditoría histórica — actualizado

Resueltos en esta sesión (de los #11-20 que quedaban abiertos):
- ✅ **#16 Soft-deletes** — implementado en `item_proveedor` (la migración `_000007` previa ya cubría clientes/proveedor/item_general/facturas/OCs/cotizaciones/remisiones).
- ⚠️ **#11 Mass assignment** — sigue siendo riesgo en `BaseModel::create_table` que llena `allowedFields` desde `getFieldNames()`. NO resuelto.
- ⚠️ **#13 DBDebug** — aún `true` en `Database.php`. NO resuelto.
- ⚠️ **#14 Tope max paginación** — resuelto en `AuditoriaController` y `NotificacionModel::listarPara` vía `Cfg`. Otros controllers siguen sin tope. Parcialmente resuelto.

### Endpoints nuevos resumidos

```
GET    /api/configuracion
GET    /api/configuracion/tipos-movimiento
GET    /api/configuracion/grupo/:grupo
GET    /api/configuracion/:clave
PUT    /api/configuracion/:clave         (admin)
PUT    /api/configuracion/bulk           (admin)
GET    /api/numeracion
POST   /api/numeracion                   (admin)
PUT    /api/numeracion/:id               (admin)
GET    /api/auditoria/login-attempts     (admin)
GET    /api/auditoria/movimientos        (admin)
GET    /api/empresa                      (devuelve objeto único)
PUT    /api/empresa                      (admin)
POST   /api/empresa/logo                 (admin)
DELETE /api/empresa/logo                 (admin)
GET    /api/empresa/logo-base64
GET    /api/categorias/:id               (faltaba)
POST   /api/categorias                   (faltaba)
PUT    /api/categorias/:id               (faltaba)
DELETE /api/categorias/:id               (faltaba)
```

---

## Sesión 2026-05-15 — Hardening + Trazabilidad + Cmd+K + Notificaciones

### P0 / P1 técnicos resueltos

- **OrdenesCompra::recibirLinea** ahora hace `SELECT … FOR UPDATE` dentro de la transacción para evitar **stock duplicado por recepciones simultáneas**.
- **BodegasController::patch_cantidad eliminado** (era bypass del audit log; la ruta ya estaba off pero el método huérfano).
- **factor_conversion > 0** validado en `ItemProveedorController::create/update/vincular`.
- **FormulacionesModel::validarSumaPorcentajes** — rechaza fórmulas cuyos % no sumen 100 (±0.5).
- **ItemProveedorModel::resolverItemGeneral** usa `GET_LOCK(md5(nombre))` para evitar duplicados de `item_general` en creaciones simultáneas. También rechaza si existe un item soft-deleted con ese nombre.
- **PreparacionesModel** excluye items soft-deleted en queries de fórmula e ingredientes (evita consumos fantasma).

### Soft-deletes consistentes

`useSoftDeletes = true` activado en: `OrdenesCompraModel`, `CotizacionesModel`, `RemisionesModel`, `FacturasModel`, `ClientesModel`, `ProveedorModel`. Las queries raw de listados (`OrdenesCompra::listar/detalle/generarNumero`, `RemisionesModel::get_remisiones/get_remision_by_id`, `ClientesModel::get_item_clientes`, `ProveedorModel::get_item_proveedores`, `CarteraModel`, `DashboardController` cartera/ventas/cotizaciones/OCs/rentabilidad) ahora todas filtran `deleted_at IS NULL` explícitamente. Antes el frontend veía registros soft-deleted porque las raw queries no respetaban la columna.

### SearchController — búsqueda global Cmd+K

`GET /api/search?q=texto&limit=5` — endpoint unificado que busca en `item_general`, `clientes`, `proveedor`, `facturas`, `cotizaciones`, `remisiones`, `ordenes_compra`, `notas_credito`. Devuelve array plano `[{tipo, id, label, sublabel, path}]`. Respeta soft-deletes.

### Trazabilidad — UI completa

`TrazabilidadController` ya existía con `porPreparacion / porLote / lotes`. Se agregó el frontend completo: `TrazabilidadPage` (sidebar Inventario), drawers reusables, **PDF de hoja de auditoría carta A4** (`ExportTrazabilidad`) con dos modos.

### AjusteManual de stock

`POST /api/inventario/ajuste-manual` — body `{item_general_id, bodega_id, cantidad, motivo, observacion?}`. Descuenta vía FIFO de la bodega especificada, recalcula promedio ponderado, registra `MovimientoInventario` tipo `AJUSTE` con metadata. Motivo obligatorio: `rotura | derrame | conteo | vencimiento | otro`. El frontend lo expone en `Inventario/Components/DataTable` (acción por fila) e `InventarioGlobalPage` (chip por bodega expandida).

### Notificaciones automáticas

`NotificacionesController::generarAutomaticas()` se ejecuta **lazy en cada `GET /notificaciones`**. Genera (con dedup por día):
- Stock crítico de MP (`dias_restantes < cfg.stock_critico_dias`)
- OCs Enviadas hace > 14 días sin recibir
- Facturas en mora > `cfg.mora_critica_dias`

### Costos / CostosIndirectos consolidados

El módulo standalone CostosIndirectos se eliminó del frontend. La administración real de costos indirectos vive **inline en cada Producción** (`ProduccionDetailModal::CostosIndirectosSection`). El tab "Indirectos" dentro de `/costos` quedó solo como **vista read-only de análisis** del catálogo.

### OrdenesCompraModel.detalle — exposición de unidad/factor

El SELECT del detalle ahora trae `factor_conversion`, `unidad_compra_nombre`, `unidad_base_nombre` (JOIN extra a `unidad ub`). Lo necesita el frontend para mostrar el banner de conversión "10 BULTOS × 25 = 250 kg" en `RecibirLineaModal` antes de confirmar la recepción. La recepción ahora también acepta `lote_proveedor` opcional en el body.

### Filtro por responsable en Movimientos

`MovimientoInventarioController::index` y `MovimientoInventarioModel::get_movimientos` aceptan `?responsable=username` y filtran exact match.

### Clonar fórmula

`POST /formulaciones/clonar` body `{from_item_id, to_item_id, nombre?}` — copia la receta activa del producto origen al destino reusando `crearFormulacion` (respeta validaciones).

### Migraciones aplicadas

```
2026-05-15-000001 AddTrazabilidadModulo            (RBAC: trazabilidad)
2026-05-15-000002 AddCostosModulos                  (RBAC: costos)
2026-05-15-000003 RemoveCostosIndirectosModulo     (limpieza)
```

### Endpoints nuevos resumidos

```
GET    /api/search?q=…&limit=5
GET    /api/trazabilidad/preparacion/:id     (ya existía)
GET    /api/trazabilidad/lote/:lote          (ya existía)
GET    /api/trazabilidad/lotes?q=            (ya existía)
POST   /api/inventario/ajuste-manual
POST   /api/formulaciones/clonar
```

### Auditoría histórica — estado al cierre

- ✅ #16 Soft-deletes — ahora consistentes (BD + modelos + queries raw).
- ⚠️ #11 Mass assignment — sigue abierto.
- ⚠️ #13 DBDebug — sigue `true`.
- ⚠️ #14 Tope max paginación — parcial (Auditoria + Notificaciones; otros controllers sin tope).
- ❌ Race conditions en otros endpoints sin verificar (solo recibirLinea fue arreglada).

---

## Sesión 2026-05-18 — Costos de Formulaciones vinculados a Proveedores

### Contexto

El frontend ahora auto-selecciona el proveedor más barato por ingrediente en la tabla de formulaciones usando `GET /formulaciones/{id}/opciones-ingredientes`. El costo mostrado por ingrediente es `precio_por_kg` del proveedor seleccionado (no `costos_item.costo_unitario`). El usuario puede cambiar el proveedor manualmente por ingrediente.

### Defecto identificado — `costos_item.costo_unitario` overwrite

`FormulacionesModel::crearFormulacion()` (líneas 996-1001) y `actualizarFormulacion()` (líneas 1070-1075) ejecutan:
```php
if (!empty($mp['costo_unitario'])) {
    $this->db->query('UPDATE costos_item SET costo_unitario = ? WHERE item_general_id = ?',
        [$mp['costo_unitario'], $mp['materia_prima_id']]);
}
```
Esto permite que el payload de una formulación sobrescriba un campo que debería ser calculado exclusivamente por `InventarioCapasModel::recalcularPromedioPonderado()`. El frontend actualmente NO envía este campo, pero la puerta está abierta. Ver `MEJORAS.md` para la recomendación de fix.

### Endpoint clave para el frontend

`GET /formulaciones/{id}/opciones-ingredientes` → `get_opciones_proveedor_formulacion()`:
- Devuelve `{ item: {...}, materias: { [mpId]: { opciones: [...sorted by precio_por_kg ASC] } } }`
- `opciones[0]` es siempre el proveedor más barato por ingrediente
- Matching: prioridad 1 = vinculado por `item_general_id`, prioridad 2-3 = match por nombre (exacto / parcial)

---

## Sesión 2026-05-19 — Hardening profundo + IVA por OC + unify costos

Sesión grande de consolidación. **23 fixes** del análisis profundo aplicados y verificados con un comando spark de regresión. **IVA persistido por OC**. **Módulo costos unificado con rentabilidad**.

### Bug en formulaciones cerrado (P0 #1 de MEJORAS)

`FormulacionesModel::crearFormulacion` y `actualizarFormulacion` ya no sobreescriben `costos_item.costo_unitario` desde el payload. Ese campo solo lo escribe `InventarioCapasModel::recalcularPromedioPonderado()` al recibir OCs.

Optimización colateral (#21 de MEJORAS): `get_opciones_proveedor_formulacion` ahora pre-filtra `item_proveedor` por `item_general_id IN (...)` de los ingredientes de la fórmula, en lugar de hacer full scan + filter en PHP. Misma lógica aplicada a `get_proveedores_formulacion`. Agregado `deleted_at IS NULL` que faltaba.

### Análisis profundo — 23 fixes aplicados

**🔴 Críticos (10) — bugs activos que rompían lógica:**

1. **`FacturasModel::recalcularSaldo` implementado** — el método se llamaba en 6 lugares (FacturasController, PagosClienteController, NotasCreditoController) pero no existía. Cobranza estaba rota. Calcula `total - pagos - NC activas` y ajusta `estado` (Pagada/Parcial). Respeta `Anulada` y `Vencida`.
2. **`InventarioCapasModel::_consumirDeCapas` lanza Exception** si `pendiente > 0.0001` post-foreach. Antes silenciaba el déficit y `produccion_insumos_detalle` recibía la cantidad teórica en vez de la real, falsificando el costo congelado. Aplica a FIFO global, por proveedor y manual.
3. **Filtros `p.estado != 3` corregidos en 5 controllers** — antes había `!= 'cancelada'` (string contra int → no filtraba nada) en `InventarioController:92` y `!= 0` (excluía pendientes) en `DashboardController:186/314`, `NotificacionesController:75`, `PreparacionesController:94`. Todos ahora `!= 3` (CANCELADA).
4. **Ruta `PUT /bodegas/item/:id` eliminada** — `BodegasModel::update_item_bodega` sobreescribía `inventario.cantidad` y `costos_item.costo_unitario` desde el payload, bypaseando capas y audit log. Removido del Routes.php, controller y modelo.
5. **`RequisicionesCompraModel::convertirAOC` usa `NumeracionModel::reservar('orden_compra')`** en lugar de `SELECT MAX(numero) + 1`. Cierra race condition de duplicación.
6. **Transacciones en `RemisionesController::create/convertir`** + **`CotizacionesController::create/convertir`** — antes INSERTs cabecera + N detalles sin tx → falla intermedia dejaba documentos huérfanos.
7. **SQL injection latente cerrada** — reemplazadas todas las `set('col', "col + {$var}", false)` por queries parametrizadas en `InventarioCapasModel::restaurarCapas/restaurarCapasRemision`, `InventarioModel::traspaso` (2 lugares) + `ingresarABodega`, `RemisionesController:252`.
8. **Validación FK en create** — `OrdenesCompraController::create` verifica `proveedor_id` existe + `deleted_at IS NULL`. Mismo para `FormulacionesController::create` con `item_general_id`.

**🟡 Importantes (9):**

9. **`MovimientoInventarioModel::registrar`**: TRASPASO/AJUSTE sin `saldo_anterior` explícito ahora loggea warning (antes inferia `saldo_anterior == saldo_nuevo` silenciosamente).
10. **`OrdenesCompraController::cambiarEstado`** envuelto en `transBegin` + `SELECT ... FOR UPDATE`. Cierra race entre transiciones concurrentes.
11. **`BaseModel::update_table`** consulta `deleted_at` antes de updatear. Si el record está archivado devuelve `['archivado' => mensaje]` en vez de updatear silenciosamente.
12. **`BaseModel::restore_table`** usa `pkOf($table)` en vez de `'id_'.$table` hardcoded. Resuelve restore para `ordenes_compra` (PK `id_orden`).
13. **`ItemProveedorModel::resolverItemGeneral`** lee el retorno de `GET_LOCK`: si timeout (`got = 0`) o error (`got = NULL`) lanza Exception. Antes asumía el lock obtenido.
14. **`RequisicionesCompraModel::verificarDisponibilidad`** ahora consulta `SUM(inventario_capas.cantidad_disponible) WHERE estado = 1` en lugar de la tabla `inventario` legacy.
15. **`_getProveedoresPorItem` + `_pickMejorProveedor`** filtran `ip.deleted_at IS NULL`.
16. **`UsuarioController::login`** registra `login_attempts` solo en fallo, y al login exitoso borra los attempts previos de la IP. Fin del autobloqueo de usuario legítimo con 5 logins en 15 min.
17. **`CatalogoController::delete`** rechaza con 409 si el item tiene `inventario_capas.cantidad_disponible > 0`. Evita rows huérfanos con `nombre = NULL` en Inventario Global.

**🟢 Menores (4):**

18. **`OrdenesCompraModel::generarNumero` + `NotasCreditoModel::generarNumero` eliminados** — código muerto, reemplazado en sesión 2026-05-14 por `NumeracionModel::reservar(...)`.
19. **`MovimientoInventarioModel::registrarMovimiento` eliminado** — método deprecated sin callers.
20. **`OrdenesCompraController::recibirLinea`** ahora setea `item_general.costo_produccion` al **promedio recalculado** (retorno de `recalcularPromedioPonderado`), no al costo de la última OC.
21. **`PreparacionesModel::update_preparacion`** mapea strings (`PENDIENTE/EN_PROCESO/COMPLETADA/CANCELADA`) → 0..3 antes de updatear. Rechaza valores fuera de rango. Antes `(int) 'CANCELADA' === 0` caía silenciosamente a PENDIENTE.

### IVA persistido por OC (estrategia B+)

**Por qué B+ y no más simple/complejo**: discusión completa en MEJORAS y la sesión. Resumen: agregar `iva_pct` por OC garantiza trazabilidad histórica (si en 2027 cambia el % global, las OCs viejas conservan el suyo) sin la complejidad de agregar régimen por proveedor o IVA por línea de detalle. Cuando aparezca esa necesidad, se extiende sin migración destructiva.

**Migración**: `2026-05-19-000001_AddIvaPctToOrdenesCompra`:
- Agrega `ordenes_compra.iva_pct DECIMAL(5,2) NULL DEFAULT NULL`.
- Backfill: lee `iva_default` de `configuracion_sistema` y aplica a OCs históricas.

**Cambios en código**:
- **`OrdenesCompraController::create`** valida + persiste `iva_pct` (override del cliente o `Cfg::n('iva_default', 19)`).
- **`OrdenesCompraModel`** tiene `enrichWithIva()` privado que aplica a `listar()` y `detalle()`. Devuelve `total` (sin IVA, semántica histórica), `iva_pct`, `iva_monto`, `total_con_iva`.
- **`DashboardController::ocsPendientes`** devuelve `valor_total` + `valor_total_con_iva` (`SUM(oc.total * (1 + COALESCE(oc.iva_pct, 0) / 100))`).
- **`RemisionesController:373`** usa `Cfg::n('iva_default', 19) / 100` en lugar de `0.19` hardcodeado al convertir remisión → factura.
- **`OrdenesCompraModel::allowedFields`** incluye `iva_pct`.

**Lo que NO cambia**:
- `total` sigue siendo la suma de subtotales sin IVA (semántica histórica).
- `inventario_capas.costo_unitario` sigue sin IVA (el IVA es descontable en régimen común — el costo real para producción es la base).
- `produccion_insumos_detalle.subtotal` igual sin IVA (cadena de costo congelado limpia).

### Merge módulo costos → rentabilidad

Costos era subset puro de Rentabilidad (mismos tabs: Producción, Compras, Indirectos). Rentabilidad añade Resumen + Ganancias. Tener ambos era duplicación.

**Migración `2026-05-19-000002_MergeCostosIntoRentabilidad`**:
- Para cada rol que tenía `costos = 1`, activa `rentabilidad = 1` (insert si no existía).
- Elimina todas las filas con `modulo = 'costos'` de `permisos_rol_modulo`.
- Cero pérdida de acceso.

Frontend eliminó la carpeta `Costos/` completa y redirige `/costos → /rentabilidad`.

### Spark command nuevo: `php spark validar:fixes`

Archivo: `app/Commands/ValidarFixes.php`. Corre **47 tests de regresión** contra la DB real, agrupados por:
- Críticos (10 tests)
- Importantes (9 tests)
- Menores (4 tests)
- IVA en OCs (5 tests)

Cada test usa `transBegin`/`transRollback` cuando muta datos (excepto los que llaman `NumeracionModel::reservar` que commitea atómicamente — efecto colateral: consume números de OC).

Output muestra ✓/✗ por test + resumen. Última corrida: **47 PASS / 0 FAIL**.

Útil para correr antes de un commit grande: `docker exec gestor-pinca-app php spark validar:fixes`.

### Migraciones aplicadas en esta sesión

```
2026-05-19-000001 AddIvaPctToOrdenesCompra
2026-05-19-000002 MergeCostosIntoRentabilidad
```

### Endpoints nuevos / modificados

```
POST /api/ordenes_compra            (acepta `iva_pct` en payload — override opcional)
GET  /api/ordenes_compra            (cada OC trae iva_pct + iva_monto + total_con_iva)
GET  /api/ordenes_compra/:id/detalle (idem)
GET  /api/dashboard                  (ocs_pendientes incluye valor_total_con_iva)
POST /api/remisiones/:id/convertir   (IVA leído de Cfg, ya no hardcoded)
```

### Estado de MEJORAS.md al cierre

- ✅ P0 #1 — Eliminado overwrite de `costos_item.costo_unitario`
- ✅ P2 #8 — Validación FKs en endpoints críticos (OC + Formulaciones)
- ✅ P2 #9 — Race conditions en `cambiarEstado` y `convertirAOC` (PreparacionesModel ya tenía tx; FacturasController::cambiarEstado revisado)
- ✅ P2 #10 — `validarSumaPorcentajes` cubre los 3 flujos
- ✅ P3 #13 — Tope de paginación parcial (otras zonas siguen)
- ✅ P3 #16 — Detalle remisiones gana FK + descuento de stock (sesión 2026-05-15)
- ✅ P4 #21 — Endpoint costos por proveedor optimizado
- ⚠️ P3 #11 — Tests: tenemos `validar:fixes` (47 tests) pero no PHPUnit formal
- ⚠️ P3 #12 — Formato de error inconsistente sigue abierto
- ⚠️ P4 #17 — Health check sin implementar
- ❌ P1 (5,6,11,12,13,14,15) — todo lo de PROD/HTTPS/security headers sigue abierto a propósito

### Pendientes que el negocio puede pedir más adelante

- `proveedor.regimen_iva` + `item_proveedor.precio_incluye_iva` para soportar régimen simplificado / items exentos.
- Endpoint `/api/health` para monitoreo / load balancer.
- Reescribir `BaseModel::create_table` para que no auto-genere `allowedFields` (mass assignment).
- PHPUnit tests formales (`tests/` sigue vacío).
- Versionado API `/api/v1/...`.
- OpenAPI / Swagger spec.

---

> **Snapshot al cierre 2026-05-19**: Backend en estado de **post-hardening profundo**. Cobranza desbloqueada, FIFO seguro, costos consistentes con/sin IVA, módulos unificados. Validador automatizado para regresiones. 47/47 PASS. Listo para seguir construyendo features sobre fundamentos sólidos. El día que se decida deploy real, el checklist de `MEJORAS.md` § Checklist pre-deploy lista lo que falta (HTTPS, DBDebug, security headers, credenciales prod).

---

## Sesión 2026-05-20 — Costos de Producción + Salud + Superadmin + Backup

### Nuevo módulo: Costos de Producción

Vista unificada que cruza fórmulas + capas + cobertura de proveedores + empaque/MO para mostrar el costo real y precio sugerido de cada producto terminado.

**`CostosProduccionController`** (nuevo):
- `GET /api/costos-produccion` — listado con stock para producir (cuello de botella + tandas posibles) por producto
- `GET /api/costos-produccion/:id/historia` — hasta 36 snapshots históricos del producto
- Migración `2026-05-19-000003_AddCostosProduccionModulo` (RBAC: admin + operador + visor)

**`FormulacionesModel::get_costos_produccion_batch()`** — nuevo método central. Por producto calcula:
- Costo MP total y por unidad
- Cobertura: cuántas MPs tienen proveedor activo (estado `completo` / `incompleto`)
- **Stock para producir**: SUM `inventario_capas.cantidad_disponible` por MP → calcula `tandas_posibles = floor(min(stock_kg / cantidad_por_tanda))`, identifica `cuello_botella`, deriva `galones_posibles`

**Tabla `costos_snapshot`** (migración `2026-05-20-000002_CreateCostosSnapshot`):
- Schema: id, item_general_id, fecha, estado, volumen_base, costo_mp_total/por_unidad, costo_empaque_mod, costo_total, porcentaje_utilidad, precio_venta_calc, mps_total, mps_cubiertas
- `UNIQUE (item_general_id, fecha)` para idempotencia

**Spark command `snapshot:costos`** (`app/Commands/SnapshotCostos.php`) — upserts un snapshot por producto con fecha actual. Pensado para correr mensualmente vía cron y poblar la evolución histórica de costos.

### Nuevo módulo: Salud del Sistema

Dashboard de diagnóstico operativo que cruza 6 categorías de issues activos.

**`SaludSistemaController`** (nuevo) — endpoint `GET /api/salud-sistema`:
- **Cobertura proveedores**: % de MPs con `item_proveedor` activo (con fallback de matching por nombre, no solo `item_general_id`)
- **MPs sin movimiento >90 días**: stock con `last_movement < NOW() - 90d`
- **Productos sin fórmula activa**
- **OCs Enviadas >14 días sin recibir**
- **Facturas en mora >`mora_critica_dias`** (lee `Cfg`)
- **Items archivados con stock pendiente**
- Devuelve `score` 0-100 (% de categorías sin issues), `issues_activos`, `total_checks` (6)
- Bug fix durante implementación: query de mora usaba `c.nombres/apellidos` (no existen en schema) → corregido a `c.nombre_empresa/nombre_encargado`
- Migración `2026-05-20-000001_AddSaludSistemaModulo` (RBAC: admin + operador + visor)

### Rol `superadmin` — gestor exclusivo de permisos

Nuevo rol por encima de admin que es el único capaz de mutar `permisos_rol_modulo`. El admin sigue viendo todo lo demás.

**Migración `2026-05-20-000003_AddSuperadminRol`**:
- `ALTER usuarios.rol` ENUM agrega `'superadmin'` → `('superadmin','admin','operador','visor')`
- `ADD COLUMN usuarios.password_must_change TINYINT(1) DEFAULT 0` — fuerza cambio de password al primer login
- Inserta usuario `1juanherrera` / `Juan Herrera` / `superadmin` / password hasheado de `root` (must_change=1)
- Inserta filas en `permisos_rol_modulo` para `superadmin` con todos los módulos activos (19)

**`JwtUserAware` extendido** (`app/Traits/JwtUserAware.php`):
- `userIsSuperadmin()` — solo `rol === 'superadmin'`
- `userHasAdminAccess()` — `rol IN ['admin', 'superadmin']`
- `userHasModule()` ahora trata superadmin como admin (acceso total)

**Cambios en controllers**:
| Controller | Antes | Después |
|---|---|---|
| `PermisosController` (update/cambiarRol) | `requireAdmin()` | `requireSuperadmin()` — solo superadmin muta permisos |
| `AuditoriaController`, `EmpresaController`, `NumeracionController`, `ConfiguracionController` | `userHasRole('admin')` | `userHasAdminAccess()` — admin O superadmin |

**`UsuarioController` ajustes**:
- `login()` devuelve `password_must_change` en `usuario` payload
- `cambiarPassword()` limpia el flag (`password_must_change = 0`) al guardar password nuevo

**Comportamiento resultante**:
- Admin: ve todos los módulos, accede a Empresa/Configuración/Auditoría/Numeración, pero recibe 403 si intenta `PUT /api/roles/.../permisos` o `PATCH /api/roles/usuarios/.../rol`
- Superadmin: lo de admin + única vista a la gestión de roles

### Backup automatizado de DB

`pinca_backend/backups/` (nuevo directorio):
- `backup-auto.sh` — corre `docker exec gestor-pinca-db mysqldump --no-tablespaces --single-transaction --routines --triggers --events 2>/dev/null > auto_pinca_YYYY-MM-DD_HH-MM-SS.sql`
- `backup-auto.bat` — wrapper Windows que ejecuta el sh via `wsl.exe`
- Rotación automática: borra dumps `auto_pinca_*.sql` con `>30 días`
- Pensado para correr desde Task Scheduler (Windows) o cron (Linux)
- **Importante**: se descartó la opción de spark command porque `gestor-pinca-app` no tiene `mysql-client` instalado. Solución: script host-side que invoca `docker exec` contra el contenedor de DB

### Endpoints nuevos/modificados

```
GET  /api/costos-produccion                         (lista con stock para producir)
GET  /api/costos-produccion/:id/historia            (hasta 36 snapshots)
GET  /api/salud-sistema                             (score + 6 categorías de issues)
PUT  /api/roles/:rol/permisos                       (cambió: ahora solo superadmin)
PATCH /api/roles/usuarios/:id/rol                   (cambió: ahora solo superadmin)
POST /api/login                                     (cambió: devuelve password_must_change)
PATCH /api/usuarios/mi-password                     (cambió: limpia password_must_change)
```

### Migraciones aplicadas en esta sesión

```
2026-05-19-000003 AddCostosProduccionModulo        (RBAC costos-produccion)
2026-05-20-000001 AddSaludSistemaModulo            (RBAC salud-sistema)
2026-05-20-000002 CreateCostosSnapshot             (tabla costos_snapshot + UNIQUE constraint)
2026-05-20-000003 AddSuperadminRol                 (ENUM + columna must_change + perms + user Juan Herrera)
```

### Coupling con frontend (saber para no romper)

El frontend depende de que:
- `GET /salud-sistema` mantenga el shape: `{ score, issues_activos, total_checks, cobertura: {pct, mps_cubiertas, mps_totales}, mps_sin_movimiento_90d: [...], productos_sin_formula: [...], ocs_retrasadas: [...], facturas_en_mora: [...], archivados_con_stock: [...] }`
- Cada item de `facturas_en_mora` traiga `cliente` (string), no `nombre_empresa`/`nombre_encargado` separados — el controller hace el coalesce
- `GET /costos-produccion` devuelva por producto: `stock_para_producir: {tandas_posibles, galones_posibles, cuello_botella: {mp_nombre, stock_kg, requerido_por_tanda, tandas_posibles}}`
- `POST /login` devuelva `usuario.password_must_change` (entero 0|1) — el frontend lo usa para bloquear con `ForceChangePasswordModal`

---

> **Snapshot al cierre 2026-05-20**: Backend con dos features nuevas (Costos de Producción + Salud del Sistema), rol superadmin para gestión exclusiva de permisos, y backup automatizado funcional. La gestión de roles ya no es accesible para admin — solo para el `superadmin` (usuario Juan Herrera). El `admin` mantiene acceso operativo a todo lo demás. Snapshot mensual de costos via `php spark snapshot:costos` (correr 1×/mes para alimentar el gráfico de evolución).

---

## Sesión 2026-05-21 — Hardening + Prorrateo de OC + Token versioning

Sesión grande coordinada con frontend. Auditoría de 5 agentes → plan en 3 fases → ejecución completa. Ver `PENDIENTES.md` en la raíz del backend para el backlog (Swagger, tests, deploy hardening, etc.).

### Migración nueva

```
2026-05-21-000001 AddTokenVersionToUsuarios
```

Agrega `usuarios.token_version INT UNSIGNED NOT NULL DEFAULT 1` (after `password_must_change`). Cada JWT incluye el token_version del usuario en su payload; el filtro lo valida contra la BD en cada request. Cuando se cambia el rol de un usuario o el usuario cambia su password, el contador se incrementa → cualquier JWT viejo deja de validar.

### Endpoints nuevos

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/auth/me` | Devuelve usuario fresco de BD (rol, módulos actualizados, password_must_change). El frontend lo consulta antes de renderizar rutas protegidas — resuelve el flash del panel cuando hay JWT expirado en localStorage. |
| `POST` | `/api/ordenes_compra/:id/recibir-prorrateado` | Recibe varias líneas en un solo lote con precio total negociado. Reparte el factor proporcional al valor de cada línea y crea capas con costo prorrateado. Todo en una transacción con `FOR UPDATE` por línea. |
| `GET` | `/api/ordenes_compra/:id/lote-sugerido` | Devuelve `{lote: "..."}` — el código de lote que se usará al recibir mercancía de esta OC. Reusa el existente si ya hay capas con lote para esta OC, o genera `LOT-OC{id}-{Ymd}`. El frontend lo consume para pre-rellenar el input. |

### Helper `resolverLoteProveedor` en `OrdenesCompraController`

Centraliza la lógica del código de lote. Es llamado por `recibirLinea`, `recibirLoteProrrateado`, y `loteSugerido`. Garantiza que todas las recepciones de una misma OC compartan el mismo lote (a menos que el usuario lo override manualmente).

Reglas:
1. Si el payload trae `lote_proveedor` no vacío → respetar (override manual).
2. Sino, si ya existen capas con `orden_compra_id=$id` y `lote_proveedor` no nulo → reusar ese código.
3. Sino, generar `LOT-OC{idOrden}-{Ymd}`.

### Modificaciones en `JwtFilter::before`

Después de decodificar el JWT, ahora valida `token_version` contra la BD:

```php
$tokenVersion = (int) ($decoded->data->token_version ?? 1);
$userId       = (int) ($decoded->data->id ?? 0);
$row = $db->table('usuarios')->select('token_version')->where('id_usuarios', $userId)->get()->getRowArray();
if ($row && (int) $row['token_version'] !== $tokenVersion) {
    return Services::response()->setJSON(['ok' => false, 'msg' => 'Sesión invalidada...'])->setStatusCode(401);
}
```

Tokens emitidos antes de esta feature (sin `token_version` en payload) se asumen v1.

### `cambiarPassword` ahora devuelve `token`

Para no patear al usuario que acaba de cambiar su password (porque el token actual sería invalidado por el bump de `token_version`), el endpoint devuelve un JWT fresco con el nuevo `token_version`. El frontend lo usa para mantener viva la sesión.

### `cambiarRol` (PermisosController) bumpea `token_version`

Ahora hace `set('token_version', 'token_version + 1', false)` además del UPDATE de rol → cualquier sesión del usuario afectado se invalida en el siguiente request.

### Fixes de integridad (Fase 1)

| # | Cambio | Archivo |
|---|---|---|
| F1.1 | `FacturasController::cambiarEstado` rewriteado: `transBegin` + `SELECT ... FOR UPDATE` + bloqueo de transición desde `Anulada` (terminal). Al anular: borra pagos_cliente asociados, marca notas_credito como Anulada, loguea con username. | `FacturasController.php` |
| F1.2 | `FacturasController::create` valida que `cliente_id` exista y `deleted_at IS NULL` antes del INSERT. | `FacturasController.php` |
| F1.3 | `CotizacionesController::create` y `::convertir`: misma validación FK de cliente. | `CotizacionesController.php` |
| F1.4 | Race condition en consumo de capas: `InventarioCapasModel::consumirCapasFIFO/PorProveedor/Manual` ahora usan `SELECT ... FOR UPDATE`. Método privado `_obtenerCapasParaConsumo` con raw query + `FOR UPDATE`. `obtenerCapas` (display) queda intacto. | `InventarioCapasModel.php` |

### Fixes de autorización (Fase 2)

DELETEs de Facturas, Clientes, Órdenes de Compra y Cotizaciones ahora chequean `userHasAdminAccess()` y loguean la acción. Trait `JwtUserAware` agregado a `ClientesController`, `CotizacionesController` y `FacturasController` (ya estaba en OrdenesCompraController).

`UsuarioModel::allowedFields` ahora incluye `password_must_change` — sin esto, CI4 silenciaba el flag al limpiarlo en `cambiarPassword`, causando que el modal forzado apareciera en cada login.

### Validación con `ValidatesJson` (Fase 2)

- `FormulacionesController::create/update`: nuevas reglas `RULES_FORMULACION` con cantidades > 0, porcentajes [0, 100], item_general_id > 0.
- `ItemProveedorController::vincular`: validación de `unidad_compra_id`, `factor_conversion`, `crear`, `tipo`. Complementa el `validarFactorConversion` existente.

### `ApiResponse` trait (pilot, Fase 3)

Archivo nuevo `app/Traits/ApiResponse.php` con métodos `apiSuccess`, `apiCreated`, `apiFail`, `apiNotFound`, `apiForbidden`, `apiValidationError`. Devuelven shape consistente `{ok, msg, data?, errors?}`. **No se migró ningún controller existente** para no romper consumidores frontend; está disponible para nuevos endpoints.

### Paginación capped

`MovimientoInventarioController::index` ahora lee `Cfg::n('page_size_default', 50)` y `Cfg::n('max_per_page', 200)` en lugar de hardcodear `min(..., 200)`. Otros controllers con `?limit=` ya estaban cappeados (Item, Preparaciones, Search, NotificacionModel).

### Modificación en `OrdenesCompraModel::detalle`

Se agregó temporalmente `ip.precio_unitario AS precio_lista_actual` para el componente AnalisisAhorroOC del frontend. **Después se revirtió** (componente borrado). El JOIN ahora vuelve al original sin esa columna.

### Módulos eliminados

- **Tambores**: `TamborController.php`, `TamborModel.php`, 6 rutas en `Routes.php`, registros `modulo='tambores'` en `permisos_rol_modulo`. La migración que creó la tabla queda en historial (no se rolea). Frontend también borrado completo.
- **Prorrateo** (frontend standalone): el endpoint `/ordenes_compra/:id/recibir-prorrateado` y la lógica backend **se mantienen** — son la pieza central del flujo prorrateado vía OrdenDrawer.

### Validador automatizado (sigue disponible)

`php spark validar:fixes` ejecuta los 47 tests de regresión existentes contra la BD real. Útil correr antes de un commit grande.

### Coupling con frontend (cosas para no romper)

- `POST /login` debe incluir `token_version` en el `data` del JWT, y `password_must_change` en `usuario` del response.
- `PATCH /usuarios/mi-password` debe devolver `{ok, msg, token}` — sin `token`, el frontend caería al login tras cambiar password.
- `GET /auth/me` debe devolver `{ok, usuario: {id, username, nombre, rol, modulos, password_must_change}}`. Si cambia el shape, Layout.jsx deja de funcionar.
- `POST /ordenes_compra/:id/recibir-prorrateado` shape: `{precio_total_pagado, lote_proveedor?, lineas: [{id_detalle, cantidad_recibida}]}`. El modal RecibirProrrateoModal envía exactamente esto.
- `GET /ordenes_compra/:id/lote-sugerido` debe devolver `{lote: "string"}`. El modal lo consume al montarse.
- Detalle de OC ya **no** trae `precio_lista_actual` (se eliminó al borrar AnalisisAhorroOC en frontend).

### Backup creado en esta sesión

`pinca_backend/backups/gestorpincadb_backup_2026-05-21_post-refactor-fase3.sql` (322 KB) — snapshot completo de la BD al cierre de la sesión.

---

> **Snapshot al cierre 2026-05-21**: Backend hardened con token_version en JWT (invalidación instantánea al cambiar rol/password), prorrateo de OC con auto-generación de código de lote y atomicidad transaccional, FacturasController::cambiarEstado atómico con reverso de pagos en anulación, InventarioCapasModel con `FOR UPDATE` cerrando race condition de consumo concurrente. Auditoría de 23 fixes anteriores + 6 nuevos críticos + 6 importantes ejecutados. Backlog en `PENDIENTES.md`. Próximo gran trabajo: deploy hardening (HTTPS, security headers, CORS prod, credenciales) cuando se decida desplegar.

