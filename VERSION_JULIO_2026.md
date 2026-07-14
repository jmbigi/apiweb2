# Versión Julio 2026 — Faristol web2

_Actualizado: 2026-07-14_

---

## 1. Lo completado ✅

### Documentación y planificación
| Tarea | Estado |
|---|---|
| REQUERIMIENTOS.md — 19 RFs documentados | Hecho |
| PLAN_FASE1.md — plan detallado de Fase 1 | Hecho |
| PLAN_IMPLEMENTACION.md — implementación técnica | Hecho |
| DETALLES_TECNICOS.md — referencia técnica | Hecho |
| VERSION_JULIO_2026.md — tracker de tareas | Hecho |
| AGENTS.MD — reglas para IA | Hecho |
| RIESGOS.md — análisis de riesgos | Hecho |
| FINANZAS.md — datos económicos | Hecho |
| COMPARATIVA.md — evolución reqs | Hecho |
| ANTECEDENTES_PRELIMINARES.md — historial previo | Hecho |

### Setup de web2
| Tarea | Estado |
|---|---|
| Limpiar basura (cache/, sitemaps, SEO) | Hecho |
| Copiar API producción → web2 | Hecho |
| composer install + npm install | Hecho |
| .env configurado (DB web2, AWS_BUCKET=faristol-web2) | Hecho |
| Migraciones existentes aplicadas | Hecho |

---

## 2. Pendiente para Julio — tareas

### Backend — Base de Datos (5 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 1 | Migración: `ensemble_id`, `uploaded_by` y `ensemble_folder_id` a `music_scores` | Alta |
| 2 | Crear tabla `ensembles` | Alta |
| 3 | Crear tabla `ensemble_user` | Alta |
| 4 | Crear tabla `rehearsals` | Alta |
| 5 | Crear tabla `ensemble_folders` | Media |

### Backend — Modelos (2 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 6 | Actualizar `MusicScore`: belongsTo Ensemble + User (uploaded_by) | Alta |
| 7 | Crear modelo `Ensemble`, `EnsembleUser`, `EnsembleFolder`, `Rehearsal` | Alta |

### Backend — API (6 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 8 | `AuthController`: login con cif opcional, register, logout, me, ensemble-status, refresh | Alta |
| 9 | `EnsembleController` CRUD | Alta |
| 10 | `EnsembleMemberController`: members, join, leave, role | Alta |
| 11 | `RehearsalController` CRUD | Alta |
| 12 | `EnsembleScoreController`: scores privados (music_scores con ensemble_id) | Alta |
| 13 | `EnsembleFolderController`: CRUD carpetas | Media |

### Landing Page (1 tarea)

| # | Tarea | Prioridad |
|---|---|---|
| 14 | Vista `home.blade.php` con 2 cards (Faristol App + Control App) | Alta |

### Flutter — Subdirectorios (2 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 15 | Servir `public/visorweb2/` con base href `/visorweb2/` | Alta |
| 16 | Servir `public/control-app/` con base href `/control-app/` | Alta |

### Flutter — visorweb2 (7 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 17 | Cambiar API base a `web2.faristol.net` | Alta |
| 18 | Menú hamburguesa con ítem Ensembles | Media |
| 19 | Pantalla My Ensembles | Alta |
| 20 | Pantalla Rehearsals | Media |
| 21 | Visor Setlist secuencial (atril virtual) | Alta |
| 22 | Premium automático según affiliation | Media |
| 23 | Ensemble folders en UI | Baja |

### Flutter — Control App (4 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 24 | Proyecto Flutter create | Baja |
| 25 | Pantalla Login | Baja |
| 26 | Dashboard: sidebar, stats, gráficos | Baja |
| 27 | Conversión a escritorio real (Electron/Windows) | Baja (Agosto) |

### QA — Verificación (6 tareas)

| # | Tarea | Prioridad |
|---|---|---|
| 28 | Tests API de production siguen pasando | Alta |
| 29 | Tests nuevos para API de ensembles | Alta |
| 30 | Verificar: partituras privadas NO visibles por no miembros | Alta |
| 31 | Verificar: premium automático eleva plan en app | Alta |
| 32 | Verificar: visor secuencial de setlist funciona | Alta |
| 33 | Verificar: landing page + ambas apps Flutter funcionan | Alta |

---

## 3. Decisiones tomadas

| Decisión | Opción elegida |
|---|---|
| web2 parte de producción | Copia de `~/apps_prod/API_Faristol` |
| Landing page | `/` con 2 cards de enlace |
| Servir Flutter builds | Apache directo (`public/visorweb2/`, `public/control-app/`) |
| Login Control App | API real de web2 (`/api/auth/login` con Sanctum + CIF) |
| Bucket S3 | `faristol-web2` (mismo disco `s3` que producción) |
| SEO | No necesario para prototipo |
| Setlist visor | Visor secuencial (atril virtual) con avance automático. Sin backend. |
| Roles ensemble | `ensemble_user.role` (archivero, administrador, maestro, usuario) |

---

## 4. Servicios activos

| Componente | Detalle |
|---|---|
| Apache | web2.faristol.net, SSL Let's Encrypt |
| PHP | 8.2.25 + FPM |
| BD | MariaDB, DB `web2`, usuario `web2` |
| Chrome headless | `/usr/bin/google-chrome` para PDFs |
| Flutter SDK | 3.44.4 en `/usr/local/flutter/bin/flutter` |
| visorweb2 | Pendiente de rebuild con base href `/visorweb2/` |
| Git | `gitlab.com/libraryscores1/DesktopDemoWeb.git` rama `main` |
