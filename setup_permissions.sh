#!/bin/bash

# Cambia esta ruta a la raíz de tu proyecto Laravel
PROJECT_PATH="/var/www/web2.faristol.net"

# Asegúrate de que el usuario del servidor web es correcto (www-data o apache)
WEB_SERVER_USER="www-data"
WEB_SERVER_GROUP="www-data"

# Establece el propietario de los archivos y carpetas
echo "Estableciendo el propietario del proyecto..."
sudo chown -R $WEB_SERVER_USER:$WEB_SERVER_GROUP $PROJECT_PATH

# Permiso no recursivo de carpeta del proyecto
sudo chmod 775 $PROJECT_PATH

# Permisos para archivos y carpetas
echo "Configurando permisos para archivos y carpetas..."
sudo find $PROJECT_PATH -type f -not -perm 664 -exec chmod 664 {} \;    # Archivos: rw-r--r--
sudo find $PROJECT_PATH -type d -not -perm 775 -exec chmod 775 {} \;    # Directorios: rwxr-xr-x

# Dar permisos de lectura, escritura y ejecución al servidor web para storage/framework y cache
echo "Configurando permisos para storage/framework y bootstrap/cache..."
sudo chmod -R 775 $PROJECT_PATH/storage/framework $PROJECT_PATH/bootstrap/cache

sudo chmod +x actualizar_rama_produccion.sh
sudo chmod +x setup_permissions.sh

# Confirmación final
echo "Configuración de permisos completada."
