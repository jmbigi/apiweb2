# 👨‍🎨 Gestión de Compositores - Faristol

## 📋 Descripción General

El sistema de gestión de compositores permite la administración completa del catálogo de compositores, incluyendo la aprobación de nuevos compositores, gestión de biografías y control de contenido asociado.

## 🎵 Catálogo de Compositores

### Estructura de Datos
```php
// Modelo Composer
class Composer extends Model
{
    protected $fillable = [
        'name',
        'biography',
        'birth_date',
        'death_date',
        'nationality',
        'period',
        'image_url',
        'users_id',
        'status'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
    
    public function musicScores()
    {
        return $this->hasMany(MusicScore::class);
    }
}
```

### API de Compositores
```http
# Listar todos los compositores
GET /api/composer/list

# Obtener compositor específico
GET /api/composer/{id}

# Crear nuevo compositor (autenticado)
POST /api/composer/create

# Actualizar compositor (autenticado)
POST /api/composer/update/{id}

# Eliminar compositor (admin)
DELETE /api/composer/delete/{id}
```

## 📝 Sistema de Solicitudes

### Proceso de Solicitud
```php
class ComposerRequestController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'real_name' => 'nullable|string|max:255',
            'biography' => 'required|string|min:100',
            'birth_date' => 'required|date|before:today',
            'death_date' => 'nullable|date|after:birth_date',
            'nationality' => 'required|string|max:100',
            'musical_education' => 'nullable|string',
            'genres' => 'required|array|min:1',
            'portfolio_url' => 'nullable|url',
            'sample_works' => 'nullable|array'
        ]);
        
        $composerRequest = ComposerRequest::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'real_name' => $request->real_name,
            'biography' => $request->biography,
            'birth_date' => $request->birth_date,
            'death_date' => $request->death_date,
            'nationality' => $request->nationality,
            'musical_education' => $request->musical_education,
            'genres' => json_encode($request->genres),
            'portfolio_url' => $request->portfolio_url,
            'sample_works' => json_encode($request->sample_works),
            'status' => 'pending'
        ]);
        
        // Notify admins
        $this->notifyAdmins($composerRequest);
        
        return response()->json([
            'status' => true,
            'message' => 'Solicitud enviada exitosamente',
            'data' => $composerRequest
        ]);
    }
}
```

### Formulario de Solicitud
```blade
<!-- Composer request form -->
<form id="composer-request-form" method="POST">
    @csrf
    
    <!-- Basic Information -->
    <div class="section">
        <h4>Información Básica</h4>
        
        <div class="row">
            <div class="col-md-6">
                <label for="name">Nombre Artístico *</label>
                <input type="text" name="name" required class="form-control">
            </div>
            <div class="col-md-6">
                <label for="real_name">Nombre Real</label>
                <input type="text" name="real_name" class="form-control">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <label for="birth_date">Fecha de Nacimiento *</label>
                <input type="date" name="birth_date" required class="form-control">
            </div>
            <div class="col-md-4">
                <label for="death_date">Fecha de Fallecimiento</label>
                <input type="date" name="death_date" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="nationality">Nacionalidad *</label>
                <select name="nationality" required class="form-control">
                    <option value="">Seleccionar...</option>
                    <option value="Spanish">Española</option>
                    <option value="French">Francesa</option>
                    <option value="German">Alemana</option>
                    <!-- More options -->
                </select>
            </div>
        </div>
    </div>
    
    <!-- Biography -->
    <div class="section">
        <h4>Biografía</h4>
        <textarea name="biography" required class="form-control" rows="6" 
                  placeholder="Describe tu trayectoria musical, formación y logros (mínimo 100 caracteres)"></textarea>
        <small class="text-muted">Mínimo 100 caracteres</small>
    </div>
    
    <!-- Musical Information -->
    <div class="section">
        <h4>Información Musical</h4>
        
        <div class="row">
            <div class="col-md-6">
                <label for="musical_education">Formación Musical</label>
                <textarea name="musical_education" class="form-control" rows="3"
                         placeholder="Conservatorios, universidades, maestros..."></textarea>
            </div>
            <div class="col-md-6">
                <label for="genres">Géneros Musicales *</label>
                <select name="genres[]" multiple required class="form-control select2">
                    <option value="Classical">Clásica</option>
                    <option value="Contemporary">Contemporánea</option>
                    <option value="Jazz">Jazz</option>
                    <option value="Folk">Folk</option>
                    <option value="Electronic">Electrónica</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Portfolio -->
    <div class="section">
        <h4>Portfolio</h4>
        
        <div class="row">
            <div class="col-md-12">
                <label for="portfolio_url">URL de Portfolio</label>
                <input type="url" name="portfolio_url" class="form-control"
                       placeholder="https://mi-portfolio.com">
            </div>
        </div>
        
        <div class="sample-works">
            <label>Obras de Muestra</label>
            <div id="sample-works-container">
                <div class="sample-work-item">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="sample_works[0][title]" 
                                   placeholder="Título de la obra" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="sample_works[0][year]" 
                                   placeholder="Año" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <input type="url" name="sample_works[0][url]" 
                                   placeholder="URL (SoundCloud, YouTube...)" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-danger remove-work">Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" id="add-work" class="btn btn-sm btn-secondary">Agregar Obra</button>
        </div>
    </div>
    
    <!-- Social Media -->
    <div class="section">
        <h4>Redes Sociales</h4>
        <div class="row">
            <div class="col-md-4">
                <label for="website">Sitio Web</label>
                <input type="url" name="social_media[website]" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="youtube">YouTube</label>
                <input type="url" name="social_media[youtube]" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="instagram">Instagram</label>
                <input type="text" name="social_media[instagram]" class="form-control" 
                       placeholder="@usuario">
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
        <button type="button" class="btn btn-secondary" onclick="saveDraft()">Guardar Borrador</button>
    </div>
</form>
```

## 🛠️ Panel de Administración

### Gestión de Solicitudes
```php
class AdminComposerRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ComposerRequest::with('user')
                ->select('composer_requests.*');
            
            return DataTables::of($data)
                ->addColumn('user_info', function ($row) {
                    return [
                        'name' => $row->user->name,
                        'email' => $row->user->email,
                        'registered' => $row->user->created_at->format('d/m/Y')
                    ];
                })
                ->addColumn('request_info', function ($row) {
                    return [
                        'composer_name' => $row->name,
                        'nationality' => $row->nationality,
                        'genres' => json_decode($row->genres, true),
                        'submitted' => $row->created_at->format('d/m/Y H:i')
                    ];
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'pending' => ['class' => 'warning', 'text' => 'Pendiente'],
                        'approved' => ['class' => 'success', 'text' => 'Aprobado'],
                        'rejected' => ['class' => 'danger', 'text' => 'Rechazado'],
                        'reviewing' => ['class' => 'info', 'text' => 'En Revisión']
                    ];
                    
                    $badge = $badges[$row->status] ?? ['class' => 'secondary', 'text' => 'Desconocido'];
                    return '<span class="badge badge-' . $badge['class'] . '">' . $badge['text'] . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $buttons = '<a href="' . route('admin.composer-requests.show', $row->id) . '" 
                                   class="btn btn-sm btn-info">Ver Detalles</a>';
                    
                    if ($row->status === 'pending') {
                        $buttons .= ' <button class="btn btn-sm btn-success approve-request" 
                                           data-id="' . $row->id . '">Aprobar</button>';
                        $buttons .= ' <button class="btn btn-sm btn-warning review-request" 
                                           data-id="' . $row->id . '">Revisar</button>';
                        $buttons .= ' <button class="btn btn-sm btn-danger reject-request" 
                                           data-id="' . $row->id . '">Rechazar</button>';
                    }
                    
                    return $buttons;
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }
        
        return view('admin.composer-requests.index');
    }
}
```

### Proceso de Aprobación
```php
public function approve(Request $request, $id)
{
    $composerRequest = ComposerRequest::findOrFail($id);
    
    DB::beginTransaction();
    try {
        // Create composer record
        $composer = Composer::create([
            'name' => $composerRequest->name,
            'biography' => $composerRequest->biography,
            'birth_date' => $composerRequest->birth_date,
            'death_date' => $composerRequest->death_date,
            'nationality' => $composerRequest->nationality,
            'period' => $this->determinePeriod($composerRequest->birth_date),
            'users_id' => $composerRequest->user_id,
            'status' => 1
        ]);
        
        // Assign composer role
        $composerRole = Role::where('name', 'composer')->first();
        $composerRequest->user->attachRole($composerRole);
        
        // Update request status
        $composerRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'composer_id' => $composer->id
        ]);
        
        // Log activity
        ActivityLog::log(
            'composer_approved',
            "Composer request approved for user {$composerRequest->user->name}",
            auth()->id(),
            $composer->id,
            'Composer'
        );
        
        // Send approval email
        Mail::to($composerRequest->user->email)
            ->send(new ComposerApprovedEmail($composerRequest->user, $composer));
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Solicitud aprobada exitosamente'
        ]);
        
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'success' => false,
            'message' => 'Error al aprobar la solicitud: ' . $e->getMessage()
        ], 500);
    }
}
```

### Vista Detallada de Solicitud
```blade
<!-- Composer request detail view -->
<div class="composer-request-detail">
    <div class="header">
        <h2>Solicitud de Compositor - {{ $request->name }}</h2>
        <span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span>
    </div>
    
    <div class="row">
        <!-- User Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Información del Usuario</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> {{ $request->user->name }}</p>
                    <p><strong>Email:</strong> {{ $request->user->email }}</p>
                    <p><strong>Registrado:</strong> {{ $request->user->created_at->format('d/m/Y') }}</p>
                    <p><strong>Partituras subidas:</strong> {{ $request->user->musicScores->count() }}</p>
                    <p><strong>Estado de cuenta:</strong> 
                        <span class="badge badge-{{ $request->user->status ? 'success' : 'danger' }}">
                            {{ $request->user->status ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Composer Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Información del Compositor</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre Artístico:</strong> {{ $request->name }}</p>
                            <p><strong>Nombre Real:</strong> {{ $request->real_name ?? 'No especificado' }}</p>
                            <p><strong>Nacionalidad:</strong> {{ $request->nationality }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha de Nacimiento:</strong> {{ $request->birth_date->format('d/m/Y') }}</p>
                            @if($request->death_date)
                                <p><strong>Fecha de Fallecimiento:</strong> {{ $request->death_date->format('d/m/Y') }}</p>
                            @endif
                            <p><strong>Géneros:</strong> 
                                @foreach(json_decode($request->genres) as $genre)
                                    <span class="badge badge-secondary">{{ $genre }}</span>
                                @endforeach
                            </p>
                        </div>
                    </div>
                    
                    <div class="biography-section">
                        <h6>Biografía</h6>
                        <div class="biography-text">
                            {{ $request->biography }}
                        </div>
                    </div>
                    
                    @if($request->musical_education)
                    <div class="education-section">
                        <h6>Formación Musical</h6>
                        <p>{{ $request->musical_education }}</p>
                    </div>
                    @endif
                    
                    @if($request->portfolio_url)
                    <div class="portfolio-section">
                        <h6>Portfolio</h6>
                        <a href="{{ $request->portfolio_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            Ver Portfolio
                        </a>
                    </div>
                    @endif
                    
                    @if($request->sample_works)
                    <div class="works-section">
                        <h6>Obras de Muestra</h6>
                        <div class="works-list">
                            @foreach(json_decode($request->sample_works, true) as $work)
                            <div class="work-item">
                                <strong>{{ $work['title'] }}</strong> ({{ $work['year'] }})
                                @if(isset($work['url']))
                                    <a href="{{ $work['url'] }}" target="_blank" class="btn btn-xs btn-link">Escuchar</a>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    @if($request->status === 'pending')
    <div class="actions-section">
        <div class="card">
            <div class="card-header">
                <h5>Acciones</h5>
            </div>
            <div class="card-body">
                <div class="btn-group">
                    <button class="btn btn-success" onclick="approveRequest({{ $request->id }})">
                        Aprobar Solicitud
                    </button>
                    <button class="btn btn-warning" onclick="requestChanges({{ $request->id }})">
                        Solicitar Cambios
                    </button>
                    <button class="btn btn-danger" onclick="rejectRequest({{ $request->id }})">
                        Rechazar Solicitud
                    </button>
                </div>
                
                <div class="notes-section mt-3">
                    <h6>Notas Internas</h6>
                    <textarea id="admin-notes" class="form-control" rows="3" 
                             placeholder="Notas para otros administradores..."></textarea>
                    <button class="btn btn-sm btn-secondary mt-2" onclick="saveNotes({{ $request->id }})">
                        Guardar Notas
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Review History -->
    @if($request->reviewHistory->count() > 0)
    <div class="review-history">
        <div class="card">
            <div class="card-header">
                <h5>Historial de Revisión</h5>
            </div>
            <div class="card-body">
                @foreach($request->reviewHistory as $review)
                <div class="review-item">
                    <div class="review-header">
                        <strong>{{ $review->admin->name }}</strong>
                        <span class="text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</span>
                        <span class="badge badge-{{ $review->action === 'approved' ? 'success' : ($review->action === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($review->action) }}
                        </span>
                    </div>
                    @if($review->notes)
                    <div class="review-notes">
                        {{ $review->notes }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
```

## 📊 Estadísticas de Compositores

### Analytics Dashboard
```php
class ComposerAnalyticsController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_composers' => Composer::count(),
            'active_composers' => Composer::where('status', 1)->count(),
            'pending_requests' => ComposerRequest::where('status', 'pending')->count(),
            'new_this_month' => Composer::whereBetween('created_at', [now()->startOfMonth(), now()])->count(),
            'most_productive' => $this->getMostProductiveComposers(),
            'popular_genres' => $this->getPopularGenres(),
            'nationality_stats' => $this->getNationalityStats(),
            'period_distribution' => $this->getPeriodDistribution()
        ];
        
        return view('admin.composers.analytics', compact('stats'));
    }
    
    private function getMostProductiveComposers()
    {
        return Composer::withCount('musicScores')
            ->orderBy('music_scores_count', 'desc')
            ->limit(10)
            ->get();
    }
    
    private function getPopularGenres()
    {
        return ComposerRequest::where('status', 'approved')
            ->get()
            ->flatMap(function ($request) {
                return json_decode($request->genres, true) ?? [];
            })
            ->countBy()
            ->sortDesc()
            ->take(10);
    }
}
```

## 🔍 Búsqueda y Filtrado

### Búsqueda Avanzada
```javascript
class ComposerSearch {
    constructor() {
        this.filters = {
            nationality: null,
            period: null,
            genres: [],
            birth_year_from: null,
            birth_year_to: null,
            has_music_scores: null
        };
        
        this.initializeFilters();
    }
    
    initializeFilters() {
        // Nationality filter
        $('#nationality-filter').on('change', (e) => {
            this.filters.nationality = e.target.value || null;
            this.search();
        });
        
        // Period filter
        $('#period-filter').on('change', (e) => {
            this.filters.period = e.target.value || null;
            this.search();
        });
        
        // Genre filter (multiple select)
        $('#genre-filter').on('change', (e) => {
            this.filters.genres = $(e.target).val() || [];
            this.search();
        });
        
        // Birth year range
        $('#birth-year-from, #birth-year-to').on('change', () => {
            this.filters.birth_year_from = $('#birth-year-from').val() || null;
            this.filters.birth_year_to = $('#birth-year-to').val() || null;
            this.search();
        });
        
        // Has music scores toggle
        $('#has-scores-toggle').on('change', (e) => {
            this.filters.has_music_scores = e.target.checked ? true : null;
            this.search();
        });
    }
    
    search() {
        const params = new URLSearchParams();
        
        Object.keys(this.filters).forEach(key => {
            const value = this.filters[key];
            if (value !== null) {
                if (Array.isArray(value)) {
                    value.forEach(v => params.append(`${key}[]`, v));
                } else {
                    params.set(key, value);
                }
            }
        });
        
        fetch(`/api/composer/search?${params}`)
            .then(response => response.json())
            .then(data => this.updateResults(data))
            .catch(error => console.error('Search error:', error));
    }
    
    updateResults(data) {
        const resultsContainer = $('#composers-results');
        resultsContainer.empty();
        
        if (data.status && data.data.composers.length > 0) {
            data.data.composers.forEach(composer => {
                resultsContainer.append(this.createComposerCard(composer));
            });
            
            // Update pagination
            this.updatePagination(data.data.pagination);
        } else {
            resultsContainer.html('<div class="no-results">No se encontraron compositores</div>');
        }
    }
    
    createComposerCard(composer) {
        return `
            <div class="composer-card">
                <div class="composer-header">
                    <h5>${composer.name}</h5>
                    <span class="nationality">${composer.nationality}</span>
                </div>
                <div class="composer-info">
                    <p class="dates">${composer.birth_date} - ${composer.death_date || 'Presente'}</p>
                    <p class="period">${composer.period}</p>
                    <p class="scores-count">${composer.music_scores_count} partituras</p>
                </div>
                <div class="composer-actions">
                    <a href="/composer/${composer.id}" class="btn btn-sm btn-primary">Ver Detalles</a>
                    <a href="/music-scores?composer=${composer.id}" class="btn btn-sm btn-outline-secondary">Ver Obras</a>
                </div>
            </div>
        `;
    }
}
```

## 📧 Notificaciones y Emails

### Email de Aprobación
```php
class ComposerApprovedEmail extends Mailable
{
    public $user;
    public $composer;
    
    public function __construct(User $user, Composer $composer)
    {
        $this->user = $user;
        $this->composer = $composer;
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 ¡Felicidades! Tu solicitud de compositor ha sido aprobada'
        );
    }
    
    public function content(): Content
    {
        return new Content(
            view: 'emails.composer_approved',
            with: [
                'user' => $this->user,
                'composer' => $this->composer,
                'next_steps' => [
                    'Sube tus primeras partituras',
                    'Completa tu perfil de compositor',
                    'Explora las herramientas disponibles'
                ],
                'upload_url' => route('music-score.create'),
                'dashboard_url' => route('composer.dashboard')
            ]
        );
    }
}
```

### Plantilla de Email
```blade
<!-- emails/composer_approved.blade.php -->
@extends('emails.layout.app')

@section('content')
<div class="celebration-header">
    <h1>¡Felicidades, {{ $user->name }}! 🎉</h1>
    <p class="lead">Tu solicitud para convertirte en compositor ha sido <strong>aprobada</strong>.</p>
</div>

<div class="composer-info">
    <h2>Perfil de Compositor Creado</h2>
    <div class="composer-card">
        <h3>{{ $composer->name }}</h3>
        <p><strong>Nacionalidad:</strong> {{ $composer->nationality }}</p>
        <p><strong>Fecha de nacimiento:</strong> {{ $composer->birth_date->format('d/m/Y') }}</p>
    </div>
</div>

<div class="next-steps">
    <h2>Próximos Pasos</h2>
    <ol>
        @foreach($next_steps as $step)
            <li>{{ $step }}</li>
        @endforeach
    </ol>
</div>

<div class="action-buttons">
    <a href="{{ $upload_url }}" class="button button-primary">
        Subir Primera Partitura
    </a>
    <a href="{{ $dashboard_url }}" class="button button-secondary">
        Ir al Dashboard
    </a>
</div>

<div class="support-info">
    <p>Si tienes alguna pregunta, no dudes en <a href="{{ route('support') }}">contactar con nuestro equipo de soporte</a>.</p>
</div>
@endsection
```

---

**Documentación actualizada**: 15 de Enero, 2024  
**Versión**: v1.0  
**Soporte**: composers@faristol.net
