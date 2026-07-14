# Reglas para el asistente AI

## Reglas de seguridad general
1. **Todo debe realizarse con extremo cuidado, de manera segura.** Este es un entorno de producción.
2. **Preguntar al usuario al menos 2 veces antes de eliminar o destruir cualquier cosa** (archivos, tablas, datos, etc.).
3. **No tomar decisiones peligrosas de manera automática.** Toda acción destructiva requiere confirmación explícita.
4. **Todo cambio debe probarse primero en `/tmp`** antes de implementarse en el proyecto real.
5. **Antes de comitear o realizar cualquier cambio en producción, probar de manera segura** (entorno de pruebas).
6. **Antes de comitear, pasar todos los tests:** `php artisan test --parallel` (o `vendor/bin/phpunit`).
7. **Los tests deben tener una cobertura cercana al 100%** del código nuevo/modificado.
8. **Protecciones contra código generado con IA:**
   - Todo código generado por IA debe ser revisado línea por línea por un humano antes de aceptarse.
   - No auto-aceptar ni auto-aplicar código generado.
   - Verificar seguridad, lógica y efectos secundarios antes de integrar.

## Reglas de estilo y calidad
9. **Ejecutar `vendor/bin/pint` (Laravel Pint)** antes de cada commit.
10. **Prohibido cometer `dd()`, `dump()`, `var_dump()`, `ray()`, `print_r()`** en código que se suba a producción.
11. **Agregar `declare(strict_types=1)`** en todos los archivos PHP nuevos.
12. **Validar siempre los request** usando Form Requests o `validate()` en los controladores.

## Reglas de base de datos y migraciones
13. **Toda migración debe ser reversible** (`up()` + `down()`) y probada en `/tmp` antes de ejecutar en producción.
14. **Nunca usar `DB::statement('DROP TABLE...')` sin confirmación explícita del usuario.**
15. **Hacer backup de la base de datos** antes de migraciones destructivas.
16. **No auto-ejecutar comandos destructivos** (`migrate:fresh`, `migrate:refresh`, `db:wipe`) sin preguntar al menos 2 veces.
17. **Usar `--force` solo con confirmación explícita del usuario.**

## Reglas de seguridad
18. **No exponer secretos:** verificar que ningún `.env`, clave API, token o contraseña quede en el código comiteado.
19. **Usar detección de N+1 queries** - revisar consultas Eloquent con `with()` para evitar lazy loading.
20. **Todas las rutas públicas deben tener throttle middleware** para prevenir ataques de fuerza bruta y scraping:
    - Rutas generales: `throttle:global` (120 req/min)
    - Rutas con APIs externas (Google Translate): `throttle:sitemap` (30 req/min)
    - Rutas de generación de archivos (PDF/imágenes): `throttle:pdf` (30 req/min)
21. **Los rate limiters se definen en `AppServiceProvider::boot()` con `RateLimiter::for()`** y se aplican por nombre en las rutas.

## Reglas de rutas y estructura
22. **Ejecutar `php artisan route:list --path=PATH`** para verificar rutas antes de agregar nuevas.
23. **Ejecutar `php artisan route:list -v` para verificar middleware** (incluyendo throttle) en las rutas.
24. **Probar en `/tmp` antes de migrar cambios al proyecto real** (copiar archivos, probar, luego implementar).
25. **Actualizar este archivo cuando se agreguen nuevas reglas**, previa confirmación del usuario.

## Reglas de tests - PELIGRO: NO EJECUTAR EN PRODUCCIÓN
26. **🚫 NUNCA usar la BD de producción ni la del `.env` para tests.** Los tests deben usar SIEMPRE `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:` (configurado en `phpunit.xml`).
27. **Si `pdo_sqlite` no está disponible, los tests con BD fallan con `$this->fail()`.** Los tests sin BD (SafetyTest, ExampleTest) se ejecutan igual.
28. **Ejecutar tests solo con:** `APP_ENV=testing vendor/bin/phpunit`.
29. **Siempre limpiar cachés antes de tests:** `php artisan config:clear && php artisan route:clear`.
30. **Los tests unitarios que NO usan BD** (RateLimiter, SafetyTest, EnvironmentTest, ExampleTest) se ejecutan sin problemas.
31. **Todo middleware nuevo debe tener tests unitarios** que verifiquen:
    - El limiter está registrado en `RateLimiter`
    - El limiter tiene los límites correctos (maxAttempts, decaySeconds)
    - Las rutas correctas tienen el middleware asignado
32. **Tests de throttle usan `CACHE_DRIVER=array`** (ya configurado en phpunit.xml) y no requieren BD.
33. **Tests que requieren BD (Feature):** usan `DatabaseTransactions` + SQLite en memoria. Sin `pdo_sqlite` fallan con mensaje claro.
34. **Test stats:** 72 tests total (32 unit + 17 app-safety + 23 feature). Ejecutar: `vendor/bin/phpunit`.

## Lecciones aprendidas (errores anteriores)
35. **Verificar `pdo_sqlite` al inicio de cada sesión** con `php -m | grep sqlite`. Sin él, los feature tests fallan.
36. **No cambiar estrategia de tests múltiples veces.** Decidir de una vez: aborto abrupto (fail), no skip graceful.
37. **`config()` no está disponible antes de `parent::setUp()`** en TestCase. Usar `getenv()` o `$_ENV` si se necesita verificar antes.
38. **Las credenciales MySQL del `.env` pueden estar obsoletas.** No asumir que funcionan. Verificar antes de usarlas.
39. **Compilar extensiones PHP en producción requiere aprobación.** Hacerlo en `/tmp` primero.
40. **No crear archivos nuevos sin preguntar** (CHECKLIST.md). Confirmar con el usuario antes.

## Procedimientos para web2.faristol.net (Demo2)

### Repositorios
- **visorweb2** (Flutter): `~/apps_flutter/visorweb2/` → `git@github.com:jmbigi/visorweb2.git` (rama `main`)
- **web2.faristol.net** (Laravel): `/var/www/web2.faristol.net/`

### Despliegue de visorweb2
```bash
sudo -u www-data /var/www/web2.faristol.net/build_visorweb2.sh
```
Este script:
1. Hace `git pull` del repo visorweb2
2. Ejecuta `flutter build web --release`
3. Copia `build/web/` a `public/web/`
4. Asigna permisos `www-data:www-data`

⚠️ Si el build falla, ejecutar:
```bash
cd ~/apps_flutter/visorweb2 && flutter clean && flutter pub upgrade && flutter build web --release
```

### Base de datos
- DB: `web2` (copia de `faristol`)
- Usuario: `web2` (mismo que producción)
- Los PDFs se generan localmente con Chrome headless (igual que producción)

### Superadmin MySQL
- Se creó usuario `superadmin_*` para administración
- Credenciales guardadas en `/root/.mysql_admin_credentials`
- Para crear nuevo superadmin: `bash /root/create_admin_safe.sh`

### Acceso MariaDB root
- Root tiene password desconocido. NO modificarlo.
- Usar `create_admin_safe.sh` para crear usuarios admin alternativos.

## Estado del proyecto

### Rama y repositorio
- Repo: `git@gitlab.com:libraryscores1/DesktopDemoWeb.git`
- Rama: `main`
- Repo visorweb2: `~/apps_flutter/visorweb2/` → `github.com/jmbigi/visorweb2.git` (rama `main`)

### Servicios
| Componente | Estado | Notas |
|-----------|--------|-------|
| Apache (web2.faristol.net) | ✅ Activo | SSL Let's Encrypt vigente hasta 2026-10-04 |
| MariaDB (DB web2) | ✅ Activo | Copia de faristol, 36 tablas, 314 partituras |
| Chrome headless | ✅ Instalado | `/usr/bin/google-chrome` para PDFs |
| Flutter SDK | ✅ 3.44.4 | `/usr/local/flutter/bin/flutter` |
| visorweb2 | ✅ Build exitoso | Desplegado en `public/web/` |

### Scripts disponibles
| Script | Ruta | Función |
|--------|------|---------|
| `build_visorweb2.sh` | `/var/www/web2.faristol.net/build_visorweb2.sh` | Compila Flutter y despliega a `public/web/` |
| `create_admin_safe.sh` | `/root/create_admin_safe.sh` | Crea usuarios superadmin MySQL sin modificar root |

### Base de datos
- **Nombre:** `web2`
- **Usuario:** `web2`
- **Superadmin:** guardado en `/root/.mysql_admin_credentials`
- **PDFs:** Se generan localmente con Chrome headless. No usan S3/Wasabi.
- **Tabla `files_s3_s`:** 353 registros de datos huérfanos (no los lee la app).
