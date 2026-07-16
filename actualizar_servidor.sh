#!/bin/bash
set -e

# Mostrar el estado actual
echo "Actualizando servidor en $(date)"

# Hacer git pull para obtener los últimos cambios
echo "Realizando git pull..."
git pull origin main || { echo "Error al hacer git pull"; exit 1; }

# Instalar dependencias de Composer
echo "Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader || { echo "Error en composer install"; exit 1; }

# 🚫 NUNCA ejecutar tests en producción. Los tests pueden destruir la BD.
# Si necesitas tests, hazlo local o en un entorno aislado.
# php artisan test -- Deshabilitado por seguridad

# Instalar dependencias de npm
echo "Instalando dependencias de npm..."
npm install --omit=optional || { echo "Error en npm install"; exit 1; }

# Opcional: limpiar y cachear configuración
echo "Limpiando caché de configuración..."
php artisan view:clear
php artisan cache:clear
php artisan route:clear
php artisan config:clear
php artisan optimize:clear

echo "Actualización completada exitosamente."
