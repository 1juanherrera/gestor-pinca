# PENDIENTES — Backlog del Sistema PINCA

> **Última limpieza 2026-05-27**. Solo quedan items abiertos. Lo resuelto vive como histórico en `CLAUDE.md` (backend y frontend) por sesión. Los detalles técnicos de algunos items están en `MEJORAS.md`.
>
> El bloque **🚀 Deploy / Producción** está separado porque el dueño del proyecto pidió no considerarlo todavía.

---

## 🚀 Deploy / Producción (hacer cuando se decida desplegar)

- [ ] **CORS a dominio real**. Hoy hardcoded a `http://localhost:5173` en `.env`. Cambiar a algo tipo `https://app.pinca.com`.
- [ ] **Credenciales DB de producción**. Hoy son `user`/`password` (desarrollo). Generar credenciales fuertes vía variables de entorno o secrets manager.
- [ ] **`.env.example` completo en backend**. Incluir documentación de `TOKEN_SECRET` (debe ser 64 chars hex random — no usar el de dev).
- [ ] **HTTPS forzado**. Descomentar `force_https()` en `app/Config/Boot/production.php`. Configurar Nginx/Apache con certificado SSL.
- [ ] **Security headers**. Crear `SecurityHeadersFilter` o agregar a `CorsFilter::after`:
  - `X-Frame-Options: DENY`
  - `X-Content-Type-Options: nosniff`
  - `Strict-Transport-Security: max-age=63072000; includeSubDomains`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- [ ] **Backup `/uploads/` incluido**. El script `backups/backup-auto.sh` hoy solo respalda la BD. Agregar `tar -czf` de `pinca_backend/public/uploads/` con la misma rotación.
- [ ] **Procedimiento de restore documentado**. README corto explicando cómo restaurar un dump SQL + copiar uploads de vuelta.
- [ ] **`JwtFilter` sin fallback débil**. Hoy si `TOKEN_SECRET` está vacío, cae a `'miClaveSuperSecreta'`. Cambiar a `throw new Exception` como ya hace `UsuarioController`.
- [ ] **MIME validation real en upload de logo**. `EmpresaController.php:121` usa `mime_content_type()` (deprecated PHP 9). Reemplazar por `finfo_file(finfo_open(FILEINFO_MIME_TYPE), $abs)`.
- [ ] **`display_errors = 0`** en `production.php` ya está OK (confirmar antes de deploy).

---

## 🎯 Features grandes (DEV — cada una sesión propia)

### Backend

- [x] ~~**Refresh token + modal "sesión por expirar"**~~ — ✅ 2026-05-27. Endpoint `POST /api/auth/refresh` rotativo (tabla `refresh_tokens`, expiry 7 días) + `SessionExpiryModal` en frontend. Login devuelve `refresh_token`, logout lo revoca.
- [ ] **OpenAPI/Swagger spec** auto-generada o manual con `nelmio/api-doc-bundle` o equivalente.
- [ ] **Versionado API `/api/v1/`** — breaking change, refactor masivo (rutas + frontend apiRoutes).
- [ ] **Unificar shapes de error** — decidir si migrar los ~24 controllers que usan `$this->fail*()` nativo de CI4 (shape `{status, error, messages}`) al shape `{ok, msg}` de `ApiResponse`. **NO es mecánico** — es un cambio de contrato que rompe el frontend, requiere coordinación. Hoy 12 controllers usan `{ok, msg}`, ~24 usan `{status, error, messages}`, 3 usan `{success, message}`. Ver `MEJORAS.md #5`.
- [ ] **Migrar respuestas de éxito a `ApiResponse`** (todos los controllers). Requiere extender el trait con `apiSuccessFlat($data, $msg)` que mergee top-level — sino rompe contrato `{ok, msg, token, usuario}`.
- [ ] **5 migraciones más con `DROP INDEX IF EXISTS`** (`2026-05-13-000001`, `2026-05-14-000003`, `_000004`, `_000007`, `_000008`, etc.). Replicar fix de `2026-05-14-000001` (ya arreglada).
- [ ] **Anulación de factura por email al cliente** — requiere infra de email.
- [ ] **Cache de configuración en Redis**. Hoy `Cfg::` cachea per-request. Migrar cuando la carga crezca.

### Frontend

- [ ] **Dark mode** — design system entero (tokens dark, ~1 semana).
- [ ] **Virtualización** en `MovimientosTable` y `ProduccionTable` con `react-window` (cuando empiecen a doler).
- [ ] **Bulk actions** (selección múltiple + acción batch en Cotizaciones/Facturas/OCs) — necesita UX design.
- [x] ~~**Export Excel** en Cotizaciones y OCs~~ — ✅ 2026-05-27. `ExportCotizacionExcel.js` + `ExportOrdenCompraExcel.js` (fila única o lista filtrada).
- [ ] **Vitest — completar install**. Setup hecho 2026-05-27 (`vitest.config.js` + 2 tests). Falta correr `npm install -D vitest @testing-library/react @testing-library/jest-dom jsdom` y luego `npm run test -- --run`. Ampliar cobertura a flujos críticos (login → crear OC → recibir → producir).
- [ ] **Búsqueda con debounce en drawers grandes** (selects de cliente/bodega/items con 100+ entradas) — pattern uno-a-uno por drawer.
- [ ] **Notificaciones real-time** (WebSockets/SSE en lugar de polling 30s).

---

## 🛠️ Tareas medianas / refinamientos

### Frontend

- [x] ~~**Popovers `FormDate`/`DateRangePicker` viewport mobile**~~ — ✅ 2026-05-27. Clamp contra `window.innerWidth` + 1 mes en `<640px`.
- [ ] **4 errores ESLint restantes** (de 33): `CapasStockPanel:351` (`Date.now()` impuro en render), `FormulacionesTable:189` (memoization skip), `FormCostProducts:226` + `FormulacionModal:317` (2 `setState in effect` reset-on-open — necesitan refactor del padre con `key`). No afectan build/runtime.
- [x] ~~**Migrar `SummaryCard` → `FlowCard`**~~ — ✅ 2026-05-27. 6 archivos migrados. `SummaryCard.jsx` quedó huérfano (borrable en cleanup futuro).
- [x] ~~**Marcar todas como leídas**~~ — ✅ ya existía en `NotificacionesDropdown.jsx` (botón "Leer todas" + `useMarcarTodasLeidas`).
- [x] ~~**Módulos sin sidebar entry**~~ — ✅ verificado 2026-05-27: `/pagos`, `/sincronizacion`, `/configuracion` por URL/links (intencional); `/roles` movido a tab del UserPanel. Todo OK, nada falta.

### Backend

- [ ] **Tope de paginación** cuando se agregue `?limit=` a `InventarioController::global`, `DashboardController`, `CostosIndirectosController`, `RemisionesController`, `CotizacionesController` (hoy ninguno acepta). Recordar usar `Cfg::n('max_per_page', 200)`.
- [x] ~~**Validación de input en Bodegas/Categoría/Unidad create**~~ — ✅ 2026-05-27.
- [ ] **Soft-deletes en entidades faltantes**: `categoria`, `unidad`, `bodegas`, `instalaciones` confirmados hard-delete (verificado 2026-05-27). Decidir si agregar `deleted_at` + `useSoftDeletes`.

---

## 🎨 Decisiones de UX pendientes

- [ ] **Modo de costo en formulaciones**: hoy hay toggle Costo real / Costo lista. Si después de un mes de uso real el usuario rara vez usa "Costo lista", borrar el toggle y dejar solo el real. Si lo usa seguido, mantener.
- [ ] **Simulación pura de prorrateo (caso pre-OC)**. Hoy el flujo de prorrateo está dentro del OrdenDrawer (requiere OC creada). Si el usuario necesita simular antes de comprometerse:
  - Opción 1 (simple): toggle "Solo simular / Confirmar recepción" en `RecibirProrrateoModal`. Si solo simular: no se crea inventario, no se modifica OC, solo se muestran los números.
  - Opción 2 (más involucrada): mini-calculator widget en el header de Compras.
- [ ] **Columna "Último precio" en formulaciones**. Algunos contadores prefieren ver el precio de la última recepción (la capa más reciente) en vez del promedio ponderado. Si se necesita, agregar como columna extra (no reemplazar el principal).

---

## Notas de operación

- **Para retomar trabajo**: revisar la sección más reciente del CLAUDE.md backend y frontend.
- **Backups**: `pinca_backend/backups/`. El más reciente es `gestorpincadb_backup_2026-05-21_post-refactor-fase3.sql`.
- **Tests**: `docker exec gestor-pinca-app php spark validar:fixes` (53/53 PASS) + `vendor/bin/phpunit --filter "InventarioCapasModelTest|NumeracionModelTest|FormulacionesModelTest"` (10/10 PASS).
