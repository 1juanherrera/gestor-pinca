@echo off
REM ─────────────────────────────────────────────────────────────────────────
REM  Backup automático de la BD Pinca (wrapper para Windows).
REM  Ejecuta el script bash via WSL.
REM
REM  Uso manual: doble click en este archivo.
REM
REM  Uso automatizado — Task Scheduler:
REM    Programa: wsl.exe
REM    Argumentos: bash -c "/mnt/c/Users/PDESARROLLO/Documents/PROYECTO_PINCA/pinca_backend/backups/backup-auto.sh"
REM    Trigger: diario a las 3:00 AM
REM ─────────────────────────────────────────────────────────────────────────

wsl bash -c "/mnt/c/Users/PDESARROLLO/Documents/PROYECTO_PINCA/pinca_backend/backups/backup-auto.sh"

if %ERRORLEVEL% NEQ 0 (
    echo Error en el backup. Codigo: %ERRORLEVEL%
    exit /b %ERRORLEVEL%
)

echo Backup completado correctamente.
