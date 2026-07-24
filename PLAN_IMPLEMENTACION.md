# Plan de Implementación — Cambios Mínimos

> Basado en el código existente. Sin refactorizaciones.
> Cada cambio justificado con patrón existente.

---

## 0. Setup de web2 desde producción

### 0.1. Limpiar basura del web2 anterior

```bash
rm -rf public/cache/
rm -f public/sitemap.xml public/sitemap-lang.xml public/robots.txt
rm -f public/ads.txt public/google53776e12c164ec3e.html
```

### 0.2. Copiar producción

```bash
rsync -av --exclude='.env' --exclude='.git/' --exclude='node_modules/' \
      --exclude='vendor/' \
      ~/apps_prod/API_Faristol/app/   /var/www/web2.faristol.net/app/
rsync -av --exclude='.env' --exclude='.git/' --exclude='node_modules/' \
      --exclude='vendor/' \
      ~/apps_prod/API_Faristol/routes/ /var/www/web2.faristol.net/routes/
rsync -av --exclude='.env' --exclude='.git/' --exclude='node_modules/' \
      --exclude='vendor/' \
      ~/apps_prod/API_Faristol/config/ /var/www/web2.faristol.net/config/
# ... same for database/, resources/, tests/, bootstrap/, storage/
```

Merge `public/`: copiar assets de producción sin sobrescribir `web/`.

### 0.3. Instalar

```bash
composer install
npm install && npm run build
```

### 0.4. `.env`

```
DB_DATABASE=web2
DB_USERNAME=web2
DB_PASSWORD=Web2DB2607
AWS_BUCKET=faristol-web2
# + Wasabi credentials
```

### 0.5. Migrar BD existente

```bash
php artisan migrate
```

---

## 1. Backend — Nuevas Tablas (Migraciones + Modelos)

### 1.1. `ensembles`

**Nuevo archivo:** `database/migrations/YYYY_MM_DD_HHMMSS_create_ensembles_table.php`

```php
// Misma estructura que las 52 migraciones existentes
return new class extends Migration {
    public function up(): void {
        Schema::create('ensembles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('cif', 20)->unique()->comment('CIF/NIF de la agrupación');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('owner_id');  // superadmin que la creó
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('owner_id')->references('id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('ensembles'); }
};
```

**Nuevo archivo:** `app/Models/Ensemble.php`

```php
// Mismo patrón que LogDisplayPersonalScore (52 líneas, ~10 efectivas)
class Ensemble extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'cif', 'description', 'owner_id', 'status'];
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function members() { return $this->belongsToMany(User::class, 'ensemble_user'); }
    public function musicScores() { return $this->hasMany(MusicScore::class, 'ensemble_id'); }
}
```

**Justificación:** Sigue el mismo patrón de las 52 migraciones existentes (clase anónima, `up()` + `down()`, timestamps, soft deletes). No modifica tablas existentes.

### 1.2. `ensemble_user`

```php
Schema::create('ensemble_user', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('ensemble_id');
    $table->unsignedBigInteger('user_id');
    $table->string('role')->default('usuario');  // archivero, administrador, maestro, usuario
    $table->boolean('status')->default(true);
    $table->timestamps();
    $table->foreign('ensemble_id')->references('id')->on('ensembles')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->unique(['ensemble_id', 'user_id']);
});
```

**Justificación:** Tabla pivote estándar. `unique(['ensemble_id', 'user_id'])` evita duplicados. `role` como string evita crear otra tabla.

### 1.3. `music_scores` — migración sobre tabla existente

```php
Schema::table('music_scores', function (Blueprint $table) {
    $table->foreignId('ensemble_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('uploaded_by')->nullable()->constrained('users');
    $table->foreignId('ensemble_folder_id')->nullable()->constrained('ensemble_folders')->nullOnDelete();
});
```

### 1.4. `ensemble_folders`

```php
Schema::create('ensemble_folders', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('ensemble_id');
    $table->string('name');
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->timestamps();
    $table->foreign('ensemble_id')->references('id')->on('ensembles')->onDelete('cascade');
    $table->foreign('parent_id')->references('id')->on('ensemble_folders')->onDelete('set null');
});
```

**Modelo:**
```php
class EnsembleFolder extends Model {
    use HasFactory;
    protected $fillable = ['ensemble_id', 'name', 'parent_id'];
    public function ensemble() { return $this->belongsTo(Ensemble::class); }
    public function children() { return $this->hasMany(EnsembleFolder::class, 'parent_id'); }
    public function parent() { return $this->belongsTo(EnsembleFolder::class, 'parent_id'); }
}
```

**Validación PATH_MAX (4096):** Al crear o renombrar una carpeta, calcular longitud total de la ruta y rechazar si excede 4096 caracteres:

```php
// En EnsembleFolderController o FormRequest
$basePath = storage_path("app/music_scores/ensembles/{$ensembleId}/");
$fullPath = $basePath . $this->buildFolderPath($request->name, $request->parent_id);

if (strlen($fullPath) > 4096) {
    throw new \Illuminate\Validation\ValidationException(
        'La ruta completa excede el límite de 4096 caracteres'
    );
}

private function buildFolderPath(string $name, ?int $parentId): string {
    $path = $name;
    while ($parentId) {
        $parent = EnsembleFolder::findOrFail($parentId);
        $path = $parent->name . '/' . $path;
        $parentId = $parent->parent_id;
    }
    return $path;
}
```

### 1.5. `rehearsals`

```php
Schema::create('rehearsals', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('ensemble_id');
    $table->string('title');
    $table->date('date');
    $table->time('time')->nullable();
    $table->string('location')->nullable();
    $table->unsignedBigInteger('instructor_id')->nullable();
    $table->text('notes')->nullable();
    $table->boolean('status')->default(true);
    $table->timestamps();
    $table->foreign('ensemble_id')->references('id')->on('ensembles')->onDelete('cascade');
    $table->foreign('instructor_id')->references('id')->on('users')->onDelete('set null');
});
```

---

## 2. Almacenamiento S3

**El S3 no distingue público/privado.** El control de acceso se hace en la aplicación (scopes de Laravel), no en el almacenamiento.

- Mismo bucket Wasabi S3 que producción (`AWS_BUCKET` en `.env`, ej: `faristol-web2` para pruebas).
- Misma estructura de archivos que partituras públicas — **sin carpeta `ensembles/` separada**.
- No se crea un disco nuevo en `config/filesystems.php`; se reutiliza el disco `s3` existente.
- Los archivos subidos por un ensemble conviven con las partituras públicas en el mismo bucket.

---

## 3. Control de acceso en listados de partituras

**Problema:** Los endpoints públicos de listado (`GET /api/music-score/list`, `/list-filtered`, `/allmusic`) no deben exponer partituras privadas de ensembles.

**Solución:** Scope local `publicOrAccessible()` en `MusicScore`:

```php
// app/Models/MusicScore.php
public function scopePublicOrAccessible(Builder $query, ?User $user): void {
    $query->whereNull('music_scores.ensemble_id')  // siempre públicas
        ->when($user, function ($q) use ($user) {
            $ensembleIds = $user->ensembles()
                ->wherePivot('status', true)
                ->pluck('ensembles.id');
            if ($ensembleIds->isNotEmpty()) {
                $q->orWhereIn('music_scores.ensemble_id', $ensembleIds);
            }
        });
}
```

**Uso en cada endpoint de listado:**
```php
MusicScore::publicOrAccessible(Auth::user())->get();
```

---

## 4. Backend — Endpoints API

### 4.1. Login unificado (Control App + Faristol App)

**Archivo a modificar:** Controlador existente de login (o nuevo `ApiAuthController`)

`POST /api/auth/login` acepta `cif` opcional:
- Sin `cif`: login normal (legacy, Faristol App)
- Con `cif`: busca ensemble por cif, verifica membresía activa del usuario, si no → 403

```php
// Lógica añadida al login existente
if ($request->filled('cif')) {
    $ensemble = Ensemble::where('cif', $request->cif)->firstOrFail();
    $membership = $ensemble->members()
        ->wherePivot('user_id', $user->id)
        ->wherePivot('status', true)
        ->first();
    if (!$membership) {
        return response()->json(['message' => 'No eres miembro de esta agrupación'], 403);
    }
}
```

Respuesta extra cuando incluye `cif`:
```json
{
    "token": "...",
    "user": { "id": 1, "email": "...", "name": "..." },
    "ensemble": { "id": 1, "name": "...", "cif": "B-12345678", "role": "admin" }
}
```

### 4.2. Rutas de Ensembles

**Archivo a modificar:** `routes/api.php`
**Patrón existente:** Las rutas públicas y autenticadas ya están definidas (174 líneas).

**Nuevas rutas a agregar al final de `api.php`:**

```php
// === ENSEMBLES ===
Route::get('/ensembles/list', [EnsembleController::class, 'list']);
Route::post('/ensembles/create', [EnsembleController::class, 'create']);  // solo superadmin
Route::post('/ensembles/{ensemble}/members', [EnsembleController::class, 'addMember']);  // admin
Route::delete('/ensembles/{ensemble}/members/{user}', [EnsembleController::class, 'removeMember']);
Route::get('/ensembles/{ensemble}/scores', [EnsembleController::class, 'scores']);
Route::post('/ensembles/{ensemble}/scores', [EnsembleController::class, 'uploadScore']);  // archivero
Route::get('/ensembles/{ensemble}/rehearsals', [EnsembleController::class, 'rehearsals']);
Route::post('/ensembles/{ensemble}/rehearsals', [EnsembleController::class, 'createRehearsal']);  // maestro
```

**Nuevo controlador:** `app/Http/Controllers/api/EnsembleController.php` (nuevo archivo, no modifica controladores existentes).

**Justificación:** No modifica ninguna ruta existente. Las nuevas rutas usan el mismo patrón `auth:sanctum`, `check_active`. El controlador es nuevo.

---

## 5. Backend — Premium Lógico

**Archivo a modificar:** `app/Services/SubscriptionService.php`

**Cambio:** Agregar verificación de membresía de ensemble al final de `formatSubscriptionDetails()`, **sin modificar** la lógica existente de suscripciones.

```php
// Al final del método, antes del return (línea ~128 del código existente)
$isEnsembleMember = optional($this->user)->ensembles()->wherePivot('status', true)->exists();
```

Y agregar al array de retorno (sin eliminar nada existente):
```php
'is_ensemble_member' => $isEnsembleMember,
```

**En Flutter:** Agregar un campo `isEnsembleMember` al modelo `SubscriptionTypeModel` (un `bool` con default `false`), y leerlo en `SubscriptionService` para determinar si mostrar beneficios premium.

**Justificación:** No modifica la lógica existente. Solo agrega un campo al response. El Flutter existente ignora campos que no conoce en `fromJson()`.

---

## 6. Flutter — Menú "Ensembles"

**Archivo a modificar 1:** `lib/src/utils/menu/menu_page.dart`

Cambios:
1. Agregar constante (1 línea):
```dart
static const ensembles = MenuItem('Ensembles');
```
2. Agregar al `getAllItems()` (1 línea):
```dart
if (isLogin) ...[ensembles],
```

**Archivo a modificar 2:** `lib/src/views/main/mainpage.dart`

Cambio:
1. Agregar caso en el switch de `getScreen()` (3 líneas):
```dart
case MenuItems.ensembles:
  return EnsemblesPage();
```

**Nuevo archivo:** `lib/src/views/main/ensembles_page.dart` (pantalla nueva con listado de agrupaciones del usuario).

**Justificación:** Sigue exactamente el mismo patrón de los items existentes (`home`, `profile`, `offlineScores`, etc.). No elimina ni modifica nada existente.

---

## 7. Flutter — API Calls

**Archivo a modificar 1:** `lib/src/network/api_service.dart`

Agregar constantes (2 líneas):
```dart
static String ensembles = 'api/ensembles/list';
static String ensembleDetail = 'api/ensembles/';
```

**Archivo a modificar 2:** `lib/src/network/api_constants.dart`

Agregar métodos GET/POST siguiendo el patrón existente (ej: `getsubscriptionType` en líneas 1317-1352).

**Nuevo archivo:** `lib/src/model/ensemble_model.dart`

Modelo con `fromJson()` y `toJson()`, mismo patrón que `offline_score_model.dart`.

**Justificación:** Mismo patrón que los 38 modelos existentes. No modifica nada del código actual.

---

## 6. Flutter — Setlist Local

**No requiere backend.** Los datos se guardan en Hive (ya implementado).

**Archivos a modificar:**
1. `lib/src/stores/pdf_store.dart` — agregar un box type para Setlist o usar el existente
2. `lib/src/views/pdfviewer/` — agregar modo secuencial (avance automático al terminar una partitura)

**Mínimo:** El Setlist es una lista de IDs de partituras que ya están en `PdfStore` (Hive). El visor secuencial reutiliza el visor PDF existente (`pdfx`) pero con avance automático en lugar de manual.

**Justificación:** No requiere nuevas dependencias. `PdfStore` y `pdfx` ya existen.

---

## 7. Control App (Windows) — Proyecto Nuevo

**No es una modificación de la app existente.** Es un proyecto nuevo.

**Pasos:**
1. `flutter create --project-name=faristol_control --platforms=windows .`
2. Agregar solo las dependencias necesarias: `http`, `get`, `shared_preferences`, `flutter_svg`, `google_fonts`
3. No incluir: `google_mobile_ads`, `screenshot_callback`, `flutter_screenshot_detect`, `pdfx`
4. La lógica de negocio está en la API (Laravel), no en la app

**Justificación:** Al ser un proyecto nuevo, no afecta en nada a la app existente. Comparten la misma API pero cada app es independiente.

---

## 8. Resumen de Cambios (Implementado)

| Archivo | Tipo de cambio | Líneas |
|---------|---------------|--------|
| Migraciones (4 nuevas + 1 sobre music_scores + 1 roles inglés) | Nuevo | ~150 total |
| Modelos (4 nuevos + MusicScore actualizado) | Nuevo | ~40 total |
| `api.php` | Agregar rutas | ~40 líneas |
| `EnsembleController.php` | Nuevo | ~399 líneas |
| `SubscriptionService.php` | Agregar campo | ~5 líneas |
| **visorweb2:** `menu_page.dart`, `ensembles_page.dart`, `ensemble_detail_page.dart`, `setlist_player_page.dart`, `search_page.dart`, `upload_score_page.dart`, `my_scores_page.dart`, `composer_request_page.dart` | Modificaciones | ~500 líneas |
| **visorweb2:** `favorites_page.dart`, `offline_scores_page.dart`, `pdf_store_service.dart` | Nuevos | ~200 líneas |
| **visorweb2:** `pdf_viewer.dart` (save offline button) | Modificado | ~10 líneas |
| **visorweb2:** `api_service.dart` (+6 métodos), `api_constants.dart` | Modificado | ~150 líneas |
| **control-app:** `dashboard_page.dart` (roles inglés) | Modificado | ~5 líneas |
| **Tests:** `EnsembleTest.php`, `ApiAuthTest.php`, `ApiSubscriptionTest.php` | Actualizados roles | ~30 líneas |
| **Tests:** `visorweb2-features-e2e.mjs` | Nuevo E2E | ~100 líneas |
| **Tests:** `ocr_basic.py` | Nuevo OCR | ~50 líneas |
| **Documentación:** Todos los docs actualizados | 2026-07-23 | — |

**Archivos existentes modificados:** ~15
**Archivos nuevos:** ~10
**Código legacy refactorizado:** **0 líneas**

### Estado final de tests

| Suite | Tests | Resultado |
|-------|-------|-----------|
| Laravel Feature | 155 (420 assertions) | ✅ |
| visorweb2 Flutter | 76 | ✅ |
| control-app Flutter | 93 | ✅ |
| E2E Playwright visorweb2 | 29 | ✅ |
| E2E Playwright nuevas features | 14 | ✅ |
| **Total** | **367** | **✅** |
