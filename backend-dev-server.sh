#!/bin/bash
# Inicia el servidor de desarrollo PHP para el backend Laravel
# Uso: ./backend-dev-server.sh [puerto]
set -euo pipefail

PORT="${1:-8083}"
echo "Iniciando servidor PHP en localhost:$PORT"
php -S "localhost:$PORT" -t public/
