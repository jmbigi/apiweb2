#!/bin/bash
# Script para probar el desafío ACME de Let's Encrypt en web2.faristol.net
set -euo pipefail

DOMAIN="web2.faristol.net"
WWW_DIR="/var/www/web2.faristol.net"
CHALLENGE_DIR="$WWW_DIR/.well-known/acme-challenge"

echo "=== Creando archivo de prueba ACME para $DOMAIN ==="
echo "Directorio: $CHALLENGE_DIR"
sudo mkdir -p "$CHALLENGE_DIR"

TEST_FILE="$CHALLENGE_DIR/testfile"
echo "OK" | sudo tee "$TEST_FILE"

sudo chmod -R 755 "$WWW_DIR/.well-known"
sudo chown -R www-data:www-data "$WWW_DIR/.well-known"

echo "=== Verificando acceso ==="
if curl -I "https://$DOMAIN/.well-known/acme-challenge/testfile" 2>/dev/null | grep -q "200\|404"; then
    echo "[OK] El archivo de prueba es accesible desde $DOMAIN"
    curl -s "https://$DOMAIN/.well-known/acme-challenge/testfile"
    echo ""
    echo "Limpieza: sudo rm \"$TEST_FILE\""
else
    echo "[ERROR] No se pudo acceder. Revisa la configuracion de Apache/Nginx."
fi
