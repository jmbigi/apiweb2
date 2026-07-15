# apiweb2

API Backend / BackOffice para el ecosistema Faristol.

Este repositorio contiene el backend Laravel que sirve como API para las dos aplicaciones demo web:

- **[visorweb2](https://github.com/jmbigi/visorweb2)** — Faristol App Mobile (demo web pública/privada)
- **[DesktopDemoWeb](https://gitlab.com/libraryscores1/DesktopDemoWeb)** — Control App (gestión de agrupaciones)

## Stack

- Laravel 10
- MySQL / MariaDB
- Laratrust (roles y permisos)
- Laravel Sanctum (autenticación API)
- Wasabi S3 (almacenamiento de partituras)
- Metronic (panel admin Blade)

## Documentación

| Archivo | Descripción |
|---------|-------------|
| `REQUERIMIENTOS.md` | 19 requerimientos funcionales (RF-01 a RF-19) |
| `DETALLES_TECNICOS.md` | Stack, DB, API endpoints y arquitectura |
| `PLAN_FASE1.md` | Plan de ejecución Jul-Ago 2026 |
| `PLAN_IMPLEMENTACION.md` | Implementación cambios mínimos |
| `RIESGOS.md` | Análisis de riesgos (R1-R14) |
| `FINANZAS.md` | Presupuesto y costos |
| `CHECKLIST.md` | Estado de tareas y verificación |
| `AGENTS.MD` | Reglas para asistentes AI |
| `VERSION_JULIO_2026.md` | Estado actual del proyecto |

## Setup local

```bash
composer install
cp .env.example .env   # configurar DB y credenciales
php artisan key:generate
php artisan migrate
php artisan serve
```

## Dominio

`web2.faristol.net` — prototipo funcional con:
- `/` Landing page con acceso a ambas apps
- `/visorweb2/` Faristol App (Flutter web)
- `/control-app/` Control App (Flutter web)
- `/api/*` Backend REST
