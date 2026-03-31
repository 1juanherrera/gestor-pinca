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
# single test file:
vendor/bin/phpunit tests/path/to/TestFile.php
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

- **CORS**: Applied globally via `$globals['after']` in `app/Config/Filters.php`. Allows all origins.
- **JWT Auth**: Applied selectively. `JwtFilter` expects `Authorization: Bearer <token>`. Tokens expire in 8 hours. Secret key comes from `TOKEN_SECRET` env var (falls back to a hardcoded default).
- All routes are prefixed with `/api` (see `app/Config/Routes.php`).

### Controllers

All controllers live in `app/Controllers/`. They either extend `BaseController` (for resource controllers) or `ResourceController` (CodeIgniter's built-in REST helper). Controllers never return HTML — they use `$this->response->setJSON(...)` or `$this->respond(...)`.

Key controllers:
- `UsuarioController`: Login endpoint, JWT generation
- `ItemController`: Products/materials with cost data via JOINs
- `FacturasController`, `CotizacionesController`, `OrdenesCompraController`: Business document workflows with state management (estado field)
- `InventarioController`, `BodegasController`: Warehouse/stock management
- `CarteraController`: Receivables/aging analysis

### Models

All models extend `BaseModel` (`app/Models/BaseModel.php`) which extends CI4's `Model`. `BaseModel` provides generic helpers:
- `get_all()` — fetch all records
- `create_table($data)` — insert
- `update_table($id, $data)` — update by primary key
- `delete_table($id)` — delete by primary key

Primary key naming convention: `id_[tablename]` (e.g., `id_item_general`, `id_usuarios`, `id_facturas`).

Models use `allowedFields` for mass assignment protection. Complex queries (JOINs, aggregates) are implemented as custom methods directly on the model.

### Database Schema Patterns

- Detail tables for line items: `facturas_detalle`, `remisiones_detalle`, `ordenes_compra_detalle`
- Cost tracking: `costos_item`, `item_proveedor`
- State fields (`estado`) on documents: controls workflow transitions
- FK naming: `cliente_id`, `proveedor_id`, `id_item_general`, etc.

SQL dumps in `/initdb/` are auto-loaded by Docker on first run.

### Route Organization

Routes follow this pattern per domain:
1. Specific sub-resource routes (e.g., `/items/detalle`, `/facturas/estado/:id`) come **before** generic `/:id` routes
2. RESTful verbs: GET (list/detail), POST (create), PUT (update), DELETE, PATCH (state changes)

All 173 routes are in `app/Config/Routes.php`.

### JWT Authentication

`app/Filters/JwtFilter.php` validates the `Authorization: Bearer` header on protected routes. On failure it returns 401. To protect a route, add it to the filter group in `app/Config/Filters.php`.

## Key Configuration Files

| File | Purpose |
|------|---------|
| `app/Config/Routes.php` | All API route definitions |
| `app/Config/Database.php` | MySQL connection (credentials hardcoded, not env-based) |
| `app/Config/Filters.php` | JWT + CORS filter assignment |
| `app/Filters/JwtFilter.php` | Token validation logic |
| `docker-compose.yml` | Full stack definition |
| `phpunit.xml.dist` | PHPUnit config with coverage output to `build/logs/` |
