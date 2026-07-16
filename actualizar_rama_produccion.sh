#!/bin/bash
set -ex

echo "=== Cambiando a la rama main para preparar la rama de producción ==="
git checkout main

# Hacer stash de los cambios locales (si existen)
if ! git diff-index --quiet HEAD --; then
    echo "Guardando cambios locales con git stash..."
    STASH_REF=$(git stash create "stash temporal antes de crear rama de producción")
    git stash store "$STASH_REF"
fi

# Obtener la fecha del último commit en formato YYYYMMDD
LAST_COMMIT_DATE=$(git log -1 --format=%cd --date=format:'%Y%m%d')
BRANCH_NAME="prod-d${LAST_COMMIT_DATE}"

# Verificar si la rama ya existe
if git show-ref --verify --quiet "refs/heads/$BRANCH_NAME"; then
    echo "La rama $BRANCH_NAME ya existe. Actualizando la rama..."
    git checkout "$BRANCH_NAME"
    git merge main --no-edit
else
    git checkout -b "$BRANCH_NAME" main
fi

# Hacer push de la rama de producción al repositorio remoto
echo "Haciendo push de $BRANCH_NAME al repositorio remoto..."
git push origin "$BRANCH_NAME" --force

# Volver a main y actualizar con los últimos cambios del remoto
git checkout main
git pull

# Restaurar el stash si fue necesario
if [ -n "$STASH_REF" ]; then
    git stash apply "$STASH_REF" && git stash drop "$STASH_REF"
fi

# Preparar y ejecutar el script de instalación
if [ -f "./instalar_y_refrescar.sh" ]; then
    chmod +x "./instalar_y_refrescar.sh"
    chmod +x node_modules/.bin/* 2>/dev/null || true
    find node_modules/@esbuild -type f -name 'esbuild' -exec chmod +x {} \; 2>/dev/null || true
    ./instalar_y_refrescar.sh
else
    echo "Advertencia: Script de instalación no encontrado."
fi

git status

echo "=== Proceso completado con éxito ==="
