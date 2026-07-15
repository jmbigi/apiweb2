# 🚀 Guía de Despliegue - Faristol

## 📋 Descripción General

Esta guía cubre el proceso completo de despliegue de Faristol en producción, incluyendo configuración de servidores, optimizaciones y mejores prácticas de seguridad.

## 🎯 Arquitectura de Producción

### Configuración Recomendada
- **Servidor Web**: Nginx + PHP-FPM
- **Base de Datos**: MySQL 8.0+ con réplicas de lectura
- **Cache**: Redis Cluster
- **CDN**: CloudFlare para assets estáticos
- **Almacenamiento**: AWS S3 o Wasabi
- **Monitoreo**: New Relic + custom dashboards

### Requisitos del Servidor
```bash
# Servidor Principal (Mínimo)
CPU: 4 cores
RAM: 8GB
Storage: 100GB SSD
Bandwidth: 1Gbps

# Base de Datos (Recomendado)
CPU: 8 cores
RAM: 16GB
Storage: 500GB SSD (RAID 10)

# Redis Cache
CPU: 2 cores
RAM: 4GB
Storage: 50GB SSD
```

## 🔧 Configuración del Servidor

### 1. Preparación del Sistema (Ubuntu 22.04)
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependencias básicas
sudo apt install -y curl wget git unzip software-properties-common

# Agregar repositorios
sudo add-apt-repository ppa:ondrej/php
sudo apt update
```

### 2. Instalación de PHP 8.1
```bash
# Instalar PHP y extensiones
sudo apt install -y php8.1-fpm php8.1-cli php8.1-common php8.1-mysql \
    php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml \
    php8.1-bcmath php8.1-json php8.1-intl php8.1-readline \
    php8.1-redis php8.1-imagick

# Configurar PHP-FPM
sudo systemctl enable php8.1-fpm
sudo systemctl start php8.1-fpm
```

### 3. Configuración PHP para Producción
```ini
# /etc/php/8.1/fpm/php.ini
memory_limit = 512M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
max_input_time = 300
max_input_vars = 3000

# Optimizaciones
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.revalidate_freq = 0
opcache.validate_timestamps = 0

# Seguridad
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
```

### 4. Instalación de Nginx
```bash
# Instalar Nginx
sudo apt install -y nginx

# Configurar firewall
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable

# Habilitar Nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 5. Configuración de Nginx
```nginx
# /etc/nginx/sites-available/faristol.net
server {
    listen 80;
    listen [::]:80;
    server_name faristol.net www.faristol.net;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name faristol.net www.faristol.net;
    
    root /var/www/faristol/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/faristol.net/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/faristol.net/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;
    
    # Rate Limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
    
    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # API Rate Limiting
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # Login Rate Limiting
    location /login {
        limit_req zone=login burst=5 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP Processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        # Timeout settings
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 180s;
        fastcgi_read_timeout 180s;
        fastcgi_buffer_size 256k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }
    
    # Static Assets
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Security
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    location /storage {
        deny all;
    }
    
    # File Upload Limits
    client_max_body_size 50M;
    client_body_timeout 60s;
    client_header_timeout 60s;
}
```

### 6. Instalación de MySQL
```bash
# Instalar MySQL
sudo apt install -y mysql-server

# Configuración inicial de seguridad
sudo mysql_secure_installation

# Crear base de datos y usuario
sudo mysql -u root -p
```

```sql
CREATE DATABASE faristol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'faristol'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON faristol.* TO 'faristol'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 7. Configuración de MySQL para Producción
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
# Configuración básica
bind-address = 127.0.0.1
max_connections = 200
thread_cache_size = 16
table_open_cache = 4000

# InnoDB Optimizations
innodb_buffer_pool_size = 4G  # 70% de RAM disponible
innodb_log_file_size = 1G
innodb_log_buffer_size = 32M
innodb_flush_log_at_trx_commit = 1
innodb_file_per_table = 1

# Query Cache (para lecturas)
query_cache_type = 1
query_cache_size = 256M
query_cache_limit = 8M

# Slow Query Log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2

# Binary Logging (para replicación)
log-bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7
```

### 8. Instalación de Redis
```bash
# Instalar Redis
sudo apt install -y redis-server

# Configurar Redis
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

```ini
# /etc/redis/redis.conf
# Configuración de memoria
maxmemory 2gb
maxmemory-policy allkeys-lru

# Persistencia
save 900 1
save 300 10
save 60 10000

# Seguridad
requirepass your_redis_password_here
bind 127.0.0.1

# Logging
loglevel notice
logfile /var/log/redis/redis-server.log
```

## 📦 Despliegue de la Aplicación

### 1. Preparación del Directorio
```bash
# Crear directorio para la aplicación
sudo mkdir -p /var/www/faristol
sudo chown -R $USER:www-data /var/www/faristol
cd /var/www/faristol

# Clonar repositorio
git clone https://github.com/your-repo/faristol.git .
```

### 2. Instalación de Dependencias
```bash
# Instalar Composer globalmente
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Instalar dependencias PHP
composer install --no-dev --optimize-autoloader

# Instalar Node.js y NPM
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Instalar dependencias Node.js
npm ci --production
```

### 3. Configuración de Entorno
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

```env
# .env para producción
APP_NAME=Faristol
APP_ENV=production
APP_KEY=base64:generated_key_here
APP_DEBUG=false
APP_URL=https://faristol.net

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=faristol
DB_USERNAME=faristol
DB_PASSWORD=secure_password_here

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password_here
REDIS_PORT=6379

MAIL_MAILER=sendgrid
SENDGRID_API_KEY=your_sendgrid_key_here
MAIL_FROM_ADDRESS=noreply@faristol.net
MAIL_FROM_NAME="Faristol"

AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=faristol-production
AWS_USE_PATH_STYLE_ENDPOINT=false

PAYPAL_MODE=live
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_secret
```

### 4. Migraciones y Optimización
```bash
# Ejecutar migraciones
php artisan migrate --force

# Ejecutar seeders de producción
php artisan db:seed --class=ProductionSeeder

# Optimizaciones
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Compilar assets
npm run build

# Crear enlaces simbólicos
php artisan storage:link
```

### 5. Configuración de Permisos
```bash
# Configurar propietario
sudo chown -R www-data:www-data /var/www/faristol

# Configurar permisos
sudo find /var/www/faristol -type f -exec chmod 644 {} \;
sudo find /var/www/faristol -type d -exec chmod 755 {} \;

# Permisos especiales para directorios de Laravel
sudo chmod -R 775 /var/www/faristol/storage
sudo chmod -R 775 /var/www/faristol/bootstrap/cache
```

## 🔄 Automatización con Scripts

### Script de Despliegue
```bash
#!/bin/bash
# deploy.sh

set -e

PROJECT_DIR="/var/www/faristol"
BACKUP_DIR="/var/backups/faristol"
DATE=$(date +%Y%m%d_%H%M%S)

echo "🚀 Iniciando despliegue de Faristol..."

# Crear backup
echo "📦 Creando backup..."
sudo mkdir -p $BACKUP_DIR
sudo cp -r $PROJECT_DIR $BACKUP_DIR/backup_$DATE

# Activar modo mantenimiento
echo "🛠️  Activando modo mantenimiento..."
cd $PROJECT_DIR
php artisan down --message="Actualizando sistema..." --retry=60

# Actualizar código
echo "📥 Descargando últimos cambios..."
git pull origin main

# Instalar dependencias
echo "📦 Instalando dependencias..."
composer install --no-dev --optimize-autoloader
npm ci --production

# Ejecutar migraciones
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force

# Limpiar y regenerar cache
echo "🧹 Limpiando cache..."
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Compilar assets
echo "🎨 Compilando assets..."
npm run build

# Reiniciar servicios
echo "🔄 Reiniciando servicios..."
sudo systemctl reload php8.1-fpm
sudo systemctl reload nginx

# Desactivar modo mantenimiento
echo "✅ Desactivando modo mantenimiento..."
php artisan up

# Verificar estado
echo "🔍 Verificando estado de la aplicación..."
curl -f https://faristol.net/health || {
    echo "❌ Error: La aplicación no responde correctamente"
    php artisan down --message="Error en despliegue, restaurando backup..."
    # Aquí iría el script de rollback
    exit 1
}

echo "🎉 Despliegue completado exitosamente!"
```

### Script de Backup
```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/var/backups/faristol"
PROJECT_DIR="/var/www/faristol"
DB_NAME="faristol"
DB_USER="faristol"
DB_PASS="secure_password_here"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

echo "📦 Iniciando backup de Faristol..."

# Crear directorio de backup
mkdir -p $BACKUP_DIR/$DATE

# Backup de base de datos
echo "🗄️  Respaldando base de datos..."
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/$DATE/database.sql

# Backup de archivos
echo "📁 Respaldando archivos..."
tar -czf $BACKUP_DIR/$DATE/files.tar.gz -C $PROJECT_DIR storage/app/public

# Backup de configuración
echo "⚙️  Respaldando configuración..."
cp $PROJECT_DIR/.env $BACKUP_DIR/$DATE/

# Comprimir backup completo
echo "🗜️  Comprimiendo backup..."
tar -czf $BACKUP_DIR/faristol_backup_$DATE.tar.gz -C $BACKUP_DIR $DATE
rm -rf $BACKUP_DIR/$DATE

# Limpiar backups antiguos
echo "🧹 Limpiando backups antiguos..."
find $BACKUP_DIR -name "faristol_backup_*.tar.gz" -mtime +$RETENTION_DAYS -delete

# Subir a S3 (opcional)
if command -v aws &> /dev/null; then
    echo "☁️  Subiendo backup a S3..."
    aws s3 cp $BACKUP_DIR/faristol_backup_$DATE.tar.gz s3://faristol-backups/
fi

echo "✅ Backup completado: $BACKUP_DIR/faristol_backup_$DATE.tar.gz"
```

## 📊 Monitoreo y Logs

### Configuración de Logs
```bash
# Crear directorios de logs
sudo mkdir -p /var/log/faristol
sudo chown www-data:www-data /var/log/faristol

# Configurar logrotate
sudo tee /etc/logrotate.d/faristol << EOF
/var/log/faristol/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.1-fpm
    endscript
}
EOF
```

### Script de Monitoreo
```bash
#!/bin/bash
# monitor.sh

# Verificar servicios críticos
services=("nginx" "php8.1-fpm" "mysql" "redis-server")

for service in "${services[@]}"; do
    if ! systemctl is-active --quiet $service; then
        echo "❌ Servicio $service no está ejecutándose"
        systemctl restart $service
    fi
done

# Verificar espacio en disco
disk_usage=$(df / | awk 'NR==2{print $5}' | sed 's/%//')
if [ $disk_usage -gt 85 ]; then
    echo "⚠️  Espacio en disco: ${disk_usage}%"
fi

# Verificar conectividad de base de datos
if ! mysql -u faristol -p$DB_PASS -e "SELECT 1" >/dev/null 2>&1; then
    echo "❌ Error de conexión a base de datos"
fi

# Verificar Redis
if ! redis-cli ping >/dev/null 2>&1; then
    echo "❌ Redis no responde"
fi
```

## 🔒 Seguridad

### Configuración de SSL (Let's Encrypt)
```bash
# Instalar Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtener certificado SSL
sudo certbot --nginx -d faristol.net -d www.faristol.net

# Configurar renovación automática
sudo crontab -e
# Agregar: 0 12 * * * /usr/bin/certbot renew --quiet
```

### Configuración de Firewall
```bash
# Configurar UFW
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### Configuración de Fail2Ban
```bash
# Instalar Fail2Ban
sudo apt install -y fail2ban

# Configurar para Laravel
sudo tee /etc/fail2ban/jail.local << EOF
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
action = iptables-multiport[name=ReqLimit, port="http,https", protocol=tcp]
logpath = /var/log/nginx/error.log
maxretry = 10

[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log
maxretry = 3
EOF

sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

**Documentación actualizada**: 15 de Enero, 2024  
**Versión**: v1.0  
**Soporte**: devops@faristol.net
