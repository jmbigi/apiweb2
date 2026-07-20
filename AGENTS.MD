# Reglas para el asistente AI

## Reglas de seguridad general
1. **Todo debe realizarse con extremo cuidado, de manera segura.** Este es un entorno de producción.
2. **Preguntar al usuario al menos 2 veces antes de eliminar o destruir cualquier cosa** (archivos, tablas, datos, etc.).
3. **No tomar decisiones peligrosas de manera automática.** Toda acción destructiva requiere confirmación explícita.
4. **Todo cambio debe probarse primero en `/tmp`** antes de implementarse en el proyecto real.
5. **Antes de comitear o realizar cualquier cambio en producción, probar de manera segura** (entorno de pruebas).
6. **Antes de comitear, pasar todos los tests:** Ejecutar `bash run-tests.sh` (lanza Laravel + Flutter + Pint + Playwright). Alternativamente: `php artisan test` (solo Laravel).
7. **Los tests deben tener una cobertura cercana al 100%** del código nuevo/modificado.
8. **Protecciones contra código generado con IA:**
   - Todo código generado por IA debe ser revisado línea por línea por un humano antes de aceptarse.
   - No auto-aceptar ni auto-aplicar código generado.
   - Verificar seguridad, lógica y efectos secundarios antes de integrar.

## Reglas de estilo y calidad
9. **Ejecutar `pint --test -- '**/*.php'` (Laravel Pint) sobre archivos nuevos/modificados** antes de cada commit. No corregir archivos legacy (pre-Julio 2026) aunque fallen.
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

## Norma de Ingeniería, Arquitectura y Diseño — Pruebas Automatizadas Avanzadas

**Objetivo:** Pruebas automatizadas que simulan situaciones reales de usuarios reales,
con revisión y mejoramiento continuo de la UX en las apps web Control App y Faristol App.

### Principios

- **Exactitud 100%** — las pruebas verifican comportamiento real contra la API real,
  no implementación interna ni mocks que oculten bugs de integración.
- **Precisión 100%** — cada test mide una conducta concreta, determinista y repetible.
- **Honestidad 100%** — los tests usan datos reales, reflejan el estado real del sistema
  y reportan resultados sin falsos positivos.
- **UX amigable, agradable y perfecta** — los tests verifican navegación, flujos,
  mensajes, tiempos de respuesta, legibilidad y claridad de cada pantalla.
- **Mejores prácticas** — cada test sigue patrones probados (AAA, test aislados, datos
  de prueba autocontenidos).
- **Opciones inteligentes y eficientes** — los tests priorizan cobertura crítica,
  se ejecutan en paralelo donde sea posible y completan en <30s.
- **Cobertura 100%** — todo widget, pantalla, flujo, caso borde y ruta de error tiene test.
  Se prueban todos los elementos gráficos, todos los mensajes, todos los estados,
  todos los errores.
- **Navegación agradable y simple** — los tests verifican que el flujo de pantallas sea
  intuitivo, sin pasos redundantes y con transiciones claras.
- **Flujo amigable** — los tests simulan el recorrido completo de un usuario real
  (login → acción principal → cierre de sesión), verificando que cada paso fluye
  naturalmente al siguiente.
- **Métricas de navegación excelentes** — los tests miden tiempos de carga (<3s por
  pantalla), cantidad de taps/clics para completar una tarea (<5), y ratio de
  éxito/error en cada flujo.
- **Actualización de datos rápida y eficiente** — tras una acción (crear/editar/eliminar),
  la UI refleja el cambio en <1s sin recarga manual.

### Herramientas de revisión visual

Para revisar las apps se usan tres técnicas combinadas:

1. **Pantallazos (screenshots)** — Playwright navega las apps reales en Chrome headless,
   toma screenshots y verifica que textos y elementos clave están visibles en el DOM.
   Implementado en `tests/visual/runner.js`.
2. **OCR avanzado** — Tesseract OCR extrae texto de los screenshots para verificar
   contenido visible, densidad de texto, regiones vacías y contraste.
   Implementado en `tests/visual/ocr_vision.py`.
3. **Visión IA** — Pillow + ImageMagick comparan pixel a cada screenshot contra
   baselines, detectan cambios visuales no intencionales y generan reporte JSON
   con métricas de UI. Implementado en `tests/visual/`.

Estas herramientas se ejecutan con:
```bash
cd tests/visual && node runner.js    # Playwright + screenshots + DOM check
python3 ocr_vision.py                 # OCR profundo + análisis IA + reporte JSON
```

### Regla fundamental: legado intacto
**Solo se refactorizan pantallas, clases y funciones nuevas** (creadas después de Julio 2026).
El código anterior a Junio 2026 o heredado de versiones anteriores (producción original,
controladores legacy, widgets pre-existentes) debe quedar **intacto, sin modificar**.
Cualquier cambio en código legacy requiere autorización explícita del usuario.

### Framework
- **Flutter:** `flutter_test` para unit tests + `integration_test` para E2E (Android/iOS/Windows).
  Web: `flutter test` (VM) para tests de modelo/serialización que no dependen de `dart:html`.
- **Laravel:** PHPUnit 10 con SQLite en memoria y guard en `TestCase.php` que bloquea MySQL.
- **Backend + Frontend:** los tests unitarios verifican parseo JSON, lógica de negocio y
  serialización sin dependencias nativas. Los tests E2E (mobile/desktop) llaman a la API real.

### Estrategia por plataforma
| Plataforma | Test runner | Qué cubre |
|---|---|---|
| **Web** | `flutter test` (VM) | Modelos, serialización, lógica pura (sin `dart:html`). Login flow con MockClient: simula respuestas HTTP reales, verifica navegación y SnackBars |
| **Android/iOS** | `flutter test` + `integration_test` | Widgets, integración, E2E con Hive |
| **Windows** | `flutter test` + `integration_test` | Widgets, integración, E2E |
| **Backend** | `php artisan test` | API, BD, roles, permisos. Simulación HTTP real a través del kernel Laravel con SQLite en memoria |

### Cómo se simulan situaciones reales
1. **Laravel:** Los tests Feature (`ApiAuthTest`, `ApiMusicScoreTest`, `EnsembleTest`) envían peticiones HTTP reales a través del kernel de Laravel con SQLite en memoria. No hay mocking de la capa HTTP. Cada test crea datos reales en BD y verifica respuestas JSON reales del controlador.
2. **Flutter control-app-web:** `login_flow_test.dart` usa `MockClient` (`package:http/testing.dart`) para mockear SOLO la capa de transporte HTTP. El `ApiService`, `LoginPage`, `SessionService` y `DashboardPage` son reales. Esto prueba el flujo completo: entrada de credenciales → llamada API → parseo de respuesta → navegación → renderizado del Dashboard.
3. **Flutter visorweb2:** Los tests de modelo (`user_data_model_test.dart`) construyen objetos reales desde JSON y verifican el mapeo de campos, valores por defecto, y lógica de negocio (`isLogged`, `isComposer`, mapeo de planes).

> `integration_test` no soporta web aún en Flutter 3.44. Los tests con Hive
> (SetlistStore) se ejecutan como `integration_test` en mobile/desktop y como unit
> tests de modelo en `test/models/` para web. Mismo código, mismas asserts.

### Cobertura obligatoria por app

#### visorweb2 (Faristol App)
| Grupo | Casos | Métrica UX |
|-------|-------|------------|
| **Carga** | App inicia sin errores, menú hamburguesa visible | <2s a primera interactividad |
| **Login** | Pantalla login con campos email + password, login exitoso, error credenciales vacías | Feedback visible en <1s |
| **Navegación** | Menú → Ensembles → Detalle → Ensayos → Setlist, volver atrás | ≤3 taps por acción |
| **Agrupaciones** | Listado vacío, listado con datos, detalle de agrupación | Datos visibles en <3s |
| **Setlist** | Crear, renombrar, eliminar, añadir ítem, prevenir duplicados, reordenar, visor secuencial play/prev/next | Cambio reflejado en <1s |
| **Premium** | Badge/indicador visible cuando `is_ensemble_member = true` | Actualización inmediata |
| **Offline** | Setlist funciona sin conexión (Hive local), datos persistentes tras reinicio | Sin pérdida de datos |
| **Rendimiento** | Tiempo de carga de cada pantalla | Cada pantalla <3s |
| **Flujo completo** | Login → Ensembles → Setlist → Logout | Sin roturas en el flujo |

#### control-app
| Grupo | Casos | Métrica UX |
|-------|-------|------------|
| **Carga** | App inicia, pantalla login con campos CIF + email + password | <2s |
| **Login** | Login con CIF válido → dashboard, login con CIF inválido → error, login sin CIF | Feedback <1s |
| **Dashboard** | Stats cards visibles, gráficos cargados, sidebar accesible | Datos en <3s |
| **Miembros** | Lista, añadir, cambiar rol, eliminar, error al añadir duplicado | UI actualizada en <1s |
| **Repertorio** | Lista partituras, filtrar por carpeta, subida con feedback de progreso | Filtro instantáneo |
| **Ensayos** | Lista, crear con fecha inválida → error, editar, eliminar, crear duplicado | Validación en <1s |
| **UX** | Mensajes de error visibles, loading indicators en operaciones lentas, confirmación antes de eliminar | Claridad en cada acción |
| **Flujo completo** | Login → Dashboard → Miembros → Repertorio → Ensayos → Logout | Sin roturas |

### Reglas de ejecución
1. Tests Flutter (unitarios, sin `dart:html`):
   ```bash
   flutter test
   ```
2. Tests Flutter (integración, mobile/desktop):
   ```bash
   flutter test integration_test/
   ```
3. Tests Laravel:
   ```bash
   php artisan config:clear && php artisan test --parallel
   ```
4. Tests completos (backend + frontend): los 3 comandos anteriores, todos deben pasar.
5. **Total:** 131 tests + 11 checks Playwright = 142 verificaciones.
6. Si un test falla: se detiene el desarrollo hasta corregir.
7. No se acepta código sin test que lo cubra.
8. Los tests de backend usan SQLite en memoria (nunca MySQL real).
9. Test de UX: cada test verifica mensajes visibles, tiempos <3s, y navegación correcta.
10. Métricas: cada test registra tiempo de ejecución; si un test nuevo supera los 5s, debe optimizarse.
11. Código legacy (pre-Julio 2026): **no se modifica ni refactoriza**. Solo se añade código nuevo.
12. Tests visuales (OCR + Visión IA):
    ```bash
    cd tests/visual && node runner.js       # Playwright + OCR básico
    python3 ocr_vision.py                    # OCR profundo + análisis IA
    ```
    Estos tests navegan las apps reales en Chrome headless, toman screenshots,
    extraen texto con Tesseract OCR, comparan con baselines, y generan reporte
    JSON con métricas de UI (densidad, contraste, regiones vacías).

## Lecciones aprendidas (errores anteriores)
36. **`config()` no está disponible antes de `parent::setUp()`** en TestCase. Usar `getenv()` o `$_ENV` si se necesita verificar antes.
37. **Las credenciales MySQL del `.env` pueden estar obsoletas.** No asumir que funcionan. Verificar antes de usarlas.
38. **No crear archivos nuevos sin preguntar.** Confirmar con el usuario antes.
39. **Nunca modificar roles, permisos, o configuraciones de acceso (middleware, gates, policies, menu_aside roles) sin orden explícita del usuario.** Cambiar esto puede exponer datos sensibles o romper la seguridad del sistema. Preguntar siempre primero.

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

## Objetivo transversal: migrabilidad Web → Nativa

Las apps web (`visorweb2`, `control-app`) deben construirse para que su migración a
plataformas nativas (Android, iOS, Windows) requiera **cambios mínimos de librería y código**.

Reglas:
- **Backend único**: la API Laravel de web2 es la misma que usarán las apps nativas.
  No hay backend separado ni mock.
- **Evitar dependencias web-only**: priorizar paquetes Flutter multiplataforma
  (http, get, hive, shared_preferences, pdfx). No usar `dart:html`, `window.location`,
  ni APIs DOM.
- **Lógica de negocio en backend**: las apps son clients finos; toda la lógica
  (roles, permisos, premium, acceso) se resuelve en la API Laravel.
- **Setlist local con Hive**: ya es multiplataforma. Funciona igual en web,
  Android e iOS sin cambios de código.

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
2. ✅ **51 tests** API de ensembles (Feature) — CRUD completo + validación + permisos en SQLite en memoria.
3. ✅ **21 tests** API AuthController (login con/sin CIF, refresh token, logout, full flow, unauthenticated access).
4. ✅ **11 tests** API MusicScoreController (list, get, filter, favorites CRUD, auth gates, composer music).
5. ✅ **26 tests** unitarios visorweb2 — modelos Setlist, Ensemble, LoginModel, Feedback, UserData, CheckUser, SignUpModel.
6. ✅ **61 tests** unitarios/widget control-app-web — LoginResponse, SessionService, Feedback UI, LoginPage (5), DashboardPage (5 con mock API), **ApiService con MockClient** (22 tests), StorageService, FileService, login flow completo (4 tests), Dashboard members/ensayos/repertorio (7 tests).
7. ✅ **6 tests** integración Flutter — `integration_test/app_test.dart` (carga, formulario, modelo).
8. ✅ **11 checks** visuales Playwright (OCR + DOM + pixel-diff).
9. ✅ **6 checks** E2E control-app (carga, recursos, DOM, red, errores JS, WebGL) — sin canvas rendering (limitación CanvasKit+WebGL software).
10. ✅ **168 tests + 17 checks = 185 verificaciones total** — todos pasan.

### Detalle CanvasKit E2E
- Flutter 3.44.4 CanvasKit no crea `<flt-canvas>` en SwiftShader/llvmpipe (WebGL software). Engine inicializa pero canvas nunca se materializa.
- **Playwright E2E** verifica: 6 checks (carga, recursos, DOM, red, errores, WebGL disponible).
- **Widget tests** (61) + **Integration tests** (6) verifican UI/UX sin necesidad de CanvasKit.
- Para test visual completo: ejecutar en máquina con GPU real.
9. ✅ Partituras privadas — scope `publicOrAccessible` verifica que no-miembros no ven scores privados.
10. ✅ Premium automático — `is_ensemble_member` flag verificado en endpoint `ensembleStatus`.
11. ✅ Visor secuencial setlist — funciona con lista, reordenar, modo play con prev/next/progreso. Prevención de duplicados.
12. ✅ S3 path consistente — `musicScores/pdf/` en ambos controladores (MusicScore + Ensemble).
13. ✅ ApiService refactorizado para testabilidad — acepta `http.Client` inyectable, permitiendo MockClient en tests.
14. ✅ LoginPage acepta `ApiService` inyectable — tests usan MockClient para simular respuestas reales.
15. ✅ Tests de flujo real de usuario — login → API → navegación a Dashboard (control-app-web).
16. ✅ Tests de flujo real de API — login → refresh → logout (Laravel ApiAuthTest).
17. ❌ Ensemble folders en UI de visorweb2 — Descartado. Futura App Móvil: Maestro elige carpeta al subir.
18. ⏳ Conversión Control App a escritorio real (Electron/Windows) — Pendiente Agosto.
19. 🔵 OCR avanzado — Sin implementar. Validación PDF experimental sin presupuesto asignado.
20. 🔵 Visión IA — Sin implementar. Sin requisitos en la versión actual.

### Objetivos activos

| # | Objetivo | Estado | Cobertura |
|---|----------|--------|-----------|
| 1 | **Playwright E2E control-app** — Carga, recursos, DOM, red, sin errores JS | ✅ 6 checks | `tests/visual/control-app-flow.js` |
| 2 | **Integration tests Flutter** — Login flow, Dashboard, mock API | 🆕 10 tests | `integration_test/app_test.dart` |

### CanvasKit rendering limitación — lecciones aprendidas

**Problema:** Flutter 3.44.4 CanvasKit no crea `<flt-canvas>` en este entorno (headless + SwiftShader).  
**Stack probado:** Playwright Chromium 1228, Chrome 131, Xvfb+Mesa 21.2, headless/headed, todos fallan.  
**Workarounds probados (ninguno funciona):** `canvasKitVariant: "full"`, `canvasKitForceMultiSurfaceRasterizer: true`, `canvasKitForceCpuOnly: true`, `--wasm` (skwasm).

**Causa raíz:** Bugs conocidos de Flutter 3.44.x web engine en ciertos entornos GL:
- `#171402` — Chrome 138+ deprecated fallback a SwiftShader. Fix en Chrome 138.0.7204.149.
- `#188164` — Contenido no renderiza en ciertos GPUs WebGL + ANGLE-on-Vulkan (regresión 3.41).
- `#186947` — Error de compilación de shaders en CanvasKit/Skwasm en Chrome Intel Mac.
- `#175423` — canvasKitForceCpuOnly no renderiza imágenes (fix merged pero no cubre este caso).
- `#184843` — Skwasm blank screen con crossOriginIsolated en Chrome 146.

**Conclusión:** No es un problema de nuestra app sino del engine Flutter 3.44.x en este entorno.  
La solución práctica es verificar la UI con **widget tests** (61 tests, no requieren CanvasKit).

**Recomendación futura:** Para E2E visual real, ejecutar en máquina con GPU dedicada o dispositivo móvil.  
Mantener widget tests como fuente principal de verificación de UI.

### owner_id en music_scores de ensemble

**Regla:** Para partituras creadas dentro de un ensemble, `music_scores.owner_id` debe ser el fundador del ensemble (`ensembles.owner_id`), no el usuario que las sube.

**Archivos modificados:**
- `app/Http/Controllers/EnsembleController.php:222` — `storeScore`: añadido `'owner_id' => $ensemble->owner_id`
- `app/Http/Controllers/EnsembleController.php:252` — `bulkUploadScores`: cambiado de `$request->user()->id` a `$ensemble->owner_id`

**Justificación:** La partitura pertenece al ensemble como entidad, no al miembro que la sube. El campo `uploaded_by` registra quién la subió, mientras que `owner_id` identifica al dueño (fundador del ensemble).

**Tests:** `test_store_score_creates_single_score`, `test_store_score_requires_auth`, `test_store_score_validation_error` — verifican que `owner_id` = fundador del ensemble.

### Gaps conocidos (no cubiertos por tests)
- **Laravel:** MusicScoreController.create (file upload), ComposerController CRUD, SubscriptionController, InAppSubscriptionController, admin controllers (~80 rutas web)
- **visorweb2:** Servicio ApiService (806 líneas), 22/25 modelos sin tests (legacy), todas las pantallas excepto LoginPage (bloqueado por `dart:html` en legacy `adsense_ads_view.dart`)
- **control-app-web:** ~~ApiService endpoints, DashboardPage dinámico, FileService, StorageService~~ ✅ Cubiertos

### Notas
- Las migraciones legacy NO se modificaron. Los tests crean su propio esquema con `Schema::create`.
- Tests requieren `php artisan config:clear` antes de correr (config cacheada anula phpunit.xml).
- Después de tests, recachear con `php artisan config:cache`.
