# 📧 Sistema de Emails - Faristol

## 📋 Descripción General

Faristol cuenta con un sistema completo de notificaciones por email que incluye emails transaccionales, campañas de marketing, y comunicaciones automatizadas con plantillas personalizables y seguimiento avanzado.

## ✨ Tipos de Emails

### 📬 Emails Transaccionales

#### Verificación de Email
```php
// UserRegisteredEmail.php
class UserRegisteredEmail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: '¡Bienvenido a Faristol! Confirma tu cuenta 🎵',
        );
    }
    
    public function content(): Content
    {
        return new Content(
            view: 'emails.user_registered',
            with: [
                'user' => $this->user,
                'verification_url' => $this->generateVerificationUrl()
            ]
        );
    }
    
    private function generateVerificationUrl()
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->user->id, 'hash' => sha1($this->user->email)]
        );
    }
}
```

#### Email de Oferta de Plan
```php
// PlanOfferEmail.php
class PlanOfferEmail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $user;
    public $offer;
    
    public function __construct(User $user, $offer = null)
    {
        $this->user = $user;
        $this->offer = $offer ?? $this->generateOffer();
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: '🎵 ¡No te vayas! Disfruta Faristol Premium 🎶',
        );
    }
    
    public function content(): Content
    {
        return new Content(
            view: 'emails.plan_offer',
            with: [
                'user' => $this->user,
                'offer' => $this->offer,
                'upgrade_url' => route('subscription.plans'),
                'unsubscribe_url' => $this->generateUnsubscribeUrl()
            ]
        );
    }
    
    private function generateOffer()
    {
        return [
            'discount_percentage' => 25,
            'valid_until' => now()->addDays(7),
            'premium_features' => [
                'Anotaciones ilimitadas',
                'Sin publicidad',
                'Acceso a favoritos',
                'Soporte prioritario'
            ]
        ];
    }
}
```

### 🔄 Emails Automáticos

#### Recordatorio de Renovación
```php
// SubscriptionReminderEmail.php
class SubscriptionReminderEmail extends Mailable
{
    public $subscription;
    public $daysUntilExpiry;
    
    public function __construct(SubscribedUser $subscription)
    {
        $this->subscription = $subscription;
        $this->daysUntilExpiry = now()->diffInDays($subscription->subscription_end_date);
    }
    
    public function envelope(): Envelope
    {
        $subject = $this->daysUntilExpiry <= 3 
            ? '⚠️ Tu suscripción expira pronto - Renueva ahora'
            : '🔔 Recordatorio: Tu suscripción expira en ' . $this->daysUntilExpiry . ' días';
            
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subject
        );
    }
    
    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription_reminder',
            with: [
                'user' => $this->subscription->user,
                'plan' => $this->subscription->plan,
                'days_until_expiry' => $this->daysUntilExpiry,
                'renewal_url' => route('subscription.renew', $this->subscription->id),
                'is_urgent' => $this->daysUntilExpiry <= 3
            ]
        );
    }
}
```

### 📧 Sistema de Plantillas

#### Plantilla Base
```blade
<!-- emails/layout/app.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Faristol' }}</title>
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Base styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8f9fa;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .tagline {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* Content */
        .email-content {
            padding: 40px 30px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        h2 {
            color: #34495e;
            margin: 25px 0 15px 0;
        }
        
        p {
            margin-bottom: 15px;
            color: #555555;
        }
        
        .lead {
            font-size: 18px;
            font-weight: 300;
            color: #2c3e50;
        }
        
        /* Buttons */
        .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .button-primary {
            background-color: #667eea;
            color: white;
        }
        
        .button-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .button-success {
            background-color: #28a745;
            color: white;
        }
        
        .button-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .button-danger {
            background-color: #dc3545;
            color: white;
        }
        
        /* Cards */
        .card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background-color: #f8f9fa;
        }
        
        .card-header {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        /* Lists */
        ul, ol {
            margin: 15px 0;
            padding-left: 25px;
        }
        
        li {
            margin-bottom: 8px;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #e9ecef;
            color: #495057;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin: 2px;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        /* Footer */
        .email-footer {
            background-color: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
            font-size: 14px;
        }
        
        .footer-links {
            margin: 15px 0;
        }
        
        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .unsubscribe {
            margin-top: 20px;
            font-size: 12px;
            color: #95a5a6;
        }
        
        .unsubscribe a {
            color: #bdc3c7;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            
            .email-content {
                padding: 20px !important;
            }
            
            .button {
                display: block !important;
                width: 100% !important;
                margin: 10px 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="logo">🎵 Faristol</div>
            <div class="tagline">Tu biblioteca musical digital</div>
        </div>
        
        <!-- Content -->
        <div class="email-content">
            @yield('content')
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-links">
                <a href="{{ config('app.url') }}">Inicio</a>
                <a href="{{ config('app.url') }}/music-scores">Partituras</a>
                <a href="{{ config('app.url') }}/composers">Compositores</a>
                <a href="{{ config('app.url') }}/support">Soporte</a>
            </div>
            
            <p>© {{ date('Y') }} Faristol. Todos los derechos reservados.</p>
            
            @if(isset($unsubscribe_url))
            <div class="unsubscribe">
                <a href="{{ $unsubscribe_url }}">Cancelar suscripción</a> | 
                <a href="{{ config('app.url') }}/privacy">Política de Privacidad</a>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
```

### 📤 Campañas de Email

#### Sistema de Campañas
```php
class EmailCampaignController extends Controller
{
    public function create()
    {
        $templates = EmailTemplate::where('status', 'active')->get();
        $segments = [
            'all_users' => 'Todos los usuarios',
            'free_users' => 'Usuarios gratuitos',
            'basic_subscribers' => 'Suscriptores básicos',
            'premium_subscribers' => 'Suscriptores premium',
            'composers' => 'Compositores',
            'inactive_users' => 'Usuarios inactivos',
            'trial_expired' => 'Prueba expirada'
        ];
        
        return view('admin.email-campaigns.create', compact('templates', 'segments'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'template_id' => 'nullable|exists:email_templates,id',
            'content' => 'required_without:template_id|string',
            'segment' => 'required|string',
            'send_at' => 'nullable|date|after:now',
            'test_email' => 'nullable|email'
        ]);
        
        $campaign = EmailCampaign::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'template_id' => $request->template_id,
            'content' => $request->content,
            'segment' => $request->segment,
            'send_at' => $request->send_at ?? now(),
            'created_by' => auth()->id(),
            'status' => 'scheduled'
        ]);
        
        // Get recipients based on segment
        $recipients = $this->getRecipientsBySegment($request->segment);
        $campaign->update(['recipient_count' => $recipients->count()]);
        
        // Queue emails
        foreach ($recipients as $user) {
            SendCampaignEmail::dispatch($campaign, $user)
                ->delay($campaign->send_at);
        }
        
        // Send test email if requested
        if ($request->test_email) {
            Mail::to($request->test_email)->send(
                new CampaignTestEmail($campaign)
            );
        }
        
        return redirect()->route('admin.email-campaigns.index')
                        ->with('success', 'Campaña programada exitosamente');
    }
    
    private function getRecipientsBySegment($segment)
    {
        switch ($segment) {
            case 'all_users':
                return User::where('status', 1)->get();
                
            case 'free_users':
                return User::where('status', 1)
                    ->whereDoesntHave('subscriptions')
                    ->orWhereHas('subscriptions.plan', function($q) {
                        $q->where('type', 0);
                    })->get();
                    
            case 'basic_subscribers':
                return User::where('status', 1)
                    ->whereHas('subscriptions.plan', function($q) {
                        $q->where('type', 1);
                    })->get();
                    
            case 'premium_subscribers':
                return User::where('status', 1)
                    ->whereHas('subscriptions.plan', function($q) {
                        $q->where('type', 2);
                    })->get();
                    
            case 'composers':
                return User::where('status', 1)
                    ->whereHas('roles', function($q) {
                        $q->where('name', 'composer');
                    })->get();
                    
            case 'inactive_users':
                return User::where('status', 1)
                    ->where('last_login_at', '<', now()->subDays(30))
                    ->get();
                    
            default:
                return collect();
        }
    }
}
```

### 📊 Seguimiento de Emails

#### Email Tracking
```php
class EmailTrackingController extends Controller
{
    public function trackOpen(Request $request, $campaignId, $userId)
    {
        EmailOpen::firstOrCreate([
            'campaign_id' => $campaignId,
            'user_id' => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'opened_at' => now()
        ]);
        
        // Return 1x1 transparent pixel
        return response()->file(public_path('images/tracking-pixel.gif'));
    }
    
    public function trackClick(Request $request, $campaignId, $userId, $linkId)
    {
        EmailClick::create([
            'campaign_id' => $campaignId,
            'user_id' => $userId,
            'link_id' => $linkId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'clicked_at' => now()
        ]);
        
        // Redirect to the actual URL
        $link = EmailLink::findOrFail($linkId);
        return redirect($link->url);
    }
    
    public function campaignStats($campaignId)
    {
        $campaign = EmailCampaign::findOrFail($campaignId);
        
        $stats = [
            'sent' => $campaign->emails_sent,
            'delivered' => $campaign->emails_delivered,
            'opened' => EmailOpen::where('campaign_id', $campaignId)->distinct('user_id')->count(),
            'clicked' => EmailClick::where('campaign_id', $campaignId)->distinct('user_id')->count(),
            'bounced' => $campaign->emails_bounced,
            'unsubscribed' => $campaign->emails_unsubscribed,
            'open_rate' => $campaign->emails_delivered > 0 ? 
                (EmailOpen::where('campaign_id', $campaignId)->distinct('user_id')->count() / $campaign->emails_delivered) * 100 : 0,
            'click_rate' => $campaign->emails_delivered > 0 ? 
                (EmailClick::where('campaign_id', $campaignId)->distinct('user_id')->count() / $campaign->emails_delivered) * 100 : 0
        ];
        
        return response()->json($stats);
    }
}
```

### 📋 Gestión de Plantillas

#### Editor de Plantillas
```blade
<!-- admin/email-templates/editor.blade.php -->
<div class="template-editor">
    <div class="row">
        <!-- Template List -->
        <div class="col-md-3">
            <div class="templates-sidebar">
                <h5>Plantillas</h5>
                <div class="template-list">
                    @foreach($templates as $template)
                    <div class="template-item" data-id="{{ $template->id }}">
                        <div class="template-name">{{ $template->name }}</div>
                        <div class="template-type">{{ $template->type }}</div>
                    </div>
                    @endforeach
                </div>
                <button class="btn btn-primary btn-sm" id="new-template">Nueva Plantilla</button>
            </div>
        </div>
        
        <!-- Editor -->
        <div class="col-md-6">
            <div class="template-editor-area">
                <div class="editor-toolbar">
                    <button class="btn btn-sm btn-outline-secondary" data-action="bold">B</button>
                    <button class="btn btn-sm btn-outline-secondary" data-action="italic">I</button>
                    <button class="btn btn-sm btn-outline-secondary" data-action="link">🔗</button>
                    <button class="btn btn-sm btn-outline-secondary" data-action="image">🖼️</button>
                    <button class="btn btn-sm btn-outline-secondary" data-action="button">📱</button>
                </div>
                
                <div class="editor-content">
                    <textarea id="template-content" rows="20" class="form-control"></textarea>
                </div>
                
                <div class="template-variables">
                    <h6>Variables Disponibles</h6>
                    <div class="variable-list">
                        <span class="variable" data-var="{{user.name}}">{{user.name}}</span>
                        <span class="variable" data-var="{{user.email}}">{{user.email}}</span>
                        <span class="variable" data-var="{{subscription.plan}}">{{subscription.plan}}</span>
                        <span class="variable" data-var="{{subscription.expires_at}}">{{subscription.expires_at}}</span>
                        <span class="variable" data-var="{{app.url}}">{{app.url}}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Preview -->
        <div class="col-md-3">
            <div class="template-preview">
                <h5>Vista Previa</h5>
                <div class="preview-controls">
                    <select id="preview-device" class="form-control form-control-sm">
                        <option value="desktop">Desktop</option>
                        <option value="tablet">Tablet</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </div>
                <div class="preview-frame">
                    <iframe id="preview-iframe" src="about:blank"></iframe>
                </div>
                <div class="preview-actions">
                    <button class="btn btn-sm btn-primary" id="send-test">Enviar Prueba</button>
                    <button class="btn btn-sm btn-success" id="save-template">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
class TemplateEditor {
    constructor() {
        this.currentTemplate = null;
        this.editor = null;
        this.initializeEditor();
        this.bindEvents();
    }
    
    initializeEditor() {
        // Initialize rich text editor (could use TinyMCE, Quill, etc.)
        this.editor = new Quill('#template-content', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    ['link', 'image'],
                    [{ 'header': [1, 2, 3, false] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });
        
        // Update preview on content change
        this.editor.on('text-change', () => {
            this.updatePreview();
        });
    }
    
    bindEvents() {
        // Template selection
        $('.template-item').on('click', (e) => {
            const templateId = $(e.currentTarget).data('id');
            this.loadTemplate(templateId);
        });
        
        // Variable insertion
        $('.variable').on('click', (e) => {
            const variable = $(e.target).data('var');
            this.insertVariable(variable);
        });
        
        // Save template
        $('#save-template').on('click', () => {
            this.saveTemplate();
        });
        
        // Send test email
        $('#send-test').on('click', () => {
            this.sendTestEmail();
        });
        
        // Preview device change
        $('#preview-device').on('change', (e) => {
            this.updatePreviewDevice(e.target.value);
        });
    }
    
    loadTemplate(templateId) {
        fetch(`/admin/email-templates/${templateId}`)
            .then(response => response.json())
            .then(template => {
                this.currentTemplate = template;
                this.editor.root.innerHTML = template.content;
                this.updatePreview();
            });
    }
    
    insertVariable(variable) {
        const range = this.editor.getSelection();
        if (range) {
            this.editor.insertText(range.index, variable);
        }
    }
    
    updatePreview() {
        const content = this.editor.root.innerHTML;
        const preview = this.processTemplate(content, this.getSampleData());
        
        const iframe = document.getElementById('preview-iframe');
        iframe.srcdoc = this.wrapPreviewContent(preview);
    }
    
    processTemplate(content, data) {
        // Simple template variable replacement
        return content.replace(/\{\{([^}]+)\}\}/g, (match, key) => {
            const keys = key.trim().split('.');
            let value = data;
            
            for (const k of keys) {
                value = value[k];
                if (value === undefined) return match;
            }
            
            return value;
        });
    }
    
    getSampleData() {
        return {
            user: {
                name: 'Juan Pérez',
                email: 'juan@example.com'
            },
            subscription: {
                plan: 'Premium',
                expires_at: '15/02/2024'
            },
            app: {
                url: 'https://faristol.net'
            }
        };
    }
    
    wrapPreviewContent(content) {
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                    /* Include email styles here */
                </style>
            </head>
            <body>
                ${content}
            </body>
            </html>
        `;
    }
    
    saveTemplate() {
        if (!this.currentTemplate) return;
        
        const content = this.editor.root.innerHTML;
        
        fetch(`/admin/email-templates/${this.currentTemplate.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                content: content
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                toastr.success('Plantilla guardada exitosamente');
            }
        });
    }
    
    sendTestEmail() {
        const testEmail = prompt('Ingresa el email de prueba:');
        if (!testEmail) return;
        
        const content = this.editor.root.innerHTML;
        
        fetch('/admin/email-templates/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                email: testEmail,
                content: content
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                toastr.success('Email de prueba enviado');
            }
        });
    }
}

// Initialize editor when page loads
document.addEventListener('DOMContentLoaded', () => {
    new TemplateEditor();
});
</script>
```

## 🔧 Configuración de Email

### Proveedores de Email
```php
// config/mail.php
return [
    'default' => env('MAIL_MAILER', 'smtp'),
    
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
        ],
        
        'sendgrid' => [
            'transport' => 'sendgrid',
            'api_key' => env('SENDGRID_API_KEY'),
        ],
        
        'mailgun' => [
            'transport' => 'mailgun',
            'domain' => env('MAILGUN_DOMAIN'),
            'secret' => env('MAILGUN_SECRET'),
            'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        ],
    ],
    
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@faristol.net'),
        'name' => env('MAIL_FROM_NAME', 'Faristol'),
    ],
];
```

---

**Documentación actualizada**: 15 de Enero, 2024  
**Versión**: v1.0  
**Soporte**: emails@faristol.net
