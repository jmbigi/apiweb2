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
- `agrupacion_id` (nullable FK → `agrupaciones.id`)
- `uploaded_by` (FK → `users.id`)

**Nuevas tablas:**

| Tabla | Columnas |
|-------|----------|
| `agrupaciones` | id, name, description, type (banda/conservatorio/orquesta), created_by (FK users), timestamps, soft_deletes |
| `affiliations` | id, user_id, agrupacion_id, role (archivero/admin/instructor/basic), status (active/inactive), timestamps |
| `rehearsals` | id, agrupacion_id, title, description, scheduled_at, location, created_by (FK users), timestamps |
| `agrupacion_folders` | id, agrupacion_id, name, parent_id (nullable self-ref), timestamps |
| `setlists` | id, user_id, name, description, timestamps |
| `setlist_items` | id, setlist_id, music_score_id, position, timestamps |

### Modelos Eloquent

- `Agrupacion` — hasMany Affiliation, hasMany Rehearsal, hasMany AgrupacionFolder
- `Affiliation` — belongsTo User, belongsTo Agrupacion; user puede tener múltiples roles simultáneos
- `Rehearsal` — belongsTo Agrupacion, belongsTo User (created_by)
- `AgrupacionFolder` — belongsTo Agrupacion, self-ref parent_id
- `Setlist` — belongsTo User, hasMany SetlistItem
- `SetlistItem` — belongsTo Setlist, belongsTo MusicScore
- `MusicScore` (actualizar) — nullable belongsTo Agrupacion, belongsTo User (uploaded_by)

### Almacenamiento de archivos

- Carpeta: `storage/app/music_scores/` (misma que partituras globales, plana, sin subcarpetas por agrupación)
- Para agosto: migrar a S3 con ruta `ensembles/{id_agrupacion}/{filename}` (a discutir con CTO)

### Premium automático (no backend)

- Backend expone endpoint `GET /api/user/agrupaciones-status` → `{ has_active_affiliation: bool }`
- La app **visorweb2 (Flutter)** eleva el plan a Premium en memoria si el usuario tiene affiliation activa
- No hay cambios en lógica de suscripciones/pagos en backend

---

## 2. Backend — API Routes (`routes/api.php`)

Servidas por Laravel. Middleware global: CORS + `throttle:api`.

### AuthController (Sanctum token)
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/auth/login` | Login (email, password) → token |
| POST | `/api/auth/register` | Register |
| POST | `/api/auth/logout` | Revocar token |
| GET | `/api/user/me` | Perfil usuario autenticado |
| GET | `/api/user/agrup-status` | `{ has_active_affiliation, agrupaciones[] }` |
| POST | `/api/auth/refresh` | Refresh token |

### AgrupacionController
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/api/agrupaciones` | auth | Mis agrupaciones |
| POST | `/api/agrupaciones` | superadmin | Crear agrupación |
| GET | `/api/agrupaciones/{id}` | auth | Detalle (solo miembros) |
| PUT | `/api/agrupaciones/{id}` | superadmin | Actualizar |
| DELETE | `/api/agrupaciones/{id}` | superadmin | Soft delete |

### AffiliationController
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/api/agrupaciones/{id}/members` | auth | Lista miembros |
| POST | `/api/agrupaciones/{id}/join` | auth | Unirse (admin invita) |
| DELETE | `/api/agrupaciones/{id}/leave` | auth | Abandonar |
| PUT | `/api/agrupaciones/{id}/members/{userId}/role` | admin | Asignar rol |

### RehearsalController
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/api/agrupaciones/{id}/rehearsals` | auth | Lista (todos los miembros) |
| POST | `/api/agrupaciones/{id}/rehearsals` | admin/instructor | Crear |
| PUT | `/api/rehearsals/{id}` | admin/instructor | Actualizar |
| DELETE | `/api/rehearsals/{id}` | admin/instructor | Eliminar |

### AgrupacionScoreController
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/api/agrupaciones/{id}/scores` | member | Listar partituras privadas |
| POST | `/api/agrupaciones/{id}/scores` | member | Subir PDF (roles con permiso) |
| DELETE | `/api/agrupaciones/{id}/scores/{scoreId}` | member | Eliminar |

### SetlistController
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| GET | `/api/setlists` | auth | Mis setlists |
| POST | `/api/setlists` | auth | Crear |
| GET | `/api/setlists/{id}` | auth | Detalle con items ordenados |
| PUT | `/api/setlists/{id}` | auth | Actualizar nombre/desc |
| DELETE | `/api/setlists/{id}` | auth | Eliminar |

### SetlistItemController
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| POST | `/api/setlists/{id}/items` | auth | Agregar score |
| PUT | `/api/setlists/{id}/items/{itemId}` | auth | Reordenar (position) |
| DELETE | `/api/setlists/{id}/items/{itemId}` | auth | Quitar score |
| POST | `/api/setlists/{id}/reorder` | auth | Recibir array ordenado de item IDs |

### PdfConcatenateController
| Método | Endpoint | Auth | Descripción |
|--------|----------|------|-------------|
| POST | `/api/setlists/{id}/pdf` | auth | Devuelve PDF concatenado de todos los items del setlist |

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
  - Nuevo ítem: "Agrupaciones" (después de Home)

- **Nueva pantalla: Mis Agrupaciones**
  - Lista de agrupaciones del usuario (desde API)
  - Botón "Leave" para abandonar cada una

- **Nueva pantalla: Ensayos**
  - Dentro de cada agrupación, listado de ensayos

- **Nuevo visor: Setlist**
  - En lugar del visor de partitura individual (coexisten):
    - Visor individual para scores sueltos
    - Visor de setlist para scores agrupados
  - Drag & drop para reordenar items
  - Botón "Generar PDF" que llama al endpoint de concatenación

- **Premium automático:**
  - Al cargar perfil, verificar `GET /api/user/agrup-status`
  - Si `has_active_affiliation === true`, elevar plan a Premium en memoria

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

- **Login**: campos Organización, Usuario, Contraseña
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
| Crear agrupación | Solo superadmin (desde backend) |
| Listar partituras privadas de agrupación | Miembros activos |
| Subir/actualizar partituras privadas | Archivero, admin, instructor |
| Gestionar miembros y roles | Admin |
| Planificar ensayos | Admin, instructor |
| Ver ensayos | Todos los miembros |
| Abandonar agrupación (Leave) | Cualquier miembro |
| Crear/editar setlists | Cualquier usuario autenticado |
| Ver setlist PDF concatenado | Propietario del setlist |

---

## 8. QA y Verificación

- [ ] Tests de producción existentes siguen pasando (34 tests, 82 assertions)
- [ ] Tests nuevos para API de agrupaciones, affiliations, rehearsals, setlists
- [ ] Verificar: partituras privadas NO listables por no miembros
- [ ] Verificar: premium automático (has_active_affiliation) eleva plan en app
- [ ] Verificar: concatenación PDF de setlist funciona
- [ ] Verificar: landing page carga correctamente
- [ ] Verificar: ambas apps Flutter funcionan en sus rutas con base href correcto
- [ ] Verificar: no se rompen rutas SEO existentes (`/score/{name}`, `/sitemap`, etc.)

---

## 9. Pendiente para Agosto

- Migración de archivos locales a S3 con ruta `ensembles/{id_agrupacion}/{filename}`
- Definir endpoint de S3 con el CTO (mismo bucket que globales vs bucket separado)
- Control App: convertir demo web a app de escritorio real (Electron/Windows)
- Posible: agregar `agrupacion_folders` en la UI de visorweb2