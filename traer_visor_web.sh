#!/bin/bash

SOURCE='/home/kubuntu/OneDrive/Trabajo/Faristol/VisorWeb/visorweb/build/web/'
DESTINATION='./public/'

# Copiar
echo "Copiando contenido Flutter de VisorWeb en Proyecto PHP Laravel. Fecha $(date)"

# Comando de copia
cp -r "$SOURCE" "$DESTINATION"

echo "Copia completada exitosamente."
