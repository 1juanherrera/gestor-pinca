#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Backup automático de la BD Pinca.
#
# Hace un dump LIMPIO (sin warnings de mysqldump) + aplica rotación.
# Ejecutar desde el HOST (no desde dentro del contenedor).
#
# Uso manual:
#   ./backup-auto.sh
#
# Uso automatizado:
#
#   Linux (host) — crontab -e:
#     0 3 * * * /ruta/a/pinca_backend/backups/backup-auto.sh >> /var/log/pinca-backup.log 2>&1
#
#   Windows — Task Scheduler:
#     Programa:  wsl
#     Argumentos: bash -c "/mnt/c/Users/PDESARROLLO/Documents/PROYECTO_PINCA/pinca_backend/backups/backup-auto.sh"
#     Trigger:   diario a las 3:00 AM
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

# ── Configuración ────────────────────────────────────────────────────────────
DB_CONTAINER="gestor-pinca-db"
DB_USER="root"
DB_PASS="password"           # ← cambiar antes de deploy real (.env real)
DB_NAME="gestorpincadb"

# Carpeta de destino — se resuelve relativa a este script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_DIR="${SCRIPT_DIR}"

RETENTION_DAYS=30            # backups auto_* más viejos se borran

# ── Ejecución ────────────────────────────────────────────────────────────────
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
OUT_FILE="${BACKUP_DIR}/auto_pinca_${TIMESTAMP}.sql"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Iniciando backup de ${DB_NAME}…"

# El dump corre dentro del contenedor de DB (donde mysqldump SÍ existe).
# Stderr → /dev/null evita que el "Warning: Using a password" se meta al .sql.
docker exec -e MYSQL_PWD="${DB_PASS}" "${DB_CONTAINER}" \
    mysqldump \
        -u"${DB_USER}" \
        --no-tablespaces \
        --single-transaction \
        --routines --triggers --events \
        "${DB_NAME}" \
    2>/dev/null \
    > "${OUT_FILE}"

# Validar tamaño mínimo (un dump sano de esta BD pesa cientos de KB).
SIZE_BYTES=$(stat -c%s "${OUT_FILE}" 2>/dev/null || stat -f%z "${OUT_FILE}")
if [[ "${SIZE_BYTES}" -lt 10000 ]]; then
    echo "ERROR: backup demasiado chico (${SIZE_BYTES} bytes). Algo falló." >&2
    rm -f "${OUT_FILE}"
    exit 1
fi

SIZE_KB=$(( SIZE_BYTES / 1024 ))
echo "  ✓ Backup OK: ${OUT_FILE} (${SIZE_KB} KB)"

# ── Rotación: borrar backups auto_* con más de RETENTION_DAYS ────────────────
DELETED=0
while IFS= read -r -d '' OLD_FILE; do
    rm -f "${OLD_FILE}"
    DELETED=$((DELETED + 1))
done < <(find "${BACKUP_DIR}" -maxdepth 1 -name 'auto_pinca_*.sql' -mtime +"${RETENTION_DAYS}" -print0)

if [[ ${DELETED} -gt 0 ]]; then
    echo "  ✓ Rotación: ${DELETED} backup(s) > ${RETENTION_DAYS} días eliminado(s)"
fi

# ── Reporte de inventario ────────────────────────────────────────────────────
TOTAL_COUNT=$(find "${BACKUP_DIR}" -maxdepth 1 -name 'auto_pinca_*.sql' | wc -l)
TOTAL_MB=$(du -shc "${BACKUP_DIR}"/auto_pinca_*.sql 2>/dev/null | tail -1 | cut -f1)
echo "  ℹ Inventario actual: ${TOTAL_COUNT} backups · ${TOTAL_MB}"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backup terminado."
