#!/bin/bash
# Script para desplegar visorweb2 (Flutter web) en web2.faristol.net
# Uso: ./traer_visor_web.sh [ruta_al_build_flutter]
#
# Si no se pasa ruta, usa el script build_visorweb2.sh (recomendado)
set -euo pipefail

FLUTTER_BUILD="${1:-}"

if [ -z "$FLUTTER_BUILD" ]; then
    echo "No se especifico ruta. Usando build_visorweb2.sh..."
    exec bash "$(dirname "$0")/build_visorweb2.sh"
fi

DESTINATION="./public/web"

if [ -d "$FLUTTER_BUILD" ]; then
    echo "Origen: $FLUTTER_BUILD"
    echo "Destino: $DESTINATION"
    rm -rf "$DESTINATION"
    mkdir -p "$DESTINATION"
    cp -r "$FLUTTER_BUILD"/* "$DESTINATION"
    chmod -R 755 "$DESTINATION"
    chown -R www-data:www-data "$DESTINATION"
    echo "[OK] visorweb2 desplegado desde $FLUTTER_BUILD"
else
    echo "[ERROR] La ruta $FLUTTER_BUILD no existe"
    exit 1
fi
