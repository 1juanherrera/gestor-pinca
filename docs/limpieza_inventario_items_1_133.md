# Limpieza de inventario — items con id_item_general ≤ 133 (bodega 1)

**Objetivo**: sacar del inventario (no del catálogo) todos los items con `id_item_general` entre 1 y 133 que están en la bodega 1. El item sigue existiendo en el catálogo, solo se elimina su stock actual.

**Estrategia**: cerrar capas activas + borrar filas agregadas de `inventario`. **No** se hace hard-delete de capas porque pueden tener consumos históricos asociados (FK desde `preparacion_consumo_capas`).

---

## ⚠️ Antes de ejecutar

1. **Hacé un backup nuevo** desde phpMyAdmin antes de tocar nada:
   - Export → SQL → guardarlo como `backups/gestorpincadb_pre_limpieza_2026-05-16.sql`.
2. Correr el bloque **PREVIEW** y revisar los números antes de hacer la ejecución.

---

## 1️⃣ PREVIEW — ver cuántos registros se verán afectados

```sql
SELECT 'inventario (filas a borrar)' AS detalle, COUNT(*) AS cantidad
FROM inventario
WHERE item_general_id <= 133 AND bodegas_id = 1
UNION ALL
SELECT 'capas activas a cerrar', COUNT(*)
FROM inventario_capas
WHERE item_general_id <= 133 AND bodegas_id = 1 AND estado = 1
UNION ALL
SELECT 'capas con consumos hist. (se conservan cerradas)', COUNT(DISTINCT pcc.capa_id)
FROM preparacion_consumo_capas pcc
JOIN inventario_capas ic ON ic.id_capa = pcc.capa_id
WHERE ic.item_general_id <= 133 AND ic.bodegas_id = 1
UNION ALL
SELECT 'items afectados (distintos)', COUNT(DISTINCT item_general_id)
FROM inventario
WHERE item_general_id <= 133 AND bodegas_id = 1;
```

---

## 2️⃣ EJECUCIÓN — correr si el preview se ve bien

```sql
START TRANSACTION;

-- A) Cerrar capas activas (preservar histórico de consumo)
UPDATE inventario_capas
SET cantidad_disponible = 0,
    estado              = 0
WHERE item_general_id BETWEEN 1 AND 133
  AND bodegas_id = 1
  AND estado = 1;

-- B) Eliminar filas agregadas de inventario en bodega 1
DELETE FROM inventario
WHERE item_general_id BETWEEN 1 AND 133
  AND bodegas_id = 1;

-- C) Recalcular el costo promedio (queda en 0 porque no hay más capas activas)
UPDATE costos_item ci
SET costo_unitario = COALESCE((
  SELECT SUM(ic.cantidad_disponible * ic.costo_unitario) / NULLIF(SUM(ic.cantidad_disponible), 0)
  FROM inventario_capas ic
  WHERE ic.item_general_id = ci.item_general_id AND ic.estado = 1
), 0)
WHERE ci.item_general_id <= 133;

COMMIT;
```

---

## 3️⃣ Verificación post-ejecución

```sql
SELECT COUNT(*) AS capas_activas_restantes
FROM inventario_capas
WHERE item_general_id <= 133 AND bodegas_id = 1 AND estado = 1;
-- esperado: 0

SELECT COUNT(*) AS filas_inventario_restantes
FROM inventario
WHERE item_general_id <= 133 AND bodegas_id = 1;
-- esperado: 0
```

---

## Qué NO se toca (a propósito)

| Tabla | Por qué se conserva |
|---|---|
| `item_general` | El item sigue en el catálogo (solo se saca del inventario) |
| `movimiento_inventario` | Histórico/audit log intacto |
| `preparacion_consumo_capas` | Trazabilidad de producciones previas |
| `ordenes_compra` + detalles | Documentos históricos sin cambio |
| `item_general_formulaciones` | Las recetas pueden seguir referenciándolos |

---

## Notas operacionales

- Si después se hace una OC que reciba alguno de estos items, las capas se crean de nuevo (frescas) y todo vuelve a funcionar normal. Solo se limpió el stock actual.
- Las capas viejas con `estado = 0` quedan disponibles para auditoría / trazabilidad histórica.
- El `costo_unitario` en `costos_item` queda en 0 hasta la próxima recepción de OC, que lo recalcula automáticamente.
