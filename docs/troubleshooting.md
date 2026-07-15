# 🐛 Guía de Solución de Problemas - Faristol

## 📋 Descripción General

Esta guía contiene soluciones para los problemas más comunes que pueden surgir durante la instalación, configuración y uso de Faristol.

## 🚨 Problemas de Instalación

### Error de Permisos de Archivos
**Síntoma**: `Permission denied` al intentar escribir en directorios

**Solución**:
```bash
# Verificar propietario actual
ls -la storage/
ls -la bootstrap/cache/

# Corregir propietario y permisos
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Si usas un usuario diferente para desarrollo
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Error de Clave de Aplicación
**Síntoma**: `No application encryption key has been specified`

**Solución**:
```bash
# Generar nueva clave
php artisan key:generate

# Si el archivo .env no existe
cp .env.example .env
php artisan key:generate

# Limpiar cache de configuración
php artisan config:clear
```

### Error de Composer
**Síntoma**: `Class not found` o errores de autoload

**Solución**:
```bash
# Regenerar autoload
composer dump-autoload

# Reinstalar dependencias
rm -rf vendor/
composer install

# Para producción
composer install --no-dev --optimize-autoloader
```

### Error de NPM/Node.js
**Síntoma**: Errores al compilar assets

**Solución**:
```bash
# Limpiar cache de NPM
npm cache clean --force

# Eliminar node_modules y reinstalar
rm -rf node_modules package-lock.json
npm install

# Verificar versión de Node.js
node --version  # Debe ser 16.x o superior

# Si es necesario, actualizar Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

## 🗄️ Problemas de Base de Datos

### Error de Conexión a MySQL
**Síntoma**: `SQLSTATE[HY000] [2002] Connection refused`

**Diagnóstico**:
```bash
# Verificar estado de MySQL
sudo systemctl status mysql

# Verificar puerto
sudo netstat -tlnp | grep 3306

# Probar conexión directa
mysql -u faristol -p -h 127.0.0.1
```

**Solución**:
```bash
# Reiniciar MySQL
sudo systemctl restart mysql

# Verificar configuración en .env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=faristol
DB_USERNAME=faristol
DB_PASSWORD=tu_password

# Limpiar cache de configuración
php artisan config:clear
```

### Error de Migraciones
**Síntoma**: `Migration table not found` o errores durante migraciones

**Solución**:
```bash
# Verificar estado de migraciones
php artisan migrate:status

# Crear tabla de migraciones si no existe
php artisan migrate:install

# Ejecutar migraciones paso a paso
php artisan migrate --step

# Rollback y ejecutar de nuevo
php artisan migrate:rollback
php artisan migrate
```

### Error de Caracteres UTF-8
**Síntoma**: Caracteres especiales no se muestran correctamente

**Solución**:
```sql
-- Verificar configuración de la base de datos
SHOW VARIABLES LIKE 'character_set%';
SHOW VARIABLES LIKE 'collation%';

-- Crear base de datos con configuración correcta
DROP DATABASE IF EXISTS faristol;
CREATE DATABASE faristol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 🔧 Problemas de Configuración

### Error de Storage Link
**Síntoma**: Imágenes no se muestran, error 404 en `/storage`

**Solución**:
```bash
# Verificar enlace simbólico
ls -la public/storage

# Recrear enlace si no existe o está roto
rm public/storage
php artisan storage:link

# Verificar permisos
sudo chown -R www-data:www-data storage/app/public
sudo chmod -R 755 storage/app/public
```

### Error de Cache
**Síntoma**: Cambios no se reflejan, errores de configuración

**Solución**:
```bash
# Limpiar todo el cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Comando todo-en-uno
php artisan optimize:clear

# Regenerar cache para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Error de Queue/Jobs
**Síntoma**: Emails no se envían, trabajos no se procesan

**Diagnóstico**:
```bash
# Verificar configuración de queue
php artisan queue:monitor

# Ver trabajos fallidos
php artisan queue:failed

# Ver logs de queue
tail -f storage/logs/laravel.log
```

**Solución**:
```bash
# Reiniciar worker de queue
php artisan queue:restart

# Procesar trabajos manualmente
php artisan queue:work --tries=3

# Reintentar trabajos fallidos
php artisan queue:retry all

# Para producción con Supervisor
sudo supervisorctl restart faristol-worker:*
```

## 📧 Problemas de Email

### Emails no se Envían
**Síntoma**: Emails no llegan a los destinatarios

**Diagnóstico**:
```bash
# Probar configuración de email
php artisan tinker
```

```php
// En tinker
Mail::raw('Test email', function($mail) {
    $mail->to('test@example.com')->subject('Test');
});
```

**Solución para SMTP**:
```env
# Verificar configuración en .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@faristol.net
MAIL_FROM_NAME="Faristol"
```

**Solución para SendGrid**:
```env
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=tu-sendgrid-api-key
```

### Error de Templates de Email
**Síntoma**: Emails se envían sin formato

**Solución**:
```bash
# Verificar que exista la vista
ls -la resources/views/emails/

# Limpiar cache de vistas
php artisan view:clear

# Regenerar cache
php artisan view:cache
```

## 🔐 Problemas de Autenticación

### Error de Sanctum Token
**Síntoma**: `Unauthenticated` en API requests

**Solución**:
```bash
# Verificar configuración de Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Ejecutar migraciones de Sanctum
php artisan migrate
```

```php
// Verificar middleware en api.php
Route::middleware(['auth:sanctum'])->group(function () {
    // Rutas protegidas
});
```

### Error de Verificación de Email
**Síntoma**: Links de verificación no funcionan

**Solución**:
```bash
# Verificar configuración de URL en .env
APP_URL=https://tu-dominio.com

# Limpiar cache
php artisan config:clear

# Verificar rutas
php artisan route:list | grep verification
```

## 🎼 Problemas de Partituras

### Error de Subida de Archivos
**Síntoma**: `File too large` o `Upload failed`

**Solución PHP**:
```ini
# /etc/php/8.1/fpm/php.ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
memory_limit = 512M
```

**Solución Nginx**:
```nginx
# En la configuración del servidor
client_max_body_size 50M;
client_body_timeout 60s;
```

**Reiniciar servicios**:
```bash
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

### Error de PDF Processing
**Síntoma**: PDFs no se procesan correctamente

**Solución**:
```bash
# Instalar dependencias necesarias
sudo apt install -y pdftk-java poppler-utils

# Verificar que pdftk funciona
pdftk --version

# Verificar permisos de almacenamiento
sudo chown -R www-data:www-data storage/app/
```

### Error de Thumbnails
**Síntoma**: Miniaturas no se generan

**Solución**:
```bash
# Instalar Imagick
sudo apt install -y php8.1-imagick

# Reiniciar PHP-FPM
sudo systemctl restart php8.1-fpm

# Verificar extensión
php -m | grep imagick
```

## 🔄 Problemas de Redis

### Error de Conexión a Redis
**Síntoma**: `Connection refused` en Redis

**Diagnóstico**:
```bash
# Verificar estado de Redis
sudo systemctl status redis-server

# Probar conexión
redis-cli ping
```

**Solución**:
```bash
# Reiniciar Redis
sudo systemctl restart redis-server

# Verificar configuración
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Si usas password
REDIS_PASSWORD=tu-redis-password
```

### Error de Memory en Redis
**Síntoma**: Redis se queda sin memoria

**Solución**:
```bash
# Verificar uso de memoria
redis-cli info memory

# Limpiar cache si es necesario
redis-cli flushall

# Ajustar configuración en /etc/redis/redis.conf
maxmemory 2gb
maxmemory-policy allkeys-lru
```

## 🌐 Problemas de Servidor Web

### Error 500 Internal Server Error
**Diagnóstico**:
```bash
# Verificar logs de Laravel
tail -f storage/logs/laravel.log

# Verificar logs de Nginx
sudo tail -f /var/log/nginx/error.log

# Verificar logs de PHP
sudo tail -f /var/log/php8.1-fpm.log
```

**Soluciones comunes**:
```bash
# Verificar permisos
sudo chown -R www-data:www-data /var/www/faristol
sudo chmod -R 755 /var/www/faristol
sudo chmod -R 775 storage bootstrap/cache

# Verificar configuración
php artisan config:clear
php artisan route:clear
```

### Error 404 Not Found
**Síntoma**: Rutas no funcionan correctamente

**Solución**:
```nginx
# Verificar configuración de Nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

```bash
# Habilitar rewrite module (Apache)
sudo a2enmod rewrite
sudo systemctl restart apache2

# Verificar archivo .htaccess (Apache)
cat public/.htaccess
```

## 📊 Problemas de Rendimiento

### Sitio Lento
**Diagnóstico**:
```bash
# Verificar uso de recursos
htop
df -h
free -m

# Verificar queries lentas
sudo tail -f /var/log/mysql/slow.log
```

**Optimizaciones**:
```bash
# Optimizar autoloader
composer dump-autoloader --optimize

# Cachear configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimizar base de datos
php artisan db:show
```

### Alto Uso de Memoria
**Solución**:
```ini
# Ajustar PHP memory_limit
memory_limit = 512M

# Optimizar configuración MySQL
innodb_buffer_pool_size = 4G  # 70% de RAM
```

## 🔧 Herramientas de Diagnóstico

### Comando de Estado General
```bash
#!/bin/bash
# health-check.sh

echo "=== ESTADO DE SERVICIOS ==="
systemctl is-active nginx php8.1-fpm mysql redis-server

echo -e "\n=== ESPACIO EN DISCO ==="
df -h

echo -e "\n=== MEMORIA ==="
free -h

echo -e "\n=== LOGS RECIENTES ==="
echo "Laravel errors (últimas 10 líneas):"
tail -10 storage/logs/laravel.log

echo -e "\nNginx errors (últimas 5 líneas):"
sudo tail -5 /var/log/nginx/error.log
```

### Verificación de Conectividad
```bash
#!/bin/bash
# connectivity-check.sh

# Verificar base de datos
echo "Probando conexión a MySQL..."
mysql -u faristol -p$DB_PASS -e "SELECT 1" 2>/dev/null && echo "✅ MySQL OK" || echo "❌ MySQL Error"

# Verificar Redis
echo "Probando conexión a Redis..."
redis-cli ping 2>/dev/null && echo "✅ Redis OK" || echo "❌ Redis Error"

# Verificar almacenamiento S3
echo "Probando conexión a S3..."
php artisan tinker --execute="Storage::disk('s3')->put('test.txt', 'test'); echo 'S3 OK';" 2>/dev/null && echo "✅ S3 OK" || echo "❌ S3 Error"
```

## 📞 Soporte

### Información para Soporte
Cuando contactes soporte, incluye la siguiente información:

```bash
# Información del sistema
echo "=== INFORMACIÓN DEL SISTEMA ==="
echo "OS: $(lsb_release -d | cut -f2)"
echo "PHP: $(php --version | head -n1)"
echo "Laravel: $(php artisan --version)"
echo "MySQL: $(mysql --version)"
echo "Nginx: $(nginx -v 2>&1)"
echo "Redis: $(redis-server --version)"

echo -e "\n=== CONFIGURACIÓN ==="
echo "APP_ENV: $(grep APP_ENV .env | cut -d'=' -f2)"
echo "APP_DEBUG: $(grep APP_DEBUG .env | cut -d'=' -f2)"
echo "DB_CONNECTION: $(grep DB_CONNECTION .env | cut -d'=' -f2)"
echo "CACHE_DRIVER: $(grep CACHE_DRIVER .env | cut -d'=' -f2)"
```

### Contacto
- **Email**: support@faristol.net
- **Sistema de tickets**: `/support/en`
- **Documentación**: Esta documentación

---

**Documentación actualizada**: 15 de Enero, 2024  
**Versión**: v1.0  
**Soporte**: support@faristol.net
