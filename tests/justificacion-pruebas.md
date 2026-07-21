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
| 9 | `didCreateEngineInitializer` disponible como función | `typeof loader.didCreateEngineInitializer === 'function'` | `page.evaluate` |
| 10 | `main()` de Dart se ejecuta | `print('[DART] main() called')` aparece en consola | logging instrumentado |
| 11 | `runApp()` se invoca (el código Dart alcanza la llamada, no implica que haya frames renderizados) | `print('[DART] runApp() returned')` aparece en consola | logging instrumentado |
| 12 | `_AppEntryState.initState()` se ejecuta | `print('[DART] _AppEntryState.initState() called')` aparece | logging instrumentado |
| 13 | `_resolveStartup()` se ejecuta | `print('[DART] _resolveStartup() called')` aparece | logging instrumentado |
| 14 | `flutter-view` está presente en el DOM (DOM parcial del engine montado) | Selector `flutter-view` encuentra el elemento | `page.waitForSelector` |
| 15 | `flt-canvas` NO se crea | `document.querySelector('flt-canvas')` es `null` | `page.evaluate` |
| 16 | `flt-scene-host` NO se crea | `document.querySelector('flt-scene-host')` es `null` | `page.evaluate` |
| 17 | `canvas` (HTML5) NO se crea | `document.querySelector('canvas')` es `null` | `page.evaluate` |

**Nota sobre #9:** La propiedad `didCreateEngineInitializer` permanece como `function` después de 60s. En modo callback (que es el que usa el bootstrap), la función original no elimina la propiedad del loader tras ser invocada, por lo que esta señal no permite determinar si fue llamada o no. Para determinar si la función fue invocada habría que instrumentarla con un proxy (como se hizo en pruebas anteriores, donde se confirmó que SÍ fue llamada). El punto exacto donde se detiene el flujo está después de la invocación de `didCreateEngineInitializer` y antes de que `runApp()` complete la creación del canvas — sin instrumentación adicional no es posible precisar más.

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

### Pruebas E2E no visuales
- Login y navegación mediante inyección de sesión en localStorage ✅
- Flujo completo login → API → estado del dashboard ✅
- Verificación de datos del ensemble en consola (`DASHBOARD|state|...`) ✅

## 3. Lo que NO funciona

### 3.1. El canvas de Flutter (CanvasKit) no se crea

A pesar de que:
- ✅ CanvasKit se carga
- ✅ El Promise de CanvasKit se resuelve
- ✅ WebGL2 funciona
- ✅ El código Dart se ejecuta (main, runApp, initState)

**El elemento `<flt-canvas>` nunca se crea en el DOM.** Sin canvas, CanvasKit no puede renderizar nada visualmente.

**La evidencia disponible no permite identificar la causa raíz.** No hay errores, excepciones ni logs que permitan determinar el punto exacto donde se interrumpe el flujo entre `didCreateEngineInitializer` y la creación del canvas. Sería necesario instrumentar el engine con un proxy en `initializeEngine()` o capturar un trace de CDP para determinar si el engine entra en esa función y, si lo hace, por qué no completa.

### 3.2. Sin canvas, no hay renderizado visual

Consecuencias:
- `page.screenshot()` produce imágenes en blanco (~20KB para 1280x800)
- No se pueden realizar pruebas E2E basadas en renderizado visual (screenshots, regresión visual)
- La interacción con widgets dentro del canvas no es posible

### 3.3. Lo que NO está demostrado

Basado en la evidencia, NO se puede afirmar que:

| Afirmación incorrecta | Realidad |
|---|---|
| "CanvasKit no funciona en headless" | ❌ Falso. Existen proyectos que ejecutan pruebas de Flutter Web en Chromium Headless con éxito. |
| "Hace falta GPU física" | ❌ Falso. SwiftShader provee WebGL por software. |
| "Ubuntu 20.04 no es compatible" | ❌ Falso. Playwright soporta Ubuntu 20.04 oficialmente. |
| "El problema es la falta de GPU" | ❌ Falso. WebGL2 funciona correctamente. |
| "Flutter 3.44.4 es incompatible con Chrome 148" | ❌ No hay evidencia. Es una hipótesis. |
| "GitHub Actions tiene GPU" | ❌ Falso. GitHub Actions no dispone de GPU física y ejecuta Playwright con SwiftShader. |

## 4. `--debug` vs `--release` (dartdevc vs dart2js)

En las pruebas realizadas **no se observó diferencia** entre ambos modos respecto a la creación del canvas. En ambos modos:
- ✅ El código Dart se ejecuta (`main()`, `runApp()`, `initState()`)
- ❌ El canvas `<flt-canvas>` no se crea

**Lo que SÍ aporta `--debug` + `--source-maps`:**
- Stack traces con nombres reales (`Offset`, `dashboard_page.dart:361`) en vez de minificados (`minified:eU`)
- Los errores de tipo (como `ParentDataWidget`) se muestran con nombres de archivo y línea reales
- Permite identificar bugs en el código Dart que en release pasan desapercibidos

**El modo de compilación es irrelevante para el problema del canvas.** Para diagnosticar por qué no se crea, se necesita instrumentación del engine, no cambiar el compilador.

## 5. Conclusión

En este servidor (Chrome 148 + Flutter 3.44.4 + dart2js), el engine de Flutter web no completa la creación del canvas de renderizado. El código Dart se ejecuta (`main()`, `runApp()`, `initState()`), pero `<flt-canvas>` nunca se materializa. La evidencia disponible no permite identificar la causa raíz.

**Esto NO es una limitación general de Chromium Headless ni de CanvasKit.** Existen proyectos que ejecutan pruebas automatizadas de Flutter Web en Chromium Headless y entornos CI/CD con éxito. El comportamiento observado es específico de este entorno y esta configuración.

### Pruebas disponibles

| Tipo | Estado | Comando |
|------|--------|---------|
| Unitarias (Dart) | ✅ | `flutter test` |
| Smoke (HTTP + engine) | ✅ | `node tests/visual/smoke-test.mjs` |
| API dashboard | ✅ | `node tests/visual/control-app-api-test.mjs` |
| E2E no visual (login, sesión, DOM) | ✅ | Inyección de sesión en localStorage |
| E2E visual (CanvasKit) | ❌ | Depende de resolver la creación del canvas |
| Regresión visual | ❌ | Depende del E2E visual |
| Trace CDP | 🔲 Pendiente | `page.context().tracing.start(); ... tracing.stop()` |

### Próximos pasos recomendados

1. **Instrumentar `didCreateEngineInitializer()` e `initializeEngine()` con un proxy** para determinar el último punto alcanzado por el bootstrap. Es la opción más barata y rápida.
2. **Obtener un trace de Playwright** (`page.context().tracing.start()`) con CDP si la instrumentación anterior no identifica el problema.
3. **Reproducir el problema en otro entorno CI/CD** (ej. GitHub Actions) para determinar si es específico de este servidor o reproducible en una instalación limpia.
4. Si se confirma reproducible, verificar si corresponde a una regresión conocida del engine de Flutter o de Chromium.
