#!/bin/bash

SOURCE='/home/kubuntu/OneDrive/Trabajo/Faristol/VisorWeb/visorweb/build/web/'
DESTINATION='./public/web'

# Verificar si existe el directorio de origen
if [ -d "$SOURCE" ]; then
    echo "Directorio de origen encontrado: $SOURCE"

    # Eliminar el destino si existe
    if [ -d "$DESTINATION" ]; then
        echo "Eliminando directorio de destino existente: $DESTINATION"
        rm -rf "$DESTINATION"
    fi

    # Crear el directorio destino y copiar el contenido
    echo "Copiando contenido Flutter de VisorWeb en Proyecto PHP Laravel. Fecha $(date)"
    mkdir -p "$DESTINATION"
    cp -r "$SOURCE." "$DESTINATION"

    # Asignar permisos 775 al directorio destino
    chmod -R 775 "$DESTINATION"

    echo "Copia completada exitosamente con permisos 775 asignados al directorio destino."
else
    echo "Error: El directorio de origen $SOURCE no existe."
fi
