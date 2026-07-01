# PINCA Backend

REST API para PINCA — ERP de manufactura y comercialización de pinturas.
Maneja catálogo, inventario con capas de costo (FIFO), órdenes de compra,
producción, facturación, cartera, RBAC y auditoría de movimientos.

Construido sobre CodeIgniter 4. No tiene vistas — todas las respuestas son
JSON. El frontend vive en un repo separado (`pinca_frontend`).

## Stack

- PHP 8.1+
- CodeIgniter 4 (^4.0)
- MySQL 8.0
- firebase/php-jwt ^6.11 (JWT)
- PHPUnit 10 (tests)
- Docker / docker-compose para desarrollo

## Setup de desarrollo

Levantar el stack completo (app + MySQL + phpMyAdmin):

```bash
docker-compose up -d
```

Servicios expuestos:

- App: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- MySQL: localhost:3306
  - DB: `gestorpincadb`
  - usuario: `user`
  - password: `password`

Los dumps SQL en `initdb/` se cargan automáticamente la primera vez que arranca
el contenedor de DB.

Si la app levanta sin `vendor/`, ejecutar dentro del contenedor:

```bash
docker exec gestor-pinca-app composer install
```

## Variables de entorno

Copiar `.env.example` a `.env`. Variables críticas:

- `TOKEN_SECRET` — secret para firmar JWT. Generar con `openssl rand -hex 32`.
  El servidor lanza excepción si está vacío o usa el fallback inseguro.
- `app.baseURL`, credenciales DB, `CORS.allowedOrigins`.

## Tests

```bash
composer test                  # PHPUnit (Feature + unit tests en tests/)
```

Validador automatizado de regresiones (47 tests contra la BD real, agrupados en
críticos / importantes / menores / IVA):

```bash
docker exec gestor-pinca-app php spark validar:fixes
```

Útil correr antes de un commit grande.

## Backup de la base de datos

Script host-side que invoca `mysqldump` vía `docker exec`:

```bash
bash backups/backup-auto.sh
```

Genera `backups/auto_pinca_YYYY-MM-DD_HH-MM-SS.sql` y rota los dumps con más
de 30 días. Pensado para correr desde cron o Windows Task Scheduler
(`backups/backup-auto.bat` es el wrapper para Windows que invoca el `.sh` vía
WSL).

## Comandos Spark útiles

```bash
php spark migrate                      # aplicar migraciones
php spark validar:fixes                # 47 tests de regresión
php spark snapshot:costos              # snapshot mensual de costos (cron sugerido)
php spark notificaciones:procesar      # generar notificaciones automáticas
```

## Documentación

Detalles arquitectónicos, decisiones de diseño, historial de sesiones,
contratos de API y pendientes están en:

- `CLAUDE.md` — guía completa para trabajar en el repo (arquitectura, sesiones)
- `MEJORAS.md` — auditoría histórica + roadmap de hardening
- `PENDIENTES.md` — backlog activo
