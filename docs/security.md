# 🔒 Guía de Seguridad - Faristol

## 📋 Descripción General

Esta guía cubre las mejores prácticas de seguridad para proteger la aplicación Faristol, incluyendo configuración del servidor, protección de datos y prevención de ataques.

## 🛡️ Seguridad del Servidor

### Configuración Básica del Sistema

#### Actualización del Sistema
```bash
# Mantener el sistema actualizado
sudo apt update && sudo apt upgrade -y

# Configurar actualizaciones automáticas
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

#### Configuración de SSH
```bash
# Editar configuración SSH
sudo nano /etc/ssh/sshd_config
```

```bash
# /etc/ssh/sshd_config - Configuración segura
Port 2222  # Cambiar puerto por defecto
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
MaxAuthTries 3
ClientAliveInterval 300
ClientAliveCountMax 2
AllowUsers tu-usuario
```

```bash
# Reiniciar SSH
sudo systemctl restart ssh

# Configurar fail2ban para SSH
sudo apt install -y fail2ban
```

#### Configuración de Firewall (UFW)
```bash
# Configuración básica
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Permitir servicios esenciales
sudo ufw allow 2222/tcp  # SSH (puerto personalizado)
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS

# Permitir solo IPs específicas para admin (opcional)
sudo ufw allow from 192.168.1.100 to any port 2222

# Activar firewall
sudo ufw enable

# Verificar estado
sudo ufw status verbose
```

### Protección contra Ataques

#### Fail2Ban Configuración Avanzada
```bash
# Instalar fail2ban
sudo apt install -y fail2ban

# Configuración personalizada
sudo tee /etc/fail2ban/jail.local << EOF
[DEFAULT]
# Configuración global
bantime = 3600     # 1 hora de ban
findtime = 600     # Ventana de tiempo de 10 minutos
maxretry = 5       # Máximo 5 intentos

# Configuración de email (opcional)
destemail = admin@faristol.net
sendername = Fail2Ban
action = %(action_mwl)s

[sshd]
enabled = true
port = 2222
logpath = /var/log/auth.log
maxretry = 3
bantime = 86400    # 24 horas para SSH

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 3

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/error.log
maxretry = 10

[nginx-botsearch]
enabled = true
filter = nginx-botsearch
logpath = /var/log/nginx/access.log
maxretry = 2
bantime = 86400

[laravel-auth]
enabled = true
filter = laravel-auth
logpath = /var/www/faristol/storage/logs/laravel.log
maxretry = 5
bantime = 3600
EOF
```

#### Filtros Personalizados para Laravel
```bash
# Crear filtro para Laravel
sudo tee /etc/fail2ban/filter.d/laravel-auth.conf << EOF
[Definition]
failregex = .*authentication attempt.*\"ip\":\"<HOST>\".*
            .*Failed login attempt.*<HOST>.*
            .*Invalid login attempt.*<HOST>.*
ignoreregex =
EOF

# Crear filtro para bots
sudo tee /etc/fail2ban/filter.d/nginx-botsearch.conf << EOF
[Definition]
failregex = ^<HOST> -.*"(GET|POST).*(\.php|\.asp|\.exe|\.pl|\.cgi|\.scgi).*".*$
            ^<HOST> -.*"(GET|POST).*(admin|phpmyadmin|wp-admin|\.env).*".*$
ignoreregex =
EOF
```

## 🔐 Seguridad de la Aplicación

### Configuración de Laravel

#### Variables de Entorno Seguras
```env
# .env - Configuración de producción
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:tu-clave-segura-aqui

# Sesiones seguras
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Configuración de base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=faristol
DB_USERNAME=faristol_limited_user
DB_PASSWORD=contraseña-muy-segura-aqui

# Cache y Redis
CACHE_DRIVER=redis
REDIS_PASSWORD=redis-password-segura

# Configuración de email
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=tu-api-key-aqui
```

#### Middleware de Seguridad
```php
// app/Http/Middleware/SecurityHeaders.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Headers de seguridad
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://js.stripe.com https://www.paypal.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self' https://api.stripe.com https://www.paypal.com;";
        
        $response->headers->set('Content-Security-Policy', $csp);
        
        return $response;
    }
}
```

#### Rate Limiting Avanzado
```php
// app/Http/Middleware/AdvancedRateLimit.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AdvancedRateLimit
{
    public function handle(Request $request, Closure $next, string $key = 'global'): Response
    {
        $identifier = $this->getIdentifier($request, $key);
        
        // Diferentes límites según el tipo de request
        $limits = [
            'api' => [60, 60], // 60 requests per minute
            'login' => [5, 900], // 5 attempts per 15 minutes
            'register' => [3, 3600], // 3 registrations per hour
            'upload' => [10, 3600], // 10 uploads per hour
            'global' => [1000, 60] // 1000 requests per minute
        ];
        
        [$maxAttempts, $decayMinutes] = $limits[$key] ?? $limits['global'];
        
        if (RateLimiter::tooManyAttempts($identifier, $maxAttempts)) {
            return response()->json([
                'error' => 'Too many requests',
                'retry_after' => RateLimiter::availableIn($identifier)
            ], 429);
        }
        
        RateLimiter::hit($identifier, $decayMinutes);
        
        return $next($request);
    }
    
    private function getIdentifier(Request $request, string $key): string
    {
        return $key . ':' . $request->ip() . ':' . ($request->user()?->id ?? 'guest');
    }
}
```

### Validación y Sanitización

#### Request Validation
```php
// app/Http/Requests/MusicScoreRequest.php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MusicScoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_\.áéíóúñü]+$/i'
            ],
            'description' => [
                'required',
                'string',
                'max:2000',
                function ($attribute, $value, $fail) {
                    if (strip_tags($value) !== $value) {
                        $fail('HTML tags are not allowed.');
                    }
                }
            ],
            'files.*' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240', // 10MB
                function ($attribute, $value, $fail) {
                    // Verificar que el archivo no esté corrupto
                    $mime = mime_content_type($value->path());
                    if ($mime !== 'application/pdf') {
                        $fail('Invalid PDF file.');
                    }
                }
            ]
        ];
    }
    
    public function prepareForValidation()
    {
        $this->merge([
            'name' => strip_tags($this->name),
            'description' => strip_tags($this->description),
        ]);
    }
}
```

#### File Upload Security
```php
// app/Services/SecureFileUpload.php
<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class SecureFileUpload
{
    private array $allowedMimes = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'png' => 'image/png'
    ];
    
    public function uploadPDF(UploadedFile $file, string $path): array
    {
        // Verificar MIME type
        if (!$this->isValidPDF($file)) {
            throw new \InvalidArgumentException('Invalid PDF file');
        }
        
        // Generar nombre único
        $filename = $this->generateSecureFilename($file);
        
        // Verificar contenido del PDF
        if (!$this->scanPDFContent($file)) {
            throw new \InvalidArgumentException('PDF contains suspicious content');
        }
        
        // Subir archivo
        $fullPath = $path . '/' . $filename;
        Storage::disk('s3')->put($fullPath, file_get_contents($file->path()));
        
        return [
            'path' => $fullPath,
            'size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName()
        ];
    }
    
    private function isValidPDF(UploadedFile $file): bool
    {
        $mime = mime_content_type($file->path());
        return $mime === 'application/pdf' && 
               $file->getClientOriginalExtension() === 'pdf';
    }
    
    private function generateSecureFilename(UploadedFile $file): string
    {
        return hash('sha256', time() . $file->getClientOriginalName()) . '.pdf';
    }
    
    private function scanPDFContent(UploadedFile $file): bool
    {
        // Verificar que el PDF no contenga JavaScript u otros elementos peligrosos
        $content = file_get_contents($file->path());
        
        $dangerous_patterns = [
            '/\/JavaScript/i',
            '/\/JS/i',
            '/\/Action/i',
            '/\/URI/i'
        ];
        
        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return false;
            }
        }
        
        return true;
    }
}
```

## 🗄️ Seguridad de Base de Datos

### Configuración Segura de MySQL
```sql
-- Crear usuario con permisos limitados
CREATE USER 'faristol_app'@'localhost' IDENTIFIED BY 'contraseña-muy-segura';

-- Otorgar solo permisos necesarios
GRANT SELECT, INSERT, UPDATE, DELETE ON faristol.* TO 'faristol_app'@'localhost';

-- Usuario para backups (solo lectura)
CREATE USER 'faristol_backup'@'localhost' IDENTIFIED BY 'otra-contraseña-segura';
GRANT SELECT, LOCK TABLES ON faristol.* TO 'faristol_backup'@'localhost';

-- Remover usuarios innecesarios
DROP USER IF EXISTS ''@'localhost';
DROP USER IF EXISTS ''@'%';
DROP USER IF EXISTS 'root'@'%';

FLUSH PRIVILEGES;
```

### Configuración my.cnf Segura
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
# Configuración básica de seguridad
bind-address = 127.0.0.1
skip-networking = false
skip-name-resolve

# Logs de seguridad
general_log = 1
general_log_file = /var/log/mysql/general.log
log_error = /var/log/mysql/error.log

# Configuración de consultas
max_connections = 100
max_user_connections = 50
max_connect_errors = 10

# Validación de contraseñas
validate_password.policy = MEDIUM
validate_password.length = 12
validate_password.mixed_case_count = 1
validate_password.number_count = 2
validate_password.special_char_count = 1

# SSL (recomendado para conexiones remotas)
ssl-ca = /etc/mysql/ssl/ca-cert.pem
ssl-cert = /etc/mysql/ssl/server-cert.pem
ssl-key = /etc/mysql/ssl/server-key.pem
```

### Prevención de SQL Injection
```php
// Uso correcto de Eloquent (seguro por defecto)
$users = User::where('email', $request->email)->get();

// Uso correcto de Query Builder
$scores = DB::table('music_scores')
    ->where('composer_id', $composerId)
    ->where('status', 'published')
    ->get();

// Para queries raw, usar bindings
$results = DB::select('
    SELECT * FROM music_scores 
    WHERE composer_id = ? AND created_at > ?
', [$composerId, $date]);

// NUNCA hacer esto (vulnerable)
// $results = DB::select("SELECT * FROM users WHERE email = '{$email}'");
```

## 🔒 Protección de Datos

### Encriptación de Datos Sensibles
```php
// app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Encrypted;

class User extends Model
{
    protected $casts = [
        'telephone' => Encrypted::class,
        'personal_data' => 'encrypted:array',
    ];
    
    // Accessor para datos sensibles
    public function getTelephoneDisplayAttribute(): string
    {
        if (!$this->telephone) return '';
        
        // Mostrar solo los últimos 4 dígitos
        $phone = $this->telephone;
        return substr($phone, 0, -4) . str_repeat('*', 4) . substr($phone, -4);
    }
}
```

### Anonimización de Logs
```php
// app/Http/Middleware/LogSanitizer.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogSanitizer
{
    private array $sensitiveFields = [
        'password',
        'password_confirmation',
        'token',
        'api_key',
        'secret'
    ];
    
    public function handle(Request $request, Closure $next)
    {
        // Limpiar datos sensibles de logs
        $cleanData = $this->sanitizeData($request->all());
        
        Log::info('Request processed', [
            'url' => $request->url(),
            'method' => $request->method(),
            'ip' => $this->anonymizeIP($request->ip()),
            'user_id' => auth()->id(),
            'data' => $cleanData
        ]);
        
        return $next($request);
    }
    
    private function sanitizeData(array $data): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }
        
        return $data;
    }
    
    private function anonymizeIP(string $ip): string
    {
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            return $parts[0] . '.' . $parts[1] . '.xxx.xxx';
        }
        
        return 'xxx.xxx.xxx.xxx';
    }
}
```

## 🔍 Auditoría y Monitoreo

### Log de Actividades de Seguridad
```php
// app/Services/SecurityAudit.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SecurityAudit
{
    public static function logSecurityEvent(string $event, array $data = [], string $level = 'info')
    {
        Log::channel('security')->{$level}($event, array_merge($data, [
            'timestamp' => now()->toISOString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'session_id' => session()->getId()
        ]));
    }
    
    public static function logFailedLogin(string $email, string $ip)
    {
        self::logSecurityEvent('failed_login_attempt', [
            'email' => $email,
            'ip' => $ip
        ], 'warning');
    }
    
    public static function logSuspiciousActivity(string $activity, array $details = [])
    {
        self::logSecurityEvent('suspicious_activity', array_merge([
            'activity' => $activity
        ], $details), 'alert');
    }
    
    public static function logPrivilegeEscalation(int $userId, string $action)
    {
        self::logSecurityEvent('privilege_escalation', [
            'target_user_id' => $userId,
            'action' => $action
        ], 'critical');
    }
}
```

### Configuración de Logs de Seguridad
```php
// config/logging.php
'channels' => [
    // ...existing channels...
    
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 90, // Mantener logs por 90 días
        'permission' => 0644,
    ],
    
    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'info',
        'days' => 365, // Mantener logs de auditoría por 1 año
    ],
];
```

## 🚨 Respuesta a Incidentes

### Script de Respuesta Automática
```bash
#!/bin/bash
# incident-response.sh

INCIDENT_TYPE=$1
ALERT_EMAIL="security@faristol.net"
LOG_FILE="/var/log/faristol/security-incidents.log"

case $INCIDENT_TYPE in
    "brute_force")
        echo "$(date): Brute force attack detected" >> $LOG_FILE
        
        # Bloquear IPs sospechosas
        fail2ban-client status nginx-limit-req
        
        # Notificar al equipo
        echo "Brute force attack detected on Faristol" | mail -s "Security Alert" $ALERT_EMAIL
        ;;
        
    "sql_injection")
        echo "$(date): SQL injection attempt detected" >> $LOG_FILE
        
        # Activar modo mantenimiento temporalmente
        cd /var/www/faristol
        php artisan down --message="Security maintenance in progress"
        
        # Notificar inmediatamente
        echo "SQL injection attempt detected. Site temporarily down." | mail -s "CRITICAL: Security Alert" $ALERT_EMAIL
        ;;
        
    "file_upload_attack")
        echo "$(date): Malicious file upload attempt" >> $LOG_FILE
        
        # Escanear archivos subidos recientemente
        find /var/www/faristol/storage/app -name "*.php" -newer /tmp/last_scan -exec rm {} \;
        
        # Actualizar timestamp
        touch /tmp/last_scan
        ;;
esac
```

### Checklist de Seguridad
```bash
#!/bin/bash
# security-checklist.sh

echo "=== FARISTOL SECURITY AUDIT ==="
echo "Fecha: $(date)"

# Verificar permisos de archivos críticos
echo -e "\n🔍 Verificando permisos de archivos..."
ls -la /var/www/faristol/.env
ls -la /var/www/faristol/storage/
ls -la /var/www/faristol/bootstrap/cache/

# Verificar usuarios de base de datos
echo -e "\n🗄️ Verificando usuarios de MySQL..."
mysql -u root -p -e "SELECT User, Host FROM mysql.user WHERE User != '';"

# Verificar logs de seguridad
echo -e "\n📋 Logs de seguridad recientes..."
tail -20 /var/log/auth.log | grep -i failed
tail -10 /var/www/faristol/storage/logs/security.log

# Verificar configuración SSL
echo -e "\n🔒 Verificando SSL..."
curl -I https://faristol.net | grep -i "strict-transport-security"

# Verificar fail2ban
echo -e "\n🛡️ Estado de Fail2Ban..."
fail2ban-client status

echo -e "\n✅ Auditoría completada"
```

---

**Documentación actualizada**: 15 de Enero, 2024  
**Versión**: v1.0  
**Soporte**: security@faristol.net
