# 👤 Gestión de Usuarios - Faristol

## 📋 Descripción General

El sistema de gestión de usuarios de Faristol maneja el registro, autenticación, roles y administración de perfiles con verificación por email y controles administrativos completos.

## 🎭 Roles de Usuario

### Roles por Defecto
- **musician**: Rol predeterminado para nuevos usuarios
- **composer**: Usuarios que pueden subir partituras musicales
- **superadmin**: Acceso completo al sistema

### Permisos Basados en Roles
```php
// Estructura de permisos
const ROLE_PERMISSIONS = [
    'musician' => [
        'view_music_scores',
        'download_music_scores',
        'add_favorites',
        'basic_annotations' // Limitadas
    ],
    'composer' => [
        'view_music_scores',
        'download_music_scores',
        'upload_music_scores',
        'manage_own_scores',
        'add_favorites',
        'advanced_annotations'
    ],
    'superadmin' => [
        'view_all',
        'manage_all',
        'admin_access',
        'user_management',
        'system_settings'
    ]
];
```

## 📝 Registro de Usuarios

### Proceso de Registro
```php
// UserController.php - Registro
public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'telephone' => 'nullable|string|unique:users',
        'country_code' => 'nullable|string'
    ]);
    
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'telephone' => $this->formatTelephone($request->telephone, $request->country_code),
        'status' => 1
    ]);
    
    // Asignar rol por defecto
    $musicianRole = Role::where('name', 'musician')->first();
    $user->attachRole($musicianRole);
    
    // Enviar email de verificación
    $user->sendEmailVerificationNotification();
    
    // Crear token de API
    $token = $user->createToken('auth_token')->plainTextToken;
    
    return response()->json([
        'status' => true,
        'message' => 'Usuario registrado exitosamente',
        'data' => [
            'user' => $user,
            'token' => $token
        ]
    ]);
}

private function formatTelephone($telephone, $countryCode)
{
    if (!$telephone) return null;
    return "(+{$countryCode}){$telephone}";
}
```

### Verificación de Email
```php
// Email verification system
class EmailVerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        
        return response()->json([
            'status' => true,
            'message' => 'Email verificado exitosamente'
        ]);
    }
    
    public function resend(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => false,
                'message' => 'Email ya verificado'
            ]);
        }
        
        $user->sendEmailVerificationNotification();
        
        return response()->json([
            'status' => true,
            'message' => 'Email de verificación enviado'
        ]);
    }
}
```

## 🔐 Sistema de Autenticación

### Login con Sanctum
```php
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);
    
    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'status' => false,
            'message' => 'Credenciales inválidas'
        ], 401);
    }
    
    $user = Auth::user();
    
    // Verificar estado de cuenta
    if (!$user->status) {
        return response()->json([
            'status' => false,
            'message' => 'Cuenta suspendida'
        ], 403);
    }
    
    // Crear token
    $token = $user->createToken('auth_token')->plainTextToken;
    
    // Obtener información de suscripción
    $subscription = app(SubscriptionService::class)->getSubscriptionDetails($user->id);
    
    return response()->json([
        'status' => true,
        'message' => 'Login exitoso',
        'data' => [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'subscription' => $subscription
            ],
            'token' => $token,
            'expires_at' => now()->addDays(30)
        ]
    ]);
}
```

### Gestión de Contraseñas

#### Sistema OTP
```php
class PasswordResetController extends Controller
{
    public function requestOTP(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users']);
        
        $user = User::where('email', $request->email)->first();
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Guardar OTP en cache (5 minutos)
        Cache::put("otp:{$user->email}", $otp, now()->addMinutes(5));
        
        // Enviar email con OTP
        Mail::to($user->email)->send(new OTPEmail($otp));
        
        return response()->json([
            'status' => true,
            'message' => 'OTP enviado por email'
        ]);
    }
    
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6'
        ]);
        
        $cachedOTP = Cache::get("otp:{$request->email}");
        
        if (!$cachedOTP || $cachedOTP !== $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'OTP inválido o expirado'
            ], 400);
        }
        
        return response()->json([
            'status' => true,
            'message' => 'OTP verificado correctamente'
        ]);
    }
    
    public function changePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|min:8|confirmed'
        ]);
        
        // Verificar OTP
        $cachedOTP = Cache::get("otp:{$request->email}");
        if (!$cachedOTP || $cachedOTP !== $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'OTP inválido'
            ], 400);
        }
        
        // Cambiar contraseña
        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);
        
        // Limpiar OTP
        Cache::forget("otp:{$request->email}");
        
        return response()->json([
            'status' => true,
            'message' => 'Contraseña cambiada exitosamente'
        ]);
    }
}
```

## 👤 Gestión de Perfiles

### Campos del Perfil de Usuario
```php
// User Model
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, EntrustUserTrait;
    
    protected $fillable = [
        'name',
        'email',
        'telephone',
        'password',
        'status'
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'boolean'
    ];
    
    // Relaciones
    public function subscriptions()
    {
        return $this->hasMany(SubscribedUser::class);
    }
    
    public function premiumTrial()
    {
        return $this->hasOne(PremiumTrial::class);
    }
    
    public function favoritesMusicScores()
    {
        return $this->belongsToMany(MusicScore::class, 'user_favorite_music_scores');
    }
    
    public function musicScores()
    {
        return $this->hasMany(MusicScore::class);
    }
    
    // Accessors
    public function getTelephoneDisplayAttribute()
    {
        if (!$this->telephone) return null;
        return preg_replace('/\(\+\d+\)(\d+)/', '(+XX)****$1', $this->telephone);
    }
}
```

### Actualización de Perfil
```php
public function updateProfile(Request $request, $id)
{
    $user = User::findOrFail($id);
    
    // Verificar autorización
    if (auth()->id() !== $user->id && !auth()->user()->hasRole('superadmin')) {
        return response()->json(['status' => false, 'message' => 'No autorizado'], 403);
    }
    
    $request->validate([
        'name' => 'string|max:255',
        'telephone' => 'nullable|string|unique:users,telephone,' . $user->id,
        'country_code' => 'nullable|string',
        'profile_image' => 'nullable|string' // Base64 image
    ]);
    
    $updateData = [];
    
    if ($request->has('name')) {
        $updateData['name'] = $request->name;
    }
    
    if ($request->has('telephone') && $request->has('country_code')) {
        $updateData['telephone'] = $this->formatTelephone(
            $request->telephone, 
            $request->country_code
        );
    }
    
    if ($request->has('profile_image')) {
        $updateData['profile_image'] = $this->uploadProfileImage($request->profile_image);
    }
    
    $user->update($updateData);
    
    return response()->json([
        'status' => true,
        'message' => 'Perfil actualizado exitosamente',
        'data' => $user->fresh()
    ]);
}

private function uploadProfileImage($base64Image)
{
    // Decodificar imagen base64
    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
    
    // Generar nombre único
    $fileName = 'profile_' . auth()->id() . '_' . time() . '.jpg';
    
    // Subir a storage
    Storage::disk('s3')->put("profiles/{$fileName}", $imageData);
    
    return Storage::disk('s3')->url("profiles/{$fileName}");
}
```

## 🛠️ Administración de Usuarios

### Panel de Control de Admin
```php
class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with(['roles', 'subscriptions.plan']);
            
            return DataTables::of($users)
                ->addColumn('index', function ($row) {
                    static $index = 0;
                    return ++$index;
                })
                ->addColumn('roles', function ($row) {
                    return $row->roles->pluck('display_name')->join(', ');
                })
                ->addColumn('subscription', function ($row) {
                    $subscription = $row->subscriptions()
                        ->where('subscription_end_date', '>', now())
                        ->first();
                    
                    return $subscription ? $subscription->plan->name : 'Free';
                })
                ->addColumn('status', function ($row) {
                    $checked = $row->status ? 'checked' : '';
                    return '<input data-id="' . $row->id . '" 
                                   class="toggle-status" 
                                   type="checkbox" 
                                   data-toggle="toggle" ' . $checked . '>';
                })
                ->addColumn('register_date', function ($row) {
                    return $row->created_at->format('d/m/Y H:i');
                })
                ->addColumn('last_updated', function ($row) {
                    return $row->updated_at->format('d/m/Y H:i');
                })
                ->addColumn('action', function ($row) {
                    return '<div class="btn-group">
                                <a href="' . route('user.edit', $row->id) . '" 
                                   class="btn btn-sm btn-primary">Editar</a>
                                <a href="' . route('user.sendUserRegisteredEmail', $row->id) . '" 
                                   class="btn btn-sm btn-info">Email</a>
                                <button data-id="' . $row->id . '" 
                                        class="btn btn-sm btn-danger delete-user">Eliminar</button>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        
        return view('admin.users.index');
    }
}
```

### Edición Avanzada de Usuario
```blade
<!-- admin/users/edit.blade.php -->
<form method="post" action="{{ route('user.update', $user->id) }}">
    @csrf
    @method('patch')
    
    <!-- Información Básica -->
    <div class="card">
        <div class="card-header">
            <h5>Información Básica</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label for="name">Nombre</label>
                    <input type="text" name="name" value="{{ $user->name }}" 
                           class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="email">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" 
                           class="form-control" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <label for="telephone">Teléfono</label>
                    <input type="text" name="telephone" value="{{ $user->telephone }}" 
                           class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="status">Estado</label>
                    <select name="user_status" class="form-control">
                        <option value="1" {{ $user->status ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$user->status ? 'selected' : '' }}>Suspendido</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gestión de Suscripción -->
    <div class="card mt-3">
        <div class="card-header">
            <h5>Gestión de Suscripción</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label>Nivel Actual: <strong>{{ $currentSubscription['plan_name'] ?? 'Free' }}</strong></label>
                    <select name="sync_subscr_type" class="form-control mt-2">
                        <option value="-1">Sin cambios</option>
                        <option value="0">Gratuito {{ $level == 0 ? '(Actual)' : '' }}</option>
                        <option value="1">Básico {{ $level == 1 ? '(Actual)' : '' }}</option>
                        <option value="2">Premium {{ $level == 2 ? '(Actual)' : '' }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Duración (días)</label>
                    <input type="number" name="subscription_duration" 
                           value="30" class="form-control" min="1">
                </div>
            </div>
            
            @if($currentSubscription)
            <div class="subscription-info mt-3">
                <p><strong>Fecha de expiración:</strong> {{ $currentSubscription['expires_at'] }}</p>
                <p><strong>Método de pago:</strong> {{ $currentSubscription['payment_method'] ?? 'N/A' }}</p>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Gestión de Prueba Premium -->
    <div class="card mt-3">
        <div class="card-header">
            <h5>Gestión de Prueba Premium</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label>Usos de Prueba Premium</label>
                    <input type="number" name="premium_trial_count" 
                           value="{{ $user->premiumTrial?->used_count ?? 0 }}" 
                           class="form-control" min="0" max="3">
                    <small class="text-muted">Máximo 3 usos permitidos</small>
                </div>
                <div class="col-md-6">
                    <label>Última Prueba</label>
                    <input type="text" 
                           value="{{ $user->premiumTrial?->last_used_at?->format('d/m/Y H:i') ?? 'Nunca' }}" 
                           class="form-control" readonly>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas de Usuario -->
    <div class="card mt-3">
        <div class="card-header">
            <h5>Estadísticas de Usuario</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <h4>{{ $user->musicScores()->count() }}</h4>
                        <p>Partituras Subidas</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <h4>{{ $user->favoritesMusicScores()->count() }}</h4>
                        <p>Favoritos</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <h4>{{ $totalViews ?? 0 }}</h4>
                        <p>Visualizaciones</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <h4>{{ $user->created_at->diffForHumans() }}</h4>
                        <p>Registrado</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Acciones -->
    <div class="card mt-3">
        <div class="card-header">
            <h5>Acciones</h5>
        </div>
        <div class="card-body">
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="{{ route('user.sendUserRegisteredEmail', $user->id) }}" 
                   class="btn btn-secondary">Enviar Email de Registro</a>
                <a href="{{ route('user.sendPlanOfferEmail', $user->id) }}" 
                   class="btn btn-info">Enviar Oferta de Plan</a>
            </div>
            
            @if($user->hasRole('composer'))
            <div class="composer-actions mt-3">
                <h6>Acciones de Compositor</h6>
                <a href="{{ route('admin.composers.show', $user->composer->id) }}" 
                   class="btn btn-sm btn-outline-primary">Ver Perfil de Compositor</a>
            </div>
            @endif
        </div>
    </div>
</form>
```

## 🎨 Sistema de Compositores

### Solicitud para ser Compositor
```php
public function requestComposerStatus(Request $request)
{
    $user = auth()->user();
    
    // Verificar si ya es compositor
    if ($user->hasRole('composer')) {
        return response()->json([
            'status' => false,
            'message' => 'Ya eres compositor'
        ]);
    }
    
    // Verificar solicitud pendiente
    $existingRequest = ComposerRequest::where('user_id', $user->id)
        ->where('status', 'pending')
        ->first();
    
    if ($existingRequest) {
        return response()->json([
            'status' => false,
            'message' => 'Ya tienes una solicitud pendiente'
        ]);
    }
    
    $request->validate([
        'composer_name' => 'required|string|max:255',
        'biography' => 'required|string|min:100',
        'portfolio_url' => 'nullable|url'
    ]);
    
    ComposerRequest::create([
        'user_id' => $user->id,
        'name' => $request->composer_name,
        'biography' => $request->biography,
        'portfolio_url' => $request->portfolio_url,
        'status' => 'pending'
    ]);
    
    return response()->json([
        'status' => true,
        'message' => 'Solicitud enviada exitosamente'
    ]);
}
```

## 📧 Notificaciones por Email

### Email de Registro
```php
class UserRegisteredEmail extends Mailable
{
    public $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Bienvenido a Faristol! 🎵'
        );
    }
    
    public function content(): Content
    {
        return new Content(
            view: 'emails.user_registered',
            with: [
                'user' => $this->user,
                'features' => [
                    '📚 Acceso a miles de partituras',
                    '🔍 Búsqueda avanzada',
                    '📝 Sistema de anotaciones',
                    '⭐ Favoritos personalizados'
                ]
            ]
        );
    }
    
    public function attachments(): array
    {
        return [];
    }
}
```

### Email de Oferta de Plan
```php
public function sendPlanOffer($userId)
{
    $user = User::findOrFail($userId);
    
    $offer = [
        'discount' => 25,
        'valid_until' => now()->addDays(7),
        'features' => [
            'Anotaciones ilimitadas',
            'Sin publicidad',
            'Acceso prioritario a contenido',
            'Soporte técnico premium'
        ]
    ];
    
    Mail::to($user->email)->send(new PlanOfferEmail($user, $offer));
    
    return response()->json([
        'status' => true,
        'message' => 'Email de oferta enviado'
    ]);
}
```

## 📊 Estados de Cuenta

### Estados Posibles
- **Active (1)**: Acceso completo
- **Suspended (0)**: Acceso limitado
- **Pending Email Verification**: Esperando verificación

### Control de Estados
```php
public function changeUserStatus(Request $request)
{
    $user = User::findOrFail($request->id);
    $user->status = $request->status;
    $user->save();
    
    // Log de actividad
    ActivityLog::create([
        'user_id' => auth()->id(),
        'action' => 'user_status_changed',
        'description' => "Changed user {$user->name} status to " . ($request->status ? 'active' : 'suspended'),
        'related_id' => $user->id,
        'related_type' => 'User'
    ]);
    
    // Notificar al usuario si es suspensión
    if (!$request->status) {
        Mail::to($user->email)->send(new AccountSuspendedEmail($user));
    }
    
    return response()->json(['success' => true]);
}
```

## 🔐 Seguridad

### Protección de Datos
```php
// Middleware para verificar propiedad de datos
class CheckUserOwnership
{
    public function handle($request, Closure $next)
    {
        $userId = $request->route('id');
        $authUser = auth()->user();
        
        // Verificar si es el mismo usuario o admin
        if ($authUser->id !== (int)$userId && !$authUser->hasRole('superadmin')) {
            return response()->json([
                'status' => false,
                'message' => 'No autorizado'
            ], 403);
        }
        
        return $next($request);
    }
}
```

## 📱 API de Usuario

### Endpoints Principales
```http
# Autenticación
POST /api/auth/user/signup
POST /api/auth/login
POST /api/auth/logout

# Gestión de perfil
GET /api/auth/user/check-user
POST /api/auth/user/edit/{id}

# Verificación
POST /api/auth/user/request-otp
POST /api/auth/user/verify-otp
POST /api/auth/user/change-password

# Estados
GET /api/auth/user/check-subscription
GET /api/auth/user/check-composer

# Admin (requiere permisos)
GET /api/admin/users
POST /api/admin/users/{id}/change-status
POST /api/admin/users/{id}/send-email
```

---

**Documentación actualizada**: 15 de Enero, 2024  
**Versión**: v1.0  
**Soporte**: users@faristol.net
