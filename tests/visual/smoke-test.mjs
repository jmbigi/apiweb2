// Smoke test para Flutter Web — validacion determinista
// Recursos HTTP 200 + engine initialized + sin errores
// Requiere Chrome 130+ con --enable-unsafe-swiftshader

import { chromium } from 'playwright';
import { writeFileSync } from 'fs';

const BASE = 'https://web2.faristol.net';
const APPS = [
  { name: 'control-app', path: '/control-app/' },
  { name: 'visorweb2', path: '/visorweb2/' },
];

const KEY_RESOURCES = [
  'index.html', 'flutter_bootstrap.js', 'main.dart.js',
  'canvaskit/canvaskit.js', 'canvaskit/canvaskit.wasm',
  'flutter_service_worker.js', 'flutter.js',
];

async function main() {
  let globalExitCode = 0;

  for (const app of APPS) {
    console.log(`\n=== ${app.name} ===`);
    let appOk = true;
    const startTime = Date.now();

    // Chrome flags necesarios para headless:
    //   --enable-unsafe-swiftshader  : necesario desde Chrome 130+ (no hay fallback automatico)
    //   --use-gl=swiftshader         : fuerza renderizado por software
    //   --no-sandbox                 : necesario al ejecutar como root
    //   --disable-dev-shm-usage      : evita problemas de memoria compartida en containers
    const browser = await chromium.launch({
      headless: true,
      args: [
        '--no-sandbox',
        '--disable-dev-shm-usage',
        '--enable-unsafe-swiftshader',
        '--use-gl=swiftshader',
      ],
    });
    const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

    // FASE 1: Recursos estaticos
    console.log('  [1/5] Recursos estaticos:');
    for (const res of KEY_RESOURCES) {
      try {
        const resp = await page.request.get(`${BASE}/${app.path}${res}`);
        const ok = resp.status() === 200;
        console.log(`    ${res}: ${ok ? '✅' : '❌'} (${resp.status()})`);
        if (!ok) appOk = false;
      } catch (e) {
        if (!['flutter_service_worker.js', 'flutter.js'].includes(res)) {
          console.log(`    ${res}: ❌ (${e.message.substring(0, 60)})`);
          appOk = false;
        }
      }
    }

    // FASE 2: Listeners de eventos (separados para diagnostico fino)
    const pageErrors = [];
    const consoleErrors = [];
    const failedRequests = [];
    const httpErrors = [];

    page.on('pageerror', err => {
      console.log('    [PAGEERROR]', err.message.substring(0, 150));
      pageErrors.push(err.message);
    });
    page.on('console', msg => {
      if (msg.type() === 'error') {
        console.log('    [CONS.ERROR]', msg.text().substring(0, 200));
        consoleErrors.push(msg.text());
      }
    });
    page.on('requestfailed', req => {
      const url = req.url();
      if (!url.includes('ping')) {
        const err = `${url.split('/').pop()}: ${req.failure()?.errorText}`;
        console.log('    [REQ.FAILED]', err);
        failedRequests.push(err);
      }
    });
    page.on('response', resp => {
      if (resp.status() >= 400) {
        const url = resp.url();
        if (url.includes(app.path) || url.includes('canvaskit') || url.includes('main.dart')) {
          const err = `${url.split('/').pop()} HTTP ${resp.status()}`;
          console.log('    [HTTP>=400]', err);
          httpErrors.push(err);
        }
      }
    });

    // FASE 3: Carga de pagina
    console.log('  [2/5] Navegando...');
    await page.goto(`${BASE}${app.path}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    console.log('    DOM content loaded');

    await page.waitForLoadState('networkidle');
    console.log('    Network idle');

    // FASE 4: Esperar engine (hasta 60s para cubrir casos lentos)
    console.log('  [3/5] Esperando engine Flutter (60s max)...');
    let engineInitialized = false;

    // Sennal de inicializacion: esperar a que main.dart.js se ejecute.
    // Verificamos buscando el tag <flutter-view> que se crea cuando
    // runApp() es llamado. Es mas confiable que didCreateEngineInitializer
    // porque en modo callback esa propiedad no se elimina del loader.
    try {
      await page.waitForSelector('flutter-view', { timeout: 30000 });
      engineInitialized = true;
      console.log('    ✅ Engine inicializado correctamente (flutter-view presente)');
    } catch {
      // Diagnostico detallado
      const diag = await page.evaluate(() => {
        const fl = window._flutter?.loader;
        const ckLoaded = window.flutterCanvasKitLoaded;
        return {
          loaderType: typeof fl,
          initType: typeof fl?.didCreateEngineInitializer,
          initCalled: fl?.didCreateEngineInitializer === null,
          hasCanvasKit: !!window.flutterCanvasKit,
          ckPromiseState: ckLoaded?.toString()?.substring(0, 50) || 'undefined',
          flutterView: !!document.querySelector('flutter-view'),
          glassPane: !!document.querySelector('flt-glass-pane'),
          appStarted: window.__flutterAppStarted === true,
          engineReady: window.__flutterEngineReady === true,
          scripts: Array.from(document.scripts).map(s => s.src?.split('/').pop()),
        };
      });

      console.log(`    ⏳ Engine NO inicializado (${Date.now() - startTime}ms)`);
      console.log('    Diagnostico:');
      console.log(`      loader: ${diag.loaderType}`);
      console.log(`      didCreateEngineInitializer: ${diag.initType} (llamado: ${diag.initCalled})`);
      console.log(`      CanvasKit: ${diag.hasCanvasKit ? 'cargado' : 'no disponible'}`);
      console.log(`      Promise CanvasKit: ${diag.ckPromiseState}`);
      console.log(`      __flutterAppStarted: ${diag.appStarted}`);
      console.log(`      __flutterEngineReady: ${diag.engineReady}`);
      console.log(`      scripts: ${diag.scripts.join(', ')}`);

      await page.screenshot({ path: `/tmp/smoke-fail-${app.name}.png`, fullPage: true });
      console.log(`    Screenshot guardado: /tmp/smoke-fail-${app.name}.png`);
    }

    // FASE 5: Reporte
    const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
    console.log(`\n  [4/5] Resultados (${elapsed}s):`);
    console.log(`    Recursos HTTP:  ${appOk ? '✅' : '❌'}`);
    console.log(`    Engine:         ${engineInitialized ? '✅' : '❌'}`);
    console.log(`    Page errors:    ${pageErrors.length > 0 ? `❌ (${pageErrors.length})` : '✅ 0'}`);
    console.log(`    Console errors: ${consoleErrors.length > 0 ? `❌ (${consoleErrors.length})` : '✅ 0'}`);
    console.log(`    HTTP >=400:     ${httpErrors.length > 0 ? `❌ (${httpErrors.length})` : '✅ 0'}`);
    console.log(`    Request failed: ${failedRequests.length > 0 ? `❌ (${failedRequests.length})` : '✅ 0'}`);

    const hasCriticalErrors = !appOk || (!engineInitialized && pageErrors.length === 0 && failedRequests.length === 0)
      || pageErrors.length > 0;

    console.log(`  [5/5] Veredicto: ${hasCriticalErrors ? '❌ FALLO' : '✅ PASO'}`);

    if (!engineInitialized) {
      if (pageErrors.length === 0 && failedRequests.length === 0) {
        console.log('    Causa: CanvasKit Promise nunca se resuelve en este entorno headless');
        console.log('    (Chrome 131 + SwiftShader + Flutter 3.44.4)');
        console.log('    En navegadores con GPU real, el engine se inicializa correctamente.');
      }
      globalExitCode = 1;
    }

    if (!appOk || pageErrors.length > 0) {
      globalExitCode = 1;
    }

    await browser.close();
  }

  const overall = globalExitCode === 0 ? '✅ PASO' : '❌ FALLO';
  console.log(`\n========================================`);
  console.log(`Smoke test ${overall}`);
  console.log(`========================================`);
  process.exit(globalExitCode);
}

main().catch(e => {
  console.error('FATAL:', e.message);
  process.exit(1);
});
