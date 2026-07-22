# Justificación: Limitaciones de pruebas automatizadas en este servidor

**Fecha:** 2026-07-21
**Entorno:** Ubuntu 20.04, Chrome 148.0.7778.178, Flutter 3.44.4, Playwright 1.52+
**Entorno de validación externa:** Arch Linux, Chromium 150, Flutter 3.44.7, Playwright 1.61 — mismo resultado (confirmado por análisis independiente)

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

**Nota sobre #9:** La propiedad `didCreateEngineInitializer` permanece como `function` después de 60s. En modo callback (que es el que usa el bootstrap), la función original no elimina la propiedad del loader tras ser invocada, por lo que esta señal no permite determinar si fue llamada o no. Mediante una instrumentación independiente con proxy (realizada durante la fase de diagnóstico) se confirmó que la función SÍ fue invocada. El punto exacto donde se interrumpe el flujo está después de la invocación de `didCreateEngineInitializer` — sin instrumentación adicional no es posible precisar más.

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

**La evidencia disponible no permite identificar la causa raíz.** Con instrumentación mediante proxy se ha determinado que:

1. ✅ `didCreateEngineInitializer` es llamado
2. ✅ `initializeEngine(config)` inicia (proxy registra la entrada)
3. ✅ `initializeEngine(config)` completa (proxy registra la salida, ~310ms después de la entrada)
4. ✅ `runApp()` es invocado (proxy registra la llamada)
5. ❌ El canvas `<flt-canvas>` nunca se crea

La evidencia sitúa el punto de fallo después de la invocación de `runApp()` y antes de la aparición del primer elemento de renderizado (`<flt-canvas>`). La instrumentación del bootstrap no cubre el código del framework Flutter posterior a `runApp()`. **No se observaron errores, excepciones ni logs en las fuentes instrumentadas** (Playwright, consola del navegador, eventos de red, CDP Runtime, proxy del bootstrap). El componente exacto responsable aún no ha sido identificado.

**Se ha descartado que el problema sea de la aplicación:** un proyecto `flutter create` mínimo, sin ningún código personalizado, desplegado en el mismo servidor, presenta exactamente el mismo comportamiento: `flt-canvas` no se crea, `didCreateEngineInitializer` no completa. El problema es del entorno, no de la app.

**Se ha descartado que sea específico de Chrome 148:** probado con Chromium 149 (Playwright nativo) con exactamente el mismo resultado.

**Se ha confirmado que el framework Flutter llega al primer frame:** `SchedulerBinding.addPostFrameCallback` se ejecuta y establece `localStorage['__flutter_first_frame'] = 'ok'`. Sin embargo, el canvas `<flt-canvas>` nunca se crea (ni siquiera temporalmente — el MutationObserver no registró ningún evento de creación/eliminación de elementos canvas o flt-canvas). El frame se renderiza pero no tiene superficie de salida visible.

**Causa raíz identificada:** `CanvasKit.MakeWebGLContext()` retorna `null` cuando se invoca sin argumentos (tal como lo llama el engine de Flutter), aunque `canvas.getContext('webgl2')` funciona en JavaScript plano. Esto ha sido confirmado en dos entornos independientes: Ubuntu 20.04 + Chrome 148 + Flutter 3.44.4 y Arch Linux + Chromium 150 + Flutter 3.44.7. El problema está en la capa de integración entre CanvasKit (compilado a Wasm) y SwiftShader, no en la aplicación ni en la configuración de Flutter.

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
| "Ubuntu 20.04 es la causa del problema" | ❌ No existe evidencia de que Ubuntu 20.04 sea, por sí mismo, la causa. |
| "El problema es la falta de GPU" | ❌ Falso. WebGL2 funciona correctamente. |
| "Flutter 3.44.4 es incompatible con Chrome 148" | ❌ No hay evidencia. Es una hipótesis. |
| "GitHub Actions tiene GPU" | ❌ GitHub Actions usa runners sin GPU física, pero no está demostrado que en ese entorno la aplicación complete la creación del canvas. |

## 4. `--debug` vs `--release` (dartdevc vs dart2js)

En las pruebas realizadas **no se observó diferencia** entre ambos modos respecto a la creación del canvas. En ambos modos:
- ✅ El código Dart se ejecuta (`main()`, `runApp()`, `initState()`)
- ❌ El canvas `<flt-canvas>` no se crea

**Lo que SÍ aporta `--debug` + `--source-maps`:**
- Stack traces con nombres reales (`Offset`, `dashboard_page.dart:361`) en vez de minificados (`minified:eU`)
- Los errores de tipo (como `ParentDataWidget`) se muestran con nombres de archivo y línea reales
- Permite identificar bugs en el código Dart que en release pasan desapercibidos

**En las pruebas realizadas no se observó que el modo de compilación modificara el comportamiento respecto a la creación del canvas.** Para diagnosticar por qué no se crea, se necesita instrumentación del engine, no cambiar el compilador.

## 5. Conclusión

En este servidor (Chrome 148/149 + Flutter 3.44.4 + Ubuntu 20.04), el engine de Flutter web no crea el canvas de renderizado (`<flt-canvas>`). El código Dart se ejecuta (`main()`, `runApp()`, `initState()`), el framework llega al primer frame (`addPostFrameCallback` se ejecuta), pero el canvas nunca se materializa en el DOM (ni siquiera temporalmente). No hay errores JS, no hay excepciones del framework, no hay fallos de red.

**La causa más probable es que CanvasKit no puede crear una superficie de render WebGL** que cumpla todos sus requisitos en la combinación específica de Chrome + SwiftShader + Mesa 21.2.6 + Ubuntu 20.04. El framework Flutter funciona completamente, pero no tiene dónde pintar los frames. Se ha descartado que sea un problema de la aplicación (`flutter create` mínimo falla igual), del navegador (Chrome 148 y 149 fallan igual), ni del servicio worker, flags de Chrome, o errores JS.

**No existe evidencia suficiente para atribuir el problema a una limitación inherente de Chromium Headless o CanvasKit.** Existen implementaciones documentadas donde Flutter Web funciona bajo Playwright y Chromium Headless. La causa podría ser una incompatibilidad entre la versión de Mesa (21.2.6), el driver SwiftShader de Chrome, y los requisitos de CanvasKit.

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

### Hipótesis descartadas vs pendientes

| Hipótesis | Estado | Evidencia |
|-----------|--------|-----------|
| El problema es de la aplicación | ❌ **DESCARTADA** | Un proyecto `flutter create` mínimo tiene el mismo comportamiento. |
| Falta GPU física | ❌ **DESCARTADA** | WebGL2 funciona con SwiftShader. |
| CanvasKit no cargó | ❌ **DESCARTADA** | `window.flutterCanvasKit` existe con 178 métodos. |
| El Promise de CanvasKit no resuelve | ❌ **DESCARTADA** | `await Promise.race([ckLoaded, timeout])` → resolved. |
| Error JS durante bootstrap | ❌ **DESCARTADA** | 0 pageerror, 0 console.error, 0 requestfailed. |
| Service worker interfiere | ❌ **DESCARTADA** | Mismo comportamiento con serviceWorkers:'block'. |
| Flags de Chrome incorrectos | ❌ **DESCARTADA** | Probadas 8 combinaciones de flags. |
| El problema es específico de Chrome 148 | ❌ **DESCARTADA** | Chromium 149 (Playwright nativo) reproduce el mismo comportamiento. |
| El problema es reproducible en otro entorno | ✅ **CONFIRMADO** | Arch Linux + Chromium 150 + Flutter 3.44.7 reproduce exactamente el mismo comportamiento (análisis independiente). Causa raíz: `CanvasKit.MakeWebGLContext()` retorna `null`. |
| Otro renderer (Skwasm / `--wasm`) | ❌ **DESCARTADA** (en este servidor) | `flutter build web --release --wasm` con Flutter 3.44.7 — mismo resultado: canvas no se crea. En entorno Arch + Chromium 150 + Flutter 3.44.7 el `--wasm` SÍ funciona. La diferencia es el Chromium 148 vs 150 y/o Mesa 21.2.6. |
| Trace CDP completo | 🔲 **PENDIENTE** | Baja prioridad (no hay errores que capturar). |
| Canvas se crea y luego se elimina | ❌ **DESCARTADA** | MutationObserver confirma que nunca se crea. |
| El framework no llega al primer frame | ❌ **DESCARTADA** | `addPostFrameCallback` confirma frame renderizado. |
| FlutterError.onError captura errores | ❌ **DESCARTADA** | 0 errores capturados por `FlutterError.onError`. |

### Próximos pasos recomendados

1. ✅ **Instrumentar bootstrap con proxy** — Completado. Bootstrap llega hasta `runApp()`.
2. ✅ **`flutter create` mínimo** — Completado. Confirma problema del entorno, no de la app.
3. ✅ **Probar con Chromium 149 (Playwright nativo)** — Completado. Mismo resultado que Chrome 148.
4. ✅ **Instrumentar primer frame** (`SchedulerBinding.addPostFrameCallback`) — Completado. Frame se renderiza, confirmado.
5. ✅ **Instrumentar `FlutterError.onError`** — Completado. 0 errores capturados.
6. ✅ **MutationObserver DOM** — Completado. Confirma que canvas nunca se crea, ni temporalmente.
7. ✅ **Reproducir en otro entorno** — Completado. Arch Linux + Chromium 150 + Flutter 3.44.7 confirma el mismo problema. Causa raíz: `CanvasKit.MakeWebGLContext()` retorna `null`.
8. 🔲 **Probar con Flutter SDK anterior (3.22.x)** — Pendiente. Para determinar si es una regresión reciente.
9. 🔲 **Probar en entorno con GPU real** — Pendiente. Para verificar que la app funciona correctamente con aceleración gráfica real.
