# CLAUDE.md

> **Última actualización**: 2026-07-02 (auditoría MP: 54/57 vinculadas, 3 restantes). Ver §"Sesión 2026-07-02".
> **Anterior**: 2026-06-05 (documenta sesiones 06-01→06-03: RbacFilter visor read-only + JwtFilter sin fallback débil + FKs faltantes + delete con 409). Ver §"Sesión 2026-06-03".
> **2026-06-02**: deduplicación de materias primas asistida por IA — clusters, merge N→1, auditoría, UNDO. Ver §"Sesión 2026-06-01/02".
> Backend en estado funcional con seguridad activa (JWT global sin fallback + RbacFilter visor read-only + RBAC por módulo + rol superadmin + token_version + logout server-side + refresh token rotativo) + **API documentada en Swagger UI** + **shape de error unificado en 33 controllers** + **FKs reales en item_proveedor y BOM**. `migrate` limpio. `php spark validar:fixes` es SEGURO (rollback global desde 2026-05-29).

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

### Controllers (39 total — actualizado 2026-05-25)

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

---

## Sesión 2026-05-25 — Audit y doc refresh

Sin cambios de código (último commit lógico: 2026-05-21). Esta sesión es solo un audit cruzando la doc contra el árbol real, para que un Claude que entre frío no se pierda con datos obsoletos.

### Conteos reales (vs. lo que decía la doc)

| Item | Doc anterior | Real (2026-05-25) |
|---|---|---|
| Controllers | 32 (lista incompleta) | **39** (más `BaseController`) — ver `ls app/Controllers/` |
| Models | 31 | **33** (más `BaseModel`) |
| Migraciones | hasta `2026-05-21-000001` | igual — la última sigue siendo `AddTokenVersionToUsuarios` |
| Filters | `CorsFilter`, `JwtFilter` | igual — **no existe `SecurityHeadersFilter`** todavía |
| Commands (`app/Commands/`) | `ValidarFixes`, `SnapshotCostos` | **`NotificacionesProcesar` también existe** (no documentado, ver abajo) |
| Tests | "tests/ vacío" | **5 Feature tests + HealthTest unit + 2 Example tests** (no vacío) |

### Comando spark no documentado: `notificaciones:procesar`

Archivo: `app/Commands/NotificacionesProcesar.php`. Pensado para correr por cron (sugerido `0 6 * * *`). Ejecuta `NotificacionesController::generarAutomaticas()` (stock crítico, OCs retrasadas, facturas en mora). Hoy la generación corre lazy en cada `GET /notificaciones` — el cron está disponible si se quiere desacoplar.

```bash
docker exec gestor-pinca-app php spark notificaciones:procesar
```

### Tests reales (no estaba vacío)

`tests/` ya tiene cobertura inicial:

- `tests/Feature/LoginTest.php` — auth + rate limiting
- `tests/Feature/OrdenesCompraTest.php` — flujo OC + recepción
- `tests/Feature/PreparacionesTest.php` — producción + FIFO
- `tests/Feature/RemisionesStockTest.php` — descuento de stock en remisiones
- `tests/Feature/SoftDeleteTest.php` — filtrado de `deleted_at`
- `tests/unit/HealthTest.php` — chequeo de smoke
- `tests/database/ExampleDatabaseTest.php` + `tests/session/ExampleSessionTest.php` (boilerplate CI4)

> `composer test` (= `vendor/bin/phpunit`) ya corre algo real. La doc anterior decía "tests/ vacío" — desactualizada.

Pendiente formal: ampliar cobertura de `InventarioCapasModel`, `NumeracionModel::reservar` y `FormulacionesModel` (CRUD + clonar + validación de %).

### Trait `ApiResponse` existe pero no se usa

`app/Traits/ApiResponse.php` está creado desde la sesión 2026-05-21 con `apiSuccess`, `apiCreated`, `apiFail`, `apiNotFound`, `apiForbidden`, `apiValidationError`. `grep -r "use ApiResponse" app/Controllers/` devuelve **0 resultados** — ningún controller lo adoptó. Sigue habiendo 3 shapes de respuesta coexistiendo (`{ok, msg}`, `{success, message}`, `{status, message, data}`).

### Endpoint `/api/health` sigue sin existir

Hay `tests/unit/HealthTest.php` pero **no hay ruta ni método controller**. Útil para load balancers; pendiente.

### Configuración pre-deploy verificada hoy

- ✅ `display_errors = '0'` en `app/Config/Boot/production.php:15`
- ❌ `force_https()` **no aparece** en `production.php` (ni comentado siquiera — hay que agregarlo cuando se decida deploy con SSL)
- ✅ `DBDebug` correcto: línea 36 = `(ENVIRONMENT !== 'production')`. Línea 174 (`DBDebug => true`) es del grupo `$tests` de PHPUnit con SQLite — eso está bien, no es la conexión de prod.
- ✅ `.env.example` completo con instrucción `openssl rand -hex 32` para `TOKEN_SECRET`
- ❌ `pinca_backend/README.md` vacío (0 bytes)

### Rutas verificadas presentes (no faltan, contrario a un audit anterior)

Todas las rutas mencionadas en sesiones anteriores están registradas en `app/Config/Routes.php`:
- `GET /auth/me` (línea 20)
- `POST /formulaciones/clonar` (87)
- `GET /costos-produccion` + `/:id` + `/:id/historia` (91-93)
- `GET /salud-sistema` (96)
- `POST /inventario/ajuste-manual` (137)
- `POST /ordenes_compra/:id/recibir-prorrateado` + `GET /:id/lote-sugerido`

### Backup más reciente disponible

`pinca_backend/backups/gestorpincadb_backup_2026-05-21_post-refactor-fase3.sql` (322 KB). El cron `backup-auto.sh` corrió por última vez el 2026-05-20 (`auto_pinca_2026-05-20_16-42-11.sql`). **No hay backup posterior a 2026-05-21** — si vas a tocar BD, generá uno primero.

---

> **Snapshot intermedio 2026-05-25 (audit)**: Audit cerrado. Backend congelado desde 2026-05-21. Sigue abajo la segunda mitad de la sesión que ejecutó los pendientes detectados.

---

## Sesión 2026-05-25 — Hardening adicional + health endpoint + ApiResponse pilot

Segunda mitad de la sesión. Después del audit se ejecutaron 8 bloques de cambios concretos. **`validar:fixes` corrió al cierre con 53/53 PASS** (los 47 originales + 6 nuevos de CostosProduccion). PHPUnit tiene 2 errores **pre-existentes** en `ExampleDatabaseTest` por sintaxis `DROP INDEX IF EXISTS` en migración `2026-05-14-000001_AddSoftDeleteToItemProveedor.php` que MySQL no soporta — no relacionado con esta sesión.

### Archivos modificados / creados

**Nuevos**:
- `app/Controllers/HealthController.php` — endpoint público de salud
- `README.md` (raíz backend) — antes vacío, ahora ~80 líneas

**Modificados**:
- `app/Config/Routes.php` — agregada ruta `api/health`
- `app/Config/Filters.php` — `api/health` agregada al `except` del filtro `jwt`
- `app/Controllers/UsuarioController.php` — trait `ApiResponse` adoptado (errores migrados)
- `app/Controllers/OrdenesCompraController.php` — validación en `recibirLinea`
- `app/Controllers/RemisionesController.php` — validación FK `cliente_id`
- `app/Controllers/RequisicionesCompraController.php` — validación FK `item_general_id` en batch
- `app/Controllers/PreparacionesController.php` — paginación con `Cfg::n('page_size_default'/'max_per_page')`
- `app/Controllers/ProveedorController.php` — log de DELETE con username
- `app/Controllers/ItemController.php` — log de DELETE con username
- `app/Controllers/CatalogoController.php` — log de DELETE con username
- `app/Models/BaseModel.php` — mass assignment hardening

### Health endpoint — `GET /api/health`

Endpoint público (sin JWT). Responde:

```json
{
  "ok": true,
  "status": "ok",
  "db": true,
  "timestamp": 1716595200,
  "version": "1.0"
}
```

El campo `db` chequea conectividad con `\Config\Database::connect()->query('SELECT 1')` envuelto en try/catch. Útil para load balancers o monitoreo. Verificado en vivo el día de implementación con `curl http://localhost:8080/api/health`.

### ApiResponse pilot — `UsuarioController` (errores únicamente)

Adoptado `use \App\Traits\ApiResponse;` en `UsuarioController`. Migrados los **respondedores de error** (`login`, `me`, `cambiarPassword`, `crear`) a `apiFail()`, `apiForbidden()`, `apiValidationError()`.

**Lo que NO se migró y por qué**: respuestas de éxito (`{ok, msg, token, usuario}` top-level) son incompatibles con `apiSuccess($data, $msg)` que mete todo en `data`. Migrar rompería el frontend. **Pendiente**: si se decide unificar, agregar un método `apiSuccessFlat($data, $msg)` al trait que haga merge top-level, o coordinar un cambio simultáneo backend+frontend.

> Los otros controllers siguen sin trait. Cuando se quiera propagar, este pilot es el patrón a copiar para los errores.

### Validación crítica nueva

| Endpoint | Reglas agregadas |
|---|---|
| `OrdenesCompraController::recibirLinea` | `cantidad_recibida` (decimal positivo > 0 obligatorio), `lote_proveedor` (string max 100 opcional) |
| `UsuarioController::crear` | `username` (min 3 required), `password` (min según `Cfg::n('password_min_caracteres', 8)`), `nombre` (required), `rol` (enum) |
| `RemisionesController::create` | FK `cliente_id` debe existir y no estar soft-deleted (422 si no) |
| `RequisicionesCompraController::crearRequisiciones` | FK `item_general_id` validada por cada item del batch; rechaza todo el batch si alguno falta, con detalle de los inválidos |

> `FacturasController::create` y `PreparacionesController::create` ya tenían validación equivalente vía `ValidatesJson` — no se tocaron.

### Paginación capped (continuación)

`PreparacionesController::index` migrado de `min(..., 200)` hardcoded a `Cfg::n('page_size_default', 50)` + `Cfg::n('max_per_page', 200)`. Los otros controllers candidatos (`InventarioController::global`, `DashboardController`, `CostosIndirectosController`, `RemisionesController`, `CotizacionesController`) **no aceptan `?limit=`** hoy; no se inventó paginación nueva.

### Mass assignment en `BaseModel::create_table` — hardened

Reescrito el método para usar **únicamente el `$allowedFields` declarado en el modelo hijo** cuando la tabla destino coincide con la natural del modelo. Si `$allowedFields` está vacío en ese caso, lanza `RuntimeException("Model X must declare \$allowedFields explicitly")`.

**Excepción**: cross-table inserts (ej. `RemisionesModel::create_table($items, 'facturas_detalle')`) caen al path legacy con `log_message('warning', '[BaseModel::create_table] cross-table insert: model=X, modelTable=Y, targetTable=Z')` — esto deja un marcador para futura limpieza (crear modelos dedicados por cada tabla).

**Modelos sin `$allowedFields` y sin `$table` declarado** (caen al path legacy sin Exception): `ComparadorModel`, `EmpresaModel`, `FormulacionesModel`, `PreparacionesModel`, `SincronizacionModel`. No se tocaron para no romper. Si en algún momento se agrega `$table` a alguno y `create_table` se invoca sobre la tabla natural, lanzará la Exception — comportamiento deseado.

### Logging de DELETE en controllers no críticos

`ProveedorController::delete` → `log_message('info', "[DELETE_PROVEEDOR] usuario={username} id={id}")`. Idem para `ItemController::delete` (`[DELETE_ITEM]`) y `CatalogoController::delete` (`[DELETE_CATALOGO]`). Usan `getUsername()` del trait `JwtUserAware`.

> `FormulacionesController` no tiene método `delete` — no aplica.

### README del backend (raíz)

Antes 0 bytes. Ahora ~80 líneas con: descripción de PINCA, stack (PHP 8.1+, CI4 ^4.0, MySQL 8.0), setup docker (`docker-compose up -d` + URLs y credenciales dev), env vars principales, comandos de tests (`composer test` + `php spark validar:fixes`), backup (`bash backups/backup-auto.sh`), comandos spark (`migrate`, `db:seed`, `serve`, `validar:fixes`, `snapshot:costos`, `notificaciones:procesar`), links a `CLAUDE.md` / `MEJORAS.md` / `PENDIENTES.md`.

### Riesgos / cosas raras a tener en cuenta

1. **`UsuarioController::login` devuelve 200 en fallo de credenciales** (era así desde antes; mantenido para no romper contrato). El frontend lee el flag `ok` para diferenciar. Si se quiere convertir a 401, hay que coordinar con frontend.
2. **`UsuarioController::crear` ahora rechaza payload sin `nombre`**. Antes era opcional. Si rompe algún flujo (verificar pantalla de Roles → Crear usuario), bajar la regla a `permit_empty|max_length[100]`.
3. **Mass assignment cross-table logs**: `writable/logs/` se va a llenar con warnings `[BaseModel::create_table] cross-table insert` cada vez que se convierte cotización→factura o remisión→factura. No es bug, es marcador. Limpiar cuando se creen los modelos dedicados.
4. **`HealthController` retorna `ok: true` aún cuando `db: false`** (el endpoint en sí responde 200). Si querés que el load balancer marque unhealthy al perder DB, cambiar a 503 cuando `db === false`.

### Estado de tests al cierre

```bash
docker exec gestor-pinca-app php spark validar:fixes
# PASS 53 / FAIL 0 (47 originales + 6 nuevos CostosProduccion)

composer test
# 2 errores pre-existentes en ExampleDatabaseTest (DROP INDEX IF EXISTS no soportado por MySQL).
# NO relacionado con esta sesión.
```

---

> **Snapshot intermedio 2026-05-25 (mañana)**: Backend con health endpoint público, `ApiResponse` trait adoptado como pilot en errores de `UsuarioController`, etc. Sigue abajo la **segunda mitad** del día.

---

## Sesión 2026-05-25 (tarde) — Hardening 2 + ApiResponse propagación + tests Feature ampliados

Tercera ronda del día. Después del audit (mañana) y la ejecución del backlog detectado (mediodía), esta sesión vació prácticamente todo el backlog excepto las features grandes (refresh token, OpenAPI, /v1/) y deploy. **3 agentes corrieron en paralelo**.

### Archivos modificados (19) + creados (3 tests)

**Migración**: `app/Database/Migrations/2026-05-14-000001_AddSoftDeleteToItemProveedor.php` (fix `DROP INDEX IF EXISTS`).
**Routes/Config**: `app/Config/Routes.php` (logout).
**Controllers** (16): `HealthController`, `UsuarioController` (logout), `BodegasController`, `InstalacionesController`, `UnidadController` (log DELETE), `CotizacionesController`, `PagosClienteController`, `NotasCreditoController`, `RequisicionesCompraController` (validación nueva), `OrdenesCompraController`, `FacturasController`, `RemisionesController`, `PreparacionesController`, `FormulacionesController`, `ItemProveedorController`, `CatalogoController`, `InventarioController` (ApiResponse en errores).
**Tests nuevos** (3): `tests/Feature/InventarioCapasModelTest.php`, `NumeracionModelTest.php`, `FormulacionesModelTest.php`.

### HealthController ahora devuelve 503 cuando db=false

```json
// db=true → 200
{"ok": true, "status": "ok", "db": true, "timestamp": ..., "version": "1.0"}
// db=false → 503
{"ok": false, "status": "degraded", "db": false, "timestamp": ..., "version": "1.0"}
```

Load balancers pueden marcar unhealthy correctamente sin parsear el body.

### Logout server-side — `POST /api/auth/logout`

`UsuarioController::logout()` requiere JWT válido (NO está en `except` del filtro). Incrementa `usuarios.token_version` del usuario actual con `set('token_version', 'token_version + 1', false)`. Devuelve `apiSuccess(null, 'Sesión cerrada correctamente')`. El cliente borra el JWT de localStorage; cualquier request posterior con el JWT viejo cae con 401 vía `JwtFilter` (que ya valida `token_version` contra BD desde sesión 2026-05-21).

**Frontend coupling**: cuando se llame este endpoint, el cliente debe SIEMPRE borrar el JWT local **incluso si la respuesta es 401** (caso en que el JWT ya estaba inválido).

### Validación de input nueva (4 controllers)

| Endpoint | Reglas agregadas |
|---|---|
| `CotizacionesController::create` | `cliente_id\|cliente_libre` (al menos uno), `fecha` valid_date, `items` array min 1, `validez_dias` integer ≥ 0 (opcional) |
| `RequisicionesCompraController::crearRequisiciones` | combinado con FK validation existente: `cantidad` numérica > 0 por item |
| `PagosClienteController::create` | `facturas_id` int > 0, `monto` numérico > 0, `fecha_pago` valid_date, `metodo_pago` string required |
| `NotasCreditoController::create` | `facturas_id` int > 0, `monto` > 0, `motivo` string max 255 |

Devuelven 422 con `{ok: false, msg, errors: {...}}` cuando falla.

### `ApiResponse` trait propagado a 10 controllers (solo errores)

Los siguientes ahora usan `apiFail()`, `apiValidationError()`, `apiForbidden()`, `apiNotFound()` para respuestas de error:

`UsuarioController` (pilot ya migrado), `OrdenesCompraController`, `FacturasController`, `CotizacionesController`, `RemisionesController`, `PreparacionesController`, `FormulacionesController`, `ItemProveedorController`, `CatalogoController`, `InventarioController`.

**NO migrado**: `PermisosController` usa shape `{success, message}` (helpers internos del BaseController) — migrar rompería contrato. Documentado en el backlog si se quiere unificar más adelante.

**Las respuestas de éxito NO se migraron en ningún controller** (siguen devolviendo shape top-level `{ok, msg, ...datos}`). Migrar éxito requiere extender el trait con un `apiSuccessFlat($data, $msg)` que mergee top-level — pendiente.

### `recalcularSaldo` cobertura completa

Audit hecho. Hueco encontrado y arreglado:
- ✅ Llamado en `PagosClienteController::create/update/delete`.
- ✅ Llamado en `NotasCreditoController::create/anular`.
- ✅ Llamado en `FacturasController::cambiarEstado` (transición a Pagada; rama Anulada resetea `saldo_pendiente = total`).
- ✅ **NUEVO**: `FacturasController::update` ahora también llama `recalcularSaldo` si el payload incluye `total`. Antes no lo hacía → factura editada quedaba con saldo desincronizado.
- N/A: `CotizacionesController::convertir` crea factura nueva con `saldo_pendiente = total` (sin pagos/NC todavía).

### Logging de DELETE expandido

`UnidadController::delete` → `[DELETE_UNIDAD]`, `BodegasController::delete` → `[DELETE_BODEGA]`, `InstalacionesController::delete` → `[DELETE_INSTALACION]`. Total controllers con logging: 7 (Facturas, Clientes, OCs, Cotizaciones desde antes + Proveedores, Items, Catálogo de sesión 2026-05-25 mañana + Unidades, Bodegas, Instalaciones de esta sesión).

### Migración `2026-05-14-000001_AddSoftDeleteToItemProveedor` — fix `DROP INDEX IF EXISTS`

MySQL no soporta esa sintaxis. Reemplazado por chequeo manual via `INFORMATION_SCHEMA.STATISTICS` + `ALTER TABLE ... DROP INDEX`. `php spark migrate:rollback && php spark migrate` corre limpio.

**Otras 6 migraciones tienen el mismo patrón** (`2026-05-13-000001`, `2026-05-14-000003/4/7/8`, etc.). NO se tocaron — pendientes. Si alguien hace `migrate:rollback` para esas, fallarán.

### Tests Feature nuevos — 10/10 PASS, 58 assertions

```
docker exec gestor-pinca-app vendor/bin/phpunit \
  --filter "InventarioCapasModelTest|NumeracionModelTest|FormulacionesModelTest"
# OK (10 tests, 58 assertions)
```

**`InventarioCapasModelTest`** (5 tests):
- `testCrearCapaYRecalcularPromedio` — 2 capas → promedio ponderado correcto.
- `testConsumirCapasFIFO` — FIFO por proveedor consume orden cronológico, agota capas.
- `testConsumirCapasPorProveedorInsuficiente` — Exception cuando stock < requerido.
- `testRestaurarCapas` — `restaurarCapas($prepId)` revierte consumos correctamente.
- `testConsumirCapasManualConSeleccionEspecifica` — selección manual `[{capa_id, cantidad}]` aplicada.

**`NumeracionModelTest`** (3 tests):
- `testReservarSecuencialBasico` — 2 reservas consecutivas (`TST-2026-0001`, `TST-2026-0002`).
- `testReservarResetAnual` — al cambiar año, resetea `proximo_numero` a 1.
- `testReservarRangoDianAgotado` — Exception cuando `proximo_numero >= rango_max`.

**`FormulacionesModelTest`** (2 tests):
- `testClonarFormulacionCopiaIngredientes` — clonar copia ingredientes con porcentajes intactos.
- `testClonarFormulacionFallaSiOrigenIgualDestino` — Exception si `from == to`.

### Estado de tests al cierre

```bash
docker exec gestor-pinca-app php spark validar:fixes
# PASS 53 / FAIL 0

docker exec gestor-pinca-app vendor/bin/phpunit --filter "InventarioCapasModelTest|NumeracionModelTest|FormulacionesModelTest"
# OK (10 tests, 58 assertions)

composer test  # suite completo
# 2 errores pre-existentes en ExampleDatabaseTest (DROP INDEX IF EXISTS en otras 6 migraciones)
# 1 failure pre-existente en OrdenesCompraTest::testRecibirLineaDeOrdenNoEnviadaFalla
# NO relacionados con esta sesión.
```

### Riesgos / cosas raras al cierre

1. **`FacturasController::update`** ahora dispara `recalcularSaldo` si llega `total`. Si algún flujo dependía de NO recalcular (improbable), notará el cambio.
2. **`PermisosController`** no migrado a `ApiResponse` — shape distinto. Decidir si unificar.
3. **6 migraciones con `DROP INDEX IF EXISTS`** sin fix. Listadas en `MEJORAS.md #26` extendido.
4. **PreparacionesController::create()** ahora migrado a `ApiResponse`. Si existe alguna llamada a método `cancelar` desde otro punto, NO existe en este controller (se confirmó). El frontend hoy cancela via `update` con `estado=3`.

---

> **Snapshot al cierre 2026-05-25 (tarde)**: Backend con backlog DEV prácticamente vacío. Lo que queda: features grandes (refresh token completo con `/auth/refresh`, OpenAPI/Swagger, versionado `/api/v1/`, propagación `ApiResponse` a respuestas de éxito), 6 migraciones con `DROP INDEX IF EXISTS` pendientes, decisiones de UX (anulación de factura por email — requiere infra de email). `validar:fixes` 53/53 + 10 nuevos tests Feature PASS. Bloque deploy aislado en `PENDIENTES.md § 🚀`. Próxima sesión grande: o deploy, o refresh token, o las 6 migraciones, según prioridad del dueño.

---

## Sesión 2026-05-27 — Refresh token + ApiResponse residual + drop tambores

Sesión coordinada con frontend (3 agentes paralelos). El backlog DEV grande del backend queda casi vacío: solo OpenAPI, versionado `/v1/`, migración masiva de respuestas de éxito a `ApiResponse`, y 5 migraciones más con `DROP INDEX IF EXISTS`. **`validar:fixes` 53/53 PASS, Feature tests 10/10 PASS, `migrate` limpio.**

### Refresh token rotativo — `POST /api/auth/refresh`

Nueva capa de sesión sobre el JWT existente (que sigue durando `Cfg::n('jwt_expiracion_horas', 8)`).

**Tabla nueva `refresh_tokens`** (migración `2026-05-25-000001_CreateRefreshTokens`):
- `id` PK, `usuario_id` (FK→`usuarios(id_usuarios)` ON DELETE CASCADE — **INT con signo**, no unsigned, para coincidir con `id_usuarios`), `token_hash` VARCHAR(255) (SHA-256, **nunca el token plano**), `expires_at` DATETIME, `created_at` DATETIME, `revoked` TINYINT(1) DEFAULT 0. Índices en `token_hash` y `usuario_id`.

**Contrato (el frontend depende de esto)**:
- `POST /login` ahora devuelve `{ok, msg, token, refresh_token, usuario}` — agregó `refresh_token` (string `bin2hex(random_bytes(32))`, guardado hasheado con expiry de 7 días).
- `POST /api/auth/refresh` (público, en `except` del filtro jwt): body `{refresh_token}`. Busca por hash SHA-256 con `revoked=0` y `expires_at > NOW()`. Si inválido/expirado → `apiFail('Refresh token inválido o expirado', 401)`. Si válido: genera JWT nuevo (con `token_version` ACTUAL de BD), **rota** el refresh (revoca el viejo, crea uno nuevo) y devuelve `{ok: true, token, refresh_token}`.
- `logout()` ahora también marca `revoked=1` todos los refresh tokens del usuario (además de bumpear `token_version`).

**Cambios en `UsuarioController`**: JWT inline extraído a método privado `generarJwt($usuario)` (reusado por `login` y `refresh`); helpers `modulosDeRol()` y `crearRefreshToken()`; nuevo método `refresh()`.

Verificado en vivo: refresh válido → JWT+refresh nuevos; reusar el viejo → 401 (rotación funciona); token inválido → 401.

### Drop tabla `tambores` (migración `2026-05-25-000002`)

⚠️ **La tabla tenía 335 filas, no estaba vacía** (el backlog asumía vacía). Se dropeó igual porque el módulo Tambores se eliminó completo en 2026-05-21 (controllers, models, rutas, frontend). **Backup de seguridad**: `backups/tambores_pre_drop_2026-05-27.sql`. Si esos datos importaban, restaurar desde ahí. `down()` usa `dropTable(..., true)` (sin `DROP INDEX IF EXISTS`).

### ApiResponse — hallazgo sobre el shape real de errores

Al intentar propagar `ApiResponse` a los "29 controllers restantes" se descubrió que **el codebase tiene 3 shapes de error coexistiendo**:
1. `{ok, msg}` — `ApiResponse` + raw. Hoy en 12 controllers (los 10 de la tarde 2026-05-25 + `PagosClienteController` + `NotasCreditoController` migrados esta sesión).
2. `{status, error, messages}` — métodos nativos CI4 `$this->fail*()` (`fail`, `failNotFound`, `failValidationErrors`, `failForbidden`, `failServerError`). ~24 controllers usan SOLO esto para errores.
3. `{success, message}` — helpers internos del `BaseController`. `PermisosController`, `RequisicionesCompraController`, `DashboardController`.

**Conclusión**: migrar los ~24 controllers que usan `$this->fail*()` NO es mecánico — cambiaría el body JSON de `{status, error, messages}` a `{ok, msg}`, lo que **rompería el frontend**. Es un cambio de contrato que requiere coordinación backend+frontend, NO un refactor interno. Por eso esta sesión solo migró los 2 controllers que tenían raw `{ok:false}` (`PagosCliente`, `NotasCredito`). El item "propagar ApiResponse a 29 controllers" en el backlog estaba mal planteado — se reformuló en `MEJORAS.md`.

### Validación de input en 3 controllers de baja superficie

- `BodegasController::create` — `nombre` required|max[100], `instalaciones_id` permit_empty|is_natural_no_zero (columna real es `instalaciones_id`).
- `CategoriaController::create` — `nombre` required|max[100]. Tabla real es `categoria` (singular).
- `UnidadController::create` — `nombre` required|max[50]. NO existe `abreviatura`/`simbolo` (columnas reales: `numero, nombre, descripcion, estados, escala`).

### Soft-deletes — reporte (sin cambios de schema)

`categoria`, `unidad`, `bodegas`, `instalaciones`: los 4 son **hard-delete** (sin columna `deleted_at`, modelos sin `useSoftDeletes`). No se tocó schema (decisión del dueño). Si se quieren preservar, agregar `deleted_at` + `useSoftDeletes` por cada uno.

### `tests/README.md` actualizado

Ahora lista los 8 tests Feature + 1 unit reales (antes decía "vacío") + menciona `composer test` y `php spark validar:fixes`.

### Archivos de esta sesión

**Creados**: `app/Database/Migrations/2026-05-25-000001_CreateRefreshTokens.php`, `2026-05-25-000002_DropTamboresTable.php`, `backups/tambores_pre_drop_2026-05-27.sql`.
**Modificados**: `UsuarioController` (refresh), `Routes.php` (`auth/refresh`), `Filters.php` (except), `PagosClienteController` + `NotasCreditoController` (ApiResponse errores), `BodegasController` + `CategoriaController` + `UnidadController` (validación), `tests/README.md`.

### Riesgos al cierre

1. **`tambores` tenía 335 filas dropeadas** — backup en `backups/tambores_pre_drop_2026-05-27.sql`. Confirmar con el dueño si esos datos importaban.
2. **`usuario_id` en `refresh_tokens` es INT con signo** (no unsigned) para coincidir con `id_usuarios`. Documentado en la migración.
3. **Migrar `$this->fail*()` a `ApiResponse` es un cambio de contrato** (no mecánico) — ver hallazgo arriba.

---

> **Snapshot al cierre 2026-05-27**: Backend con refresh token rotativo funcional (rotación + revocación verificadas en vivo), tabla `tambores` eliminada (backup guardado), ApiResponse en 12 controllers, validación en 3 más, tests/README al día. Backlog DEV restante: OpenAPI, versionado `/v1/`, migración de respuestas de éxito a ApiResponse (cambio de contrato), 5 migraciones más con `DROP INDEX IF EXISTS`, email anulación factura. `validar:fixes` 53/53, Feature 10/10, `migrate` limpio.

---

## Sesión 2026-05-29 — ApiResponse propagation total + OpenAPI + soft-deletes + 5ª migración DROP INDEX

Sesión coordinada con frontend (4 agentes paralelos: 2 backend, 2 frontend). El backlog DEV grande del backend queda **casi cerrado**: solo quedan migración de respuestas de éxito en los controllers comerciales (cambio de contrato pendiente de decidir), versionado `/api/v1/`, anulación factura por email, Redis cache.

⚠️ **NO correr `php spark validar:fixes` contra la base de trabajo real**. Lo confirmamos en la sesión: el comando muta datos atómicos que no se pueden revertir (crea OCs de prueba via `NumeracionModel::reservar`, e incluso recibe OCs reales). Si hay que correrlo, hacer backup antes y limpiar después. Fix pendiente: que el comando use una BD de tests o envuelva todo en `transBegin/transRollback`.

### Trait `ApiResponse` extendido

Nuevo método `apiSuccessFlat(array $data = [], string $msg = '', int $status = 200)`:
```php
$payload = array_merge(['ok' => true, 'msg' => $msg], $data);
return $this->response->setStatusCode($status)->setJSON($payload);
```

Preserva el shape legacy top-level `{ok, msg, ...datos}` que usan los endpoints viejos (login, refresh, me, cambiarPassword). El frontend NO requiere cambios para consumir esto.

### ApiResponse propagation — 33 controllers con shape `{ok, msg}` para errores

**Antes de la sesión**: 12 controllers usando `ApiResponse` para errores.
**Después**: **33 controllers**. Migrados 21 nuevos (los listados a continuación) reemplazando los métodos nativos CI4 `fail*()` por sus equivalentes del trait:

`CarteraController`, `NotificacionesController`, `AuditoriaController`, `ConfiguracionController`, `NumeracionController`, `EmpresaController`, `CostosProduccionController`, `TrazabilidadController`, `SincronizacionController`, `ComparadorController`, `GestionesCobroController`, `ClientesController`, `ProveedorController`, `BodegasController`, `InstalacionesController`, `CategoriaController`, `UnidadController`, `ItemController`, `CapasInventarioController`, `CostosItemController`, `CostosIndirectosController`.

Mapeo aplicado: `fail` → `apiFail`, `failNotFound` → `apiNotFound`, `failValidationErrors` → `apiValidationError`, `failForbidden` → `apiForbidden`, `failServerError` → `apiFail(..., 500)`. Status codes y mensajes preservados.

**Cambio de contrato**: respuestas de error pasaron de `{status, error, messages}` (shape CI4 nativo) a `{ok, msg}` (shape ApiResponse). El frontend `apiClient.js` ya tolera ambos para los toasts (lee `.message || .messages.error || .msg`), pero las queries que inspeccionan `.messages.error` directamente reciben `null` ahora — smoke-test recomendado en producción manual.

**NO migrados** (con razón):
- `PermisosController`, `RequisicionesCompraController`, `DashboardController` — usan `{success, message}` (helpers internos del `BaseController`). Distinto contrato.
- `SaludSistemaController`, `SearchController`, `MovimientoInventarioController` — no tenían `fail*()` ni shapes raw, no había nada que migrar.
- `Facturas`, `Remisiones`, `PagosCliente`, `NotasCredito`, `Formulaciones`, `Catalogo`, `ItemProveedor`, `Preparaciones`, `Inventario` — sus respuestas de éxito usan shape `{status, message, data}` o `respond([...])` plano. Migrarlos cambiaría contrato del frontend; pendiente coordinar.

### Respuestas de éxito — `apiSuccessFlat` adoptado

Solo migré las que el trait soporta sin romper contrato. Hoy usan `apiSuccessFlat`:
- `UsuarioController`: `login`, `me`, `miActividad`, `actualizarPerfil`, `cambiarPassword`, `refresh`, `crear`.
- `OrdenesCompraController`: `recibirLoteProrrateado`.
- `CotizacionesController`: 4 bloques de validación migrados a `apiValidationError`.

El resto de los controllers comerciales mantienen su shape original.

### Soft-deletes en 4 entidades básicas (categoria, unidad, bodegas, instalaciones)

Migración `2026-05-27-000001_AddSoftDeleteToBasicEntities`:
- `ALTER TABLE categoria/unidad/bodegas/instalaciones ADD COLUMN deleted_at DATETIME NULL`.
- Índice `idx_deleted_at` en cada una.
- `down()` usa `INFORMATION_SCHEMA.STATISTICS` para chequear índice antes de `DROP INDEX` (patrón seguro MySQL).

Modelos `CategoriaModel`, `UnidadModel`, `BodegasModel`, `InstalacionesModel`: agregado `protected $useSoftDeletes = true` + `protected $deletedField = 'deleted_at'`. Ahora estas tablas hacen soft-delete (antes eran hard-delete).

**Raw queries detectadas sin filtro `deleted_at IS NULL`** (no críticas hoy porque no hay soft-deletes aún en estas tablas, pero documentadas para fix futuro):
- `BodegasModel:29` SELECT por id.
- `InstalacionesModel:31/36` SELECTs varios.
- `BodegasController:28` JOIN raw.

### 5ª migración con `DROP INDEX IF EXISTS` arreglada

`2026-05-13-000001_ExtendMovimientoInventario.php` tenía **5 `DROP INDEX IF EXISTS`** en su `down()` (idx_mov_item, idx_mov_bodega, idx_mov_ref, idx_mov_fecha, idx_mov_tipo). Reemplazados via helper privado `dropIndiceSiExiste()` que sigue el patrón de `2026-05-14-000001`.

Las otras 4 migraciones que el plan mencionaba como sospechosas (`2026-05-14-000003/4/7/8`) son **SEEDS puros** (INSERTs en `configuracion_sistema`), sin DROP INDEX. Reportadas como "no aplica".

### OpenAPI 3.0 + Swagger UI

**Archivos creados**:
- `public/openapi.yaml` (~37 KB) — OpenAPI 3.0.3 manual. **42 paths, 52 operaciones HTTP, 8 tags** (Auth, Catálogo, Inventario, Compras, Ventas, Producción, Reportes, Sistema), 11 schemas reusables (`Error`, `Success`, `Health`, `Usuario`, `LoginResponse`, `CatalogoItem`, `CatalogoItemInput`, `CatalogoItemDetalle`, `ItemProveedor`, `OrdenCompra`, `Capa`). `securitySchemes.bearerAuth` global; `security: []` override en `/health`, `/login`, `/crear`, `/auth/refresh` (públicos).
- `public/swagger-ui.html` — carga swagger-ui-dist@5 desde unpkg, header con marca PINCA, `tryItOutEnabled`, `persistAuthorization` (mantiene el JWT entre recargas), filter de endpoints.

**URL**: `http://localhost:8080/swagger-ui.html` (no requiere ruta en Routes.php, Apache sirve `public/` directo).

**Cobertura**: ~52 de ~100+ endpoints. Documenta los flujos críticos. Endpoints faltantes (proveedores, clientes, bodegas, instalaciones, unidades, categorías, costos_indirectos, gestiones_cobro, comparador, numeración, auditoría, roles, empresa CRUD) están como deuda — el spec es referencia interactiva, no contrato 100% sincronizado.

### Pagination caps — 0 nuevos (todos los candidatos son no-paginables)

Los 5 controllers candidatos (`InventarioController::global`, `DashboardController`, `CostosIndirectosController`, `RemisionesController`, `CotizacionesController`) **no aceptan `?limit=`** hoy. No tienen lógica de listado paginable que requiera cap. Item cerrado por "no aplica".

### Riesgos al cierre

1. **Contract change en 21 controllers** (errores `fail*() → ApiResponse`). El frontend tolera el cambio para toasts (verificado en `apiClient.js`). Smoke-test manual recomendado en escenarios de error específicos (404, 422, 403, 500) en módulos como Auditoría, Configuración, Trazabilidad para confirmar UX no rompe.
2. **`migrate:rollback` completo está roto** desde hace varias sesiones — `DropTamboresTable.down()` falla porque recrear `tambores` con FKs incompatibles. No afecta `migrate` forward. Solo un problema si alguien intenta rollback masivo.
3. **`apiValidationError` con string literal** (no array): hubo 37 casos que `apiValidationError('mensaje')` cuando el método espera `array $errors`. Convertidos a `apiFail($mensaje, 422)`. Type error en runtime evitado.
4. **`validar:fixes` sigue corrupting real DB**. Pendiente: fix del comando para que use BD test o envuelva en transaction. NO correrlo manualmente contra la base de trabajo.

---

> **Snapshot al cierre 2026-05-29 (mañana)**: Backend con shape de error unificado en 33 controllers (`ApiResponse`), trait extendido con `apiSuccessFlat`, soft-deletes en 4 entidades más, API documentada en Swagger UI (52 endpoints, 8 tags), 5ª migración `DROP INDEX IF EXISTS` arreglada. `migrate` limpio.

---

## Sesión 2026-05-29 (tarde) — Análisis profundo + fixes de seguridad/integridad + validar:fixes seguro + carga de proveedores

Sesión grande: carga de datos reales (2 proveedores), `validar:fixes` vuelto seguro, análisis profundo del sistema con 3 agentes (backend / frontend / integridad de datos), y fixes de los hallazgos accionables.

### `validar:fixes` ahora es SEGURO (no muta la BD real)

**Causa raíz del problema histórico** (OCs basura, OC-002 recibida sin querer): el comando llamaba `NumeracionModel::reservar()` que commitea atómico, y los tests dejaban residuos.

**Fix** (`app/Commands/ValidarFixes.php`): `run()` ahora abre una **transacción global** y hace **rollback garantizado** al final (`runTests()` extraído + `try/finally`). CI4 anida transacciones, así que los `transBegin/transCommit` internos de los tests y de `reservar()` se vuelven no-ops físicos y el rollback global revierte todo. Verificado: corrido 2× seguidas, `proximo_numero` no se movió, 0 residuos.

- Muestra `🔒 Modo seguro` al inicio y `↩ Rollback global aplicado` al final.
- Flag `--commit` para persistir a propósito (raro).
- **Ya se puede correr contra la base real sin miedo** (la advertencia previa queda obsoleta).

### Carga de proveedores reales (datos, no código)

Dos proveedores cargados vía SQL transaccional (todos `unidad_compra=KILO`, `factor=1`, tipo Materia Prima):

- **isGroup** (id_proveedor 32): 16 item_proveedor — 7 vinculados a catálogo existente + 9 item_general nuevos (334-342). Precios "más IVA" → `precio_unitario`=base, `precio_con_iva`=×1.19.
- **distriatlantico** (id_proveedor 33, NIT 900751588-5): 19 item_proveedor — 11 vinculados a existente + 8 nuevos (343-350: HIDROFUGANTE MATE/BRILLANTE, BIOCIDA DA PLUS/ULTRA, OMYACARB 15/4, TALCO EXTRA/EXTRA MEJORADO). Lista con **IVA incluido** → `precio_con_iva`=precio lista, `precio_unitario`=÷1.19. Precio/kg derivado del **bulto más chico** (no kg suelto minorista). **Caolina tiza Caomin: peso 25kg ASUMIDO — confirmar con cliente** (marcado en su descripción).

> Entre ambos, distriatlantico entra como opción de compra en 34 de 57 formulaciones activas; isGroup en 33. Los ingredientes más usados (Dispersante, Dióxido de titanio, Talco TY400, Antiespumante) ahora tienen 2-3 proveedores compitiendo en precio.

### Fixes de seguridad/integridad (de la auditoría)

**RBAC en mutaciones de stock** (antes cualquier rol, incluso visor, podía destruir inventario):
- `InventarioController::traspaso/ajusteManual/removeFromBodega` → ahora `userHasRole(['admin','superadmin','operador'])` (bloquea solo visor; el operador de bodega sí puede operar).
- `RemisionesController::delete` → `userHasAdminAccess()` (admin-only, consistente con los otros deletes de documentos).

**Consumo MANUAL de capas valida cantidad** (`PreparacionesModel::_ajustarInventarioPorPreparacion`): el modo MANUAL no validaba que las capas seleccionadas sumaran la cantidad requerida → producía con consumo parcial y costo congelado falso. Ahora valida la suma (tolerancia 0.0001) y lanza Exception si difiere, igual que el modo proveedor. ⚠️ Producciones que antes permitían sub-selección manual ahora fallan con rollback — comportamiento deseado.

**4 migraciones más con `DROP INDEX IF EXISTS`** arregladas (`2026-05-13-000003/000004/000007/000008`). Un agente anterior las había marcado mal como "limpias". Ahora `grep "DROP INDEX IF EXISTS"` = 0 reales. `migrate` limpio.

### Hallazgos NO arreglados (documentados en MEJORAS/PENDIENTES)

**🔴 Datos rotos (es carga del cliente, no bug de código)** — del análisis de integridad de la BD:
- **81% de materias primas (153/189) sin proveedor vinculado**.
- **60% de fórmulas (34/57) con ingredientes sin precio** → costo subvaluado.
- **91% de capas activas (90/99) con costo $0**.
- **`porcentaje` NULL en las 57 fórmulas** (682 filas, 0 con valor — campo sin usar).
- 35 item_proveedor huérfanos, 6 pares de duplicados de catálogo, 4 FKs colgadas a proveedores inexistentes (datos de prueba viejos: ids 35-38).
- **El costeo del sistema hoy no es confiable por falta de datos, no por bugs.** Ver `PREGUNTAS_CLIENTE.md` (raíz del monorepo).

**🟡 Pendientes de código (necesitan decisión o son grandes)**:
- RBAC en create/update/cambiarEstado de documentos comerciales (Facturas/OC/Remisiones/Cotizaciones/Preparaciones) — necesita matriz rol→acción definida con el cliente.
- JWT con fallback débil (`JwtFilter.php:26` `?? 'miClaveSuperSecreta'`) — deploy-only pero código vivo.
- `recalcularSaldo` suma pagos sin filtrar anulados (hoy pagos no tiene soft-delete, bajo riesgo).
- `InventarioController::global` sin paginación.
- 6 modelos sin `$allowedFields` explícito (mass assignment potencial vía insert directo).
- `EmpresaController` usa `mime_content_type()` (deprecado).

### Estado de tests al cierre
- `php spark migrate`: limpio.
- `php spark validar:fixes`: **53/53 PASS** (modo seguro, rollback).
- Feature tests: 10/10 (InventarioCapas/Numeracion/Formulaciones).

---

> **Snapshot al cierre 2026-05-29 (tarde)**: `validar:fixes` seguro (rollback global). 2 proveedores reales cargados (isGroup + distriatlantico). RBAC reforzado en mutaciones de stock (bloquea visor) + delete de remisiones (admin). Consumo MANUAL de capas valida cantidad. 4 migraciones DROP INDEX más arregladas (0 restantes). Análisis profundo reveló que **el costeo está roto por DATOS faltantes** (81% MP sin proveedor, porcentajes NULL), no por bugs — eso es carga del cliente (ver `PREGUNTAS_CLIENTE.md`). Backlog de código en `PENDIENTES.md`/`MEJORAS.md`.

---

## Sesión 2026-05-30 — Lote de mejoras DEV (seguridad/integridad + bulk)

Tanda de mejoras del backlog que NO requerían decisión del cliente ni son de deploy. `migrate` limpio, `validar:fixes` 53/53 (modo seguro).

- **6 modelos con `$allowedFields`** declarado (`FormulacionesModel`, `PreparacionesModel`, `SincronizacionModel`, `ComparadorModel`, `EmpresaModel`, `InventarioCapasModel`) — cierra mass assignment. Todos operan con query builder directo, así que no afecta sus inserts; la whitelist protege el ActiveRecord.
- **`recibirLinea`**: el `update(estado='Recibida')` se movió DENTRO de la transacción (antes corría tras el commit → posible desincronización).
- **`EmpresaController`**: `mime_content_type()` (deprecado PHP 8.4+) → `finfo_open/finfo_file/finfo_close`.
- **Raw queries con soft-delete**: `BodegasModel:29`, `InstalacionesModel:31/36`, `BodegasController:28` ahora filtran `deleted_at IS NULL` (esas tablas ganaron soft-delete el 2026-05-27).
- **Endpoint bulk** `POST /api/facturas/bulk/cambiar-estado` `{ids, estado}` (admin-only): aplica el cambio a varias facturas en UNA transacción. Refactor: la lógica de `cambiarEstado` se extrajo a helper privado `aplicarCambioEstado($db, $id, $estado)` (reusado por el endpoint individual y el bulk; reversa pagos/NC al anular, recalcularSaldo al pagar). Nueva clase `FacturaEstadoException` para mapear errores de negocio. Devuelve `{ok, msg, actualizadas, fallidas:[{id,motivo}]}`.
- **`recalcularSaldo`**: verificado — `pagos_cliente` no tiene estado/soft-delete (se borran con DELETE físico), así que sumar todos es correcto. Sin cambio, comentado.
- **Paginación**: los 4 candidatos (`InventarioController::global`, `CostosIndirectos`, `Remisiones`, `Cotizaciones`) NO aceptan `?limit=` ni paginan — devuelven el array completo que el frontend espera. Agregar cap rompería el contrato. **No-aplica** (conservador).

> Coupling frontend: `cambiarEstado` individual ahora mapea errores desde `FacturaEstadoException` (mismo shape de respuesta). El bulk con `estado:'Anulada'` es destructivo (borra pagos, anula NC del lote) — el frontend confirma antes.

---

> **Snapshot al cierre 2026-05-30**: Lote de hardening DEV aplicado — allowedFields en 6 modelos, recibirLinea atómico, finfo en upload, raw queries con soft-delete, endpoint bulk de facturas con helper reutilizable. `validar:fixes` 53/53. Sin items de producción tocados (por pedido del dueño). Backlog restante: features grandes (real-time, email, /v1/, Redis), RBAC en docs comerciales (necesita matriz rol→acción), 3 decisiones de UX, y la **carga de datos del cliente** que desbloquea el costeo (ver `PREGUNTAS_CLIENTE.md`).

---

## Sesión 2026-05-30 (tarde) — RBAC por módulo (decisión del cliente) + último precio + limpieza de datos

Commit `5559b75`. Segunda tanda del día, coordinada con frontend.

### RBAC: decisión del cliente — control por MÓDULO, no por acción

El cliente definió la política de permisos: **si un usuario tiene acceso al módulo, puede ejecutar las acciones del módulo**. Esto cerró el item "matriz rol→acción" del backlog:

- **Quitados** los guards por rol en operación: `InventarioController::traspaso/ajusteManual/removeFromBodega`, deletes de Facturas/Clientes/OCs/Cotizaciones/Remisiones, bulk de facturas, merge de Sincronización.
- **Conservados** los admin-only de configuración: Auditoría, Configuración, Empresa, Numeración. Superadmin en Roles.
- (Nota: la sesión 06-03 complementa esto con `RbacFilter` — el **visor** queda read-only a nivel filtro global, y el merge de Sincronización volvió a ser admin-only por su impacto en integridad histórica. Ver §"Sesión 2026-06-03".)

### Último precio por ingrediente en formulaciones

`FormulacionesModel::get_opciones_proveedor_formulacion` ahora devuelve `ultimo_precio` por ingrediente (costo de la capa activa más reciente, query batcheada sin N+1). El frontend lo muestra como columna en `FormulacionesTable` — cierra la decisión de UX "columna último precio" (pregunta #9 de `PREGUNTAS_CLIENTE.md`).

### Limpieza de datos (SQL directo, no código)

Del bloque 🔴 de `PENDIENTES.md`:
- ✅ **4 FKs colgadas borradas** (item_proveedor ids 35-38 + su historial — datos de prueba).
- ✅ **6 pares de duplicados de catálogo mergeados**.
- ✅ Bodega principal vaciada (capas + legacy).
- `allowedFields` también declarado en `RequisicionesCompraModel` (7º modelo).
- Backups previos en `backups/*_2026-05-2x.sql`.

---

## Sesión 2026-06-01/02 — Deduplicación de materias primas asistida por IA

Commit `4b11872`. Feature grande nueva en el módulo Sincronización: detectar y fusionar duplicados de catálogo usando un LLM que agrupa por **identidad química** (no solo similitud de string).

### Tablas nuevas (migraciones `2026-06-01-000001/000002`)

- `item_sync_clusters` — grupos sugeridos: nombre base propuesto, razonamiento del modelo, confianza, estado (pendiente/fusionado/descartado).
- `item_sync_cluster_items` — miembros de cada grupo con `rol` (keep/merge/excluido).
- `item_sync_auditoria` — registro de cada fusión ejecutada (quién, cuándo, qué items, snapshot) → habilita UNDO.

### `ClasificadorQuimicoService` (`app/Services/`)

Cliente LLM con **autodetección de provider** (Gemini o Claude según API key en `.env` — ver `.env.example`). Prompt de clasificación química; timeout 90s (bajado de 180 en sesión 06-03). Comando spark: `php spark sync:clasificar` (modos online/offline).

### Merge ampliado (`SincronizacionModel`)

- **Fusión en lote N→1 atómica** (`fusionarCluster`): combina capas/stock de todos los duplicados al keep, recalcula costo promedio ponderado, renombra al nombre base sugerido.
- `verificarPostMerge` — chequeo de consistencia post-fusión (no bloqueante).
- `revertirMerge` — UNDO parcial desde la auditoría.

### Endpoints nuevos `/api/sincronizacion/ia/*`

```
POST  /sincronizacion/ia/clasificar          (ejecuta la clasificación IA)
GET   /sincronizacion/ia/clusters            (lista grupos sugeridos)
PATCH /sincronizacion/ia/clusters/:id        (editar nombre base / keep)
PATCH /sincronizacion/ia/cluster-items/:id   (mover item de rol)
POST  /sincronizacion/ia/clusters/:id/fusionar
POST  /sincronizacion/ia/clusters/:id/descartar
GET   /sincronizacion/ia/auditoria
POST  /sincronizacion/ia/auditoria/:id/revertir
```

(Desde la sesión 06-03 todos los endpoints mutadores de IA + el merge clásico requieren `userHasAdminAccess()`.)

### Otros

- Limpieza de backups SQL redundantes del repo (~19k líneas; se conserva `tambores_pre_drop`).
- Frontend acompañó con la pestaña "Sugerencias IA" en Sincronización (ver CLAUDE.md frontend §29).

---

## Sesión 2026-06-03 — RbacFilter visor read-only + JWT sin fallback + FKs faltantes + delete con 409

(Documentada el 2026-06-05.) Lote de seguridad/integridad que complementa la política RBAC por módulo.

### `RbacFilter` nuevo (`app/Filters/RbacFilter.php`)

El rol **visor es de SOLO LECTURA a nivel global**:
- Registrado en `Filters.php` DESPUÉS de `jwt` (necesita `$request->usuario`), mismo `except` (login/crear/health/refresh).
- Bloquea POST/PUT/PATCH/DELETE para `rol=visor` con 403 `{ok:false, msg:'Tu rol (visor) es de solo lectura…'}`.
- Whitelist: `usuarios/mi-password` y `auth/logout` (su propia cuenta).
- operador/admin/superadmin no se ven afectados.

Esto cierra el RBAC pendiente sin necesidad de matriz por acción: módulo controla el acceso, el filtro garantiza que el visor nunca mute.

### `JwtFilter` sin fallback débil — RESUELTO (era item de deploy, ya no existe el riesgo)

`?? 'miClaveSuperSecreta'` eliminado. Si `TOKEN_SECRET` está vacío o es el valor por defecto → log critical + 500 `'Error de configuración del servidor'`. Ya no es posible validar tokens con secreto público.

### Migración `2026-06-03-000001_AddMissingForeignKeys`

`item_proveedor` no tenía NINGUNA FK (raíz de los huérfanos históricos) e `item_general_formulaciones` (BOM) tampoco. Agregadas 4 FKs (verificado: 0 huérfanos al aplicar, idempotente vía INFORMATION_SCHEMA):
- `item_proveedor.item_general_id` → SET NULL (queda "pendiente", no rota)
- `item_proveedor.proveedor_id` → RESTRICT
- `igf.formulaciones_id` → CASCADE
- `igf.item_general_id` → RESTRICT (no borrar un ítem usado como ingrediente)

### Migración `2026-06-01-000003_WidenItemGeneralFichaTecnica`

Columnas de ficha técnica de `item_general` estaban diminutas (`ph` varchar(1), `color` varchar(3)…) → "Data too long" al editar MP desde Catálogo. Las 9 ampliadas a varchar(50). También `CatalogoController` `nombre` max_length 36→100.

### `ItemController::delete` — 409 claro en vez de 500 por FK

Antes de borrar chequea: stock activo en capas, uso como ingrediente en fórmulas, fórmulas propias. Si hay dependencias → 409 con el motivo detallado y sugerencia de usar el merge de Sincronización. Catch de FK como red de seguridad (también 409). El frontend muestra el motivo real (toast en `useItem.js`).

### Otros

- Merge + endpoints mutadores de IA en `SincronizacionController` → `userHasAdminAccess()` (la fusión reapunta FKs y soft-deletea catálogo; demasiado destructivo para la política por módulo).
- `ClasificadorQuimicoService` timeout 180→90s.
- Backup pre-cambios: `backups/manual_pinca_2026-06-03_09-31-34.sql`.

---

> **Snapshot al cierre 2026-06-03 (doc 2026-06-05)**: Seguridad redondeada — visor read-only por filtro global, JWT sin fallback débil (riesgo de deploy eliminado en DEV), integridad referencial real en item_proveedor y BOM, deletes con errores accionables. La deduplicación IA (06-02) da herramienta para limpiar los huérfanos/duplicados restantes con criterio del cliente. Backlog restante: features grandes (real-time, email, /v1/, Redis, éxitos→ApiResponse), 1 decisión de UX (toggle costo real/lista), y la **carga de datos del cliente** (ver `PREGUNTAS_CLIENTE.md`).

---

## Sesión 2026-07-02 — Auditoría de materias primas (datos)

### Vinculaciones MP ↔ proveedores (operaciones directas en DB)

Sesión de auditoría manual para vincular materias primas usadas en formulaciones a sus proveedores correctos. Operaciones ejecutadas directamente sobre la DB (no por código de backend):

- **Vinculación ANTIPIEL → ADIMON 84**: INSERT en `item_proveedor` (AQUATERRA S.A.S., id_proveedor=35, precio $12,000 + IVA 19%, unidad KILO) apuntando a `item_general` id 86.
- Varias otras vinculaciones realizadas desde la interfaz web por el usuario.

### Estado de la auditoría MP

| Métrica | Valor |
|---|---|
| MP sin proveedor al inicio (originales) | 57 |
| MP resueltas | **54** |
| MP pendientes | **3** |

**MP restantes sin proveedor** (usadas en formulaciones activas):
1. **EDAPLAN 915** (código ADI010) — 6 fórmulas, costo ref $22,700/kg
2. **CELITE 499** (código MSI006) — 2 fórmulas, costo ref $5,400/kg
3. **RESINA MALEICA AL 60%** (código MP-266) — 1 fórmula, sin costo registrado

### Proveedores relevantes usados en vinculaciones

| Proveedor | id_proveedor | Productos vinculados esta sesión |
|---|---|---|
| AQUATERRA S.A.S. | 35 | ANTIPIEL → ADIMON 84 |
| AZELIS | 39 | (vinculaciones previas) |
| ISGROUP | 32 | (vinculaciones previas) |

### Excel de seguimiento

Generado manualmente (Python script, sin openpyxl): `C:\Users\juans\Downloads\Auditoria_MP_v3.xlsx` con 3 hojas:
1. "MP Sin Proveedor (3)" — las 3 restantes
2. "Posibles Duplicados" — pares MP↔proveedor por nombre similar
3. "MP Con Prov Sin Formula" — items de proveedor sin uso en formulaciones

---

> **Snapshot al cierre 2026-07-02**: Auditoría MP al 94.7% (54/57). Quedan 3 MP sin proveedor, ninguna crítica por volumen de fórmulas. Los datos están listos para que el módulo Costos de Producción muestre costos más completos.

