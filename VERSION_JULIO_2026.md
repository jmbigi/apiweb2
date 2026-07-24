# Versión Julio 2026 — Faristol web2

_Actualizado: 2026-07-23_

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
| PREGUNTAS_PENDIENTES.md — 6 preguntas resueltas | Hecho |

### Setup de web2
| Tarea | Estado |
|---|---|
| Limpiar basura (cache/, sitemaps, SEO) | Hecho |
| Copiar API producción → web2 | Hecho |
| composer install + npm install | Hecho |
| .env configurado (DB web2, AWS_BUCKET=faristol-web2, Wasabi S3) | Hecho |
| Migraciones existentes aplicadas | Hecho |

### Backend — Base de Datos
| # | Tarea | Estado |
|---|---|---|
| 1 | Migración: `ensemble_id`, `uploaded_by`, `ensemble_folder_id` a `music_scores` | ✅ |
| 2 | Crear tabla `ensembles` | ✅ |
| 3 | Crear tabla `ensemble_user` | ✅ |
| 4 | Crear tabla `rehearsals` | ✅ |
| 5 | Crear tabla `ensemble_folders` | ✅ |
| — | Migración roles español→inglés (`2026_07_23_164029`) | ✅ |

### Backend — Modelos
| # | Tarea | Estado |
|---|---|---|
| 6 | Actualizar `MusicScore`: belongsTo Ensemble + User (uploaded_by) | ✅ |
| 7 | Crear modelo `Ensemble`, `EnsembleUser`, `EnsembleFolder`, `Rehearsal` | ✅ |
| — | Scope `publicOrAccessible()` en MusicScore | ✅ |

### Backend — API
| # | Tarea | Estado |
|---|---|---|
| 8 | `AuthController`: login con cif opcional | ✅ |
| 9 | `EnsembleController` CRUD (index, store, show, update, destroy) | ✅ |
| 10 | Members CRUD (add, update, remove) | ✅ |
| 11 | Rehearsals CRUD (store, update, destroy) | ✅ |
| 12 | Scores CRUD (list, store) | ✅ |
| 13 | Folders CRUD (store, update, destroy) | ✅ |
| — | Bulk upload scores (multi-PDF) | ✅ |
| — | User lookup by email (para invitar miembros) | ✅ |
| — | ensemble-status endpoint | ✅ |
| — | my-ensembles endpoint | ✅ |
| — | Restricción upload por rol (admin/archivist/teacher) | ✅ |

### Landing Page
| # | Tarea | Estado |
|---|---|---|
| 14 | Vista `home.blade.php` con 3 cards (Faristol App, Control App, Faristol Admin) | ✅ |

### Flutter — Subdirectorios
| # | Tarea | Estado |
|---|---|---|
| 15 | Servir `public/visorweb2/` con base href `/visorweb2/` | ✅ |
| 16 | Servir `public/control-app/` con base href `/control-app/` | ✅ |

### Flutter — visorweb2 (Fase A-E previa + extras)
| # | Tarea | Estado |
|---|---|---|
| 17 | Cambiar API base a `web2.faristol.net` | ✅ |
| 18 | Menú hamburguesa con ítem Ensembles + Favorites + Offline Scores | ✅ |
| 19 | Pantalla My Ensembles | ✅ |
| 20 | Pantalla Ensemble Detail (rehearsals solo lectura + scores + upload) | ✅ |
| 21 | Visor Setlist (atril virtual + player + reorder) | ✅ |
| 22 | Premium automático (is_ensemble_member flag) | ✅ |
| — | Botón "Add to setlist" en music score detail | ✅ |
| — | Search (A), Upload (B), My Scores (C), Composer Request (D) | ✅ |
| — | Setlist→PDF conexión real (fetch pdfId vía API) | ✅ |
| — | Favoritos (lista + API add/remove) | ✅ |
| — | Offline Scores (PdfStoreService + save botón en visor) | ✅ |
| — | Ensemble upload con selector de carpeta | ✅ |

### Flutter — Control App
| # | Tarea | Estado |
|---|---|---|
| 24 | Proyecto Flutter create | ✅ |
| 25 | Pantalla Login (con CIF opcional) | ✅ |
| 26 | Dashboard: sidebar, stats cards, gráficos | ✅ |
| — | Miembros CRUD (añadir/editar/eliminar) | ✅ |
| — | Repertorio: listado partituras + filtrar por carpeta | ✅ |
| — | Carpetas CRUD (crear/renombrar/eliminar) | ✅ |
| — | Subida masiva de PDFs (multi-file → Wasabi) | ✅ |
| — | Ensayos CRUD (crear/editar/eliminar) | ✅ |
| — | Roles actualizados a inglés (admin, archivist, teacher, member) | ✅ |

### S3 / Wasabi
| Tarea | Estado |
|---|---|
| Wasabi S3 configurado (bucket `faristol-web2`) | ✅ |
| Endpoint: `https://s3.us-west-1.wasabisys.com/` | ✅ |

### QA
| # | Tarea | Estado |
|---|---|---|
| 33 | Landing page + ambas apps Flutter responden 200 | ✅ |
| 34 | 155 tests Feature para API (SQLite en memoria) | ✅ |
| 35 | Partituras privadas NO visibles por no miembros | ✅ |
| 36 | Premium automático: is_ensemble_member flag verificado | ✅ |
| 37 | Visor secuencial setlist: lista, reorder, play, prev/next | ✅ |
| 38 | Prevención de duplicados en Add to setlist | ✅ |
| 39 | Landing page: target=_blank en apps, 3ra card Faristol Admin | ✅ |
| — | 76 tests Flutter visorweb2 + 93 tests control-app | ✅ |
| — | 14 checks E2E nuevas features (favoritos, offline, ensemble) | ✅ |

---

## 2. Pendientes

| # | Área | Tarea | Prioridad |
|---|---|---|---|
| 27 | Control App | Conversión a escritorio real (Electron/Windows) | Pendiente 15 Agosto. Demo web funcional |

---

## 3. Decisiones tomadas

| Decisión | Opción elegida |
|---|---|
| web2 parte de producción | Copia de `~/apps_prod/API_Faristol` |
| Landing page | `/` con 3 cards: Faristol App + Control App (+ `target=_blank`) y Faristol Admin (`/dashboard`) |
| Servir Flutter builds | Apache directo (`public/visorweb2/`, `public/control-app/`) — no ruta Laravel |
| Login Control App | API real de web2 (`/api/auth/login` con Sanctum + CIF) |
| Bucket S3 | `faristol-web2` (disco `Wasabi` en filesystems.php) |
| SEO | No necesario para prototipo |
| Setlist visor | Visor secuencial con avance automático. Sin backend. |
| Roles ensemble | Inglés: `admin`, `archivist`, `teacher`, `member`. Migrados desde español. |
| Ensemble CRUD | Solo superadmin desde backoffice (no en Control App) |
| Rehearsals en visorweb2 | Solo lectura — se gestionan desde Control App |
| Members en visorweb2 | No visible — privacidad. Solo rol propio mostrado. |
| Subida partituras ensemble | Admin/Archivist/Teacher pueden subir con selector de carpeta |
| Visor PDF visorweb2 | Solo usuarios logueados. Solo partituras de compositor interno Faristol (owner_id=109). Las partituras de ensemble sí visibles para miembros. |
| Migración Android 15 Ago | Eliminar restricción `owner_id=109` del visor PDF. Mantener resto de lógica. |
| Servir subdirectorios Flutter | Apache directo (`public/visorweb2/`, `public/control-app/`) |

---

## 4. Servicios activos

| Componente | Detalle |
|---|---|
| Apache | web2.faristol.net, SSL Let's Encrypt |
| PHP | 8.2.25 + FPM |
| BD | MariaDB, DB `web2`, usuario `web2` |
| Chrome headless | `/usr/bin/google-chrome` para PDFs |
| Flutter SDK | 3.44.7 en `/usr/local/flutter/bin/flutter` |
| Tesseract OCR | 4.1.1 (eng+spa) — tests visuales |
| Python3 | 3.12 + Pillow 12.3.0 + pytesseract 0.3.13 |
| visorweb2 | Build en `public/visorweb2/` — funcionando |
| control-app | Build en `public/control-app/` — funcionando |
| Git (apiweb2) | `github.com/jmbigi/apiweb2.git` rama `main` |
| Git (visorweb2) | `github.com/jmbigi/visorweb2.git` rama `main` |
| Git (DesktopDemoWeb) | `gitlab.com/jmbigi/desktop-demo-web.git` rama `master` |
