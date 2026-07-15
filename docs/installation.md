# 📦 Guía de Instalación - Faristol

## 🎯 Requisitos del Sistema

### Requisitos Mínimos
- **PHP**: 8.1 o superior
- **Composer**: 2.0+
- **Node.js**: 16.x o superior
- **NPM/Yarn**: Última versión
- **Base de Datos**: MySQL 8.0+ o PostgreSQL 13+
- **Servidor Web**: Apache 2.4+ o Nginx 1.18+
- **Memoria**: 512MB RAM (recomendado 1GB+)
- **Almacenamiento**: 5GB libres

### Extensiones PHP Requeridas
```bash
# Verificar extensiones instaladas
php -m | grep -E "(openssl|pdo|mbstring|tokenizer|xml|ctype|json|bcmath|curl|fileinfo|gd|zip)"
```

Extensiones necesarias:
- `openssl`
- `pdo` + `pdo_mysql`/`pdo_pgsql`
- `mbstring`
- `tokenizer`
- `xml`
- `ctype`
- `json`
- `bcmath`
- `curl`
- `fileinfo`
- `gd` (para procesamiento de imágenes)
- `zip`

## 🚀 Instalación Paso a Paso

### 1. Preparación del Entorno

#### Clonar el Repositorio
```bash
# Clonar desde repositorio
git clone <repository-url> faristol
cd faristol

# O descargar ZIP y extraer
wget <zip-url>
unzip faristol.zip
cd faristol
```

#### Verificar Requisitos
```bash
# Verificar versión PHP
php --version

# Verificar Composer
composer --version

# Verificar Node.js
node --version
npm --version
```

### 2. Instalación de Dependencias

#### Dependencias PHP
```bash
# Instalar dependencias PHP con Composer
composer install --optimize-autoloader --no-dev

# Para desarrollo (incluye herramientas de testing)
composer install
```

#### Dependencias JavaScript
```bash
# Instalar dependencias Node.js
npm install

# O con Yarn
yarn install
```

### 3. Configuración del Entorno

#### Archivo de Configuración
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

#### Configuración de Base de Datos
Editar `.env`:
```env
# Configuración MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=faristol
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# O PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=faristol
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

#### Configuración de Email
```env
# SMTP Básico
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-password-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@faristol.net
MAIL_FROM_NAME="Faristol"

# SendGrid (Recomendado para producción)
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=tu_sendgrid_api_key
```

#### Configuración de Almacenamiento
```env
# Local (Desarrollo)
FILESYSTEM_DISK=local

# AWS S3 (Producción)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=tu_access_key
AWS_SECRET_ACCESS_KEY=tu_secret_key
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=faristol-storage
AWS_URL=https://tu-bucket.s3.amazonaws.com

# Wasabi (Alternativa económica)
FILESYSTEM_DISK=wasabi
WASABI_ACCESS_KEY_ID=tu_wasabi_key
WASABI_SECRET_ACCESS_KEY=tu_wasabi_secret
WASABI_DEFAULT_REGION=eu-central-1
WASABI_BUCKET=faristol-wasabi
```

#### Configuración de PayPal
```env
# PayPal Sandbox (Desarrollo)
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=tu_sandbox_client_id
PAYPAL_CLIENT_SECRET=tu_sandbox_secret

# PayPal Live (Producción)
PAYPAL_MODE=live
PAYPAL_CLIENT_ID=tu_live_client_id
PAYPAL_CLIENT_SECRET=tu_live_secret
```

### 4. Configuración de Base de Datos

#### Crear Base de Datos
```sql
-- MySQL
CREATE DATABASE faristol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- PostgreSQL
CREATE DATABASE faristol WITH ENCODING 'UTF8';
```

#### Ejecutar Migraciones
```bash
# Ejecutar migraciones
php artisan migrate

# Con datos de prueba (recomendado para desarrollo)
php artisan migrate --seed

# Solo datos esenciales (producción)
php artisan db:seed --class=ProductionSeeder
```

### 5. Configuración de Almacenamiento

#### Enlaces Simbólicos
```bash
# Crear enlace simbólico para storage
php artisan storage:link

# Verificar que el enlace se creó correctamente
ls -la public/storage
```

#### Permisos de Archivos
```bash
# Linux/macOS
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Si no tienes permisos sudo
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 6. Compilación de Assets

#### Desarrollo
```bash
# Compilar assets para desarrollo
npm run dev

# Modo watch (recompila automáticamente)
npm run watch
```

#### Producción
```bash
# Compilar y optimizar para producción
npm run build

# Verificar archivos generados
ls -la public/build/
```

### 7. Configuración del Servidor Web

#### Apache
Crear archivo `.htaccess` en la raíz del proyecto:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

Archivo de configuración del virtual host:
```apache
<VirtualHost *:80>
    ServerName faristol.local
    DocumentRoot /path/to/faristol/public
    
    <Directory /path/to/faristol/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/faristol_error.log
    CustomLog ${APACHE_LOG_DIR}/faristol_access.log combined
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name faristol.local;
    root /path/to/faristol/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 8. Configuración de Colas y Cache

#### Redis (Recomendado para producción)
```env
# Cache
CACHE_DRIVER=redis

# Sesiones
SESSION_DRIVER=redis

# Colas
QUEUE_CONNECTION=redis

# Configuración Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### Configurar Worker de Colas
```bash
# Instalar supervisor (Ubuntu/Debian)
sudo apt-get install supervisor

# Crear archivo de configuración
sudo nano /etc/supervisor/conf.d/faristol-worker.conf
```

Contenido del archivo:
```ini
[program:faristol-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/faristol/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/faristol/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Recargar configuración supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start faristol-worker:*
```

### 9. Configuración de Tareas Programadas

#### Crontab
```bash
# Editar crontab
crontab -e

# Agregar línea (ajustar ruta)
* * * * * cd /path/to/faristol && php artisan schedule:run >> /dev/null 2>&1
```

### 10. Optimización para Producción

#### Cache de Configuración
```bash
# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar autoloader
composer install --optimize-autoloader --no-dev
```

#### Configuraciones de Producción
```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

## ✅ Verificación de Instalación

### Comandos de Verificación
```bash
# Verificar estado general
php artisan about

# Verificar base de datos
php artisan migrate:status

# Verificar colas
php artisan queue:monitor

# Verificar almacenamiento
php artisan storage:link --verify
```

### Tests de Funcionalidad
```bash
# Ejecutar tests
php artisan test

# Test específico de instalación
php artisan test --group=installation
```

### Verificación de URLs
- **Frontend**: `http://tu-dominio/`
- **Admin Login**: `http://tu-dominio/login`
- **API Health**: `http://tu-dominio/api/health`
- **Storage Test**: `http://tu-dominio/storage/test.txt`

## 🔧 Post-Instalación

### Crear Usuario Administrador
```bash
# Crear super admin
php artisan db:seed --class=SuperAdminSeeder

# O crear manualmente
php artisan tinker
```

```php
// En tinker
$user = App\Models\User::create([
    'name' => 'Super Admin',
    'email' => 'admin@faristol.net',
    'password' => bcrypt('tu-password-segura'),
    'status' => 1,
    'email_verified_at' => now()
]);

$role = App\Models\Role::where('name', 'superadmin')->first();
$user->attachRole($role);
```

### Configurar Planes de Suscripción
```bash
# Crear planes básicos
php artisan db:seed --class=SubscriptionPlansSeeder
```

### Configurar Instrumentos y Estilos
```bash
# Datos básicos de instrumentos
php artisan db:seed --class=InstrumentsSeeder

# Estilos musicales básicos
php artisan db:seed --class=StylesSeeder
```

## 🐛 Solución de Problemas

### Problemas Comunes

#### Error de Permisos
```bash
# Solución
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

#### Error de Clave de Aplicación
```bash
# Solución
php artisan key:generate
php artisan config:cache
```

#### Error de Base de Datos
```bash
# Verificar conexión
php artisan tinker
DB::connection()->getPdo();
```

#### Error de Storage Link
```bash
# Recrear enlace
rm public/storage
php artisan storage:link
```

#### Problemas de Email
```bash
# Test de email
php artisan tinker
Mail::raw('Test email', function($mail) {
    $mail->to('test@example.com')->subject('Test');
});
```

### Logs de Depuración
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Logs específicos
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log
```

### Comandos de Limpieza
```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Limpiar todo
php artisan optimize:clear
```

## 📞 Soporte

Si encuentras problemas durante la instalación:

1. Consulta la sección [Troubleshooting](troubleshooting.md)
2. Revisa los logs en `storage/logs/`
3. Contacta soporte técnico: `support@faristol.net`
4. Crea un issue en el repositorio

---

**¡Instalación completada!** 🎉 Continúa con la [Configuración del Sistema](configuration.md).
