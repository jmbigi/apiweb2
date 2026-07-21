# Justificación: Limitaciones de pruebas automatizadas en este servidor

**Fecha:** 2026-07-21
**Entorno:** Ubuntu 20.04, Chrome 148.0.7778.178, Flutter 3.44.4, Playwright 1.52+

---

## 1. Hechos comprobados (evidencia reproducible)

| # | Afirmación | Evidencia | Método |
|---|-----------|-----------|--------|
| 1 | `main.dart.js` se carga | HTTP 200, presente en `document.scripts` | `curl` + `page.evaluate` |
| 2 | `window.flutterCanvasKit` existe | 178 métodos disponibles (`MakeImageFromEncoded`, `Color`, etc.) | `Object.getOwnPropertyNames()` |
| 3 | `window.flutterCanvasKitLoaded` Promise SE RESUELVE | `await Promise.race([ckLoaded, timeout(5s)])` → `resolved: true` | `page.evaluate(async)` |
| 4 | WebGL2 funciona | `canvas.getContext('webgl2', {...})` OK, `clearColor` + `clear` OK | test directo en página |
| 5 | `pageerror` | 0 eventos en todas las pruebas | `page.on('pageerror')` |
| 6 | `console.error` | 0 eventos en todas las pruebas | `page.on('console')` |
| 7 | `requestfailed` | 0 eventos | `page.on('requestfailed')` |
| 8 | `_flutter.loader` existe | `typeof loader === 'object'` | `page.evaluate` |
| 9 | `didCreateEngineInitializer` disponible | `typeof loader.didCreateEngineInitializer === 'function'` | `page.evaluate` |
| 10 | `didCreateEngineInitializer` NO es llamado por `main.dart.js` | Sigue siendo `function` después de 60s (en modo callback nunca se elimina) | `waitForFunction(30s)` |
| 11 | `main()` de Dart se ejecuta | `print('[DART] main() called')` aparece en consola | logging instrumentado |
| 12 | `runApp()` se ejecuta | `print('[DART] runApp() returned')` aparece en consola | logging instrumentado |
| 13 | `_AppEntryState.initState()` se ejecuta | `print('[DART] _AppEntryState.initState() called')` aparece | logging instrumentado |
| 14 | `_resolveStartup()` se ejecuta | `print('[DART] _resolveStartup() called')` aparece | logging instrumentado |
| 15 | `flutter-view` está presente en el DOM | Selector `flutter-view` encuentra el elemento | `page.waitForSelector` |
| 16 | `flt-canvas` NO se crea | `document.querySelector('flt-canvas')` es `null` | `page.evaluate` |
| 17 | `flt-scene-host` NO se crea | `document.querySelector('flt-scene-host')` es `null` | `page.evaluate` |
| 18 | `canvas` (HTML5) NO se crea | `document.querySelector('canvas')` es `null` | `page.evaluate` |

## 2. Lo que funciona correctamente

### Pruebas unitarias y de API
- `flutter test` (68 tests) ✅
- `php artisan test` (155 tests) ✅
- Smoke test HTTP + engine (`smoke-test.mjs`) ✅ (verifica HTTP 200 + flutter-view presente + 0 errores)
- Login API + sesión + dashboard API endpoints ✅

### Pruebas de lógica de negocio
- SessionService: guardar/recuperar/limpiar ✅
- LoginModel: parseo JSON ✅
- ApiService: mocking HTTP ✅
- DashboardPage: creación con/sin ensemble ✅

## 3. Lo que NO funciona y por qué

### 3.1. El canvas de Flutter (CanvasKit) no se crea

A pesar de que:
- ✅ CanvasKit se carga
- ✅ El Promise de CanvasKit se resuelve
- ✅ WebGL2 funciona
- ✅ El código Dart se ejecuta (main, runApp, initState)

**El elemento `<flt-canvas>` nunca se crea en el DOM.** Sin canvas, CanvasKit no puede renderizar nada. La app Flutter ejecuta su código pero no hay salida visual.

**Estado:** Se desconoce la causa exacta. `didCreateEngineInitializer` no completa su ciclo en modo callback: se llama pero `_onEntrypointLoaded` (que ejecuta `engine.initializeEngine(config).then(app => app.runApp())`) nunca resuelve. No hay errores, no hay excepciones, no hay logs. El engine simplemente se detiene silenciosamente.

### 3.2. Sin canvas, no hay renderizado visual

Consecuencias:
- `page.screenshot()` produce imágenes en blanco (~20KB para 1280x800)
- No se puede verificar visualmente que los widgets se renderizan correctamente
- No se puede hacer regresión visual (comparación de screenshots)
- No se puede probar E2E con interacción visual (clics en elementos del canvas)

### 3.3. Lo que NO está demostrado

Basado en la evidencia, NO se puede afirmar que:

| Afirmación incorrecta | Realidad |
|---|---|
| "CanvasKit no funciona en headless" | ❌ Falso. Chromium Headless con SwiftShader funciona en CI/CD de otros proyectos. |
| "Hace falta GPU física" | ❌ Falso. SwiftShader provee WebGL por software. |
| "Ubuntu 20.04 no es compatible" | ❌ Falso. Playwright soporta Ubuntu 20.04. |
| "El problema es la falta de GPU" | ❌ Falso. WebGL2 funciona correctamente. |

## 4. Conclusión

En este servidor (Chrome 148 + Flutter 3.44.4 + dart2js), el engine de Flutter web no completa la creación del canvas de renderizado. El código Dart se ejecuta, pero `flutter-canvas` nunca se materializa. La causa exacta no está identificada. No hay errores, excepciones ni logs que permitan determinar el punto exacto de fallo.

**Esto NO es una limitación general de Chromium Headless ni de CanvasKit.** Es una observación específica de este entorno y esta configuración. En otros entornos CI/CD con configuraciones similares, Flutter Web funciona correctamente.

### Pruebas disponibles

| Tipo | Estado | Comando |
|------|--------|---------|
| Unitarias (Dart) | ✅ | `flutter test` |
| Smoke (HTTP + engine) | ✅ | `node tests/visual/smoke-test.mjs` |
| API dashboard | ✅ | `node tests/visual/control-app-api-test.mjs` |
| E2E visual | ❌ | No disponible sin canvas |
| Regresión visual | ❌ | No disponible sin canvas |

### Próximos pasos para habilitar E2E visual
1. Identificar por qué `initializeEngine` no resuelve en este Chrome 148
2. O actualizar Flutter a una versión compatible con Chrome 148
3. O ejecutar en un entorno CI/CD con GPU (GitHub Actions, etc.)
