# 🛠️ Funciones de Administrador - Faristol

## 📋 Descripción General

El panel de administración de Faristol proporciona herramientas completas para gestionar usuarios, contenido, suscripciones y configuraciones del sistema con una interfaz intuitiva y funcionalidades avanzadas.

## 🏠 Dashboard Principal

### Métricas en Tiempo Real
```php
// Dashboard metrics
class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'users' => [
                'total' => User::count(),
                'active' => User::where('status', 1)->count(),
                'new_this_month' => User::whereBetween('created_at', [now()->startOfMonth(), now()])->count(),
                'composers' => User::whereHas('roles', function($q) {
                    $q->where('name', 'composer');
                })->count()
            ],
            'music_scores' => [
                'total' => MusicScore::count(),
                'published' => MusicScore::where('status', 'published')->count(),
                'pending_review' => MusicScore::where('status', 'draft')->count(),
                'views_today' => LogViewMusicScoreDetail::whereDate('created_at', today())->count()
            ],
            'subscriptions' => [
                'free' => $this->getSubscriptionStats(0),
                'basic' => $this->getSubscriptionStats(1),
                'premium' => $this->getSubscriptionStats(2),
                'revenue_this_month' => $this->getMonthlyRevenue()
            ],
            'system' => [
                'storage_used' => $this->getStorageUsage(),
                'active_sessions' => $this->getActiveSessions(),
                'last_backup' => $this->getLastBackupDate()
            ]
        ];
        
        return view('admin.dashboard', compact('metrics'));
    }
}
```

### Widgets de Estado
- **Usuarios Activos**: Contador en tiempo real
- **Partituras Subidas**: Total y nuevas hoy
- **Ingresos**: Mensual y total
- **Rendimiento**: Tiempo de respuesta y uptime
- **Almacenamiento**: Uso actual y límites
- **Actividad Reciente**: Log de acciones importantes

## 👥 Gestión de Usuarios

### Listado de Usuarios
```javascript
// DataTables configuration for users
$('#users-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: '/get_users',
    columns: [
        { data: 'index', name: 'index', orderable: false },
        { data: 'name', name: 'name' },
        { data: 'email', name: 'email' },
        { data: 'register_date', name: 'created_at' },
        { data: 'last_updated', name: 'updated_at' },
        { data: 'status', name: 'status', orderable: false },
        { data: 'action', name: 'action', orderable: false }
    ],
    order: [[1, 'asc']],
    pageLength: 25,
    responsive: true
});
```

### Creación de Usuarios
```php
// User creation form
public function create()
{
    $roles = Role::all();
    $countries = Country::all();
    return view('admin.users.create', compact('roles', 'countries'));
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'telephone' => 'nullable|string',
        'country_code' => 'nullable|string',
        'role' => 'required|exists:roles,name'
    ]);
    
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'telephone' => $this->formatTelephone($request->telephone, $request->country_code),
        'status' => $request->user_status === 'on' ? 1 : 0,
        'email_verified_at' => now()
    ]);
    
    $role = Role::where('name', $request->role)->first();
    $user->attachRole($role);
    
    return redirect()->route('user.index')->with('success', 'Usuario creado exitosamente');
}
```

### Edición de Usuarios
```blade
<!-- User edit form -->
<form method="post" action="{{ route('user.update', $user->id) }}">
    @csrf
    @method('patch')
    
    <!-- Basic Information -->
    <div class="row">
        <div class="col-md-6">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" 
                         :value="old('name', $user->name)" required />
        </div>
        <div class="col-md-6">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" 
                         :value="old('email', $user->email)" required />
        </div>
    </div>
    
    <!-- Subscription Management -->
    <div class="subscription-section">
        <h4>Gestión de Suscripción</h4>
        <select name="sync_subscr_type" class="form-control">
            <option value="-1">Sin cambios</option>
            <option value="0">Gratuito {{ $level == 0 ? '(Actual)' : '' }}</option>
            <option value="1">Básico {{ $level == 1 ? '(Actual)' : '' }}</option>
            <option value="2">Premium {{ $level == 2 ? '(Actual)' : '' }}</option>
        </select>
    </div>
    
    <!-- Premium Trial Management -->
    <div class="trial-section">
        <h4>Gestión de Prueba Premium</h4>
        <input type="number" name="premium_trial_count" 
               value="{{ $user->premiumTrial?->used_count }}" 
               class="form-control" />
    </div>
    
    <!-- Action Buttons -->
    <div class="actions">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('user.sendUserRegisteredEmail', $user->id) }}" 
           class="btn btn-secondary">Enviar Email de Registro</a>
        <a href="{{ route('user.sendPlanOfferEmail', $user->id) }}" 
           class="btn btn-info">Enviar Oferta de Plan</a>
    </div>
</form>
```

### Funciones Avanzadas de Usuario
- **Cambio de Estado**: Toggle activo/suspendido
- **Gestión de Roles**: Asignación y modificación
- **Historial de Actividad**: Log completo de acciones
- **Estadísticas de Uso**: Métricas individuales
- **Exportación de Datos**: CSV y Excel

## 🎼 Gestión de Partituras

### Moderación de Contenido
```php
class AdminMusicScoreController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MusicScore::with(['composer', 'user', 'instruments', 'style'])
                ->select('music_scores.*');
            
            return DataTables::of($data)
                ->addColumn('composer_name', function ($row) {
                    return $row->composer->name ?? 'N/A';
                })
                ->addColumn('instruments_list', function ($row) {
                    return $row->instruments->pluck('name')->join(', ');
                })
                ->addColumn('status_toggle', function ($row) {
                    $checked = $row->status === 'published' ? 'checked' : '';
                    return '<input data-id="' . $row->id . '" 
                                   class="toggle-status" 
                                   type="checkbox" 
                                   data-toggle="toggle" 
                                   data-on="Publicado" 
                                   data-off="Borrador" ' . $checked . '>';
                })
                ->addColumn('statistics', function ($row) {
                    return '<span class="badge badge-info">Views: ' . $row->views_count . '</span>
                            <span class="badge badge-success">Downloads: ' . $row->downloads_count . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    return '<div class="btn-group">
                                <a href="' . route('music_score.show', $row->id) . '" 
                                   class="btn btn-sm btn-primary">Ver</a>
                                <a href="' . route('music_score.edit', $row->id) . '" 
                                   class="btn btn-sm btn-warning">Editar</a>
                                <button data-id="' . $row->id . '" 
                                        class="btn btn-sm btn-danger delete-score">Eliminar</button>
                            </div>';
                })
                ->rawColumns(['status_toggle', 'statistics', 'actions'])
                ->make(true);
        }
        
        return view('admin.music-scores.index');
    }
}
```

### Herramientas de Moderación
- **Revisión de Contenido**: Aprobación manual
- **Filtros de Calidad**: Validación automática
- **Reportes de Usuario**: Sistema de denuncias
- **Edición Masiva**: Operaciones en lote
- **Categorización**: Asignación automática y manual

## 👨‍🎨 Gestión de Compositores

### Solicitudes de Compositor
```php
class ComposerRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ComposerRequest::with('user')
                ->select('composer_requests.*');
            
            return DataTables::of($data)
                ->addColumn('user_name', function ($row) {
                    return $row->user->name;
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger'
                    ];
                    $class = $badges[$row->status] ?? 'secondary';
                    return '<span class="badge badge-' . $class . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('actions', function ($row) {
                    $buttons = '';
                    if ($row->status === 'pending') {
                        $buttons .= '<button class="btn btn-sm btn-success approve-request" 
                                           data-id="' . $row->id . '">Aprobar</button>';
                        $buttons .= '<button class="btn btn-sm btn-danger reject-request" 
                                           data-id="' . $row->id . '">Rechazar</button>';
                    }
                    $buttons .= '<a href="' . route('composer_request.show', $row->id) . '" 
                                   class="btn btn-sm btn-info">Ver Detalles</a>';
                    return $buttons;
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }
        
        return view('admin.composer-requests.index');
    }
    
    public function approve($id)
    {
        $request = ComposerRequest::findOrFail($id);
        
        // Create composer record
        $composer = Composer::create([
            'name' => $request->name,
            'biography' => $request->biography,
            'birth_date' => $request->birth_date,
            'death_date' => $request->death_date,
            'nationality' => $request->nationality,
            'users_id' => $request->user_id,
            'status' => 1
        ]);
        
        // Assign composer role
        $role = Role::where('name', 'composer')->first();
        $request->user->attachRole($role);
        
        // Update request status
        $request->update(['status' => 'approved']);
        
        // Send notification email
        Mail::to($request->user->email)->send(new ComposerApprovedEmail($request->user, $composer));
        
        return response()->json(['success' => 'Solicitud aprobada exitosamente']);
    }
}
```

## 💳 Gestión de Suscripciones

### Panel de Suscripciones
```php
class SubscriptionController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::with('subscribedUsers')->get();
        $statistics = [
            'total_revenue' => $this->calculateTotalRevenue(),
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'active_subscriptions' => SubscribedUser::where('subscription_end_date', '>', now())->count(),
            'churn_rate' => $this->calculateChurnRate()
        ];
        
        return view('admin.subscriptions.index', compact('plans', 'statistics'));
    }
    
    public function createPlan(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|integer|in:0,1,2',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'description' => 'required|string',
            'features' => 'required|array'
        ]);
        
        SubscriptionPlan::create([
            'name' => $request->name,
            'type' => $request->type,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'description' => $request->description,
            'features' => json_encode($request->features),
            'status' => 1,
            'start_date' => now(),
            'end_date' => $request->end_date
        ]);
        
        return redirect()->back()->with('success', 'Plan creado exitosamente');
    }
}
```

### Gestión de Usuarios Suscritos
```blade
<!-- Subscribed users table -->
<table id="subscribed-users-table" class="table table-striped">
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Plan</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Estado</th>
            <th>Método Pago</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($subscribedUsers as $subscription)
        <tr>
            <td>{{ $subscription->user->name }}</td>
            <td>
                <span class="badge badge-{{ $subscription->plan->type == 2 ? 'primary' : ($subscription->plan->type == 1 ? 'info' : 'secondary') }}">
                    {{ $subscription->plan->name }}
                </span>
            </td>
            <td>{{ $subscription->created_at->format('d/m/Y') }}</td>
            <td>{{ $subscription->subscription_end_date ? $subscription->subscription_end_date->format('d/m/Y') : 'N/A' }}</td>
            <td>
                @if($subscription->subscription_end_date && $subscription->subscription_end_date < now())
                    <span class="badge badge-danger">Expirado</span>
                @else
                    <span class="badge badge-success">Activo</span>
                @endif
            </td>
            <td>
                @if($subscription->paypal_subscription_id)
                    <i class="fab fa-paypal"></i> PayPal
                @else
                    <i class="fas fa-mobile-alt"></i> In-App
                @endif
            </td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-warning extend-subscription" 
                            data-id="{{ $subscription->id }}">Extender</button>
                    <button class="btn btn-sm btn-danger cancel-subscription" 
                            data-id="{{ $subscription->id }}">Cancelar</button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

## 🎹 Gestión de Instrumentos y Estilos

### CRUD de Instrumentos
```php
class InstrumentController extends Controller
{
    public function index()
    {
        $instruments = Instrument::with('family')->paginate(20);
        $families = FamilyInstrument::all();
        return view('admin.instruments.index', compact('instruments', 'families'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:instruments',
            'family_instrument_id' => 'required|exists:family_instruments,id',
            'description' => 'nullable|string'
        ]);
        
        Instrument::create($request->all());
        return redirect()->back()->with('success', 'Instrumento creado exitosamente');
    }
    
    public function updateStatus(Request $request)
    {
        $instrument = Instrument::findOrFail($request->id);
        $instrument->status = $request->status;
        $instrument->save();
        
        return response()->json(['success' => 'Estado actualizado']);
    }
}
```

### Gestión de Familias de Instrumentos
```javascript
// Family instrument management
class FamilyInstrumentManager {
    constructor() {
        this.initializeTable();
        this.bindEvents();
    }
    
    initializeTable() {
        this.table = $('#family-instruments-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/get-family-instruments',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'description', name: 'description' },
                { data: 'instruments_count', name: 'instruments_count', orderable: false },
                { data: 'status', name: 'status', orderable: false },
                { data: 'actions', name: 'actions', orderable: false }
            ]
        });
    }
    
    bindEvents() {
        // Status toggle
        $(document).on('change', '.family-status-toggle', (e) => {
            const familyId = $(e.target).data('id');
            const status = $(e.target).is(':checked') ? 1 : 0;
            
            this.updateStatus(familyId, status);
        });
        
        // Delete family
        $(document).on('click', '.delete-family', (e) => {
            const familyId = $(e.target).data('id');
            this.deleteFamily(familyId);
        });
    }
    
    updateStatus(familyId, status) {
        $.post('/change-family-instrument-status', {
            id: familyId,
            status: status,
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done((response) => {
            toastr.success('Estado actualizado exitosamente');
        }).fail(() => {
            toastr.error('Error al actualizar el estado');
        });
    }
}
```

## 📊 Reportes y Analytics

### Generación de Reportes
```php
class ReportsController extends Controller
{
    public function userActivity(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->subDays(30);
        $dateTo = $request->date_to ?? now();
        
        $data = User::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as new_users,
                COUNT(CASE WHEN status = 1 THEN 1 END) as active_users
            ')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return response()->json($data);
    }
    
    public function subscriptionRevenue(Request $request)
    {
        $revenue = SubscribedUser::join('subscription_plans', 'subscribed_users.subscription_plan_id', '=', 'subscription_plans.id')
            ->selectRaw('
                DATE_FORMAT(subscribed_users.created_at, "%Y-%m") as month,
                SUM(subscription_plans.price) as total_revenue,
                COUNT(*) as subscriptions_count
            ')
            ->where('subscription_plans.price', '>', 0)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        return response()->json($revenue);
    }
    
    public function contentStatistics()
    {
        $stats = [
            'music_scores' => [
                'total' => MusicScore::count(),
                'by_status' => MusicScore::groupBy('status')->selectRaw('status, COUNT(*) as count')->pluck('count', 'status'),
                'by_difficulty' => MusicScore::groupBy('difficulty')->selectRaw('difficulty, COUNT(*) as count')->pluck('count', 'difficulty'),
                'most_viewed' => MusicScore::withCount('viewLogs')->orderBy('view_logs_count', 'desc')->limit(10)->get()
            ],
            'composers' => [
                'total' => Composer::count(),
                'active' => Composer::where('status', 1)->count(),
                'most_productive' => Composer::withCount('musicScores')->orderBy('music_scores_count', 'desc')->limit(10)->get()
            ]
        ];
        
        return response()->json($stats);
    }
}
```

### Dashboard de Analytics
```blade
<!-- Analytics dashboard -->
<div class="analytics-dashboard">
    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Ingresos Mensuales</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenue-chart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- User Growth Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Crecimiento de Usuarios</h5>
                </div>
                <div class="card-body">
                    <canvas id="users-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Statistics -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Estadísticas de Contenido</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 id="total-scores">{{ $stats['total_scores'] }}</h3>
                                <p>Partituras Totales</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 id="total-composers">{{ $stats['total_composers'] }}</h3>
                                <p>Compositores</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 id="total-downloads">{{ $stats['total_downloads'] }}</h3>
                                <p>Descargas</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h3 id="active-users">{{ $stats['active_users'] }}</h3>
                                <p>Usuarios Activos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

## 🔧 Configuración del Sistema

### Configuraciones Generales
```php
class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'mail_from_name' => config('mail.from.name'),
            'mail_from_address' => config('mail.from.address'),
            'storage_driver' => config('filesystems.default'),
            'paypal_mode' => config('paypal.mode'),
            'max_upload_size' => ini_get('upload_max_filesize'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version()
        ];
        
        return view('admin.settings.index', compact('settings'));
    }
    
    public function updateEmail(Request $request)
    {
        $request->validate([
            'mail_driver' => 'required|string',
            'mail_host' => 'required_if:mail_driver,smtp|string',
            'mail_port' => 'required_if:mail_driver,smtp|integer',
            'mail_username' => 'required_if:mail_driver,smtp|string',
            'mail_password' => 'required_if:mail_driver,smtp|string',
            'mail_from_name' => 'required|string',
            'mail_from_address' => 'required|email'
        ]);
        
        // Update .env file
        $this->updateEnvFile([
            'MAIL_MAILER' => $request->mail_driver,
            'MAIL_HOST' => $request->mail_host,
            'MAIL_PORT' => $request->mail_port,
            'MAIL_USERNAME' => $request->mail_username,
            'MAIL_PASSWORD' => $request->mail_password,
            'MAIL_FROM_NAME' => $request->mail_from_name,
            'MAIL_FROM_ADDRESS' => $request->mail_from_address
        ]);
        
        return redirect()->back()->with('success', 'Configuración de email actualizada');
    }
}
```

## 📧 Sistema de Emails Masivos

### Campañas de Email
```php
class EmailCampaignController extends Controller
{
    public function create()
    {
        $templates = EmailTemplate::where('status', 'active')->get();
        return view('admin.email-campaigns.create', compact('templates'));
    }
    
    public function send(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'template_id' => 'required|exists:email_templates,id',
            'recipient_type' => 'required|in:all_users,subscribers,free_users,composers',
            'send_at' => 'nullable|date|after:now'
        ]);
        
        $recipients = $this->getRecipients($request->recipient_type);
        
        $campaign = EmailCampaign::create([
            'subject' => $request->subject,
            'template_id' => $request->template_id,
            'recipient_type' => $request->recipient_type,
            'recipient_count' => $recipients->count(),
            'send_at' => $request->send_at ?? now(),
            'status' => 'scheduled'
        ]);
        
        // Queue the emails
        foreach ($recipients as $user) {
            SendCampaignEmail::dispatch($campaign, $user)
                ->delay($request->send_at ?? now());
        }
        
        return redirect()->route('email-campaigns.index')
                        ->with('success', 'Campaña programada exitosamente');
    }
}
```

## 📱 Gestión de API

### Monitor de API
```php
class ApiMonitorController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_requests' => $this->getTotalRequests(),
            'requests_today' => $this->getRequestsToday(),
            'average_response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
            'top_endpoints' => $this->getTopEndpoints(),
            'top_users' => $this->getTopApiUsers()
        ];
        
        return view('admin.api.dashboard', compact('stats'));
    }
    
    public function rateLimits()
    {
        $limits = [
            'public_endpoints' => 60, // per minute
            'authenticated_endpoints' => 1000, // per minute
            'upload_endpoints' => 10, // per minute
            'download_endpoints' => 100 // per minute
        ];
        
        return view('admin.api.rate-limits', compact('limits'));
    }
}
```

## 🛡️ Seguridad y Auditoría

### Log de Actividades
```php
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->user_id, function($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->when($request->action, function($query, $action) {
                return $query->where('action', $action);
            })
            ->when($request->date_from, function($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('admin.activity-logs.index', compact('logs'));
    }
    
    public static function log($action, $description, $userId = null, $relatedId = null, $relatedType = null)
    {
        ActivityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'related_id' => $relatedId,
            'related_type' => $relatedType,
            'created_at' => now()
        ]);
    }
}
```

### Herramientas de Seguridad
- **Monitor de Intentos de Login**: Detección de ataques
- **Blacklist de IPs**: Bloqueo automático
- **Auditoría de Permisos**: Revisión de accesos
- **Backup Automático**: Respaldo de datos críticos
- **SSL Monitoring**: Verificación de certificados

## 📞 Soporte y Mantenimiento

### Centro de Soporte
```php
class SupportController extends Controller
{
    public function tickets()
    {
        $tickets = SupportTicket::with('user')
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.support.tickets', compact('tickets'));
    }
    
    public function respond(Request $request, $ticketId)
    {
        $ticket = SupportTicket::findOrFail($ticketId);
        
        SupportResponse::create([
            'ticket_id' => $ticketId,
            'admin_id' => auth()->id(),
            'response' => $request->response,
            'is_internal' => $request->is_internal ?? false
        ]);
        
        if (!$request->is_internal) {
            $ticket->update(['status' => 'responded']);
            Mail::to($ticket->user->email)->send(new SupportResponseEmail($ticket, $request->response));
        }
        
        return redirect()->back()->with('success', 'Respuesta enviada exitosamente');
    }
}
```

---

**Documentación actualizada**: 15 de Enero, 2024  
**Versión**: v1.0  
**Soporte**: admin@faristol.net
