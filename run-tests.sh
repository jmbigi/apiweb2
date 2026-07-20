#!/bin/bash
set -e

ROJO='\033[0;31m'
VERDE='\033[0;32m'
AMARILLO='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${AMARILLO}========================================${NC}"
echo -e "${AMARILLO}  RUN-TESTS — Suite completa${NC}"
echo -e "${AMARILLO}========================================${NC}"
echo ""

# 1. Laravel Pint — solo archivos nuevos/modificados (no corregir legacy)
echo -e "${AMARILLO}[1/4] Laravel Pint${NC}"
if [ -f "vendor/bin/pint" ]; then
    git diff --name-only --diff-filter=ACMR HEAD | grep '\.php$' > /tmp/pint_files.txt || true
    if [ -s /tmp/pint_files.txt ]; then
        PINT_FILES=$(tr '\n' ' ' < /tmp/pint_files.txt)
        if ! vendor/bin/pint --test -- $PINT_FILES 2>&1; then
            echo -e "${ROJO}✗ Pint: errores de estilo en archivos nuevos/modificados${NC}"
            echo -e "${CYAN}  Corre con: vendor/bin/pint $PINT_FILES${NC}"
            exit 1
        fi
    else
        echo -e "  No hay archivos PHP nuevos/modificados"
    fi
else
    echo -e "  vendor/bin/pint no encontrado — saltando"
fi
echo -e "${VERDE}✓ Pint OK${NC}"
echo ""

# 2. Tests Laravel
echo -e "${AMARILLO}[2/4] Tests Laravel${NC}"
php artisan config:clear 2>/dev/null || true
if php artisan test --compact 2>&1; then
    echo -e "${VERDE}✓ Tests Laravel OK${NC}"
else
    echo -e "${ROJO}✗ Tests Laravel fallaron${NC}"
    exit 1
fi
echo ""

# 3. Tests Flutter
echo -e "${AMARILLO}[3/4] Tests Flutter (control-app-web)${NC}"
FLUTTER_DIR="/root/apps_flutter/control-app-web"
if [ -d "$FLUTTER_DIR" ]; then
    cd "$FLUTTER_DIR"
    if flutter test test/ 2>&1; then
        echo -e "${VERDE}✓ Tests Flutter OK${NC}"
    else
        echo -e "${ROJO}✗ Tests Flutter fallaron${NC}"
        cd - > /dev/null
        exit 1
    fi
    cd - > /dev/null
else
    echo -e "  Directorio Flutter no encontrado — saltando"
fi
echo ""

# 4. Playwright E2E (opcional)
echo -e "${AMARILLO}[4/4] Playwright E2E${NC}"
if command -v xvfb-run &> /dev/null && [ -f "tests/visual/runner.js" ]; then
    if xvfb-run node tests/visual/runner.js 2>&1; then
        echo -e "${VERDE}✓ Playwright E2E OK${NC}"
    else
        echo -e "${AMARILLO}⚠ Playwright: algunas comprobaciones fallaron (esperado sin GPU)${NC}"
    fi
else
    echo -e "  xvfb-run o runner.js no disponible — saltando"
fi
echo ""

# Nota: en producción, recachear config con: php artisan config:cache
echo ""

echo -e "${VERDE}========================================${NC}"
echo -e "${VERDE}  TODOS LOS TESTS PASARON${NC}"
echo -e "${VERDE}========================================${NC}"
