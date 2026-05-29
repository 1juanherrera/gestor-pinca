# PENDIENTES — Backlog del Sistema PINCA

> **Última limpieza 2026-05-29 (tarde)**. Solo quedan items abiertos. Lo resuelto vive como histórico en `CLAUDE.md` (backend y frontend) por sesión. Los detalles técnicos de algunos items están en `MEJORAS.md`.
>
> El bloque **🚀 Deploy / Producción** está separado porque el dueño del proyecto pidió no considerarlo todavía.

---

## 🔴 PRIORIDAD: datos rotos (bloquean el costeo confiable) — carga del CLIENTE

El análisis de integridad de la BD (2026-05-29) reveló que **el costeo del sistema hoy no es confiable, por falta de datos** (no por bugs). En cadena:

- [ ] **81% de materias primas (153/189) sin proveedor vinculado**. Causa raíz de todo lo demás. → cargar proveedores+precios (ver `PREGUNTAS_CLIENTE.md` #13).
- [ ] **`porcentaje` NULL en las 57 fórmulas** (682 filas de ingredientes, 0 con valor). El campo está sin usar. → definir si el costeo usa cantidad o porcentaje, y poblarlo.
- [ ] **60% de fórmulas (34/57) con ingredientes sin precio** → costo subvaluado. Se resuelve al vincular proveedores.
- [ ] **91% de capas activas (90/99) con costo $0** → valuación de inventario irreal. Se corrige con recepciones reales de OC (que setean costo) o `recalcularPromedioPonderado`.
- [ ] **35 item_proveedor huérfanos** (sin `item_general_id`) → vincular o limpiar vía módulo Sincronización.
- [ ] **6 pares de duplicados de catálogo** (BENTOCLAY BP 184, CHEMOSPERSE 77, DIOXIDO SULFATO 2196, EDAPLAN/LANSPERSE, ETANOL 96%, MICROTALC C20) → fusionar con el merge de Sincronización.
- [ ] **4 FKs colgadas**: item_proveedor ids 35-38 apuntan a proveedores inexistentes (8 y 2) — datos de prueba viejos, además con IVA mal (~1.14). → borrar.

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
- [ ] **`JwtFilter` sin fallback débil**. Hoy si `TOKEN_SECRET` está vacío, cae a `'miClaveSuperSecreta'`. Cambiar a `throw new Exception` como ya hace `UsuarioController`.
- [ ] **MIME validation real en upload de logo**. `EmpresaController.php:121` usa `mime_content_type()` (deprecated PHP 9). Reemplazar por `finfo_file(finfo_open(FILEINFO_MIME_TYPE), $abs)`.
- [ ] **`display_errors = 0`** en `production.php` ya está OK (confirmar antes de deploy).

---

## 🎯 Features grandes (DEV — cada una sesión propia)

### Backend

- [ ] **Versionado API `/api/v1/`** — breaking change, refactor masivo (rutas + frontend apiRoutes).
- [ ] **Migrar respuestas de éxito a `ApiResponse`** en controllers comerciales (`Facturas`, `Remisiones`, `PagosCliente`, `NotasCredito`, `Formulaciones`, `Catalogo`, `ItemProveedor`, `Preparaciones`, `Inventario`) — sus respuestas hoy usan shape `{status, message, data}` o `respond([])` plano. Migrar **cambia el contrato del frontend** — requiere coordinación.
- [ ] **Anulación de factura por email al cliente** — requiere infra de email (SMTP/SES/SendGrid).
- [ ] **Cache de configuración en Redis**. Hoy `Cfg::` cachea per-request. Migrar cuando la carga crezca.
- [ ] **RBAC en create/update/cambiarEstado de documentos comerciales** (`Facturas`, `OC`, `Remisiones`, `Cotizaciones`, `Preparaciones`). Hoy un `visor` puede crear facturas, recibir OC y producir. Falta una **matriz rol→acción** definida con el cliente (ver `PREGUNTAS_CLIENTE.md` #21). Ya se gatearon las mutaciones de stock (traspaso/ajuste/remove → operador+; remisiones delete → admin).
- [x] ~~**Fix `php spark validar:fixes`**~~ — ✅ resuelto 2026-05-29 (tarde). Transacción global con rollback garantizado. Ahora es seguro contra la BD real. Flag `--commit` para persistir a propósito.

### Frontend

- [ ] **Notificaciones real-time** (WebSockets/SSE en lugar de polling 30s).
- [ ] **Búsqueda con debounce en drawers grandes** (selects de cliente/bodega/items con 100+ entradas) — pattern uno-a-uno por drawer.
- [ ] **Audit visual de dark mode (verificación final del usuario)**. El audit estático cerró todos los anti-patrones conocidos (backdrops, botones Export, chips translúcidos). Falta la confirmación visual abriendo cada módulo en dark — si aparece algo, es un `text-white` sobre fondo nuevo que se escapó.
- [ ] **Replicar bulk actions** en `CotizacionesTab` y `OrdenesTab` (patrón ya documentado en `FacturasTable.jsx`).
- [ ] **Endpoint backend bulk dedicado** para cambios de estado masivos (`POST /facturas/bulk/cambiar-estado`) — hoy se hacen N requests paralelos con `Promise.allSettled`.
- [ ] **recharts en chunk eager** — `vite.config.js` lo agrupa con lucide-react en `vendor-ui`, así entra en el bundle inicial aunque solo se use en Dashboard/Rentabilidad/CostosProduccion. Sacarlo a su propio chunk lazy.
- [ ] **~30 hooks con rutas hardcodeadas** en vez de `API_ROUTES` (`useBodegas`, `useClientes`, `useCotizaciones`, `useFactura`, `useItem`, etc.). Migrar para que cambios de ruta backend no rompan silenciosamente.
- [ ] **Modal sin focus-trap** (`src/shared/Modal.jsx`) — sin autofocus, Tab escapa al fondo, no restaura foco al cerrar. A11y/teclado.
- [ ] **Ocultar acciones de inventario por rol** — espejo del RBAC backend: el visor ahora recibe 403 en traspaso/ajuste/remove; ocultar esos botones en la UI para que no los vea.

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
- [ ] **`CapasStockPanel`: 3 efectos que llaman callbacks del padre** sin memoizar (los warnings ESLint reales). Riesgo de re-render en cascada si el padre recrea las callbacks. `useCallback` en el padre + incluir en deps.
- [ ] **Smoke-test después de migración de errores**: los controllers que cambiaron shape de error (`{status, error, messages}` → `{ok, msg}`) podrían afectar UX en módulos que inspeccionan `.messages.error`. `apiClient.js` tiene fallback global (toasts OK); revisar handlers específicos. `useCatalogo` ya se arregló.

### Backend (más del análisis 2026-05-29)

- [ ] **JWT con fallback débil** (`JwtFilter.php:26` `?? 'miClaveSuperSecreta'`). Deploy-only pero código vivo. Cambiar a `throw` si `TOKEN_SECRET` vacío (como ya hace `UsuarioController`).
- [ ] **6 modelos sin `$allowedFields`** (`FormulacionesModel`, `PreparacionesModel`, `SincronizacionModel`, `ComparadorModel`, `EmpresaModel`, `InventarioCapasModel`) — mass assignment potencial vía insert directo. Declarar allowedFields o usar arrays explícitos.
- [ ] **`recalcularSaldo` suma pagos sin filtrar anulados** (`FacturasModel.php:58`). Hoy `pagos_cliente` no tiene soft-delete (bajo riesgo); filtrar explícitamente si se agrega anulación de pagos.
- [ ] **`recibirLinea` marca estado con `$this->model->update()` fuera de la transacción** (`OrdenesCompraController.php:446`). Mover dentro del lock para evitar desincronización si el commit falla parcialmente.
- [ ] **`EmpresaController` usa `mime_content_type()`** (deprecado PHP 8.4+). Reemplazar por `finfo_file`.

---

## 📚 Documentación

- [ ] **Ampliar OpenAPI spec** (`public/openapi.yaml`). Hoy cubre 52 de ~100+ endpoints. Faltan: proveedores, clientes, bodegas, instalaciones, unidades, categorías, costos_indirectos, gestiones_cobro, comparador, numeración, auditoría, roles, empresa CRUD.

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
- **Proveedores cargados** (2026-05-29): isGroup (id 32) e isGroup distriatlantico (id 33). Datos faltantes de isGroup (NIT/contacto) y peso real de Caolina pendientes — ver `PREGUNTAS_CLIENTE.md`.
- **Backups**: `pinca_backend/backups/`. Generar uno antes de tocar datos.
- **Tests**:
  - Backend: `docker exec gestor-pinca-app php spark migrate` (limpio) + `php spark validar:fixes` (53/53, **ahora SEGURO** con rollback global) + Feature `vendor/bin/phpunit --filter "InventarioCapasModelTest|NumeracionModelTest|FormulacionesModelTest"` (10/10).
  - Frontend: `npm run test -- --run` (34/34) + `npm run lint` (0 errors) + `npm run build` (~9s).
- **OpenAPI**: `http://localhost:8080/swagger-ui.html` con `persistAuthorization` (mantiene JWT entre recargas).
- **`PREGUNTAS_CLIENTE.md`** (raíz del monorepo): preguntas de negocio que desbloquean el costeo confiable. Las 🔴 son prioridad.
