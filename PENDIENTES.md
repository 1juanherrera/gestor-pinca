# PENDIENTES — Backlog del Sistema PINCA

> Última actualización: 2026-05-21.
> Listado de mejoras pendientes (frontend + backend) ordenadas por prioridad y propósito.
> Cuando se aborde un item, marcarlo como `[X]` o mover a una sección "Resuelto".

---

## 🚀 Deploy / Producción (hacer cuando se decida desplegar)

Estos items **no son urgentes en desarrollo**, pero son **obligatorios antes de salir a producción**.

- [ ] **CORS a dominio real**. Hoy hardcoded a `http://localhost:5173` en `.env`. Cambiar a algo tipo `https://app.pinca.com`.
- [ ] **Credenciales DB de producción**. Hoy son `user`/`password` (desarrollo). Generar credenciales fuertes + setearlas vía variables de entorno o secrets manager.
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
- [ ] **`DBDebug = (ENVIRONMENT !== 'production')`** en `app/Config/Database.php`. Hoy está `true`.
- [ ] **`display_errors = 0`** en `production.php` ya está OK (confirmar antes de deploy).

---

## 🎯 Features funcionales pendientes

### Auth y sesión

- [ ] **Refresh token / modal "sesión por expirar"**. Hoy el JWT dura 8h y cuando expira, el frontend cae al login sin aviso (perdés trabajo). Opciones:
  - Endpoint `POST /api/auth/refresh` con refresh token de 7 días.
  - O modal "Tu sesión expira en 5 min, ¿extender?" 5 minutos antes del exp.
- [ ] **Logout server-side**. Hoy el logout solo borra el JWT del frontend. Agregar invalidación server-side (incrementar `token_version` del usuario, igual que cuando cambia rol/password).

### Forms

- [ ] **Validación blur completa en CotizacionForm y RemisionForm**. Hoy solo el input de "cliente libre" tiene `onBlur`. Migrar los 3 forms grandes a `react-hook-form` con `mode: 'onBlur'`, o aplicar el hook `useFormValidation` (ya disponible) a todos los campos required.
- [ ] **Errores backend por campo en items de tabla**. El hook `useFieldErrors` ya mapea `cliente_id`, pero el backend devuelve también `items.0.cantidad` etc. Habría que modificar `RowInput` para aceptar `error` y mostrar borde rojo.
- [ ] **Búsqueda dentro de drawers grandes**. Los selects de cliente / bodega / items en CotizacionForm, FacturaForm crecen sin filtro cuando hay 100+ entradas. Agregar input de búsqueda con debounce.

### Tablas / listados

- [ ] **Bulk actions**. Selección múltiple + acción batch en Cotizaciones, Facturas, OCs. Útil para cambios de estado masivos.
- [ ] **Virtualización en tablas 100+ filas**. `MovimientosTable` y `ProduccionTable` se pueden volver lentas con muchas filas. Considerar `react-window` cuando empiecen a doler.
- [ ] **Export a Excel** en Cotizaciones y OCs (hoy solo PDF). Si lo pide finanzas.

### Notificaciones

- [ ] **Bell-icon real-time**. Hoy polling cada 30s. Migrar a WebSockets o Server-Sent Events para alertas instantáneas (stock crítico, OCs retrasadas, mora).
- [ ] **Marcar todas como leídas con un click**. Ya existe `/notificaciones/leer-todas` backend; verificar que el bell-icon tenga el botón.

### Otros

- [ ] **Dark mode**. Pinca 2.0 todavía no tiene tokens dark. Sería un trabajo de design system completo.
- [ ] **`document.title` dinámico por ruta**. Ya existe `PageTitle.jsx` que lo hace pero no cubre todas las rutas (faltan algunas como `/sedes`, `/costos-produccion`, `/sincronizacion`).
- [ ] **EmptyStates con CTA clara** en Cotizaciones, OCs vacíos. Hoy dice "Sin datos" — agregar botón "Crear nueva".
- [ ] **Renombrar `FormTexarea.jsx` → `FormTextarea.jsx`** (typo legacy, cosmético).
- [ ] **Migrar `SummaryCard` → `FlowCard`** en módulos que aún lo usan. Cuando se toquen esas páginas.
- [ ] **Touch targets ≥ 44px en ActionMenu**. Mobile/tablet UX.
- [ ] **Popovers FormDate y DateRangePicker** que respeten viewport en mobile (hoy se desbordan).
- [ ] **5 módulos sin sidebar entry** (`/pagos`, `/prorrateo` borrado, `/roles` en UserPanel): documentado en CLAUDE.md como intencional. Verificar si está OK.

---

## 📚 Documentación y testing

- [ ] **OpenAPI / Swagger spec**. Hoy no hay documentación auto-generada de la API. Útil si en el futuro hay integradores externos. Considerar `nelmio/api-doc-bundle` o equivalente en PHP.
- [ ] **Versionado API `/api/v1/`**. Hoy todas las rutas son `/api/...`. Si hay un breaking change, no hay forma de mantener retrocompatibilidad. Prefijo `/v1/` para nuevos endpoints.
- [ ] **PHPUnit tests**. `tests/` está vacío. Prioridad:
  1. `InventarioCapasModel` — consumo FIFO, promedio ponderado, restauración.
  2. `OrdenesCompraController::recibirLinea` y `::recibirLoteProrrateado` — conversión de unidades, creación de capas, atomicidad.
  3. `PreparacionesModel` — consumo por proveedor, costo congelado, rollback.
  4. `NumeracionModel::reservar` — reset anual, rango DIAN.
  5. `FormulacionesModel` — CRUD, clonar, validación de porcentajes.
- [ ] **Frontend tests**. `npm run test` no está configurado. Si se agrega, usar Vitest + testing-library. Prioridad en flujos críticos: login → crear OC → recibir → producir.
- [ ] **README raíz del proyecto**. Hoy hay `CLAUDE.md` en cada repo pero no un README que ate ambos. Crear `/PROYECTO_PINCA/README.md` con: descripción, stack, cómo levantar el stack completo (docker compose + npm install + seed), troubleshooting común.

---

## 🛡️ Hardening adicional (no urgente)

- [ ] **Estandarizar formato de respuesta** en todos los controllers a `{ok, msg, data?, errors?}` usando el trait `ApiResponse` que ya está creado. Hoy coexisten 3 formatos: `{ok, msg}`, `{success, message}`, `{status, message, data}`.
- [ ] **Mass assignment en `BaseModel`**. `BaseModel::create_table()` llena `allowedFields` desde `getFieldNames()` → cualquier campo de la tabla aceptable. Cada modelo debería declarar explícitamente sus `allowedFields`.
- [ ] **Logging de DELETEs en otros controllers** (proveedores, items, formulaciones). Hoy solo los 4 críticos (facturas/clientes/OCs/cotizaciones) lo hacen.
- [ ] **Tope de paginación** en otros endpoints que aún no lo tienen (revisar `InventarioController::global`, `DashboardController`, `CostosIndirectosController`).
- [ ] **`recalcularSaldo` en otros puntos**. Verificar que se llama en TODOS los puntos donde el saldo de una factura puede cambiar: pago, NC, anulación, modificación de detalle. Hoy se llama en pagos, NC y anulación.
- [ ] **`BaseModel::update_table` ya respeta `deleted_at`** (fix de la sesión 2026-05-19). Confirmar antes de borrar este item.
- [ ] **Anulación de factura no notifica al cliente**. Si el cliente ya recibió la factura por email, debería avisarle que se anuló. Email opcional.

---

## 🎨 Decisiones de UX pendientes

- [ ] **Modo de costo en formulaciones**: hoy hay toggle Costo real / Costo lista. Si después de un mes de uso real el usuario rara vez usa "Costo lista", borrar el toggle y dejar solo el real. Si lo usa seguido, mantener.
- [ ] **Simulación pura de prorrateo (caso pre-OC)**. Hoy el flujo de prorrateo está dentro del OrdenDrawer (requiere OC creada). Si el usuario necesita simular antes de comprometerse a una OC, agregar:
  - Opción 1 (más simple): toggle "Solo simular / Confirmar recepción" en `RecibirProrrateoModal`. Si solo simular: no se crea inventario, no se modifica OC, solo se muestran los números.
  - Opción 2 (más involucrada): mini-calculator widget en el header de Compras.
- [ ] **Columna "Último precio" en formulaciones**. Algunos contadores prefieren ver el precio de la última recepción (la capa más reciente) en vez del promedio ponderado. Si se necesita, agregar como columna extra (no reemplazar el principal). Hoy: solo se ve el promedio.

---

## 🧹 Limpieza técnica (cuando haya tiempo)

- [ ] **Errores ESLint pre-existentes** en `FormulacionesPage.jsx` (3 unused vars: `proveedoresFormulacion`, `isLoadingProveedores`, `isLoadingCostosProveedor`) y `FormulacionesTable.jsx` (useMemo en return condicional, setState-in-effect).
- [ ] **Errores ESLint pre-existentes** en `UserPanel.jsx` (línea 508 missing deps, línea 579 setState-in-effect).
- [ ] **Errores ESLint pre-existentes** en `main.jsx` (Fast refresh — `ToastLimiter` componente en mismo archivo que exports no-componente).
- [ ] **Bundle > 500KB**. Hoy `index.js` pesa 1.78 MB (gzip 466 KB). Considerar code-splitting con `import()` dinámico para módulos pesados (Formulaciones, Produccion).
- [ ] **Migración 2026-04-17 que creó la tabla `tambores`** queda en el historial aunque el módulo se eliminó. La tabla sigue en la BD vacía. Si en algún punto se decide limpiar, agregar una migración nueva que la dropee.

---

## ✅ Resuelto en sesión 2026-05-21 (referencia)

Listo de lo que SE hizo este día — no requiere acción, es solo para no confundirse al leer el backlog.

- [x] **Fase 1**: recalcularSaldo en anulación + revertir pagos/NC, FK cliente validada en factura/cotización, FacturasController::cambiarEstado atómico, race condition en consumo capas, 3 archivos renombrados (typo space), Race condition consumo capas con FOR UPDATE.
- [x] **Fase 2**: DELETEs con guard admin, validateJson en Formulaciones e ItemProveedor, setActiveTitle centralizado en Layout, 401 sin flash (Opción B: /auth/me + loader), filtros URL en Tambores, cambio de rol invalida JWT (token_version migración 2026-05-21-000001).
- [x] **Fase 3**: apiClient sin setTimeout, paginación capped vía Cfg, confirm al borrar línea inline en 3 forms, Modal/Drawer respetan `isDirty`, useFieldErrors hook, ApiResponse trait creado.
- [x] **Refactors UX**: Tambores eliminado completo, Roles polish (superadmin column, propio rol bloqueado, confirm en promoción), password_must_change agregado a allowedFields, GlobalTopProgressBar centralizado en Layout, toggle Costo real vs Costo lista en Formulaciones (refactor eliminando useEffect auto-poblar).
- [x] **Prorrateo OC**: recibirLoteProrrateado con atomicidad + FOR UPDATE por línea, modal RecibirProrrateoModal en OrdenDrawer, auto-generación de código de lote `LOT-OC{id}-{Ymd}` con reuso entre líneas de misma OC, endpoint /lote-sugerido para pre-llenar input, formato peso COP en input de precio.
- [x] **Eliminaciones**: /prorrateo standalone (incluyendo lógica de "Crear OC desde aquí"), AnalisisAhorroOC en OC drawer, JOIN extra `precio_lista_actual` en OrdenesCompraModel::detalle (revertido).

---

## Notas de operación

- **Para retomar trabajo**: revisar la sección §20 del frontend CLAUDE.md y la última sección del backend CLAUDE.md (ambas con fecha 2026-05-21).
- **Para ver backups**: `pinca_backend/backups/`. El más reciente es `gestorpincadb_backup_2026-05-21_post-refactor-fase3.sql`.
- **OC de prueba**: la sesión creó OC-018 (id=38) para testing del flujo de prorrateo. Si ya se probó, puede borrarse (estado: Enviada — primero hay que pasarla a Cancelada).
