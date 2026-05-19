# MEJORAS.md — Backend Pinca

> Mejoras identificadas, priorizadas por impacto y riesgo. Actualizado 2026-05-18.

---

## P0 — Bugs / Defectos activos

### 1. `costos_item.costo_unitario` se puede sobrescribir desde Formulaciones

**Archivo**: `app/Models/FormulacionesModel.php` (líneas 996-1001 y 1070-1075)

**Problema**: `crearFormulacion()` y `actualizarFormulacion()` ejecutan `UPDATE costos_item SET costo_unitario = ?` si el payload de un ingrediente incluye `costo_unitario`. Este campo debería ser de solo lectura, calculado exclusivamente por `InventarioCapasModel::recalcularPromedioPonderado()` al recibir OCs.

**Impacto**: Si el frontend (o un cliente API) envía `costo_unitario` en el payload de formulación, se rompe la integridad del promedio ponderado. El costo queda desvinculado de la realidad del inventario.

**Estado actual**: El frontend NO envía este campo, pero la puerta está abierta.

**Fix recomendado**: Eliminar ambos bloques `if (!empty($mp['costo_unitario']))` de `crearFormulacion()` y `actualizarFormulacion()`. Opcionalmente, agregar un log de warning si algún cliente lo intenta.

```php
// ELIMINAR estos bloques (líneas 996-1001 y 1070-1075):
if (!empty($mp['costo_unitario'])) {
    $this->db->query('UPDATE costos_item SET costo_unitario = ? WHERE item_general_id = ?',
        [$mp['costo_unitario'], $mp['materia_prima_id']]);
}
```

---

## P1 — Seguridad (pre-deploy)

### 2. Validación de input con `$this->validate()`

**Archivos**: La mayoría de controllers (`ItemProveedorController`, `FormulacionesController`, `OrdenesCompraController`, etc.)

**Problema**: Los endpoints solo hacen chequeos de "no vacío" (`if (empty(...))`). No hay validación de tipos, rangos, ni formato. Un payload malformado puede causar errores SQL no controlados o datos inconsistentes.

**Fix recomendado**: Agregar `$this->validate([...])` con reglas CI4 en cada endpoint de mutación (POST/PUT/PATCH/DELETE). Priorizar:
- `OrdenesCompraController::recibirLinea` — cantidades, precios (decimal positivo)
- `FormulacionesController::crear/actualizar` — porcentajes (sum ~100), cantidades (positivas)
- `ItemProveedorController::create/update` — precio_unitario (>0), factor_conversion (>0)
- `UsuarioController::crear` — email format, password strength

### 3. `DBDebug: true` en producción

**Archivo**: `app/Config/Database.php`

**Problema**: Si `DBDebug` es `true`, los errores SQL se muestran con stack traces completos, incluyendo queries, nombres de tablas y columnas.

**Fix**: `'DBDebug' => (ENVIRONMENT !== 'production')`

### 4. HTTPS no forzado

**Archivo**: `app/Config/Boot/production.php`

**Problema**: `force_https()` está comentado. En producción, las cookies y tokens JWT viajan en texto plano por HTTP.

**Fix**: Descomentar `force_https()` y configurar el reverse proxy (Nginx/Apache) con certificado SSL.

### 5. Security headers ausentes

**Archivo**: Nuevo middleware o en `CorsFilter`

**Headers faltantes**:
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Strict-Transport-Security: max-age=63072000; includeSubDomains`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`

**Fix**: Crear `SecurityHeadersFilter` y aplicarlo globalmente, o agregar al `CorsFilter::after`.

### 6. Mass assignment en `BaseModel`

**Archivo**: `app/Models/BaseModel.php`

**Problema**: `create_table()` llena `allowedFields` dinámicamente desde `getFieldNames()`, lo que permite que cualquier campo de la tabla sea escrito desde el request.

**Fix**: No auto-generar `allowedFields`. Cada modelo debe declarar explícitamente qué campos acepta (ya lo hacen los modelos que extienden `BaseModel` con `$allowedFields`, pero el método genérico `create_table` los ignora).

### 7. Credenciales de base de datos

**Archivo**: `.env`

**Problema**: Credenciales actuales son `user`/`password` (las de desarrollo). Deben cambiarse antes del deploy.

---

## P2 — Integridad de datos

### 8. Validación de FKs antes de INSERT

**Problema**: No se valida que las FKs existan antes de insertar. Se confía en el constraint de la BD para rechazar, pero el error que devuelve es genérico y no informativo.

**Fix recomendado**: En endpoints críticos (formulaciones, OCs, preparaciones), validar existencia de:
- `item_general_id` existe y no está soft-deleted
- `proveedor_id` existe y está activo
- `bodega_id` existe
- `formulaciones_id` existe y `estado = 1`

### 9. Race conditions en endpoints de mutación

**Estado**: Solo `OrdenesCompraController::recibirLinea` usa `SELECT ... FOR UPDATE`. Otros endpoints que mutan inventario o estados de documentos no tienen protección contra concurrencia.

**Endpoints en riesgo**:
- `PreparacionesModel::create_preparacion` — consumo de capas sin lock
- `FacturasController::cambiarEstado` — transiciones de estado sin verificar estado actual en transacción
- `NumeracionModel::reservar` — ya usa `FOR UPDATE` (OK)

**Fix**: Envolver mutaciones críticas en `transBegin()` + `SELECT ... FOR UPDATE` del registro principal.

### 10. Porcentajes de formulación sin validación backend

**Archivo**: `FormulacionesModel`

**Problema**: `validarSumaPorcentajes` existe pero no se llama en todos los flujos. Verificar que `crearFormulacion`, `actualizarFormulacion` y `clonar` lo invocan.

---

## P3 — Deuda técnica

### 11. Tests

**Estado**: `tests/` vacío. Sin PHPUnit tests.

**Prioridad de tests**:
1. `InventarioCapasModel` — consumo FIFO, promedio ponderado, restauración
2. `OrdenesCompraController::recibirLinea` — conversión de unidades, creación de capas
3. `PreparacionesModel` — consumo por proveedor, costo congelado, rollback
4. `NumeracionModel::reservar` — reset anual, rango DIAN
5. `FormulacionesModel` — CRUD, clonar, validación de porcentajes

### 12. Formato de error inconsistente

**Problema**: Coexisten `{ok: false, msg}`, `{success: false, message}`, `{status: 'error'}` dependiendo del controller.

**Fix**: Estandarizar en `{ok: bool, msg: string, data?: any}` o `{success: bool, message: string, data?: any}`. Crear un trait `ApiResponse` con métodos `successResponse()` y `errorResponse()`.

### 13. Tope de paginación

**Estado**: Solo `AuditoriaController` y `NotificacionModel` tienen tope (`Cfg::n('max_per_page', 200)`). Otros controllers aceptan `?limit=9999`.

**Fix**: Aplicar `min($limit, Cfg::n('max_per_page', 200))` en todos los endpoints que acepten `limit`.

### 14. Sin `.env.example`

Facilita el onboarding. Copiar `.env` quitando valores sensibles y dejando placeholders.

### 15. Sin versionado de API

Todas las rutas son `/api/...`. Si se necesita un breaking change, no hay forma de mantener retrocompatibilidad.

**Fix a futuro**: Prefijo `/api/v1/` para nuevos endpoints. No urgente mientras el único cliente sea el frontend propio.

### 16. `RemisionesController` detalle sin FK a `item_general`

**Problema**: El detalle de remisiones es texto libre — no descuenta stock ni se puede trazar.

**Fix**: Agregar `item_general_id` y `cantidad` al detalle de remisiones, y descontar stock al crear (como hace Preparaciones).

---

## P4 — Optimización / Nice-to-have

### 17. Health check endpoint

`GET /api/health` que retorne `{ status: 'ok', db: bool, timestamp }`. Util para monitoreo y load balancers.

### 18. OpenAPI / Swagger

Documentación auto-generada de la API. Facilita integración con terceros y testing.

### 19. Soft-deletes en entidades faltantes

`item_general` y `categorias` aún no tienen soft-delete (verificar). Las queries raw que los referencian deberían filtrarlo.

### 20. Cache de configuración en Redis

`Cfg::` actualmente cachea por request (estático en PHP). Si la carga crece, mover a Redis con TTL de 5 min evitaría queries por request.

### 21. Endpoint de costos por proveedor optimizado

`GET /formulaciones/{id}/opciones-ingredientes` ejecuta un full scan de `item_proveedor` para luego filtrar por matching. Con formulaciones grandes y muchos proveedores, podría ser lento. Considerar pre-filtrar por `item_general_id IN (...)` de los ingredientes de la formulación.

---

## Checklist rápido pre-deploy

```
□ Fix P0 #1 — Eliminar overwrite de costos_item desde formulaciones
□ Fix P1 #3 — DBDebug = false en producción
□ Fix P1 #4 — HTTPS forzado
□ Fix P1 #5 — Security headers
□ Fix P1 #7 — Credenciales DB de producción
□ Fix P1 #2 — Validación de input (al menos en endpoints críticos)
□ Fix P2 #9 — Race conditions en preparaciones
□ P3 #11 — Al menos tests de InventarioCapasModel y recibirLinea
```
