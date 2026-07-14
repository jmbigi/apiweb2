# PLAN FASE 1 — Faristol web2 (Julio-Agosto 2026)

## Apps en web2.faristol.net

```
web2.faristol.net/
├── /                          → Landing page: "Faristol App" y "Control App"
├── /visorweb2/                → Faristol App (con soporte Agrupaciones)
├── /control-app/              → Control App demo (gestión escritorio)
└── /api/*                     → Backend REST (Laravel Sanctum)
```

---

## 1. Backend — Base de Datos y Modelos

### Migraciones

**Tabla `music_scores` — migración sobre tabla existente:**
- `ensemble_id` (nullable FK → `ensembles.id`)
- `uploaded_by` (FK → `users.id`)
- `ensemble_folder_id` (nullable FK → `ensemble_folders.id`)

**Nuevas tablas:**

| Tabla | Columnas |
|-------|----------|
| `ensembles` | id, name, cif (unique), description, owner_id (FK users), status, timestamps, soft_deletes |
| `ensemble_user` | id, ensemble_id, user_id, role (archivero/admin/instructor/basic), status, timestamps |
| `rehearsals` | id, ensemble_id, title, date, time, location, instructor_id (FK users), notes, status, timestamps |
| `ensemble_folders` | id, ensemble_id, name, parent_id (nullable self-ref), timestamps |

**Validación:** Al crear/renombrar carpeta, verificar que `longitud(ruta_completa) ≤ 4096` (PATH_MAX). Ruta: `storage/app/music_scores/ensembles/{id}/{carpeta1}/{carpeta2}/...`

### Modelos Eloquent

- `Ensemble` — belongsToMany User via ensemble_user, hasMany MusicScore, hasMany Rehearsal, hasMany EnsembleFolder
- `EnsembleUser` — belongsTo Ensemble, belongsTo User (pivote con role)
- `Rehearsal` — belongsTo Ensemble, belongsTo User (instructor_id)
- `MusicScore` (actualizar) — nullable belongsTo Ensemble, belongsTo User (uploaded_by)
- `EnsembleFolder` — belongsTo Ensemble, self-ref parent_id

_Setlist es local (Hive en el dispositivo), no requiere modelo backend._

### Almacenamiento de archivos

- Mismo sistema que app Android: subida directa a Wasabi S3.
- **El S3 no distingue público/privado.** El control de acceso es en la app (Laravel), no en el almacenamiento.
- Mismo bucket que producción (`AWS_BUCKET` en `.env`). Sin carpeta `ensembles/` separada.
- No se crea disco nuevo en `filesystems.php`; se reutiliza el disco `s3` existente.

### Control de acceso en listados de partituras

Los endpoints de listado/búsqueda (`GET /api/music-score/list`, `/list-filtered`, `/allmusic`, etc.) deben filtrar según permisos:

| Usuario | Scores visibles |
|---------|----------------|
| No autenticado | Solo `WHERE ensemble_id IS NULL` (catálogo público) |
| Autenticado + miembro de ensembles | Públicos `UNION` privados de sus ensembles donde `ensemble_user.status = 1` |
| Autenticado sin ensembles | Solo públicos |

Implementar como **scope global** en `MusicScore` o **scope local** `publicOrAccessible(User)` aplicado en cada endpoint de listado.

### Premium automático (no backend)

- Backend expone endpoint `GET /api/user/ensemble-status` → `{ has_active_ensemble: bool, ensembles[] }`
- La app **visorweb2 (Flutter)** eleva el plan a Premium en memoria si el usuario tiene membresía activa en un ensemble
- No hay cambios en lógica de suscripciones/pagos en backend

---

## 2. Backend — API Routes (`routes/api.php`)

Servidas por Laravel. Middleware global: CORS + `throttle:api`.

### AuthController (Sanctum token)
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/auth/login` | Login (email, password) → token. Si envía `cif`, valida membresía activa del ensemble |
| POST | `/api/auth/register` | Register |
| POST | `/api/auth/logout` | Revocar token |
| GET | `/api/user/me` | Perfil usuario autenticado |
| GET | `/api/user/ensemble-status` | `{ has_active_ensemble, ensembles[] }` |
| POST | `/api/auth/refresh` | Refresh token |

### EnsembleController
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/api/ensembles` | auth | Mis ensembles |
| POST | `/api/ensembles` | superadmin | Crear ensemble |
| GET | `/api/ensembles/{id}` | auth | Detalle (solo miembros) |
| PUT | `/api/ensembles/{id}` | superadmin | Actualizar |
| DELETE | `/api/ensembles/{id}` | superadmin | Soft delete |

### EnsembleMemberController
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/api/ensembles/{id}/members` | auth | Lista miembros |
| POST | `/api/ensembles/{id}/join` | auth | Unirse (admin invita) |
| DELETE | `/api/ensembles/{id}/leave` | auth | Abandonar |
| PUT | `/api/ensembles/{id}/members/{userId}/role` | admin | Asignar rol |

### RehearsalController
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/api/ensembles/{id}/rehearsals` | auth | Lista (todos los miembros) |
| POST | `/api/ensembles/{id}/rehearsals` | admin/instructor | Crear |
| PUT | `/api/rehearsals/{id}` | admin/instructor | Actualizar |
| DELETE | `/api/rehearsals/{id}` | admin/instructor | Eliminar |

### EnsembleScoreController (sobre `music_scores` filtrado por `ensemble_id`)
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/api/ensembles/{id}/scores` | member | Listar partituras privadas |
| POST | `/api/ensembles/{id}/scores` | member | Subir PDF (roles con permiso) |
| DELETE | `/api/ensembles/{id}/scores/{scoreId}` | member | Eliminar |

_Setlist es local (Hive en el dispositivo) — no requiere controller backend. El visor secuencial se implementa en Flutter._

---

## 3. Landing Page

- Nueva vista `resources/views/home.blade.php`
- Renderizada por `HomeController@index`
- 2 cards:
  - **"Faristol App"** → enlace a `/visorweb2/`
  - **"Control App"** → enlace a `/control-app/`
- Rutas SEO existentes se mantienen intactas: `/score/{name}`, `/sitemap`, list, stats, etc.
- Diseño: fondo `#0C1934`, logo Faristol, estilo coherente con el actual

---

## 4. Subdirectorios Flutter (servidos vía Laravel)

- `public/visorweb2/` — build de visorweb2 con `<base href="/visorweb2/">`
- `public/control-app/` — build de control_app con `<base href="/control-app/">`
- Ruta Laravel para servir el `index.html` de cada subdirectorio
- Assets estáticos servidos directamente desde Apache

---

## 5. visorweb2 — Modificaciones Flutter

### Cambios en el código existente (`/root/apps_flutter/visorweb2/`)

- **API base URL**: cambiar de `ios.faristol.net` a `web2.faristol.net`
  - `lib/presentation/services/api_constants.dart`
  - `lib/presentation/services/api_service.dart`

- **Menú hamburguesa** (`menu_page.dart`):
  - Nuevo ítem: "Ensembles" (después de Home)

- **Nueva pantalla: My Ensembles**
  - Lista de ensembles del usuario (desde API)
  - Botón "Leave" para abandonar cada una

- **Nueva pantalla: Ensayos**
  - Dentro de cada agrupación, listado de ensayos

- **Nuevo visor: Setlist (atril virtual)**
  - En lugar del visor de partitura individual (coexisten):
    - Visor individual para scores sueltos
    - Visor secuencial de setlist para scores agrupados
  - Drag & drop para reordenar items
  - Al abrir un setlist, el músico ve las partituras en orden
  - Al terminar una obra, avanza automáticamente a la siguiente
  - Experiencia "como si estuvieran físicamente concatenadas"
  - Opción "Play/Open setlist" en la app Android/iOS

- **Premium automático:**
  - Al cargar perfil, verificar `GET /api/user/ensemble-status`
  - Si `has_active_ensemble === true`, elevar plan a Premium en memoria

### Build y deploy

```bash
cd /root/apps_flutter/visorweb2
flutter build web
cp -r build/web/* /var/www/web2.faristol.net/public/visorweb2/
```

---

## 6. Control App — Demo Web Flutter (Julio)

### Nuevo proyecto en `/root/apps_flutter/control_app/`

```bash
cd /root/apps_flutter
flutter create control_app
```

### Pantallas

- **Login**: campos CIF, Email, Contraseña
- **Dashboard**:
  - Sidebar de navegación (color azul)
  - Stats cards en cabecera
  - Gráfico de barras verticales
  - Gráfico circular (pie chart)
- Funcionalidad demo (simulación, sin backend real conectado)

### Build y deploy

```bash
cd /root/apps_flutter/control_app
flutter build web
cp -r build/web/* /var/www/web2.faristol.net/public/control-app/
```

---

## 7. Control de Acceso y Roles

| Recurso | Quién puede |
|---------|-------------|
| Crear ensemble | Solo superadmin (desde backend) |
| Listar partituras privadas del ensemble | Miembros activos |
| Subir/actualizar partituras privadas | Archivero, admin, instructor |
| Gestionar miembros y roles | Admin |
| Planificar ensayos | Admin, instructor |
| Ver ensayos | Todos los miembros |
| Abandonar ensemble (Leave) | Cualquier miembro |
| Crear/editar setlists | Cualquier usuario autenticado |
| Abrir setlist en visor secuencial (atril virtual) | Cualquier usuario autenticado |

---

## 8. QA y Verificación

- [ ] Tests de producción existentes siguen pasando (34 tests, 82 assertions)
- [ ] Tests nuevos para API de ensembles, miembros, rehearsals, setlists
- [ ] Verificar: partituras privadas NO listables por no miembros
- [ ] Verificar: premium automático (has_active_ensemble) eleva plan en app
- [ ] Verificar: visor secuencial de setlist funciona (avance automático entre scores, atril virtual)
- [ ] Verificar: landing page carga correctamente
- [ ] Verificar: ambas apps Flutter funcionan en sus rutas con base href correcto
- [ ] Verificar: no se rompen rutas SEO existentes (`/score/{name}`, `/sitemap`, etc.)

---

## 9. Pendiente para Agosto

- Migración de archivos locales a S3 con ruta `ensembles/{id}/{filename}`
- Definir endpoint de S3 con el CTO (mismo bucket que globales vs bucket separado)
- Control App: convertir demo web a app de escritorio real (Electron/Windows)
- Posible: agregar `ensemble_folders` en la UI de visorweb2