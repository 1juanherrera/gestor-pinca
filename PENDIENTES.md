# PENDIENTES — Backlog del Sistema PINCA

> **Última limpieza 2026-06-05** (marcados los resueltos de las sesiones 05-30 tarde, 06-02 y 06-03). Lo resuelto vive como histórico en `CLAUDE.md` (backend y frontend) por sesión. Los detalles técnicos de algunos items están en `MEJORAS.md`.
>
> El bloque **🚀 Deploy / Producción** está separado porque el dueño del proyecto pidió no considerarlo todavía.

---

## 🔴 PRIORIDAD: datos rotos (bloquean el costeo confiable) — carga del CLIENTE

El análisis de integridad de la BD (2026-05-29) reveló que **el costeo del sistema hoy no es confiable, por falta de datos** (no por bugs). En cadena:

- [ ] **81% de materias primas (153/189) sin proveedor vinculado**. Causa raíz de todo lo demás. → cargar proveedores+precios (ver `PREGUNTAS_CLIENTE.md` #13).
- [ ] **`porcentaje` NULL en las 57 fórmulas** (682 filas de ingredientes, 0 con valor). El campo está sin usar. → definir si el costeo usa cantidad o porcentaje, y poblarlo.
- [ ] **60% de fórmulas (34/57) con ingredientes sin precio** → costo subvaluado. Se resuelve al vincular proveedores.
- [ ] **91% de capas activas (90/99) con costo $0** → valuación de inventario irreal. Se corrige con recepciones reales de OC (que setean costo) o `recalcularPromedioPonderado`.
- [ ] **item_proveedor huérfanos** (sin `item_general_id`) → vincular o limpiar vía Sincronización. Desde 06-02 hay herramienta nueva: **Sugerencias IA** (clusters por identidad química + fusión en lote). Desde 06-03 hay FK real con `ON DELETE SET NULL` — no se generan huérfanos colgados nuevos.
- [x] ~~**6 pares de duplicados de catálogo**~~ — ✅ 2026-05-30 (tarde): mergeados vía SQL (BENTOCLAY, CHEMOSPERSE, DIOXIDO SULFATO, EDAPLAN/LANSPERSE, ETANOL 96%, MICROTALC C20).
- [x] ~~**4 FKs colgadas** (item_proveedor ids 35-38)~~ — ✅ 2026-05-30 (tarde): borradas con su historial. Además 06-03 agregó FKs reales a `item_proveedor` y `item_general_formulaciones` (migración `AddMissingForeignKeys`) → este tipo de corrupción ya no puede repetirse.

> Estos NO se arreglan inventando datos. Las respuestas del cliente (`PREGUNTAS_CLIENTE.md`) los desbloquean.

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
- [x] ~~**`JwtFilter` sin fallback débil**~~ — ✅ resuelto 2026-06-03 (ya no es item de deploy: rechaza con 500 si `TOKEN_SECRET` falta).
- [x] ~~**MIME validation real en upload de logo**~~ — ✅ resuelto 2026-05-30 (`finfo_file`).
- [ ] **`display_errors = 0`** en `production.php` ya está OK (confirmar antes de deploy).

---

## 🎯 Features grandes (DEV — cada una sesión propia)

### Backend

- [ ] **Versionado API `/api/v1/`** — breaking change, refactor masivo (rutas + frontend apiRoutes).
- [ ] **Migrar respuestas de éxito a `ApiResponse`** en controllers comerciales (`Facturas`, `Remisiones`, `PagosCliente`, `NotasCredito`, `Formulaciones`, `Catalogo`, `ItemProveedor`, `Preparaciones`, `Inventario`) — sus respuestas hoy usan shape `{status, message, data}` o `respond([])` plano. Migrar **cambia el contrato del frontend** — requiere coordinación.
- [ ] **Anulación de factura por email al cliente** — requiere infra de email (SMTP/SES/SendGrid).
- [ ] **Cache de configuración en Redis**. Hoy `Cfg::` cachea per-request. Migrar cuando la carga crezca.
- [x] ~~**RBAC en create/update/cambiarEstado de documentos comerciales**~~ — ✅ resuelto en dos partes: 2026-05-30 (tarde) el cliente definió **control por MÓDULO, no por acción** (se quitaron los guards por rol en operación; admin-only solo en config: Auditoría/Configuración/Empresa/Numeración, superadmin en Roles). 2026-06-03 se agregó **`RbacFilter`**: el `visor` es solo-lectura global (403 en cualquier POST/PUT/PATCH/DELETE salvo mi-password/logout). El merge de Sincronización + endpoints IA quedaron admin-only por su impacto en integridad.
- [x] ~~**Fix `php spark validar:fixes`**~~ — ✅ resuelto 2026-05-29 (tarde). Transacción global con rollback garantizado. Ahora es seguro contra la BD real. Flag `--commit` para persistir a propósito.

### Frontend

- [ ] **Notificaciones real-time** (WebSockets/SSE en lugar de polling 30s).
- [x] ~~**Búsqueda con debounce en drawers grandes**~~ — ✅ 2026-05-30: SearchSelect de Cotizacion/RemisionForm debouncea ~200ms. (selects de cliente/bodega/items con 100+ entradas) — pattern uno-a-uno por drawer.
- [ ] **Audit visual de dark mode (verificación final del usuario)**. El audit estático cerró todos los anti-patrones conocidos (backdrops, botones Export, chips translúcidos). Falta la confirmación visual abriendo cada módulo en dark — si aparece algo, es un `text-white` sobre fondo nuevo que se escapó.
- [x] ~~**Replicar bulk actions** en CotizacionesTab y OrdenesTab~~ — ✅ 2026-05-30. (patrón ya documentado en `FacturasTable.jsx`).
- [x] ~~**Endpoint bulk de facturas**~~ — ✅ 2026-05-30: `POST /facturas/bulk/cambiar-estado`. Falta migrar el frontend de Facturas a usarlo (hoy N requests). (`POST /facturas/bulk/cambiar-estado`) — hoy se hacen N requests paralelos con `Promise.allSettled`.
- [x] ~~**recharts en chunk eager**~~ — ✅ 2026-05-30: `vendor-charts` propio, vendor-ui 426→44 KB. — `vite.config.js` lo agrupa con lucide-react en `vendor-ui`, así entra en el bundle inicial aunque solo se use en Dashboard/Rentabilidad/CostosProduccion. Sacarlo a su propio chunk lazy.
- [ ] **~15 hooks con rutas hardcodeadas restantes** (Cartera, Configuracion, Movimientos, Auditoria, etc.). 2026-05-30 se migraron 9 de alto tráfico a API_ROUTES. en vez de `API_ROUTES` (`useBodegas`, `useClientes`, `useCotizaciones`, `useFactura`, `useItem`, etc.). Migrar para que cambios de ruta backend no rompan silenciosamente.
- [x] ~~**Modal sin focus-trap**~~ — ✅ 2026-05-30: focus-trap en Modal.jsx y Drawer.jsx. (`src/shared/Modal.jsx`) — sin autofocus, Tab escapa al fondo, no restaura foco al cerrar. A11y/teclado.
- [x] ~~**Ocultar acciones de inventario por rol**~~ — ⚠️ REVERTIDO 2026-05-30 (tarde) por decisión del cliente (control por módulo, no por rol). Cubierto desde 2026-06-03 por `RbacFilter` backend: el visor recibe 403 con mensaje claro ("Tu rol es de solo lectura") en cualquier mutación — la UI no oculta botones.

---

## 🛠️ Tareas medianas

### Backend

- [ ] **Tope de paginación** cuando se agregue `?limit=` a `InventarioController::global`, `DashboardController`, `CostosIndirectosController`, `RemisionesController`, `CotizacionesController` (hoy ninguno acepta). Recordar usar `Cfg::n('max_per_page', 200)`.
- [ ] **Raw queries en modelos básicos sin filtro `deleted_at IS NULL`** (post soft-deletes 2026-05-29):
  - `BodegasModel:29` `SELECT * FROM bodegas WHERE id_bodegas = ?`
  - `InstalacionesModel:31/36` SELECTs varios
  - `BodegasController:28` JOIN raw

  No críticos hoy (no hay soft-deletes en esas tablas todavía), pero filtrar cuando alguien empiece a soft-deletar.

### Frontend

- [ ] **Sort en headers de tablas virtualizadas** (`react-window` v2 modo virtual). Rare path (>200 filas), no urgente.
- [x] ~~**CapasStockPanel: callbacks del padre sin memoizar**~~ — ✅ 2026-05-30: useCallback en preparationModal + deps en effects. sin memoizar (los warnings ESLint reales). Riesgo de re-render en cascada si el padre recrea las callbacks. `useCallback` en el padre + incluir en deps.
- [ ] **Smoke-test después de migración de errores**: los controllers que cambiaron shape de error (`{status, error, messages}` → `{ok, msg}`) podrían afectar UX en módulos que inspeccionan `.messages.error`. `apiClient.js` tiene fallback global (toasts OK); revisar handlers específicos. `useCatalogo` ya se arregló.

### Backend (más del análisis 2026-05-29)

- [x] ~~**JWT con fallback débil**~~ — ✅ 2026-06-03: `JwtFilter` rechaza con 500 + log critical si `TOKEN_SECRET` está vacío o es el valor por defecto. Ya no existe el fallback `'miClaveSuperSecreta'`.
- [x] ~~**6 modelos sin `$allowedFields`**~~ — ✅ 2026-05-30: declarados en los 6. (`FormulacionesModel`, `PreparacionesModel`, `SincronizacionModel`, `ComparadorModel`, `EmpresaModel`, `InventarioCapasModel`) — mass assignment potencial vía insert directo. Declarar allowedFields o usar arrays explícitos.
- [ ] **`recalcularSaldo` suma pagos sin filtrar anulados** (`FacturasModel.php:58`). Hoy `pagos_cliente` no tiene soft-delete (bajo riesgo); filtrar explícitamente si se agrega anulación de pagos.
- [x] ~~**`recibirLinea` update fuera de transacción**~~ — ✅ 2026-05-30: movido dentro del bloque transaccional. (`OrdenesCompraController.php:446`). Mover dentro del lock para evitar desincronización si el commit falla parcialmente.
- [x] ~~**`EmpresaController` mime_content_type deprecado**~~ — ✅ 2026-05-30: finfo_file. (deprecado PHP 8.4+). Reemplazar por `finfo_file`.

---

## 📚 Documentación

- [ ] **Ampliar OpenAPI spec** (`public/openapi.yaml`). Hoy cubre 52 de ~100+ endpoints. Faltan: proveedores, clientes, bodegas, instalaciones, unidades, categorías, costos_indirectos, gestiones_cobro, comparador, numeración, auditoría, roles, empresa CRUD.

---

## 🎨 Decisiones de UX pendientes

- [ ] **Modo de costo en formulaciones**: hoy hay toggle Costo real / Costo lista. Si después de un mes de uso real el usuario rara vez usa "Costo lista", borrar el toggle y dejar solo el real. Si lo usa seguido, mantener.
- [x] ~~**Simulación pura de prorrateo (caso pre-OC)**~~ — ✅ 2026-05-30 (tarde): `CalculadoraProrrateo.jsx` — simulación pura sin OC, abierta desde el header de Compras (la "Opción 2").
- [x] ~~**Columna "Último precio" en formulaciones**~~ — ✅ 2026-05-30 (tarde): backend devuelve `ultimo_precio` por ingrediente (capa activa más reciente, sin N+1) y `FormulacionesTable` lo muestra como columna extra.

---

## Notas de operación

- **Para retomar trabajo**: revisar la sección más reciente del CLAUDE.md backend y frontend.
- **Proveedores cargados** (2026-05-29): isGroup (id 32) e isGroup distriatlantico (id 33). Datos faltantes de isGroup (NIT/contacto) y peso real de Caolina pendientes — ver `PREGUNTAS_CLIENTE.md`.
- **Backups**: `pinca_backend/backups/`. Generar uno antes de tocar datos.
- **Tests**:
  - Backend: `docker exec gestor-pinca-app php spark migrate` (limpio) + `php spark validar:fixes` (53/53, **ahora SEGURO** con rollback global) + Feature `vendor/bin/phpunit --filter "InventarioCapasModelTest|NumeracionModelTest|FormulacionesModelTest"` (10/10).
  - Frontend: `npm run test -- --run` (34/34) + `npm run lint` (0 errors) + `npm run build` (~9s).
- **OpenAPI**: `http://localhost:8080/swagger-ui.html` con `persistAuthorization` (mantiene JWT entre recargas).
- **`PREGUNTAS_CLIENTE.md`** (raíz del monorepo): preguntas de negocio que desbloquean el costeo confiable. Las 🔴 son prioridad.
