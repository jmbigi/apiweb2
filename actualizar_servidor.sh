#!/bin/bash

# Mostrar el estado actual
echo "Actualizando servidor en $(date)"

# Hacer git pull para obtener los últimos cambios
echo "Realizando git pull..."
git pull origin main || { echo "Error al hacer git pull"; exit 1; }

# Instalar dependencias de Composer
echo "Instalando dependencias de Composer..."
composer install --optimize-autoloader || { echo "Error en composer install"; exit 1; }

# Instalar dependencias de npm
echo "Instalando dependencias de npm..."
npm install --omit=optional || { echo "Error en npm install"; exit 1; }

# Opcional: limpiar y cachear configuración
echo "Limpiando caché de configuración..."
php artisan config:clear
php artisan config:cache

echo "Actualización completada exitosamente."
