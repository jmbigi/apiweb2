# Detalles Técnicos — Faristol: Actualización para Agrupaciones

> Documento técnico de referencia basado en el código fuente existente
> y los requerimientos definidos en Julio 2026.
>
> ⚠️ Sin modificar: no altera ningún archivo de los proyectos existentes.

---

## 1. Stack Tecnológico Actual

### 1.1. Backend (API Faristol)

| Componente | Tecnología | Versión |
|-----------|-----------|---------|
| Framework | Laravel | ^10.0 |
| PHP | PHP | ^8.1 |
| Base de datos | MySQL / MariaDB | — |
| API Auth | Laravel Sanctum | ^3.2 |
| Roles/Permisos | Laratrust | ^7.2 |
| Almacenamiento | Wasabi (S3-compatible) | eu-west-2 |
| Archivos | Polymorphic `files_s3_s` | — |
| Admin panel | Blade + Metronic theme | — |
| DataTables | Yajra DataTables | ^10.8 |
| Paypal | srmklive/paypal | ^3.0 |
| PDF | pdftk + spatie/pdf-to-image | — |

### 1.2. Frontend Móvil (Faristol App)

| Componente | Tecnología | Versión |
|-----------|-----------|---------|
| Framework | Flutter | >=3.24.3 |
| Lenguaje | Dart | >=3.5.3 |
| Estado | GetX (get) | ^4.6.6 |
| PDF | pdfx | ^2.6.0 |
| Almacenamiento local | Hive + SharedPreferences | — |
| HTTP | http package | ^1.1.0 |
| Charts | Syncfusion Flutter Charts | ^30.1.42 |
| Ads | Google Mobile Ads | ^6.0.0 |
| WebView | webview_flutter | ^4.8.0 |

### 1.3. Admin Web (Blade)

Panel de administración con Metronic theme, accesible vía web.
CRUD completo para: usuarios, compositores, partituras, instrumentos,
estilos musicales, familias de instrumentos, planes de suscripción.

### 1.4. Servidores

| Propósito | Hosting | Dominio |
|-----------|---------|---------|
| API + Admin web | DigitalOcean / Contabo | ios.faristol.net |
| Web (WordPress) | DigitalOcean / Hostinger | faristol.net |
| **Prototipo web** | **DigitalOcean** | **web2.faristol.net** |
| Almacenamiento | Wasabi (S3) | s3.eu-west-2.wasabisys.com |

**Especificaciones del servidor web2:**

| Componente | Detalle |
|-----------|---------|
| URL | `https://web2.faristol.net` |
| IP | 134.209.224.219 (mismo servidor que producción) |
| Apache | Puerto 80 + SSL (Let's Encrypt) |
| PHP | **8.2.25** FPM (`/var/run/php/php8.2-fpm.sock`) |
| Laravel | **10.48.23** |
| Base de datos | MariaDB 10.3, DB `web2`, usuario `web2` |
| Flutter SDK | 3.44.4 en `/usr/local/flutter/bin/flutter` |
| Git remote | `git@gitlab.com:libraryscores1/DesktopDemoWeb.git` (rama `main`) |
| Ruta | `/var/www/web2.faristol.net` |
| Usuarios en BD | 558 (copia de producción) |

---

## 2. Base de Datos — Estructura Existente

### 2.1. Tablas principales

| Tabla | Propósito |
|-------|-----------|
| `users` | Usuarios (con soft deletes, campo `status` para suspensión) |
| `roles` / `permissions` | Laratrust — roles y permisos |
| `role_user` / `permission_user` / `permission_role` | Pivotes Laratrust |
| `music_scores` | Partituras (relacionado a compositores, instrumentos, estilos) |
| `files_s3_s` | Almacenamiento polimórfico de archivos (Wasabi S3) |
| `composers` | Compositores |
| `instruments` / `family_instruments` / `style_musics` | Catálogos musicales |
| `subscription_plan` / `subscribed_user` | Planes y suscripciones |
| `premium_trials` | Trials gratuitos |
| `order` / `webhook_log` | Pagos PayPal |
| `log_display_music_scores` / `log_view_music_score_details` | Logs de visualización |
| `log_display_personal_scores` | Logs de partituras personales |
| `fav_music_score` | Favoritos |
| `link_infos` | Enlaces externos de partituras |
| `composer_request` / `composer_status` / `request_status` | Solicitudes de compositor |
| `personal_access_tokens` | Tokens Sanctum |

### 2.2. Convenciones existentes

- Tablas: **snake_case** plural (`music_scores`, `subscription_plan`)
- Modelos Laravel: **PascalCase** (`MusicScore.php`, `SubscriptionPlan`)
- Pivotes: prefijo `fk_` para relaciones muchos-a-muchos (`fk_music_score_composer`)
- Foreign keys: `snake_case_id` (`user_id`, `music_scores_id`)
- Timestamps: `created_at`, `updated_at` (y `deleted_at` para soft deletes)
- Fillable: mass-assignment protection vía `$fillable` array

---

## 3. Roles y Permisos Existentes (Laratrust)

### 3.1. Roles actuales

| Rol | Descripción | Permisos clave |
|-----|-------------|----------------|
| `superadmin` | Acceso total | Todos los permisos |
| `editorial` | Gestión de partituras | CRUD partituras, instrumentos, estilos |
| `composer` | Compositor | Mismos que editorial |
| `musician` | Músico (rol por defecto) | Visualizar, anotar, sugerir |

### 3.2. Permisos existentes (21)

```
create_music_score, edit_music_score, get_list_music_score,
get_list_myMusicScores, get_music_score_info, get_music_score_file,
get_report_music_score, anotate_music_score, edit_data_profile,
get_mydata_profile, solicitate_composer_role, suggest_instrument,
get_list_instruments, get_instrument, suggest_family_instrument,
suggest_style_music, get_list_style_music, get_style_music,
suggest_composer, get_list_composer, get_composer,
delete-profile, read-profile, update-profile
```

### 3.3. Roles nuevos propuestos para Agrupaciones

Los roles de agrupación no se implementan como roles Laratrust globales,
sino como **roles dentro de cada agrupación** almacenados en la tabla
`ensemble_user` con un campo `role`.

| Rol en agrupación | Descripción |
|-------------------|-------------|
| `archivero` | Subir/actualizar partituras del repositorio privado |
| `administrador` | Gestionar miembros, roles, subir partituras |
| `maestro` | Subir partituras y planificar ensayos |
| `usuario` | Consultar partituras y ensayos (rol por defecto) |

El **superadmin** global (Laratrust) es el administrador de Biblioscores/Faristol
que crea y gestiona las agrupaciones. Este rol ya existe.

---

## 4. API — Endpoints Existentes

### 4.1. Públicos (sin autenticación)

```
GET  /api/music-score/list
GET  /api/music-score/get/{id}
GET  /api/music-score/list-filtered
GET  /api/music-score/allmusic
GET  /api/composer/list
GET  /api/instruments/list
GET  /api/style-music/list
POST /api/auth/login                    → Login. Body: { email, password, cif? }
                                           cif opcional: si se envía, valida membresía activa del ensemble
POST /api/auth/user/signup
```

**Nota:** Los endpoints `GET /api/music-score/list`, `/list-filtered`, `/allmusic` deben aplicar el scope
`publicOrAccessible()` para no exponer partituras privadas de ensembles a usuarios no miembros.

### 4.2. Autenticados (middleware: `auth:sanctum`, `check_active`)

```
GET    /api/auth/user/check-user
GET    /api/auth/user/check-subscription
POST   /api/auth/user/edit/{id}
POST   /api/music-score/create
POST   /api/music-score/update/{id}
DELETE /api/music-score/delete/{id}
GET    /api/music-score/fav-music-score
GET    /api/music-score/user-fav-music-score
POST   /api/subscription/subscribed-user
GET    /api/subscription/subscription-plans
POST   /api/inapp-subscription/sync-subscribe
POST   /api/analytics/log-personal-file-view
```

### 4.3. Nuevos endpoints propuestos (para Agrupaciones)

```
GET    /api/ensembles/list                    → Listar agrupaciones del usuario
GET    /api/ensembles/{id}                    → Ver detalle de agrupación
POST   /api/ensembles/create                  → Crear agrupación (solo superadmin)
POST   /api/ensembles/update/{id}             → Actualizar agrupación
DELETE /api/ensembles/delete/{id}             → Eliminar agrupación
POST   /api/ensembles/{id}/members           → Gestionar miembros
GET    /api/ensembles/{id}/members           → Listar miembros
POST   /api/ensembles/{id}/scores            → Subir partitura al repositorio privado
GET    /api/ensembles/{id}/scores            → Listar partituras del repositorio privado
GET    /api/ensembles/{id}/scores/{scoreId}  → Ver partitura privada
DELETE /api/ensembles/{id}/scores/{scoreId}  → Eliminar partitura privada
GET    /api/ensembles/{id}/rehearsals         → Listar ensayos
POST   /api/ensembles/{id}/rehearsals/create  → Crear ensayo
```

*Los endpoints exactos se definirán durante el desarrollo.*

---

## 5. Nuevas Tablas Propuestas

Siguiendo las convenciones del código existente (snake_case plural):

### 5.1. `ensembles`

| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigint, PK | |
| name | string, unique | Nombre de la agrupación |
| cif | string(20), unique, not null | CIF/NIF de la agrupación (requerido para login Control App) |
| description | text, nullable | |
| owner_id | bigint, FK->users | Superadmin que la creó |
| status | boolean, default:1 | Activa/inactiva |
| created_at / updated_at | timestamps | |
| deleted_at | timestamp, nullable | Soft deletes |

### 5.2. `ensemble_user` (pivote con rol)

| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigint, PK | |
| ensemble_id | bigint, FK->ensembles | |
| user_id | bigint, FK->users | |
| role | string, nullable | archivero, administrador, maestro, usuario |
| invited_at | timestamp, nullable | |
| joined_at | timestamp, nullable | |
| status | boolean, default:1 | Miembro activo/inactivo |
| created_at / updated_at | timestamps | |

**Unique:** (`ensemble_id`, `user_id`)

### 5.3. `music_scores` — columnas añadidas

| Columna | Tipo | Notas |
|---------|------|-------|
| ensemble_id | bigint, FK->ensembles, nullable | null = catálogo global, not null = partitura privada del ensemble |
| uploaded_by | bigint, FK->users, nullable | Quién subió la partitura |
| ensemble_folder_id | bigint, FK->ensemble_folders, nullable | Carpeta dentro del repositorio del ensemble |

### 5.4. `rehearsals`

| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigint, PK | |
| ensemble_id | bigint, FK->ensembles | |
| title | string | Título del ensayo |
| date | date | Fecha del ensayo |
| time | time, nullable | Hora |
| location | string, nullable | Lugar |
| instructor_id | bigint, FK->users, nullable | Maestro/instructor |
| notes | text, nullable | Notas adicionales |
| status | boolean, default:1 | |
| created_at / updated_at | timestamps | |

### 5.5. `ensemble_folders`

| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigint, PK | |
| ensemble_id | bigint, FK->ensembles | |
| name | string | Nombre de la carpeta |
| parent_id | bigint, FK->ensemble_folders, nullable | Auto-referencia para subcarpetas |
| created_at / updated_at | timestamps | |

**Validación:** Toda la ruta concatenada (`storage/app/music_scores/ensembles/{id}/carpeta1/carpeta2/...`) no debe exceder 4096 caracteres (PATH_MAX de Linux).

### 5.6. `setlists` (local en app móvil)

El Setlist es una funcionalidad **local** que no requiere tabla en backend.
Los datos se almacenan en el dispositivo mediante **Hive** (ya implementado en la app).
No hay sincronización con servidor.

---

## 6. Modelos Laravel Propuestos

Siguiendo la convención existente (PascalCase, snake_case table names):

| Modelo | Tabla | Relaciones clave |
|--------|-------|-----------------|
| `Ensemble` | `ensembles` | belongsToMany(User via ensemble_user), hasMany(MusicScore via ensemble_id), hasMany(Rehearsal), hasMany(EnsembleFolder) |
| `EnsembleUser` | `ensemble_user` | belongsTo(Ensemble), belongsTo(User) |
| `EnsembleFolder` | `ensemble_folders` | belongsTo(Ensemble), hasMany(EnsembleFolder as children), belongsTo(EnsembleFolder as parent) |
| `Rehearsal` | `rehearsals` | belongsTo(Ensemble), belongsTo(User as instructor) |
| `MusicScore` (actualizar) | `music_scores` | belongsTo(Ensemble, nullable via ensemble_id), belongsTo(User as uploader) |

No se requiere modelo para Setlist (es local).

---

## 7. Estructura de la App Flutter

### 7.1. Directorios relevantes existentes

```
lib/src/
  caches/                           ← Caché local (Hive)
  model/                            ← Modelos de datos (38 archivos)
  model_controller/                 ← Controladores de modelos
  network/                          ← API (api_constants.dart, api_service.dart)
  services/                         ← Servicios (offline, suscripciones, PDF)
  stores/                           ← Almacenamiento local (Hive)
  views/                            ← Pantallas
    main/                           ← Pantalla principal (menú lateral)
    pdfviewer/                      ← Visor de PDF (con editor de anotaciones)
    offline_scores/                 ← Partituras personales/offline
    searches/                       ← Búsqueda
    subscriptionplan/               ← Planes de suscripción
  widgets/                          ← Widgets reutilizables
```

### 7.2. Modificaciones previstas

| Componente | Cambio |
|-----------|--------|
| Menú lateral izquierdo | Añadir opción "Ensembles" |
| `views/main/` | Nueva pantalla de listado de agrupaciones |
| `views/pdfviewer/` | Modo Setlist: avance automático entre partituras |
| `network/api_constants.dart` | Nuevos endpoints de agrupaciones |
| `services/offline_scores_service.dart` | Integración con repositorio privado |

---

## 8. Suscripciones y Premium

### 8.1. Modelo actual

| Plan | Tipo | Precio | Características |
|------|------|--------|-----------------|
| Free | `FREE` (0) | 0€ | Anuncios, sin favoritos, 5 anotaciones máximo |
| Basic | `BASIC` (1) | 2,99€/mes | Sin anuncios, favoritos, 15 anotaciones |
| Premium | `PREMIUM` (2) | 5,99€/mes | Sin anuncios, favoritos, anotaciones ilimitadas |

### 8.2. Premium automático para miembros de agrupación

Los miembros de una agrupación se consideran **como si fueran Premium**
mientras sean miembros activos. No se crea un registro en `subscribed_user`;
es un upgrade lógico verificado por la existencia de un registro activo
en `ensemble_user` con `status = 1`.

**Regla:** si `user` tiene `ensemble_user.status = 1` → tratar como Premium.
Si abandona o es removido → pierde beneficios.

### 8.3. Pago de agrupaciones

Las agrupaciones pagan por **transferencia bancaria** fuera de la app.
El superadmin activa/desactiva manualmente (`ensembles.status`).
No hay Stripe ni PayPal para agrupaciones.

---

## 9. Almacenamiento de Archivos

### 9.1. Sistema actual

Las partituras se almacenan en **Wasabi** (S3-compatible) vía la tabla
polimórfica `files_s3_s` con `storagePlace = 'Wasabi'`.

### 9.2. Partituras de agrupaciones

Las partituras del repositorio privado de agrupaciones se almacenan
en el mismo Wasabi, mismo bucket que las públicas (el S3 no distingue
público/privado). El control de acceso se hace en la aplicación (scope
`publicOrAccessible()` en `MusicScore`, filtra por `ensemble_id`).
El archivo físico usa la misma estructura que las partituras existentes.
No se usa disco `s3` separado ni carpeta `ensembles/` en S3.

### 9.3. Setlist

Los Setlists son **locales** (no se almacenan en servidor).
Las partituras referenciadas ya existen en el dispositivo (Personal/Offline).

---

## 10. Presupuesto de Horas

El proyecto completo está presupuestado en **100 horas totales**.

| Actividad | Horas | Detalle |
|-----------|-------|---------|
| Prototipo web (web2.faristol.net) | 15h | Setup + flujos funcionales para validación |
| Backend API | 20h | Migraciones, modelos, endpoints CRUD |
| Control App (Flutter Windows) | 15h | Nueva app solo con dependencias necesarias |
| Faristol App (Android/iOS) | 5h | Menú Ensembles, filtro, ensayos, Setlist local |
| Reuniones, coordinación | 8h | Coordinación con Biblioscores/Faristol |
| Pruebas y correcciones (APK) | 12h | Pruebas en dispositivos reales |
| Distribuciones y builds | 10h | Builds Android, iOS, Windows |
| Publicación en tiendas | 8h | Google Play + App Store |
| Margen / imprevistos | 7h | Desviaciones o tareas no contempladas |
| **Total** | **100h** | |

## 11. Timeline de Desarrollo

| Fase | Duración | Horas | Actividad |
|------|----------|-------|-----------|
| **1. Prototipo Web** | Jul–Ago 2026 | 20h | Prototipo funcional en web2.faristol.net (BD web2). Validación con Biblioscores. |
| **2. Desarrollo Web + App** | ~1 semana | 40h | Partiendo del prototipo validado, desarrollo web definitivo + actualización app Flutter. |
| **3. APK pruebas** | ~2 días | 10h | Versión APK para pruebas internas. Corrección de errores. |
| **4. Distribuciones** | ~3 días | 10h | Builds finales (Android, iOS, Windows). Assets y metadata. |
| **5. Publicación tiendas** | ~3 días | 5h | Revisión y aprobación en Google Play y App Store. |
| **Gestión** | — | 15h | Reuniones (10h) + margen (5h). |

**Total desde aprobación del prototipo:** ~2–3 semanas hasta publicación, 100h totales.

---

## 11. Prototipado Web Previo (Julio–Agosto 2026)

Antes del desarrollo definitivo, se construirá un **prototipo web totalmente
funcional y completo** (no una maqueta) para probar y refinar todas las
funcionalidades nuevas.

### 10.1. Especificaciones del prototipo

- **URL:** `web2.faristol.net`
- **Base de datos:** `web2` — copia reciente de la base de producción
  (ligeramente anterior). Independiente de la base de producción activa.
- **Funcionalidad:** debe ser completa y operativa, con datos reales para
  validar todos los flujos de ambas aplicaciones:
  - Control App (escritorio): gestión, roles, ensayos
  - Faristol App (móvil): navegación, ensembles, setlist
- **Regla fundamental:** toda funcionalidad implementada en el prototipo debe
  estar **técnica y prácticamente resuelta desde el día 1** para funcionar
  en la versión final. No se incluirán características que no sean factibles
  de llevar a producción. El prototipo valida, no experimenta con ideas
  inviables.
- **Transición a producción:** debe ser **muy rápida (~3 días)**. Todo debe
  quedar investigado y validado durante la fase de prototipo.
- El prototipo es **temporal** y se presentará a Biblioscores/Faristol para
  obtener feedback antes del desarrollo definitivo.

---

## 11. Consideraciones de Desarrollo

### 10.1. Convenciones a respetar

- Seguir el mismo patrón de código del proyecto existente
- Usar GetX para estado (ya implementado en la app)
- Respetar nombres de tablas en snake_case plural
- Usar Laratrust para roles globales (superadmin)
- Roles de agrupación en `ensemble_user.role`
- Soft deletes donde aplique
- Timestamps estándar (`created_at`, `updated_at`)

### 10.2. Checklist de implementación

- [ ] Migraciones: `ensembles`, `ensemble_user`, `ensemble_score`, `rehearsals`
- [ ] Modelos: `Ensemble`, `EnsembleUser`, `EnsembleScore`, `Rehearsal`
- [ ] API endpoints CRUD para agrupaciones
- [ ] Verificación de premium lógico en endpoint de suscripción
- [ ] Menú "Ensembles" en app Flutter (menú lateral izquierdo)
- [ ] Listado de agrupaciones del usuario
- [ ] Repositorio privado (subir/ver partituras)
- [ ] Listado de ensayos
- [ ] Setlist local (visor secuencial, sin backend)
- [ ] Control App (Flutter Windows 11, independiente)

---

> Documento generado a partir del análisis del código fuente existente
> en `/home/kubuntu/Documentos/Faristol/` y `/home/kubuntu/OneDrive/Trabajo/Faristol/`.
> No modifica ningún archivo de los proyectos existentes.
