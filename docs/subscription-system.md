# 💳 Sistema de Suscripciones - Faristol

## 📋 Descripción General

El sistema de suscripciones de Faristol permite a los usuarios acceder a diferentes niveles de funcionalidades mediante planes de pago, incluyendo integración con PayPal y gestión automática de renovaciones.

## 💰 Planes de Suscripción

### Niveles Disponibles

#### Plan Gratuito (Tipo 0)
```php
const FREE_PLAN_FEATURES = [
    'annotations' => 5,
    'advertisements' => true,
    'favorites' => false,
    'priority_support' => false,
    'high_quality_pdf' => false,
    'download_limit' => 5
];
```

#### Plan Básico (Tipo 1)
```php
const BASIC_PLAN_FEATURES = [
    'annotations' => 15,
    'advertisements' => false,
    'favorites' => false,
    'priority_support' => false,
    'high_quality_pdf' => true,
    'download_limit' => 50
];
```

#### Plan Premium (Tipo 2)
```php
const PREMIUM_PLAN_FEATURES = [
    'annotations' => 'unlimited',
    'advertisements' => false,
    'favorites' => true,
    'priority_support' => true,
    'high_quality_pdf' => true,
    'download_limit' => 'unlimited'
];
```

### Estructura de Planes
```php
// SubscriptionPlan Model
class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'price',
        'currency',
        'duration_days',
        'features',
        'status',
        'start_date',
        'end_date'
    ];
    
    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];
    
    public function subscribedUsers()
    {
        return $this->hasMany(SubscribedUser::class);
    }
    
    public function isActive()
    {
        return $this->status && 
               (!$this->start_date || $this->start_date <= now()) &&
               (!$this->end_date || $this->end_date >= now());
    }
}
```

## 🔄 Gestión de Suscripciones

### Modelo de Usuario Suscrito
```php
class SubscribedUser extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'subscription_start_date',
        'subscription_end_date',
        'paypal_subscription_id',
        'payment_method',
        'auto_renew',
        'status'
    ];
    
    protected $casts = [
        'subscription_start_date' => 'datetime',
        'subscription_end_date' => 'datetime',
        'auto_renew' => 'boolean'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
    
    public function isActive()
    {
        return $this->status === 'active' && 
               (!$this->subscription_end_date || $this->subscription_end_date > now());
    }
    
    public function isExpired()
    {
        return $this->subscription_end_date && $this->subscription_end_date < now();
    }
}
```

### Servicio de Suscripciones
```php
class SubscriptionService
{
    public function getSubscriptionDetails($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        $activeSubscription = SubscribedUser::where('user_id', $userId)
            ->where('subscription_end_date', '>', now())
            ->where('status', 'active')
            ->with('plan')
            ->first();
        
        if (!$activeSubscription) {
            return [
                'level' => 0,
                'plan_name' => 'Free',
                'is_paid' => false,
                'expires_at' => null,
                'features' => $this->getFreeFeatures()
            ];
        }
        
        return [
            'level' => $activeSubscription->plan->type,
            'plan_name' => $activeSubscription->plan->name,
            'is_paid' => $activeSubscription->plan->price > 0,
            'expires_at' => $activeSubscription->subscription_end_date,
            'payment_method' => $activeSubscription->payment_method,
            'auto_renew' => $activeSubscription->auto_renew,
            'features' => $activeSubscription->plan->features
        ];
    }
    
    public function subscribe($userId, $planType, $paymentData = [])
    {
        $user = User::findOrFail($userId);
        $plan = SubscriptionPlan::where('type', $planType)
            ->where('status', 1)
            ->first();
        
        if (!$plan) {
            throw new \Exception('Plan no disponible');
        }
        
        // Cancelar suscripción activa si existe
        $this->cancelActiveSubscription($userId);
        
        // Crear nueva suscripción
        $subscription = SubscribedUser::create([
            'user_id' => $userId,
            'subscription_plan_id' => $plan->id,
            'subscription_start_date' => now(),
            'subscription_end_date' => $plan->duration_days ? 
                now()->addDays($plan->duration_days) : null,
            'paypal_subscription_id' => $paymentData['paypal_subscription_id'] ?? null,
            'payment_method' => $paymentData['payment_method'] ?? 'manual',
            'auto_renew' => $paymentData['auto_renew'] ?? false,
            'status' => 'active'
        ]);
        
        // Enviar email de confirmación
        Mail::to($user->email)->send(new SubscriptionConfirmationEmail($user, $subscription));
        
        return $subscription;
    }
    
    public function cancelActiveSubscription($userId)
    {
        $activeSubscriptions = SubscribedUser::where('user_id', $userId)
            ->where('status', 'active')
            ->get();
        
        foreach ($activeSubscriptions as $subscription) {
            $subscription->update(['status' => 'cancelled']);
            
            // Cancelar en PayPal si es necesario
            if ($subscription->paypal_subscription_id) {
                $this->cancelPayPalSubscription($subscription->paypal_subscription_id);
            }
        }
    }
}
```

## 🎁 Sistema de Prueba Premium

### Gestión de Pruebas
```php
class PremiumTrial extends Model
{
    protected $fillable = [
        'user_id',
        'used_count',
        'last_used_at'
    ];
    
    protected $casts = [
        'last_used_at' => 'datetime'
    ];
    
    const MAX_TRIALS = 3;
    const TRIAL_DURATION_DAYS = 31;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function canUseTrial()
    {
        return $this->used_count < self::MAX_TRIALS;
    }
    
    public function getRemainingTrials()
    {
        return max(0, self::MAX_TRIALS - $this->used_count);
    }
}

class PremiumTrialController extends Controller
{
    public function applyTrial(Request $request)
    {
        $user = auth()->user();
        
        // Verificar si ya tiene suscripción activa
        $activeSubscription = SubscribedUser::where('user_id', $user->id)
            ->where('subscription_end_date', '>', now())
            ->where('status', 'active')
            ->first();
        
        if ($activeSubscription) {
            return response()->json([
                'status' => false,
                'message' => 'Ya tienes una suscripción activa'
            ]);
        }
        
        // Obtener o crear registro de prueba
        $trial = PremiumTrial::firstOrCreate(
            ['user_id' => $user->id],
            ['used_count' => 0]
        );
        
        // Verificar límite de pruebas
        if (!$trial->canUseTrial()) {
            return response()->json([
                'status' => false,
                'message' => 'Has agotado tus pruebas premium gratuitas'
            ]);
        }
        
        // Obtener plan premium
        $premiumPlan = SubscriptionPlan::where('type', 2)->first();
        
        // Crear suscripción temporal
        $subscription = SubscribedUser::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $premiumPlan->id,
            'subscription_start_date' => now(),
            'subscription_end_date' => now()->addDays(PremiumTrial::TRIAL_DURATION_DAYS),
            'payment_method' => 'trial',
            'auto_renew' => false,
            'status' => 'active'
        ]);
        
        // Actualizar contador de pruebas
        $trial->increment('used_count');
        $trial->update(['last_used_at' => now()]);
        
        return response()->json([
            'status' => true,
            'message' => 'Prueba premium activada por 31 días',
            'data' => [
                'expires_at' => $subscription->subscription_end_date,
                'remaining_trials' => $trial->getRemainingTrials()
            ]
        ]);
    }
}
```

## 💳 Integración PayPal

### Configuración PayPal
```php
// config/paypal.php
return [
    'mode' => env('PAYPAL_MODE', 'sandbox'),
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'webhook_url' => env('PAYPAL_WEBHOOK_URL'),
    
    'plans' => [
        'basic' => env('PAYPAL_BASIC_PLAN_ID'),
        'premium' => env('PAYPAL_PREMIUM_PLAN_ID')
    ]
];
```

### Servicio PayPal
```php
class PayPalService
{
    private $client;
    
    public function __construct()
    {
        $this->client = new PayPalHttpClient($this->environment());
    }
    
    private function environment()
    {
        $mode = config('paypal.mode');
        return $mode === 'live' ? 
            new ProductionEnvironment(config('paypal.client_id'), config('paypal.client_secret')) :
            new SandboxEnvironment(config('paypal.client_id'), config('paypal.client_secret'));
    }
    
    public function createSubscription($planId, $returnUrl, $cancelUrl)
    {
        $request = new SubscriptionsCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'plan_id' => $planId,
            'application_context' => [
                'brand_name' => 'Faristol',
                'locale' => 'es-ES',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED'
                ],
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl
            ]
        ];
        
        try {
            $response = $this->client->execute($request);
            return $response->result;
        } catch (HttpException $ex) {
            throw new \Exception('Error creating PayPal subscription: ' . $ex->getMessage());
        }
    }
    
    public function getSubscription($subscriptionId)
    {
        $request = new SubscriptionsGetRequest($subscriptionId);
        
        try {
            $response = $this->client->execute($request);
            return $response->result;
        } catch (HttpException $ex) {
            throw new \Exception('Error getting PayPal subscription: ' . $ex->getMessage());
        }
    }
    
    public function cancelSubscription($subscriptionId, $reason = 'User requested cancellation')
    {
        $request = new SubscriptionsCancelRequest($subscriptionId);
        $request->body = [
            'reason' => $reason
        ];
        
        try {
            $response = $this->client->execute($request);
            return true;
        } catch (HttpException $ex) {
            throw new \Exception('Error cancelling PayPal subscription: ' . $ex->getMessage());
        }
    }
}
```

### Controlador de Suscripciones
```php
class SubscriptionController extends Controller
{
    private $paypalService;
    private $subscriptionService;
    
    public function __construct(PayPalService $paypalService, SubscriptionService $subscriptionService)
    {
        $this->paypalService = $paypalService;
        $this->subscriptionService = $subscriptionService;
    }
    
    public function subscribe(Request $request)
    {
        $request->validate([
            'type' => 'required|integer|in:1,2',
            'return_url' => 'required|url',
            'cancel_url' => 'required|url'
        ]);
        
        $user = auth()->user();
        $planType = $request->type;
        
        // Obtener plan
        $plan = SubscriptionPlan::where('type', $planType)->first();
        if (!$plan) {
            return response()->json([
                'status' => false,
                'message' => 'Plan no encontrado'
            ]);
        }
        
        // Crear suscripción en PayPal
        $paypalPlanId = $planType === 1 ? 
            config('paypal.plans.basic') : 
            config('paypal.plans.premium');
        
        try {
            $paypalSubscription = $this->paypalService->createSubscription(
                $paypalPlanId,
                $request->return_url,
                $request->cancel_url
            );
            
            // Encontrar link de aprobación
            $approvalLink = collect($paypalSubscription->links)
                ->firstWhere('rel', 'approve');
            
            return response()->json([
                'status' => true,
                'data' => [
                    'subscription_id' => $paypalSubscription->id,
                    'approval_url' => $approvalLink->href,
                    'plan' => $plan
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al crear suscripción: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function confirmSubscription(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|string',
            'plan_type' => 'required|integer|in:1,2'
        ]);
        
        try {
            // Verificar suscripción en PayPal
            $paypalSubscription = $this->paypalService->getSubscription($request->subscription_id);
            
            if ($paypalSubscription->status !== 'ACTIVE') {
                return response()->json([
                    'status' => false,
                    'message' => 'Suscripción no activa en PayPal'
                ]);
            }
            
            // Crear suscripción local
            $subscription = $this->subscriptionService->subscribe(
                auth()->id(),
                $request->plan_type,
                [
                    'paypal_subscription_id' => $request->subscription_id,
                    'payment_method' => 'paypal',
                    'auto_renew' => true
                ]
            );
            
            return response()->json([
                'status' => true,
                'message' => 'Suscripción activada exitosamente',
                'data' => $subscription
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al confirmar suscripción: ' . $e->getMessage()
            ], 500);
        }
    }
}
```

## 🔔 Webhooks de PayPal

### Manejo de Webhooks
```php
class PayPalWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verificar webhook de PayPal
        if (!$this->verifyWebhook($request)) {
            return response()->json(['error' => 'Invalid webhook'], 400);
        }
        
        $eventType = $request->input('event_type');
        $resource = $request->input('resource');
        
        switch ($eventType) {
            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                $this->handleSubscriptionActivated($resource);
                break;
                
            case 'BILLING.SUBSCRIPTION.CANCELLED':
                $this->handleSubscriptionCancelled($resource);
                break;
                
            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                $this->handleSubscriptionSuspended($resource);
                break;
                
            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                $this->handlePaymentFailed($resource);
                break;
                
            case 'PAYMENT.SALE.COMPLETED':
                $this->handlePaymentCompleted($resource);
                break;
        }
        
        return response()->json(['status' => 'processed']);
    }
    
    private function handleSubscriptionActivated($resource)
    {
        $subscription = SubscribedUser::where('paypal_subscription_id', $resource['id'])->first();
        if ($subscription) {
            $subscription->update(['status' => 'active']);
            
            // Enviar email de confirmación
            Mail::to($subscription->user->email)
                ->send(new SubscriptionActivatedEmail($subscription));
        }
    }
    
    private function handleSubscriptionCancelled($resource)
    {
        $subscription = SubscribedUser::where('paypal_subscription_id', $resource['id'])->first();
        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'auto_renew' => false
            ]);
            
            // Enviar email de cancelación
            Mail::to($subscription->user->email)
                ->send(new SubscriptionCancelledEmail($subscription));
        }
    }
    
    private function handlePaymentFailed($resource)
    {
        $subscription = SubscribedUser::where('paypal_subscription_id', $resource['billing_agreement_id'])->first();
        if ($subscription) {
            // Marcar como suspendida temporalmente
            $subscription->update(['status' => 'payment_failed']);
            
            // Enviar email de problema de pago
            Mail::to($subscription->user->email)
                ->send(new PaymentFailedEmail($subscription));
        }
    }
}
```

## 📊 Dashboard de Suscripciones

### Panel de Usuario
```php
public function subscriptionDashboard()
{
    $user = auth()->user();
    $subscriptionDetails = app(SubscriptionService::class)->getSubscriptionDetails($user->id);
    
    // Obtener historial de suscripciones
    $subscriptionHistory = SubscribedUser::where('user_id', $user->id)
        ->with('plan')
        ->orderBy('created_at', 'desc')
        ->get();
    
    // Obtener uso actual
    $currentUsage = [
        'annotations_used' => $this->getAnnotationsCount($user->id),
        'downloads_this_month' => $this->getDownloadsCount($user->id),
        'favorites_count' => $user->favoritesMusicScores()->count()
    ];
    
    return response()->json([
        'status' => true,
        'data' => [
            'current_subscription' => $subscriptionDetails,
            'usage' => $currentUsage,
            'history' => $subscriptionHistory,
            'available_plans' => SubscriptionPlan::where('status', 1)->get()
        ]
    ]);
}
```

### Panel de Admin
```blade
<!-- admin/subscriptions/dashboard.blade.php -->
<div class="subscription-dashboard">
    <div class="row">
        <!-- Métricas de Ingresos -->
        <div class="col-md-3">
            <div class="metric-card">
                <h3>€{{ number_format($monthlyRevenue, 2) }}</h3>
                <p>Ingresos del Mes</p>
                <small class="text-success">+{{ $revenueGrowth }}% vs mes anterior</small>
            </div>
        </div>
        
        <!-- Suscripciones Activas -->
        <div class="col-md-3">
            <div class="metric-card">
                <h3>{{ $activeSubscriptions }}</h3>
                <p>Suscripciones Activas</p>
                <small class="text-info">{{ $newSubscriptions }} nuevas este mes</small>
            </div>
        </div>
        
        <!-- Tasa de Conversión -->
        <div class="col-md-3">
            <div class="metric-card">
                <h3>{{ number_format($conversionRate, 1) }}%</h3>
                <p>Tasa de Conversión</p>
                <small class="text-warning">De trial a pago</small>
            </div>
        </div>
        
        <!-- Churn Rate -->
        <div class="col-md-3">
            <div class="metric-card">
                <h3>{{ number_format($churnRate, 1) }}%</h3>
                <p>Tasa de Cancelación</p>
                <small class="text-danger">Últimos 30 días</small>
            </div>
        </div>
    </div>
    
    <!-- Gráficos -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Ingresos Mensuales</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenue-chart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Distribución de Planes</h5>
                </div>
                <div class="card-body">
                    <canvas id="plans-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
```

## 🔧 Comandos de Mantenimiento

### Comando para Verificar Expiraciones
```php
// app/Console/Commands/CheckExpiredSubscriptions.php
class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';
    protected $description = 'Check and handle expired subscriptions';
    
    public function handle()
    {
        $expiredSubscriptions = SubscribedUser::where('subscription_end_date', '<', now())
            ->where('status', 'active')
            ->get();
        
        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 'expired']);
            
            // Enviar email de expiración
            Mail::to($subscription->user->email)
                ->send(new SubscriptionExpiredEmail($subscription));
            
            $this->info("Expired subscription for user: {$subscription->user->email}");
        }
        
        $this->info("Processed {$expiredSubscriptions->count()} expired subscriptions");
    }
}
```

### Comando para Recordatorios
```php
class SendRenewalReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders';
    
    public function handle()
    {
        // Recordatorios 7 días antes
        $expiringIn7Days = SubscribedUser::where('subscription_end_date', '>', now())
            ->where('subscription_end_date', '<=', now()->addDays(7))
            ->where('status', 'active')
            ->get();
        
        foreach ($expiringIn7Days as $subscription) {
            Mail::to($subscription->user->email)
                ->send(new SubscriptionReminderEmail($subscription, 7));
        }
        
        // Recordatorios 1 día antes
        $expiringIn1Day = SubscribedUser::where('subscription_end_date', '>', now())
            ->where('subscription_end_date', '<=', now()->addDay())
            ->where('status', 'active')
            ->get();
        
        foreach ($expiringIn1Day as $subscription) {
            Mail::to($subscription->user->email)
                ->send(new SubscriptionReminderEmail($subscription, 1));
        }
        
        $this->info("Sent renewal reminders");
    }
}
```

---

**Documentación actualizada**: 15 de Enero, 2024  
**Versión**: v1.0  
**Soporte**: subscriptions@faristol.net
