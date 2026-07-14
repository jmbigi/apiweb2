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

## 0. Setup inicial

### 0.1. Limpiar basura

Borrar lo que no sirve del web2 anterior:

| Archivo | Motivo |
|---------|--------|
| `public/cache/` | PDF/JPG pre-generados del web-view anterior |
| `public/sitemap.xml` | SEO — no necesario para prototipo |
| `public/sitemap-lang.xml` | SEO |
| `public/robots.txt` | SEO |
| `public/ads.txt` | AdSense |
| `public/google53776e12c164ec3e.html` | Search Console |

### 0.2. Copiar producción

```bash
rsync -av --exclude='.env' --exclude='.git/' --exclude='node_modules/' \
      --exclude='vendor/' --exclude='public/' \
      ~/apps_prod/API_Faristol/ /var/www/web2.faristol.net/
```

Luego merge de `public/`: copiar assets de producción pero conservar `web/` (visorweb2 build).

### 0.3. Instalar dependencias

```bash
composer install
npm install && npm run build
```

### 0.4. Configurar `.env`

- `DB_DATABASE=web2`, `DB_USERNAME=web2`, `DB_PASSWORD=Web2DB2607`
- `AWS_BUCKET=faristol-web2`
- Credenciales Wasabi S3

### 0.5. Migrar BD

```bash
php artisan migrate
```

---

## 1. Landing Page

- Vista simple en `resources/views/home.blade.php`
- Renderizada por `HomeController@index`
- 2 cards:
  - **"Faristol App"** → enlace a `/visorweb2/`
  - **"Control App"** → enlace a `/control-app/`
- Root `/` reemplaza al redirect a login que trae producción
- Diseño: fondo `#0C1934`, logo Faristol

---

## 2. Subdirectorios Flutter (servidos vía Apache directo)

- `public/visorweb2/` — build de visorweb2 con `<base href="/visorweb2/">`
- `public/control-app/` — build de control_app con `<base href="/control-app/">`
- Assets estáticos servidos directamente desde Apache
- Laravel sirve el `index.html` de cada subdirectorio (fallback)

---

## 3. Backend — API base (producción)

Se copia la API completa de `~/apps_prod/API_Faristol`:

- Controladores API (MusicScoreController, AuthController, ComposerController, etc.)
- Controladores admin (CRUD completo)
- Rutas API (174 líneas en `routes/api.php`)
- Modelos, migraciones, config
- Panel admin (Blade + Metronic)

**No se modifica nada existente.** Todo lo nuevo es additive.

---

## 4. Backend — Base de Datos y Modelos (Ensembles)

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

**Validación:** Al crear/renombrar carpeta, verificar que `longitud(ruta_completa) ≤ 4096` (PATH_MAX).

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
- Mismo bucket (`AWS_BUCKET=faristol-web2`). Sin carpeta `ensembles/` separada.
- Se reutiliza el disco `s3` existente en `config/filesystems.php`.

### Control de acceso en listados de partituras

Los endpoints públicos de listado (`GET /api/music-score/list`, `/list-filtered`, `/allmusic`, etc.) deben filtrar según permisos:

| Usuario | Scores visibles |
|---------|----------------|
| No autenticado | Solo `WHERE ensemble_id IS NULL` (catálogo público) |
| Autenticado + miembro de ensembles | Públicos `UNION` privados de sus ensembles donde `ensemble_user.status = 1` |
| Autenticado sin ensembles | Solo públicos |

Implementar como **scope local** `publicOrAccessible(User)` en `MusicScore`.

### Premium automático (no backend)

- Backend expone endpoint `GET /api/user/ensemble-status` → `{ has_active_ensemble: bool, ensembles[] }`
- La app **visorweb2 (Flutter)** eleva el plan a Premium en memoria si el usuario tiene membresía activa en un ensemble
- No hay cambios en lógica de suscripciones/pagos en backend

---

## 5. Backend — API Routes (nuevas, añadidas después de las de producción)

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

_Setlist es local (Hive en el dispositivo) — no requiere controller backend._

---

## 6. visorweb2 — Modificaciones Flutter

### Cambios en el código existente (`/root/apps_flutter/visorweb2/`)

- **API base URL**: cambiar de `ios.faristol.net` a `web2.faristol.net`
- **Menú hamburguesa**: nuevo ítem "Ensembles" (después de Home)
- **Nueva pantalla: My Ensembles** — lista de ensembles del usuario
- **Nueva pantalla: Ensayos** — dentro de cada agrupación
- **Nuevo visor: Setlist (atril virtual)** — visor secuencial con avance automático
- **Premium automático:** verificar `GET /api/user/ensemble-status`

### Build y deploy

```bash
cd /root/apps_flutter/visorweb2
flutter build web --release --base-href=/visorweb2/
cp -r build/web/* /var/www/web2.faristol.net/public/visorweb2/
```

---

## 7. Control App — Demo Web Flutter

### Nuevo proyecto en `/root/apps_flutter/control_app/`

```bash
cd /root/apps_flutter
flutter create control_app
```

### Pantallas

- **Login**: campos CIF, Email, Contraseña
- **Dashboard**: sidebar, stats cards, gráficos (demo)

### Build y deploy

```bash
cd /root/apps_flutter/control_app
flutter build web --release --base-href=/control-app/
cp -r build/web/* /var/www/web2.faristol.net/public/control-app/
```

---

## 8. Control de Acceso y Roles

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

## 9. QA y Verificación

- [ ] Tests de producción existentes siguen pasando
- [ ] Tests nuevos para API de ensembles, miembros, rehearsals
- [ ] Verificar: partituras privadas NO listables por no miembros
- [ ] Verificar: premium automático eleva plan en app
- [ ] Verificar: visor secuencial de setlist funciona
- [ ] Verificar: landing page carga correctamente
- [ ] Verificar: ambas apps Flutter funcionan en sus rutas
- [ ] Verificar: API de producción responde correctamente en web2
