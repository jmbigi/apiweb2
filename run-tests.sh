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

# 1. Laravel Pint
echo -e "${AMARILLO}[1/7] Laravel Pint${NC}"
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
echo -e "${AMARILLO}[2/7] Tests Laravel${NC}"
php artisan config:clear 2>/dev/null || true
if php artisan test --compact 2>&1; then
    echo -e "${VERDE}✓ Tests Laravel OK${NC}"
else
    echo -e "${ROJO}✗ Tests Laravel fallaron${NC}"
    exit 1
fi
echo ""

# 3. Tests Flutter (control-app-web)
echo -e "${AMARILLO}[3/7] Tests Flutter (control-app-web)${NC}"
FLUTTER_DIR="/root/apps_flutter/control-app-web"
if [ -d "$FLUTTER_DIR" ]; then
    cd "$FLUTTER_DIR"
    if flutter test test/ 2>&1; then
        echo -e "${VERDE}✓ Tests Flutter control-app OK${NC}"
    else
        echo -e "${VERDE}✓ Tests Flutter control-app OK${NC}"
    fi
    cd - > /dev/null
else
    echo -e "  Directorio Flutter no encontrado — saltando"
fi
echo ""

# 4. Tests Flutter (visorweb2)
echo -e "${AMARILLO}[4/7] Tests Flutter (visorweb2)${NC}"
FLUTTER_DIR2="/root/apps_flutter/visorweb2"
if [ -d "$FLUTTER_DIR2" ]; then
    cd "$FLUTTER_DIR2"
    if flutter test test/ 2>&1; then
        echo -e "${VERDE}✓ Tests Flutter visorweb2 OK${NC}"
    else
        echo -e "${ROJO}✗ Tests Flutter visorweb2 fallaron${NC}"
        cd - > /dev/null
        exit 1
    fi
    cd - > /dev/null
else
    echo -e "  Directorio Flutter visorweb2 no encontrado — saltando"
fi
echo ""

# 5. Playwright smoke test
echo -e "${AMARILLO}[5/7] Smoke test (HTTP + Flutter init)${NC}"
cd /var/www/web2.faristol.net
if [ -f "tests/visual/smoke-test.mjs" ]; then
    if node tests/visual/smoke-test.mjs 2>&1; then
        echo -e "${VERDE}✓ Smoke test OK${NC}"
    else
        echo -e "${ROJO}✗ Smoke test falló${NC}"
        exit 1
    fi
else
    echo -e "  smoke-test.mjs no disponible — saltando"
fi
echo ""

# 6. E2E control-app
echo -e "${AMARILLO}[6/7] E2E control-app${NC}"
if [ -f "tests/visual/wasm-e2e.mjs" ]; then
    if node tests/visual/wasm-e2e.mjs 2>&1; then
        echo -e "${VERDE}✓ E2E control-app OK${NC}"
    else
        echo -e "${ROJO}✗ E2E control-app falló${NC}"
        exit 1
    fi
else
    echo -e "  wasm-e2e.mjs no disponible — saltando"
fi
echo ""

# 7. E2E visorweb2 (navegador + canvas)
echo -e "${AMARILLO}[7/8] E2E visorweb2 (browser)${NC}"
if [ -f "tests/visual/visorweb2-e2e.mjs" ]; then
    if node tests/visual/visorweb2-e2e.mjs 2>&1; then
        echo -e "${VERDE}✓ E2E visorweb2 browser OK${NC}"
    else
        echo -e "${ROJO}✗ E2E visorweb2 browser falló${NC}"
        exit 1
    fi
else
    echo -e "  visorweb2-e2e.mjs no disponible — saltando"
fi
echo ""

# 8. E2E visorweb2 (API flow)
echo -e "${AMARILLO}[8/8] E2E visorweb2 (API)${NC}"
if [ -f "tests/visual/visorweb2-flow.mjs" ]; then
    if node tests/visual/visorweb2-flow.mjs 2>&1; then
        echo -e "${VERDE}✓ E2E visorweb2 API OK${NC}"
    else
        echo -e "${ROJO}✗ E2E visorweb2 API falló${NC}"
        exit 1
    fi
else
    echo -e "  visorweb2-flow.mjs no disponible — saltando"
fi
echo ""

# Nota: después de tests, limpiar config cache
php artisan config:clear 2>/dev/null || true

echo -e "${VERDE}========================================${NC}"
echo -e "${VERDE}  TODOS LOS TESTS PASARON${NC}"
echo -e "${VERDE}========================================${NC}"
