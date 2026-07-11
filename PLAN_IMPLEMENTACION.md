# Plan de Implementación — Cambios Mínimos

> Basado en el código existente. Sin refactorizaciones.
> Cada cambio justificado con patrón existente.

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
    protected $fillable = ['name', 'description', 'owner_id', 'status'];
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function members() { return $this->belongsToMany(User::class, 'ensemble_user'); }
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

### 1.3. `ensemble_score`

```php
Schema::create('ensemble_score', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('ensemble_id');
    $table->unsignedBigInteger('music_score_id')->nullable(); // partitura existente en catálogo
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('folder')->nullable();
    $table->unsignedBigInteger('uploaded_by');
    $table->boolean('status')->default(true);
    $table->timestamps();
    $table->foreign('ensemble_id')->references('id')->on('ensembles')->onDelete('cascade');
    $table->foreign('music_score_id')->references('id')->on('music_scores')->onDelete('set null');
    $table->foreign('uploaded_by')->references('id')->on('users');
});
```

### 1.4. `rehearsals`

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

## 2. Backend — Endpoints API

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

## 3. Backend — Premium Lógico

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

## 4. Flutter — Menú "Ensembles"

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

## 5. Flutter — API Calls

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

## 8. Resumen de Cambios

| Archivo | Tipo de cambio | Líneas |
|---------|---------------|--------|
| Migraciones (4 nuevas) | Nuevo | ~120 total |
| Modelos (4 nuevos) | Nuevo | ~40 total |
| `api.php` | Agregar rutas | ~10 líneas |
| `EnsembleController.php` | Nuevo | ~200 líneas |
| `SubscriptionService.php` | Agregar campo | ~5 líneas |
| `menu_page.dart` | Agregar item | ~2 líneas |
| `mainpage.dart` | Agregar case | ~3 líneas |
| `api_service.dart` | Agregar constantes | ~2 líneas |
| `api_constants.dart` | Agregar métodos | ~30 líneas |
| `ensemble_model.dart` | Nuevo | ~30 líneas |
| `ensembles_page.dart` | Nueva pantalla | ~100 líneas |
| `SubscriptionTypeModel` | Agregar campo | ~3 líneas |
| Control App | Proyecto nuevo | Independiente |

**Archivos existentes modificados:** ~6 (solo para agregar, nunca eliminar ni refactorizar)
**Archivos nuevos:** ~10
**Código existente refactorizado:** **0 líneas**
