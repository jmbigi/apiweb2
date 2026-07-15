#!/bin/bash
set -e  # Detener la ejecución si algún comando falla
set -x  # Mostrar cada comando antes de ejecutarlo

composer install
composer dump-autoload
npm install
npm run build
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan optimize:clear

# Comprobar si la variable env AWS_DEFAULT_REGION está configurada
AWS_REGION=$(php artisan tinker --execute="echo env('AWS_DEFAULT_REGION');")

if [ -z "$AWS_REGION" ]; then
    echo "ERROR: La variable de entorno 'AWS_DEFAULT_REGION' no está configurada."
    echo "Por favor, asegúrate de que está definida en tu archivo .env antes de continuar."
    exit 1
else
    echo "La variable de entorno 'AWS_DEFAULT_REGION' está configurada."
fi

echo "Script completado exitosamente."
