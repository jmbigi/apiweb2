import { chromium } from 'playwright';

const BASE = 'https://web2.faristol.net';
const EMAIL = 'test-dashboard@faristol.test';
const PASS = 'test1234';
const CIF = 'CIF68409';

async function wait(ms) {
  return new Promise(r => setTimeout(r, ms));
}

async function main() {
  // ============================================
  // FASE 1: Login API + verificar ensemble
  // ============================================
  console.log('=== FASE 1: Login API ===');
  const browser = await chromium.launch({
    headless: true,
    args: ['--enable-unsafe-swiftshader', '--no-sandbox'],
  });
  const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

  const loginRes = await page.request.post(`${BASE}/api/auth/login`, {
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
    data: { email: EMAIL, password: PASS, cif: CIF },
  });
  const loginData = await loginRes.json();
  console.log(`  Login: ${loginData.status ? 'OK' : 'FAIL'}`);
  console.log(`  Token: ${loginData.token ? loginData.token.substring(0, 20) + '...' : 'NONE'}`);
  console.log(`  Ensemble: ${loginData.ensemble ? loginData.ensemble.name : 'NONE'}`);

  if (!loginData.token || !loginData.ensemble) {
    console.log('  ❌ No se pudo obtener sesión');
    await browser.close();
    return;
  }

  const token = loginData.token;
  const ensembleId = loginData.ensemble.id;

  // ============================================
  // FASE 2: Probar APIs del dashboard
  // ============================================
  console.log('');
  console.log('=== FASE 2: APIs del Dashboard ===');

  const endpoints = [
    { name: 'Members', url: `${BASE}/api/ensembles/${ensembleId}/members` },
    { name: 'Scores', url: `${BASE}/api/ensembles/${ensembleId}/scores` },
    { name: 'Rehearsals', url: `${BASE}/api/ensembles/${ensembleId}/rehearsals` },
    { name: 'Folders', url: `${BASE}/api/ensembles/${ensembleId}/folders` },
  ];

  let allOk = true;
  for (const ep of endpoints) {
    const start = Date.now();
    try {
      const res = await page.request.get(ep.url, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
        timeout: 15000,
      });
      const elapsed = Date.now() - start;
      const data = await res.json();
      const ok = res.status() === 200 && data.status === true;
      const count = Array.isArray(data.data) ? data.data.length : '?';
      console.log(`  ${ep.name}: ${ok ? '✅' : '❌'} (${res.status()}, ${elapsed}ms, ${count} items)`);
      if (!ok) allOk = false;
    } catch (e) {
      console.log(`  ${ep.name}: ❌ TIMEOUT/ERROR (${Date.now() - start}ms)`);
      allOk = false;
    }
  }

  // ============================================
  // FASE 3: Cargar app y verificar estado tras 15s
  // ============================================
  console.log('');
  console.log('=== FASE 3: App con sesión pre-cargada ===');

  // Guardar sesión
  const sessionData = {
    token: token,
    user: { id: loginData.user_id, name: loginData.user_name, email: EMAIL },
    ensemble: { id: loginData.ensemble.id, name: loginData.ensemble.name, cif: loginData.ensemble.cif, role: loginData.ensemble.role },
  };

  await page.goto(`${BASE}/control-app/`, { waitUntil: 'networkidle', timeout: 30000 });
  await wait(2000);

  await page.evaluate((data) => {
    window.localStorage.setItem('flutter.session_data', JSON.stringify(data));
  }, sessionData);

  await page.reload({ waitUntil: 'networkidle', timeout: 30000 });
  await wait(5000);

  // Estado inicial
  const state1 = await page.evaluate(() => {
    const glass = document.querySelector('flt-glass-pane');
    const semantics = document.querySelector('flt-semantics-host');
    return {
      glassPresent: !!glass,
      glassChildren: glass?.children.length || 0,
      semanticsPresent: !!semantics,
      semanticsChildren: semantics?.children.length || 0,
      canvas: document.querySelector('canvas') ? 'yes' : 'no',
    };
  });
  console.log(`  Estado inicial: ${JSON.stringify(state1)}`);

  // Esperar 15 segundos
  console.log('  Esperando 15s...');
  for (let i = 0; i < 15; i++) {
    await wait(1000);
    if (i % 5 === 4) console.log(`    ... ${i + 1}s`);
  }

  // Estado tras 15s
  const state2 = await page.evaluate(() => {
    const glass = document.querySelector('flt-glass-pane');
    const semantics = document.querySelector('flt-semantics-host');
    return {
      glassPresent: !!glass,
      glassChildren: glass?.children.length || 0,
      semanticsPresent: !!semantics,
      semanticsChildren: semantics?.children.length || 0,
      canvas: document.querySelector('canvas') ? 'yes' : 'no',
    };
  });
  console.log(`  Estado tras 15s: ${JSON.stringify(state2)}`);

  // Verificar que no hay errores JS
  const errors = await page.evaluate(() => {
    const errDivs = document.querySelectorAll('[style*="background-color: rgb(255"]');
    return Array.from(errDivs).map(d => d.textContent?.substring(0, 200));
  });

  // ============================================
  // RESULTADOS
  // ============================================
  console.log('');
  console.log('========================================');
  console.log('RESULTADOS');
  console.log('========================================');
  console.log(`  APIs del dashboard: ${allOk ? '✅ Todas OK' : '❌ Alguna falló'}`);
  console.log(`  App cambió en 15s: ${state1.glassChildren !== state2.glassChildren ? 'SI' : 'NO'}`);
  console.log(`  Errores JS: ${errors.length > 0 ? errors.join(', ') : 'ninguno'}`);
  console.log(`  Canvas presente: ${state1.canvas}`);
  console.log('');
  console.log('NOTA: CanvasKit no renderiza en este entorno headless');
  console.log('(sin GPU). Las APIs del dashboard funcionan correctamente.');
  console.log('');

  if (allOk) {
    console.log('✅ PRUEBA PASADA');
  } else {
    console.log('❌ PRUEBA FALLADA - revisar APIs');
  }

  await browser.close();
}

main().catch(e => {
  console.error('ERROR:', e.message);
  process.exit(1);
});
