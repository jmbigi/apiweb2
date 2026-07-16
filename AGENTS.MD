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

## Reglas de tests
26. **NUNCA usar la BD de produccion.** Los tests usan SIEMPRE `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:` (configurado en `phpunit.xml`).
27. **Guard en `tests/TestCase.php`:** lanza `RuntimeException` si el driver conectado no es `sqlite`. Bloquea cualquier test contra MySQL.
28. **Antes de tests, limpiar cache de config:** `php artisan config:clear && php artisan route:clear`. La config cacheada anula `phpunit.xml`.
29. **Despues de tests, recachear:** `php artisan config:cache` (produccion).
30. **Tests unitarios** (sin BD) se ejecutan sin restricciones.
31. **Tests Feature:** corren contra SQLite en memoria. Migraciones existentes con sintaxis MySQL (`ALTER TABLE ... CHANGE`) fallaran -- es esperado.
32. **Cobertura:** todo codigo nuevo/modificado debe tener tests.

## Lecciones aprendidas (errores anteriores)
33. **`config()` no está disponible antes de `parent::setUp()`** en TestCase. Usar `getenv()` o `$_ENV` si se necesita verificar antes.
34. **Las credenciales MySQL del `.env` pueden estar obsoletas.** No asumir que funcionan. Verificar antes de usarlas.
35. **No crear archivos nuevos sin preguntar.** Confirmar con el usuario antes.

## Procedimientos para web2.faristol.net (Demo2)

### Repositorios
- **visorweb2** (Flutter): `~/apps_flutter/visorweb2/` → `git@github.com:jmbigi/visorweb2.git` (rama `main`)
- **control_app** (Flutter): `~/apps_flutter/control_app/` (proyecto nuevo)
- **web2.faristol.net** (Laravel): `/var/www/web2.faristol.net/` — clon de `~/apps_prod/API_Faristol` + Ensembles

### Despliegue de visorweb2
```bash
sudo -u www-data /var/www/web2.faristol.net/build_visorweb2.sh
```
Este script:
1. Hace `git pull` del repo visorweb2
2. Ejecuta `flutter build web --release --base-href=/visorweb2/`
3. Copia `build/web/` a `public/visorweb2/`
4. Asigna permisos `www-data:www-data`

⚠️ Si el build falla, ejecutar:
```bash
cd ~/apps_flutter/visorweb2 && flutter clean && flutter pub upgrade && flutter build web --release
```

### Despliegue de control_app
```bash
cd ~/apps_flutter/control_app
flutter build web --release --base-href=/control-app/
cp -r build/web/* /var/www/web2.faristol.net/public/control-app/
chown -R www-data:www-data /var/www/web2.faristol.net/public/control-app/
```

### Estructura de web2
```
web2.faristol.net/
├── /                          → Landing page (switch apps)
├── /visorweb2/                → Faristol App (Flutter web)
├── /control-app/              → Control App (Flutter web)
└── /api/*                     → API Laravel (producción + ensembles)
```

### Base de datos
- DB: `web2`
- Usuario: `web2`
- Bucket S3: `faristol-web2` (Wasabi)
- Los PDFs se generan localmente con Chrome headless

### Superadmin MySQL
- Credenciales guardadas en `/root/.mysql_admin_credentials`
- Para crear nuevo superadmin: `bash /root/create_admin_safe.sh`

### Acceso MariaDB root
- Root tiene password desconocido. NO modificarlo.
- Usar `create_admin_safe.sh` para crear usuarios admin alternativos.

## Estado del proyecto

### Repositorios
| Proyecto | Ruta local | Remoto | Rama |
|----------|-----------|--------|------|
| API Backend | `/var/www/web2.faristol.net/` | `github.com/jmbigi/apiweb2.git` | `main` |
| visorweb2 | `~/apps_flutter/visorweb2/` | `github.com/jmbigi/visorweb2.git` | `main` |
| Control App | `~/apps_flutter/control-app-web/` | `gitlab.com/jmbigi/desktop-demo-web.git` | `master` |

### Servicios
| Componente | Estado | Notas |
|-----------|--------|-------|
| Apache (web2.faristol.net) | ✅ Activo | SSL Let's Encrypt vigente hasta 2026-10-04 |
| MariaDB (DB web2) | ✅ Activo | Copia de faristol |
| Chrome headless | ✅ Instalado | `/usr/bin/google-chrome` para PDFs |
| Flutter SDK | ✅ 3.44.4 | `/usr/local/flutter/bin/flutter` |
| API producción | ✅ Copiada | `~/apps_prod/API_Faristol` → web2 |
| visorweb2 | ✅ Deployed | `public/visorweb2/` — 200 OK |
| control-app | ✅ Deployed | `public/control-app/` — 200 OK |
| S3 Wasabi | ✅ Configurado | bucket `faristol-web2`, endpoint `s3.us-west-1.wasabisys.com` |
| SQLite3 (PHP 8.2) | ✅ Compilado desde source | `sqlite3.so` + `pdo_sqlite.so` en `/usr/lib/php/20220829/` |

### Scripts disponibles
| Script | Ruta | Función |
|--------|------|---------|
| `deploy_web.sh` | `/root/apps_flutter/deploy_web.sh` | Compila y despliega ambos Flutter web |
| `actualizar_servidor.sh` | `/var/www/web2.faristol.net/actualizar_servidor.sh` | Git pull + composer + npm + cache clear |
| `create_admin_safe.sh` | `/root/create_admin_safe.sh` | Crea usuarios superadmin MySQL |

### Base de datos
- **Nombre:** `web2`
- **Usuario:** `web2`
- **Bucket S3:** `faristol-web2` (Wasabi, us-west-1)
- **PDFs:** Se generan localmente con Chrome headless
- **SEO:** No aplica para web2 (sitemaps, robots.txt, ads.txt eliminados)

### QA completado
1. ✅ Tests API de producción — no ejecutables por SQLite <3.35 sin DROP COLUMN. Usar MySQL nativa para testing legacy.
2. ✅ 31 tests nuevos para API de ensembles — todos pasan en SQLite en memoria.
3. ✅ Partituras privadas — scope `publicOrAccessible` verifica que no-miembros no ven scores privados.
4. ✅ Premium automático — `is_ensemble_member` flag verificado en endpoint `ensembleStatus`.
5. ✅ Visor secuencial setlist — funciona con lista, reordenar, modo play con prev/next/progreso. Se añadió prevención de duplicados.
6. ❌ Ensemble folders en UI de visorweb2 — Descartado. Futura App Móvil: Maestro elige carpeta al subir.
7. ⏳ Conversión Control App a escritorio real (Electron/Windows) — Pendiente Agosto.

### Notas
- Las migraciones legacy NO se modificaron. Los tests crean su propio esquema con `Schema::create`.
- Tests requieren `php artisan config:clear` antes de correr (config cacheada anula phpunit.xml).
- Después de tests, recachear con `php artisan config:cache`.
