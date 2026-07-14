# Versión Julio 2026 — Faristol web2

_Actualizado: 2026-07-10_

---

## 1. Lo completado ✅

### Seguridad y configuración
| Tarea | Estado |
|---|---|
| `phpunit.xml` — DB_CONNECTION sqlite descomentado | Hecho |
| `.env.testing` creado aislando tests | Hecho |
| `.env` no trackeado por git — sin riesgo | Verificado |
| Rate limiting en rutas públicas (120 req/min) | Hecho |

### Repositorio y limpieza
| Tarea | Estado |
|---|---|
| Scripts `certbot_testfile.sh` y `traer_visor_web.sh` actualizados | Hecho |
| `public/backend-laravel-run.sh` eliminado, reemplazado por `backend-dev-server.sh` | Hecho |

### Tests
| Tarea | Estado |
|---|---|
| `EnvironmentTest` — aislamiento `.env.testing` | Hecho |
| `SafetyTest` — 14 tests sin BD | Hecho |
| `SafetyAppTest` — 17 tests con app (saltados sin pdo_sqlite) | Hecho |
| **Total: 72 tests** (32 unit + 17 safety + 23 feature) | 32 pasan, 17 saltados, 23 fallan (sin pdo_sqlite) |

### Caché
| Tarea | Estado |
|---|---|
| Comando `artisan cache:clear-pdfs` con TTL y `--dry-run` | Hecho |

### Gestión de requerimientos
| Tarea | Estado |
|---|---|
| `taskwarrior` + `taskwarrior-tui` instalados y configurados | Hecho |
| Checklist migrados a Taskwarrior (55 tareas) | Hecho |
| 6 preguntas urgentes resueltas (superadmin, Apache, API, gráficos, migraciones, setlists) | Hecho |
| `TASKWR_GUIDE.md` creado | Hecho |
| `PLAN_FASE1.md` comiteado | Hecho |
| `PREGUNTAS_PENDIENTES.md` comiteado | Hecho |

---

## 2. Pendiente para Julio — 39 tareas

### Backend — Base de Datos (6 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 1 | Migración: `ensemble_id`, `uploaded_by` y `folder` a `music_scores` | Alta |
| 2 | Crear tabla `ensembles` | Alta |
| 3 | Crear tabla `ensemble_user` | Alta |
| 4 | Crear tabla `rehearsals` | Alta |
| 5 | Crear tabla `ensemble_folders` | Media |

### Backend — Modelos (2 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 6 | Actualizar `MusicScore`: belongsTo Ensemble + User (uploaded_by) | Alta |
| 7 | Endpoint `GET /api/user/ensemble-status` | Alta |

### Backend — API (6 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 8 | `AuthController`: login (con cif opcional), register, logout, me, ensemble-status, refresh | Alta |
| 9 | `EnsembleController` CRUD | Alta |
| 10 | `EnsembleMemberController`: members, join, leave, role | Alta |
| 11 | `RehearsalController` CRUD | Alta |
| 12 | `EnsembleScoreController`: scores privados (music_scores con ensemble_id) | Alta |
| 13 | `EnsembleFolderController`: CRUD carpetas | Media |

### Backend — Storage (2 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 17 | Migrar archivos locales a S3 (ruta `ensembles/{id}/{filename}`) | Baja (Agosto) |
| 18 | Definir endpoint de S3 con CTO | Baja (Agosto) |

### Landing Page (2 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 19 | Vista `home.blade.php` con `HomeController@index` | Alta |
| 20 | Diseño: 2 cards (Faristol App + Control App) | Alta |

### Flutter — Subdirectorios (2 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 21 | Servir `public/visorweb2/` con base href (Apache directo) | Alta |
| 22 | Servir `public/control-app/` con base href (Apache directo) | Alta |

### Flutter — visorweb2 (7 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 23 | Cambiar API base a `web2.faristol.net` | Alta |
| 24 | Menú hamburguesa con ítem Agrupaciones | Media |
| 25 | Pantalla Mis Agrupaciones | Alta |
| 26 | Pantalla Ensayos | Media |
| 27 | Visor Setlist secuencial (atril virtual): avance automático al terminar cada obra | Alta |
| 28 | Premium automático según affiliation | Media |
| 29 | Agregar `agrupacion_folders` en UI | Baja |

### Flutter — Control App (4 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 30 | Proyecto Flutter create | Baja (puede esperar) |
| 31 | Pantalla Login | Baja |
| 32 | Dashboard: sidebar, stats, gráficos | Baja |
| 33 | Conversión a escritorio real (Electron/Windows) | Baja (Agosto) |

### Android — Setlists locales (1 tarea)

| # | Tarea | Prioridad |
|---|---|---|
| 34 | Faristol Android (online): permitir setlists locales/privados | Media |

### QA — Verificación (8 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 35 | Tests producción existentes siguen pasando | Alta |
| 36 | Tests nuevos para API de agrupaciones, affiliations, rehearsals, setlists | Alta |
| 37 | Verificar: partituras privadas NO listables por no miembros | Alta |
| 38 | Verificar: premium automático eleva plan en app | Alta |
| 39 | Verificar: visor secuencial de setlist funciona (avance automático, atril virtual) | Alta |
| 40 | Verificar: landing page carga correctamente | Alta |
| 41 | Verificar: ambas apps Flutter funcionan con base href correcto | Alta |
| 42 | Verificar: no se rompen rutas SEO existentes | Alta |

---

## 3. Decisiones tomadas

| Decisión | Opción elegida |
|---|---|
| Servir Flutter builds | Apache directo (`public/visorweb2/`, `public/control-app/`) |
| Login Control App | API real de web2 (`/api/auth/login` con Sanctum) |
| Gráficos Control App | Datos reales (desde BD) |
| Migración `music_scores` | `php artisan make:migration` (esperar alcance completo) |
| Setlist visor | Visor secuencial (atril virtual) con avance automático. No se genera PDF concatenado. Mix global + privadas con permiso. |
| Superadmin web2 | Ya existe: `superadmin_test@email.com` |

---

## 4. Servicios activos

| Componente | Detalle |
|---|---|
| Apache | web2.faristol.net, SSL Let's Encrypt (vigente hasta oct 2026) |
| PHP | 8.2.25 + FPM |
| BD | MariaDB, DB `web2`, usuario `web2` |
| Chrome headless | `/usr/bin/google-chrome` para PDFs |
| Flutter SDK | 3.44.4 en `/usr/local/flutter/bin/flutter` |
| visorweb2 | Desplegado en `public/web/` |
| Git | `gitlab.com/libraryscores1/DesktopDemoWeb.git` rama `main` |

---

## 5. Para Agosto

- Migración a S3 (ruta `ensembles/{id_agrupacion}/{filename}`)
- Control App como app escritorio real (Electron/Windows)
- Posible: `agrupacion_folders` en UI de visorweb2
