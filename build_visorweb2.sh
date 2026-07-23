#!/bin/bash
# ------------------------------------------------------------------
# Script para construir y desplegar visorweb2 (Flutter web app)
# en web2.faristol.net
# ------------------------------------------------------------------
set -euo pipefail

FLUTTER_PROJECT="$HOME/apps_flutter/visorweb2"
DEST="/var/www/web2.faristol.net/public/visorweb2"
FLUTTER_BIN="/usr/local/flutter/bin/flutter"

echo "=== Pull latest visorweb2 ==="
cd "$FLUTTER_PROJECT"
git pull origin main

echo "=== Building Flutter web ==="
# Nota: si falla por google_fonts u otras dependencias,
# probar con: flutter clean && flutter pub upgrade
$FLUTTER_BIN build web --wasm --release --base-href=/visorweb2/

echo "=== Deploying to web2 ==="
rm -rf "$DEST"
mkdir -p "$DEST"
cp -r build/web/* "$DEST"
chown -R www-data:www-data "$DEST"
chmod -R 755 "$DEST"

echo "=== visorweb2 deployed successfully ==="
