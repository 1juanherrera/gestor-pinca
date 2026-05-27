# MEJORAS.md — Backend Pinca

> Mejoras técnicas identificadas y todavía pendientes. **Última limpieza 2026-05-27** — items resueltos eliminados de esta lista (su histórico vive en `CLAUDE.md` por sesión). El backlog operativo con checkboxes vive en `PENDIENTES.md`.

---

## P1 — Seguridad (Deploy)

### 1. HTTPS no forzado — 🚀 DEPLOY-ONLY

`force_https()` no aparece en `app/Config/Boot/production.php` ni siquiera comentado. **Requerido solo cuando se decida deploy con SSL**.

### 2. Security headers ausentes — 🚀 DEPLOY-ONLY

No existe `SecurityHeadersFilter`. Filters actuales: solo `CorsFilter` + `JwtFilter`. Headers a agregar: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Strict-Transport-Security: max-age=63072000; includeSubDomains`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: camera=(), microphone=(), geolocation=()`.

### 3. Credenciales de base de datos — 🚀 DEPLOY-ONLY

Hoy `user`/`password` (dev). Cambiar antes de deploy a credenciales fuertes vía variables de entorno o secrets manager.

---

---

## P3 — Deuda técnica abierta

### 5. Formato de error inconsistente — 3 shapes coexisten (cambio de contrato pendiente)

**Hallazgo 2026-05-27**: el item original ("29 controllers sin ApiResponse") estaba mal planteado. El codebase tiene **3 shapes de error**:

1. `{ok, msg}` — `ApiResponse` + raw. Hoy en **12 controllers**: los 10 de 2026-05-25 (`UsuarioController`, `OrdenesCompra`, `Facturas`, `Cotizaciones`, `Remisiones`, `Preparaciones`, `Formulaciones`, `ItemProveedor`, `Catalogo`, `Inventario`) + `PagosCliente` + `NotasCredito` (2026-05-27).
2. `{status, error, messages}` — métodos nativos CI4 `$this->fail*()` (`fail`, `failNotFound`, `failValidationErrors`, `failForbidden`, `failServerError`). **~24 controllers** usan SOLO esto.
3. `{success, message}` — helpers internos del `BaseController`. `PermisosController`, `RequisicionesCompraController`, `DashboardController`.

**Lo que falta NO es mecánico**: migrar los ~24 controllers que usan `$this->fail*()` cambiaría el body de `{status, error, messages}` a `{ok, msg}` → **rompe el frontend**. Es un **cambio de contrato coordinado backend+frontend**, no un refactor interno. Decidir si vale la pena unificar o dejar los 3 shapes (el frontend ya los maneja).

- **Respuestas de éxito en TODOS los controllers** siguen top-level (`{ok, msg, ...datos}`). `apiSuccess($data, $msg)` mete todo en `data` — incompatible. Pendiente: extender trait con `apiSuccessFlat($data, $msg)`.

### 6. Tope de paginación — pendiente cuando se agregue

`InventarioController::global`, `DashboardController`, `CostosIndirectosController`, `RemisionesController`, `CotizacionesController` no aceptan `?limit=` hoy. Cuando se les agregue paginación, usar `Cfg::n('max_per_page', 200)`. Tracker para no olvidarlo.

### 7. Versionado de API `/api/v1/` — ❌ ABIERTO

Todas las rutas son `/api/...`. Pendiente cuando aparezca consumidor externo. Refactor masivo de routes + frontend `apiRoutes.js`.

---

## P4 — Optimización / Nice-to-have

### 8. OpenAPI / Swagger — ❌ ABIERTO

Pendiente. Sin urgencia mientras el único cliente sea el frontend propio.

### 9. Soft-deletes en entidades faltantes — ⚠️ REVISADO 2026-05-27, decisión pendiente

Soft-deletes activos en: `clientes`, `proveedor`, `item_general`, `facturas`, `cotizaciones`, `ordenes_compra`, `remisiones`, `item_proveedor`.

**Confirmado hard-delete** (sin `deleted_at`, modelos sin `useSoftDeletes`): `categoria` (singular), `unidad`, `bodegas`, `instalaciones`. Los 4 borran permanentemente. No se tocó schema (decisión del dueño). Si se quieren preservar, agregar `deleted_at` + `useSoftDeletes` por cada uno.

### 10. Cache de configuración en Redis — ❌ ABIERTO

Hoy `Cfg::` cachea per-request (static en PHP). OK mientras la carga sea baja.

---

## P5 — Items residuales detectados en sesión 2026-05-25

### 11. Migraciones con `DROP INDEX IF EXISTS` — 5 archivos pendientes

`2026-05-14-000001_AddSoftDeleteToItemProveedor.php` ya se arregló (usar como referencia). **Falta**: ~5 migraciones más con el mismo patrón — `2026-05-13-000001`, `2026-05-14-000003`, `_000004`, `_000007`, `_000008` y posiblemente otras. Mientras existan, `composer test` falla con 2 errores en `ExampleDatabaseTest`. Fix: aplicar `INFORMATION_SCHEMA.STATISTICS` check + `ALTER TABLE ... DROP INDEX` en cada.

### 12. ~~`tests/README.md` desactualizado~~ — ✅ RESUELTO 2026-05-27

Actualizado con los 8 tests Feature + 1 unit reales + comandos `composer test` / `php spark validar:fixes`.

### 13. ~~Tabla `tambores`~~ — ✅ DROPEADA 2026-05-27 (⚠️ tenía 335 filas)

Migración `2026-05-25-000002_DropTamboresTable` aplicada. ⚠️ **La tabla NO estaba vacía — tenía 335 filas**. Backup en `backups/tambores_pre_drop_2026-05-27.sql`. Confirmar con el dueño si esos datos importaban.

---

## Checklist pre-deploy (consolidado)

> **Producción NO está en agenda inmediata**. Cuando se reabra, todos los items 🚀 de arriba más:

```
🚀 P1 #1 — HTTPS forzado (force_https())
🚀 P1 #2 — Security headers (SecurityHeadersFilter)
🚀 P1 #3 — Credenciales DB de producción
🚀 CORS_ALLOWED_ORIGIN al dominio real (hoy http://localhost:5173)
🚀 JwtFilter sin fallback débil (lanzar excepción como UsuarioController)
🚀 MIME validation real en upload de logo (finfo_file en lugar de mime_content_type)
🚀 backup-auto.sh debe incluir tar.gz de /public/uploads/
🚀 Procedimiento de restore documentado
```

Detalle en `PENDIENTES.md § 🚀 Deploy / Producción`.
