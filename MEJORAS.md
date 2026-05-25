# MEJORAS.md — Backend Pinca

> Mejoras técnicas identificadas y todavía pendientes. **Última limpieza 2026-05-25 (tarde)** — items resueltos eliminados de esta lista (su histórico vive en `CLAUDE.md` por sesión). El backlog operativo con checkboxes vive en `PENDIENTES.md`.

---

## P1 — Seguridad (Deploy)

### 1. HTTPS no forzado — 🚀 DEPLOY-ONLY

`force_https()` no aparece en `app/Config/Boot/production.php` ni siquiera comentado. **Requerido solo cuando se decida deploy con SSL**.

### 2. Security headers ausentes — 🚀 DEPLOY-ONLY

No existe `SecurityHeadersFilter`. Filters actuales: solo `CorsFilter` + `JwtFilter`. Headers a agregar: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Strict-Transport-Security: max-age=63072000; includeSubDomains`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: camera=(), microphone=(), geolocation=()`.

### 3. Credenciales de base de datos — 🚀 DEPLOY-ONLY

Hoy `user`/`password` (dev). Cambiar antes de deploy a credenciales fuertes vía variables de entorno o secrets manager.

---

## P2 — Integridad de datos (residual)

### 4. Validación de input — faltantes de baja superficie

Controllers sin validación nueva todavía: `BodegasController::create`, `CategoriaController::create`, `UnidadController::create`. No urgente — son endpoints administrativos de baja superficie.

---

## P3 — Deuda técnica abierta

### 5. Formato de error inconsistente — 29 controllers sin `ApiResponse`

10 controllers ya adoptaron `ApiResponse` para errores (`UsuarioController`, `OrdenesCompraController`, `FacturasController`, `CotizacionesController`, `RemisionesController`, `PreparacionesController`, `FormulacionesController`, `ItemProveedorController`, `CatalogoController`, `InventarioController`).

**Falta**:
- 29 controllers de tráfico medio/bajo (Cartera, Notificaciones, Auditoría, Configuración, Numeración, Empresa, Salud, CostosProduccion, Trazabilidad, Sincronizacion, Search, Comparador, GestionesCobro, etc.).
- `PermisosController` requiere decisión aparte — usa shape `{success, message}` distinto al `{ok, msg}` de `ApiResponse`. Migrar rompería contrato.
- **Respuestas de éxito en TODOS los controllers** (siguen con shape top-level `{ok, msg, ...datos}`). El método `apiSuccess($data, $msg)` mete todo en `data` — incompatible. Pendiente: extender trait con `apiSuccessFlat($data, $msg)` que mergee top-level.

### 6. Tope de paginación — pendiente cuando se agregue

`InventarioController::global`, `DashboardController`, `CostosIndirectosController`, `RemisionesController`, `CotizacionesController` no aceptan `?limit=` hoy. Cuando se les agregue paginación, usar `Cfg::n('max_per_page', 200)`. Tracker para no olvidarlo.

### 7. Versionado de API `/api/v1/` — ❌ ABIERTO

Todas las rutas son `/api/...`. Pendiente cuando aparezca consumidor externo. Refactor masivo de routes + frontend `apiRoutes.js`.

---

## P4 — Optimización / Nice-to-have

### 8. OpenAPI / Swagger — ❌ ABIERTO

Pendiente. Sin urgencia mientras el único cliente sea el frontend propio.

### 9. Soft-deletes en entidades faltantes — ⚠️ REVISAR

Soft-deletes activos en: `clientes`, `proveedor`, `item_general`, `facturas`, `cotizaciones`, `ordenes_compra`, `remisiones`, `item_proveedor`. Verificar si `categorias`, `unidad`, `bodegas`, `instalaciones` lo necesitan.

### 10. Cache de configuración en Redis — ❌ ABIERTO

Hoy `Cfg::` cachea per-request (static en PHP). OK mientras la carga sea baja.

---

## P5 — Items residuales detectados en sesión 2026-05-25

### 11. Migraciones con `DROP INDEX IF EXISTS` — 6 archivos pendientes

`2026-05-14-000001_AddSoftDeleteToItemProveedor.php` ya se arregló (usar como referencia). **Falta**: 6 migraciones más con el mismo patrón — `2026-05-13-000001`, `2026-05-14-000003`, `_000004`, `_000007`, `_000008` y posiblemente otras. Mientras existan, `composer test` falla con 2 errores en `ExampleDatabaseTest`. Fix: aplicar `INFORMATION_SCHEMA.STATISTICS` check + `ALTER TABLE ... DROP INDEX` en cada.

### 12. `tests/README.md` desactualizado

Sigue diciendo "vacío" cuando ya hay 8 tests Feature + 1 unit. Actualizar para listar lo presente.

### 13. Tabla `tambores` en BD vacía

Migración 2026-04-17 creó la tabla; el módulo se eliminó pero la tabla quedó. Si se decide limpiar, crear migración que la dropee.

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
