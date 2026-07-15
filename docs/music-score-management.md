# 🎼 Gestión de Partituras - Faristol

## 📋 Descripción General

El sistema de gestión de partituras de Faristol permite a usuarios y compositores subir, organizar, buscar y descargar partituras musicales con funcionalidades avanzadas de categorización y control de acceso.

## ✨ Características Principales

### 📚 Biblioteca de Partituras
- **Catálogo Extenso**: Miles de partituras organizadas
- **Búsqueda Avanzada**: Filtros múltiples y búsqueda por texto
- **Categorización**: Por compositor, instrumento, estilo y dificultad
- **Metadatos Ricos**: Información detallada de cada partitura

### 📤 Subida de Partituras
- **Carga Múltiple**: Subida de varios archivos simultáneamente
- **Formatos Soportados**: PDF, MIDI, MusicXML
- **Validación Automática**: Verificación de formato y calidad
- **Procesamiento**: Generación automática de thumbnails y metadatos

### 🔍 Sistema de Búsqueda
- **Filtros Avanzados**: Por múltiples criterios
- **Búsqueda por Texto**: En títulos, descripciones y compositores
- **Sugerencias**: Autocompletado y sugerencias inteligentes
- **Historial**: Búsquedas recientes y guardadas

## 🏗️ Estructura de Datos

### Modelo de Partitura
```php
// Campos principales
- id: Identificador único
- name: Título de la partitura
- composer_id: ID del compositor
- description: Descripción detallada
- difficulty: Nivel de dificultad
- duration_minutes: Duración estimada
- key_signature: Tonalidad
- time_signature: Compás
- tempo: Indicación de tempo
- created_at: Fecha de creación
- updated_at: Última modificación
- status: Estado (draft, published, archived)
```

### Relaciones
- **Compositor**: Pertenece a un compositor
- **Instrumentos**: Relación many-to-many
- **Estilo Musical**: Pertenece a un estilo
- **Archivos**: Tiene múltiples archivos asociados
- **Favoritos**: Relación many-to-many con usuarios
- **Estadísticas**: Tiene estadísticas de visualización

## 📤 Proceso de Subida

### 1. Preparación de Archivos
```bash
# Formatos aceptados
.pdf    # Partitura principal (requerido)
.mid    # Archivo MIDI (opcional)
.xml    # MusicXML (opcional)
.mp3    # Audio de referencia (opcional)
.jpg    # Thumbnail personalizado (opcional)
```

### 2. Formulario de Subida
```html
<!-- Campos requeridos -->
<form enctype="multipart/form-data">
    <input name="name" required>
    <select name="composer_id" required>
    <select name="instruments[]" multiple required>
    <select name="style_music_id" required>
    <select name="difficulty" required>
    <textarea name="description">
    <input type="file" name="files[]" multiple accept=".pdf">
</form>
```

### 3. Validación
```php
// Reglas de validación
'name' => 'required|string|max:255',
'composer_id' => 'required|exists:composers,id',
'instruments' => 'required|array|min:1',
'style_music_id' => 'required|exists:style_music,id',
'difficulty' => 'required|in:beginner,intermediate,advanced',
'files' => 'required|array|min:1',
'files.*' => 'file|mimes:pdf|max:10240', // 10MB max
```

### 4. Procesamiento
- **Almacenamiento**: Subida a S3/Wasabi
- **Metadatos**: Extracción automática de PDF
- **Thumbnails**: Generación de vista previa
- **Indexación**: Preparación para búsqueda
- **Notificaciones**: Email a administradores

## 🔍 Sistema de Búsqueda

### Filtros Disponibles
```javascript
// Parámetros de búsqueda
{
    search: "Bach Invención",           // Búsqueda de texto
    composer_id: 1,                     // Filtro por compositor
    instruments: [1, 2, 3],             // Filtro por instrumentos
    style_id: 1,                        // Filtro por estilo
    difficulty: "intermediate",          // Filtro por dificultad
    key_signature: "C Major",           // Filtro por tonalidad
    time_signature: "4/4",              // Filtro por compás
    duration_min: 2,                    // Duración mínima
    duration_max: 10,                   // Duración máxima
    date_from: "2024-01-01",           // Fecha desde
    date_to: "2024-12-31",             // Fecha hasta
    has_audio: true,                    // Con audio
    free_only: false,                   // Solo gratuitas
    sort: "name",                       // Ordenar por
    order: "asc",                       // Dirección
    page: 1,                           // Página
    limit: 20                          // Items por página
}
```

### Implementación de Búsqueda
```php
// Controller method
public function search(Request $request)
{
    $query = MusicScore::with(['composer', 'instruments', 'style'])
        ->where('status', 'published');
    
    // Búsqueda por texto
    if ($request->search) {
        $query->where(function($q) use ($request) {
            $q->where('name', 'like', "%{$request->search}%")
              ->orWhere('description', 'like', "%{$request->search}%")
              ->orWhereHas('composer', function($cq) use ($request) {
                  $cq->where('name', 'like', "%{$request->search}%");
              });
        });
    }
    
    // Filtros específicos
    if ($request->composer_id) {
        $query->where('composer_id', $request->composer_id);
    }
    
    if ($request->instruments) {
        $query->whereHas('instruments', function($q) use ($request) {
            $q->whereIn('instruments.id', $request->instruments);
        });
    }
    
    // Ordenación
    $query->orderBy($request->sort ?? 'created_at', $request->order ?? 'desc');
    
    return $query->paginate($request->limit ?? 20);
}
```

## 📊 Sistema de Favoritos

### Agregar a Favoritos
```php
// API endpoint
GET /api/music-score/fav-music-score?music_score_id={id}

// Implementation
public function favMusicScore(Request $request)
{
    $user = auth()->user();
    $musicScoreId = $request->music_score_id;
    
    $favorite = $user->favoritesMusicScores()
        ->where('music_score_id', $musicScoreId)
        ->first();
    
    if (!$favorite) {
        $user->favoritesMusicScores()->attach($musicScoreId);
        return response()->json(['status' => true, 'action' => 'added']);
    }
    
    return response()->json(['status' => false, 'message' => 'Already in favorites']);
}
```

### Listar Favoritos
```php
// Get user favorites
public function userFavorites()
{
    $user = auth()->user();
    $favorites = $user->favoritesMusicScores()
        ->with(['composer', 'instruments', 'style'])
        ->paginate(20);
    
    return response()->json([
        'status' => true,
        'data' => $favorites
    ]);
}
```

## 📋 Sistema de Anotaciones

### Límites por Suscripción
```php
// Subscription levels
const ANNOTATION_LIMITS = [
    0 => 5,          // Free
    1 => 15,         // Basic
    2 => 'unlimited' // Premium
];

// Check annotation limit
public function canAnnotate($userId, $musicScoreId)
{
    $subscription = $this->getSubscriptionLevel($userId);
    $limit = ANNOTATION_LIMITS[$subscription];
    
    if ($limit === 'unlimited') {
        return true;
    }
    
    $currentAnnotations = $this->getUserAnnotations($userId, $musicScoreId);
    return count($currentAnnotations) < $limit;
}
```

### Guardar Anotaciones
```javascript
// Frontend annotation system
class AnnotationManager {
    constructor(musicScoreId, userId) {
        this.musicScoreId = musicScoreId;
        this.userId = userId;
        this.annotations = [];
    }
    
    async saveAnnotation(page, x, y, content) {
        const annotation = {
            music_score_id: this.musicScoreId,
            page: page,
            x_position: x,
            y_position: y,
            content: content,
            color: '#ffff00'
        };
        
        try {
            const response = await fetch('/api/annotations/save', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(annotation)
            });
            
            return response.json();
        } catch (error) {
            console.error('Error saving annotation:', error);
        }
    }
}
```

## 📱 Visualización de PDFs

### Procesamiento de PDFs
```php
// PDF processing service
class PdfProcessor
{
    public function processUpload($file, $musicScoreId)
    {
        // Store original PDF
        $path = $file->store("music-scores/{$musicScoreId}", 's3');
        
        // Generate thumbnails for each page
        $pdf = new Pdf($file->path());
        $pageCount = $pdf->getNumberOfPages();
        
        for ($page = 1; $page <= $pageCount; $page++) {
            $this->generatePageThumbnail($file->path(), $page, $musicScoreId);
        }
        
        return [
            'path' => $path,
            'pages' => $pageCount,
            'thumbnails_generated' => true
        ];
    }
    
    private function generatePageThumbnail($pdfPath, $page, $musicScoreId)
    {
        // Convert PDF page to image
        $imagick = new \Imagick();
        $imagick->setResolution(150, 150);
        $imagick->readImage("{$pdfPath}[" . ($page - 1) . "]");
        $imagick->setImageFormat('jpeg');
        
        // Save to storage
        $thumbnailPath = "thumbnails/{$musicScoreId}/page-{$page}.jpg";
        Storage::disk('s3')->put($thumbnailPath, $imagick->getImageBlob());
        
        return $thumbnailPath;
    }
}
```

### API de Contenido PDF
```php
// Get PDF page content
public function getPdfContent(Request $request)
{
    $musicScoreId = $request->music_score_id;
    $page = $request->page ?? 1;
    $quality = $request->quality ?? 'medium';
    
    // Check user subscription for quality access
    $subscription = $this->getSubscriptionLevel(auth()->id());
    
    if ($quality === 'high' && $subscription < 2) {
        return response()->json([
            'status' => false,
            'message' => 'Premium subscription required for high quality'
        ], 403);
    }
    
    $musicScore = MusicScore::findOrFail($musicScoreId);
    $imagePath = "thumbnails/{$musicScoreId}/page-{$page}-{$quality}.jpg";
    
    if (!Storage::disk('s3')->exists($imagePath)) {
        // Generate on demand
        $this->generatePageImage($musicScore, $page, $quality);
    }
    
    $imageUrl = Storage::disk('s3')->url($imagePath);
    
    return response()->json([
        'status' => true,
        'data' => [
            'page' => $page,
            'total_pages' => $musicScore->pdf_pages,
            'image_url' => $imageUrl,
            'quality' => $quality,
            'annotations_allowed' => $subscription > 0
        ]
    ]);
}
```

## 📊 Estadísticas y Analytics

### Registro de Visualizaciones
```php
// Log view activity
class ViewLogger
{
    public static function logView($musicScoreId, $userId = null, $page = null)
    {
        LogViewMusicScoreDetail::create([
            'music_score_id' => $musicScoreId,
            'user_id' => $userId,
            'page' => $page,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'created_at' => now()
        ]);
    }
}

// Usage in controller
public function show($id)
{
    $musicScore = MusicScore::findOrFail($id);
    
    // Log the view
    ViewLogger::logView($id, auth()->id());
    
    return response()->json([
        'status' => true,
        'data' => $musicScore
    ]);
}
```

### Estadísticas de Partitura
```php
public function getStatistics($id)
{
    $musicScore = MusicScore::findOrFail($id);
    
    $stats = [
        'views' => [
            'total' => $musicScore->viewLogs()->count(),
            'unique' => $musicScore->viewLogs()->distinct('user_id')->count(),
            'this_month' => $musicScore->viewLogs()
                ->whereBetween('created_at', [now()->startOfMonth(), now()])
                ->count(),
        ],
        'downloads' => [
            'total' => $musicScore->downloads()->count(),
            'this_month' => $musicScore->downloads()
                ->whereBetween('created_at', [now()->startOfMonth(), now()])
                ->count(),
        ],
        'favorites' => [
            'total' => $musicScore->favoritedBy()->count()
        ],
        'ratings' => [
            'average' => $musicScore->ratings()->avg('rating'),
            'count' => $musicScore->ratings()->count()
        ]
    ];
    
    return response()->json([
        'status' => true,
        'data' => $stats
    ]);
}
```

## 🛡️ Control de Acceso

### Verificación de Permisos
```php
// Middleware for score access
class CheckScoreAccess
{
    public function handle($request, Closure $next)
    {
        $musicScoreId = $request->route('id');
        $user = auth()->user();
        
        $musicScore = MusicScore::findOrFail($musicScoreId);
        
        // Check if score is published
        if ($musicScore->status !== 'published') {
            if (!$user || ($user->id !== $musicScore->composer->user_id && !$user->hasRole('superadmin'))) {
                return response()->json(['error' => 'Score not available'], 403);
            }
        }
        
        // Check subscription level for premium content
        if ($musicScore->is_premium) {
            $subscription = app(SubscriptionService::class)->getSubscriptionDetails();
            if ($subscription['level'] < 1) {
                return response()->json(['error' => 'Subscription required'], 402);
            }
        }
        
        return $next($request);
    }
}
```

## 🔧 Administración

### Panel de Administración
```php
// Admin controller for music scores
class AdminMusicScoreController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MusicScore::with(['composer', 'user'])
                ->select('music_scores.*');
            
            return DataTables::of($data)
                ->addColumn('status', function ($row) {
                    $checked = $row->status === 'published' ? 'checked' : '';
                    return '<input data-id="' . $row->id . '" 
                                   class="toggle-status" 
                                   type="checkbox" 
                                   data-toggle="toggle" ' . $checked . '>';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('music_score.show', $row->id) . '" 
                               class="btn btn-sm btn-primary">View</a>
                            <a href="#" data-id="' . $row->id . '" 
                               class="btn btn-sm btn-danger delete-score">Delete</a>';
                })
                ->make(true);
        }
        
        return view('admin.music-scores.index');
    }
    
    public function changeStatus(Request $request)
    {
        $musicScore = MusicScore::findOrFail($request->id);
        $musicScore->status = $request->status ? 'published' : 'draft';
        $musicScore->save();
        
        return response()->json(['success' => true]);
    }
}
```

## 📱 API de Partituras

### Endpoints Principales
```http
# Listar partituras
GET /api/music-score/list

# Obtener partitura específica
GET /api/music-score/get/{id}

# Crear nueva partitura
POST /api/music-score/create

# Actualizar partitura
POST /api/music-score/update/{id}

# Eliminar partitura
DELETE /api/music-score/delete/{id}

# Obtener PDF
GET /api/music-score/getMusicScorePdf/{id}

# Obtener contenido por páginas
POST /api/music-score/getPdfContent

# Gestión de favoritos
GET /api/music-score/fav-music-score
GET /api/music-score/remove-fav-music-score
GET /api/music-score/user-fav-music-score

# Estadísticas
GET /api/music-score/statistics/{id}
```

## 🧪 Testing

### Tests de Funcionalidad
```php
// Feature test for music score upload
class MusicScoreUploadTest extends TestCase
{
    public function test_user_can_upload_music_score()
    {
        $user = User::factory()->create();
        $composer = Composer::factory()->create();
        $instrument = Instrument::factory()->create();
        $style = StyleMusic::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/music-score/create', [
                'name' => 'Test Score',
                'composer_id' => $composer->id,
                'instruments' => [$instrument->id],
                'style_music_id' => $style->id,
                'difficulty' => 'intermediate',
                'description' => 'Test description',
                'files' => [
                    UploadedFile::fake()->create('score.pdf', 1000, 'application/pdf')
                ]
            ]);
        
        $response->assertStatus(201)
                ->assertJson(['status' => true]);
        
        $this->assertDatabaseHas('music_scores', [
            'name' => 'Test Score',
            'composer_id' => $composer->id
        ]);
    }
}
```

## 📞 Soporte y Resolución de Problemas

### Problemas Comunes

#### Error de Subida de Archivos
```bash
# Verificar límites PHP
php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time)"

# Ajustar en php.ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

#### Problemas de Almacenamiento
```bash
# Verificar configuración S3
php artisan tinker
Storage::disk('s3')->put('test.txt', 'Hello World');
Storage::disk('s3')->get('test.txt');
```

#### PDF no se procesa
```bash
# Verificar PDFtk instalación
pdftk --version

# Instalar si es necesario (Ubuntu)
sudo apt-get install pdftk-java
```

---

**Documentación actualizada**: 15 de Enero, 2024  
**Versión**: v1.0  
**Soporte**: support@faristol.net
