# MEJORAS.md — Backend Pinca

> Mejoras técnicas identificadas y todavía pendientes. **Última limpieza 2026-05-29**. Items resueltos eliminados de esta lista (su histórico vive en `CLAUDE.md` por sesión). El backlog operativo con checkboxes vive en `PENDIENTES.md`.

---

## P1 — Seguridad (Deploy)

### 1. HTTPS no forzado — 🚀 DEPLOY-ONLY

`force_https()` no aparece en `app/Config/Boot/production.php`. Requerido solo cuando se decida deploy con SSL.

### 2. Security headers ausentes — 🚀 DEPLOY-ONLY

No existe `SecurityHeadersFilter`. Filters actuales: solo `CorsFilter` + `JwtFilter`. Headers a agregar: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Strict-Transport-Security`, `Referrer-Policy`, `Permissions-Policy`.

### 3. Credenciales de base de datos — 🚀 DEPLOY-ONLY

Hoy `user`/`password` (dev). Cambiar antes de deploy a credenciales fuertes vía variables de entorno o secrets manager.

---

## P3 — Deuda técnica abierta

### 4. Formato de respuesta — éxitos en controllers comerciales

**Errores ya unificados** en 33 controllers usando `{ok, msg}` de `ApiResponse` (sesión 2026-05-29).

**Quedan pendientes**:
- Respuestas de éxito en controllers comerciales (`Facturas`, `Remisiones`, `PagosCliente`, `NotasCredito`, `Formulaciones`, `Catalogo`, `ItemProveedor`, `Preparaciones`, `Inventario`) que hoy usan shape `{status, message, data}` o `respond([])` plano. Migrar es un cambio de contrato del frontend — requiere coordinación.
- `PermisosController`, `RequisicionesCompraController`, `DashboardController` usan `{success, message}` (helpers internos del `BaseController`). Distinto contrato, decisión de unificar pendiente.

### 5. Versionado de API `/api/v1/` — ❌ ABIERTO

Todas las rutas son `/api/...`. Pendiente cuando aparezca consumidor externo. Refactor masivo de routes + frontend `apiRoutes.js`.

### 6. `validar:fixes` muta la base real — ✅ RESUELTO 2026-05-29 (tarde)

`run()` ahora abre transacción global + rollback garantizado (`runTests()` + `try/finally`). CI4 anida transacciones → los `reservar()` y tests internos no persisten. Verificado: 2 corridas seguidas, 0 cambios en la BD. Flag `--commit` para persistir a propósito.

---

## P2 — Seguridad de aplicación (del análisis 2026-05-29)

### 7. RBAC en mutaciones — ⚠️ PARCIAL

**Resuelto**: mutaciones de stock gateadas — `InventarioController::traspaso/ajusteManual/removeFromBodega` (operador+, bloquea visor), `RemisionesController::delete` (admin-only).

**Falta**: create/update/cambiarEstado de documentos comerciales (Facturas, OC, Remisiones, Cotizaciones, Preparaciones) — hoy un visor puede crearlos/recibir OC/producir. Necesita matriz rol→acción del cliente (`PREGUNTAS_CLIENTE.md` #21).

### 8. JWT con fallback débil — ❌ ABIERTO (deploy)

`JwtFilter.php:26` `?? 'miClaveSuperSecreta'`. Si `TOKEN_SECRET` no carga, valida tokens con secreto público = bypass de auth. Cambiar a `throw`. Deploy-only pero código vivo.

### 9. Mass assignment en 6 modelos — ✅ RESUELTO 2026-05-30

Los 6 (`FormulacionesModel`, `PreparacionesModel`, `SincronizacionModel`, `ComparadorModel`, `EmpresaModel`, `InventarioCapasModel`) ahora declaran `$allowedFields` con sus columnas reales. Operan con query builder directo, así que la whitelist protege el ActiveRecord sin afectar inserts existentes.

### 10. Consumo MANUAL de capas — ✅ RESUELTO 2026-05-29 (tarde)

`PreparacionesModel::_ajustarInventarioPorPreparacion`: el modo MANUAL no validaba que las capas seleccionadas sumaran la cantidad requerida → costo congelado falso. Ahora valida (tolerancia 0.0001) y lanza Exception, igual que el modo proveedor.

---

## P2.5 — Integridad de datos (carga del cliente, NO código)

Del análisis de la BD 2026-05-29 — **el costeo no es confiable por falta de datos**:
- 81% de MP (153/189) sin proveedor vinculado.
- `porcentaje` NULL en las 57 fórmulas (campo sin usar).
- 60% de fórmulas con ingredientes sin precio, 91% de capas con costo $0.
- 35 item_proveedor huérfanos, 6 pares de duplicados, 4 FKs colgadas (ids 35-38, datos de prueba).

Detalle y plan en `PENDIENTES.md § 🔴 PRIORIDAD` + `PREGUNTAS_CLIENTE.md`.

---

## P4 — Optimización / Nice-to-have

### 7. OpenAPI / Swagger — ⚠️ PARCIAL

Cubierto 52 endpoints (de ~100+) en `public/openapi.yaml`, accesible en `/swagger-ui.html`. Faltan: proveedores, clientes, bodegas, instalaciones, unidades, categorías, costos_indirectos, gestiones_cobro, comparador, numeración, auditoría, roles, empresa CRUD.

### 8. Cache de configuración en Redis — ❌ ABIERTO

Hoy `Cfg::` cachea per-request (static en PHP). OK mientras la carga sea baja.

### 9. Tope de paginación — pendiente cuando se agregue `?limit=`

Los 5 controllers candidatos (`InventarioController::global`, `DashboardController`, `CostosIndirectosController`, `RemisionesController`, `CotizacionesController`) no aceptan `?limit=` hoy. Cuando se les agregue, usar `Cfg::n('max_per_page', 200)`.

### 10. Anulación de factura por email al cliente — ❌ ABIERTO

Requiere infra de email (SMTP/SES/SendGrid).

---

## P5 — Residuales

### 11. Raw queries en modelos básicos sin filtro `deleted_at IS NULL`

Detectadas en sesión 2026-05-29 al agregar soft-deletes a `bodegas/instalaciones/categoria/unidad`. No críticas hoy (no hay registros soft-deleted en esas tablas todavía), pero filtrar cuando alguien empiece a soft-deletar:
- `BodegasModel:29` `SELECT * FROM bodegas WHERE id_bodegas = ?`
- `InstalacionesModel:31/36` SELECTs varios
- `BodegasController:28` JOIN raw

### 12. `migrate:rollback` completo está roto

`DropTamboresTable.down()` falla al recrear `tambores` con FKs incompatibles. Defecto preexistente. No afecta `migrate` forward, solo intentos de rollback masivo.

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
