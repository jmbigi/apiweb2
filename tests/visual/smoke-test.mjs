// Smoke test para Flutter Web
// Valida: recursos HTTP, engine initialization, ausencia de errores
// Sin xvfb-run (Chrome headless new mode basta)

import { chromium } from 'playwright';

const BASE = 'https://web2.faristol.net';
const APPS = [
  { name: 'control-app', path: '/control-app/' },
  { name: 'visorweb2', path: '/visorweb2/' },
];

const KEY_RESOURCES = [
  'index.html',
  'flutter_bootstrap.js',
  'main.dart.js',
  'canvaskit/canvaskit.js',
  'canvaskit/canvaskit.wasm',
  'flutter_service_worker.js',
  'flutter.js',
];

async function main() {
  let globalExitCode = 0;

  for (const app of APPS) {
    console.log(`\n=== ${app.name} ===`);
    let appOk = true;

    // --- FASE 1: Recursos estaticos via HTTP ---
    console.log('  [1/5] Recursos estaticos:');
    const browser = await chromium.launch({
      headless: true,
      args: ['--no-sandbox', '--use-gl=swiftshader'],
    });
    const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

    for (const res of KEY_RESOURCES) {
      try {
        const resp = await page.request.get(`${BASE}/${app.path}${res}`);
        const ok = resp.status() === 200;
        console.log(`    ${res}: ${ok ? '✅' : '❌'} (${resp.status()})`);
        if (!ok) appOk = false;
      } catch (e) {
        if (res !== 'flutter_service_worker.js' && res !== 'flutter.js') {
          console.log(`    ${res}: ❌ (${e.message.substring(0, 60)})`);
          appOk = false;
        }
      }
    }

    // --- FASE 2: Listeners de eventos ---
    const pageErrors = [];
    const consoleErrors = [];
    const failedRequests = [];

    page.on('pageerror', err => { pageErrors.push(err.message); console.log('    [PAGEERROR]', err.message.substring(0, 150)); });
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    page.on('requestfailed', req => {
      const url = req.url();
      if (!url.includes('ping')) {
        failedRequests.push(`${url.split('/').pop()}: ${req.failure()?.errorText}`);
      }
    });

    // --- FASE 3: Carga de pagina con trazado ---
    console.log('  [2/5] Navegando...');
    await page.goto(`${BASE}${app.path}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    console.log('    DOM content loaded');

    await page.waitForLoadState('networkidle');
    console.log('    Network idle');

    // --- FASE 4: Esperar engine ---
    console.log('  [3/5] Esperando engine Flutter...');
    let engineInitialized = false;
    let engineWaitResult = 'timeout';

    try {
      await page.waitForFunction(
        () => window._flutter?.loader?.didCreateEngineInitializer === null,
        { timeout: 15000, polling: 500 }
      );
      engineInitialized = true;
      engineWaitResult = 'inicializado';
      console.log('    ✅ Engine inicializado (didCreateEngineInitializer fue llamado)');
    } catch {
      engineWaitResult = 'no llamado - timeout 15s';
      console.log('    ⏳ Engine NO inicializado');
      // Diagnostico: que estado tiene el engine?
      const diag = await page.evaluate(() => {
        const fl = window._flutter?.loader;
        const ck = window.flutterCanvasKit;
        const ckLoaded = window.flutterCanvasKitLoaded;
        const view = document.querySelector('flutter-view');
        const glassPane = document.querySelector('flt-glass-pane');
        const scripts = Array.from(document.scripts).map(s => s.src?.split('/').pop());
        return {
          loaderType: typeof fl,
          hasDidCreateEngineInitializer: fl ? 'didCreateEngineInitializer' in fl : false,
          initType: typeof fl?.didCreateEngineInitializer,
          initNull: fl?.didCreateEngineInitializer === null,
          hasCanvasKit: !!ck,
          canvasKitLoaded: ckLoaded?.toString()?.substring(0, 50) || 'undefined',
          flutterView: !!view,
          glassPane: !!glassPane,
          scripts: scripts,
        };
      });
      console.log('    Diagnostico:', JSON.stringify(diag, null, 2).replace(/\n/g, '\n    '));
    }

    // --- FASE 5: Reporte ---
    console.log(`  [4/5] Resultados:`);
    console.log(`    Recursos HTTP: ${appOk ? '✅' : '❌'}`);
    console.log(`    Engine: ${engineInitialized ? '✅' : '❌'}`);
    console.log(`    Page errors: ${pageErrors.length > 0 ? `❌ (${pageErrors.length})` : '✅ 0'}`);
    console.log(`    Console errors: ${consoleErrors.length > 0 ? `❌ (${consoleErrors.length})` : '✅ 0'}`);
    console.log(`    Failed requests: ${failedRequests.length > 0 ? `❌ (${failedRequests.length})` : '✅ 0'}`);

    if (pageErrors.length > 0) {
      console.log('\n    Page errors:');
      pageErrors.forEach(e => console.log(`      ❌ ${e.substring(0, 200)}`));
    }
    if (consoleErrors.length > 0) {
      console.log('\n    Console errors:');
      consoleErrors.forEach(e => console.log(`      ❌ ${e.substring(0, 200)}`));
    }

    const hasCriticalErrors = !appOk || !engineInitialized || pageErrors.length > 0;
    console.log(`  [5/5] Veredicto: ${hasCriticalErrors ? '❌ FALLO' : '✅ PASO'}`);
    if (hasCriticalErrors) {
      globalExitCode = 1;
      if (!engineInitialized) {
        console.log('    -> didCreateEngineInitializer nunca se llamo');
        console.log('    -> main.dart.js se cargo pero no ejecuto su codigo principal');
      }
    }

    await browser.close();
  }

  console.log(`\n========================================`);
  console.log(`Smoke test ${globalExitCode === 0 ? '✅ PASO' : '❌ FALLO'}`);
  console.log(`========================================`);
  process.exit(globalExitCode);
}

main().catch(e => {
  console.error('FATAL:', e);
  process.exit(1);
});
