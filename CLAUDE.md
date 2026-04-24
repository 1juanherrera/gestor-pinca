# CLAUDE.md

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

- **CORS**: `CorsFilter` applied via aliases in `app/Config/Filters.php`.
- **JWT Auth**: Applied selectively per route. `JwtFilter` expects `Authorization: Bearer <token>`. Tokens expire in 8 hours. Secret key comes from `TOKEN_SECRET` env var (falls back to a hardcoded default).
- All routes are prefixed with `/api` (see `app/Config/Routes.php`).

### Controllers (29 total)

All controllers live in `app/Controllers/`. They either extend `BaseController` or CodeIgniter's `ResourceController`. Controllers never return HTML — they use `$this->response->setJSON(...)` or `$this->respond(...)`.

| Controller | Domain |
|---|---|
| `UsuarioController` | Login endpoint, JWT generation |
| `ItemController` | Products/materials with cost data via JOINs |
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

### Models (29 total)

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
4. `2026-04-23-000001_CreateInventarioCapasSystem` — creates `inventario_capas` and `preparacion_consumo_capas` tables; migrates existing inventory saldos into legacy layers

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
- `obtenerCapas(int $itemId, ?int $bodegaId)` — returns active layers with provider name, bodega name, unit conversion info, days in stock. Ordered by `fecha_ingreso ASC` (FIFO-ready)
- `resumenStock(int $itemId, ?int $bodegaId)` — returns `stock_total` and `costo_promedio_ponderado`
- `consumirCapasFIFO(int $itemId, float $cantidad, int $prepId, ?int $bodegaId)` — consumes from oldest layers first. Optional bodega filter
- `consumirCapasManual(array $seleccion, int $prepId)` — consumes specific amounts from specific layers: `[{capa_id, cantidad}]`
- `restaurarCapas(int $prepId)` — reverses consumption when production order is cancelled
- `registrarConsumos(int $prepId, array $consumos)` — writes to `preparacion_consumo_capas` table
- `recalcularPromedioPonderado(int $itemId)` — updates `costos_item.costo_unitario` with weighted average from active layers

**Tables:**
- `inventario_capas` — one row per cost layer: `id_capa`, `item_general_id`, `bodega_id`, `cantidad_original`, `cantidad_disponible`, `costo_unitario`, `proveedor_id`, `lote_proveedor`, `orden_compra_id`, `fecha_ingreso`, `estado` (activa/agotada)
- `preparacion_consumo_capas` — consumption records: `capa_id`, `preparacion_id`, `cantidad_consumida`, `costo_unitario_consumo`

### CapasInventarioController (`app/Controllers/CapasInventarioController.php`)

**Routes:**
- `GET /api/inventario/{id}/capas?bodega_id=X` — all active layers for an item, with provider/bodega details
- `GET /api/inventario/capas/bodegas` — distinct bodegas with active layers
- `GET /api/inventario/capas/preparacion/{id}` — consumption history for a production order

### PreparacionesModel — Layer Integration

`_ajustarInventarioPorPreparacion` was rewritten to support cost layers:
- For consumption: checks if item has layers via `tieneCapas()`. If MANUAL mode with capas specified → `consumirCapasManual()`. Otherwise → `consumirCapasFIFO()` with optional bodega filter
- For cancellation: calls `restaurarCapas($prepId)` to reverse consumption
- Calculates real weighted cost from consumed layers
- Still updates aggregate `inventario` table for backward compatibility

### OrdenesCompraController — Layer Creation on Receipt

`recibirLinea` was modified to:
- Fetch `item_proveedor` data for `factor_conversion`
- Calculate `cantidadBase = cantidadRecibida × factorConversion` and `costoUnitarioKg = precio_unit / factorConversion`
- Create a cost layer via `crearCapa()` with provider, OC, unit conversion, lot info
- Call `recalcularPromedioPonderado()` to update `costos_item`

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

## Pending / Next Steps

- **Requisiciones management page**: frontend page in Compras module to list, approve, and convert requisitions to OC.
- **"Sin vincular" badge**: visual indicator on item_proveedor table rows with no `item_general_id`.
