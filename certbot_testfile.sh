#!/bin/bash

# Definir la ruta donde Certbot intenta colocar los archivos de validación
WELLKNOw_DIR="/var/www/web.faristol.net/.well-known"
CHALLENGE_DIR="$WELLKNOw_DIR/acme-challenge"

# Crear el directorio si no existe
echo "$CHALLENGE_DIR"
sudo mkdir -p "$CHALLENGE_DIR"

# Crear un archivo de prueba
TEST_FILE="$CHALLENGE_DIR/testfile"
echo "Este es un archivo de prueba para verificar la accesibilidad del desafío Let's Encrypt." | sudo tee "$TEST_FILE"
sudo cat "$TEST_FILE"

# Asegurar permisos adecuados
sudo chmod -R 755 "$WELLKNOw_DIR"
sudo chown -R www-data:www-data "$WELLKNOw_DIR"
sudo chown www-data:www-data "$TEST_FILE"
echo "$CHALLENGE_DIR"
sudo ls -la "$CHALLENGE_DIR"

# Mostrar la URL para probar en el navegador
echo "Prueba la accesibilidad en tu navegador o con curl:"
#echo "http://$(hostname -I | awk '{print $1}')/.well-known/acme-challenge/testfile"
echo "http://web.faristol.net/.well-known/acme-challenge/testfile"

# Reiniciar Apache para aplicar cambios
sudo systemctl restart apache2

# Verificar si el archivo es accesible localmente
if curl -I "http://web.faristol.net/.well-known/acme-challenge/testfile" 2>/dev/null | grep -q "200 OK"; then
    echo "✅ El archivo de prueba es accesible localmente."
    curl "http://web.faristol.net/.well-known/acme-challenge/testfile"
else
    echo "❌ El archivo de prueba no es accesible. Revisa la configuración de Apache."
fi
