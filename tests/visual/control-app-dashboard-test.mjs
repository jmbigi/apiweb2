import { chromium } from 'playwright';

const BASE = 'https://web2.faristol.net';
const CIF = 'CIF68409';
const EMAIL = 'test-dashboard@faristol.test';
const PASS = 'test1234';

async function wait(ms) {
  return new Promise(r => setTimeout(r, ms));
}

async function main() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
  const page = await context.newPage();

  console.log('1. Login via API directa...');
  const loginRes = await page.request.post(`${BASE}/api/auth/login`, {
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
    data: { email: EMAIL, password: PASS, cif: CIF },
  });
  const loginData = await loginRes.json();
  console.log(`   Status: ${loginData.status}, Token: ${loginData.token ? loginData.token.substring(0, 20) + '...' : 'NONE'}`);
  console.log(`   Ensemble: ${loginData.ensemble ? loginData.ensemble.name : 'NONE'}`);

  if (!loginData.token) {
    console.log('   Login falló, revisar credenciales');
    await browser.close();
    return;
  }

  // Guardar sesión en localStorage como SharedPreferences (flujo web de Flutter)
  const sessionData = {
    token: loginData.token,
    user: { id: loginData.user_id, name: loginData.user_name, email: EMAIL },
    ensemble: loginData.ensemble ? { id: loginData.ensemble.id, name: loginData.ensemble.name, cif: loginData.ensemble.cif, role: loginData.ensemble.role } : null,
  };

  console.log('2. Abriendo control-app con sesión pre-cargada...');
  // Primero cargar la página para tener el localStorage disponible
  await page.goto(`${BASE}/control-app/`, { waitUntil: 'networkidle', timeout: 30000 });
  await wait(2000);

  // Inyectar sesión en localStorage (SharedPreferences en web usa prefijo flutter.)
  await page.evaluate((data) => {
    window.localStorage.setItem('flutter.session_data', JSON.stringify(data));
  }, sessionData);

  // Verificar que se guardó
  const saved = await page.evaluate(() => window.localStorage.getItem('flutter.session_data'));
  console.log(`   Sesión guardada: ${saved ? saved.substring(0, 100) + '...' : 'FALLÓ'}`);

  console.log('3. Recargando con sesión...');
  await page.reload({ waitUntil: 'networkidle', timeout: 30000 });
  await wait(5000);

  // Capturar estado inicial
  await page.screenshot({ path: '/tmp/control-app-01-loaded.png', fullPage: true });
  console.log('   Screenshot 1 tomado');

  // Verificar DOM de Flutter
  const domInfo = await page.evaluate(() => {
    const fltCanvas = document.querySelector('flt-canvas');
    const fltGlass = document.querySelector('flt-glass-pane');
    const canvasEl = document.querySelector('canvas');
    const bodyChildren = document.body.children.length;
    const scripts = document.querySelectorAll('script').length;
    return {
      hasFltCanvas: !!fltCanvas,
      hasFltGlass: !!fltGlass,
      hasCanvas: !!canvasEl,
      bodyChildren,
      scripts,
      bodyHTML: document.body.innerHTML.substring(0, 1000),
    };
  });
  console.log('   DOM:', JSON.stringify(domInfo, null, 2));

  // Esperar 15 segundos
  console.log('4. Esperando 15 segundos...');
  for (let i = 0; i < 15; i++) {
    await wait(1000);
    if (i % 5 === 4) console.log(`   ... ${i + 1}s`);
  }

  // Capturar estado tras 15s
  await page.screenshot({ path: '/tmp/control-app-02-after-15s.png', fullPage: true });
  console.log('   Screenshot 2 tomado');

  // Verificar si hubo cambios en el DOM
  const domAfter = await page.evaluate(() => ({
    bodyChildren: document.body.children.length,
    bodyHTML: document.body.innerHTML.substring(0, 1000),
  }));
  const changed = domAfter.bodyChildren !== domInfo.bodyChildren;
  console.log(`   DOM cambió: ${changed ? 'SI' : 'NO'}`);
  console.log(`   Children antes: ${domInfo.bodyChildren}, después: ${domAfter.bodyChildren}`);

  // Verificar errores JS
  const errors = await page.evaluate(() => {
    const errs = [];
    // @ts-ignore - check for error divs
    const errDivs = document.querySelectorAll('[style*="background-color: rgb(255"]');
    errDivs.forEach(d => errs.push(d.textContent?.substring(0, 200)));
    return errs;
  });
  console.log(`   Errores visibles: ${errors.length > 0 ? errors.join(' | ') : 'ninguno'}`);

  console.log('');
  console.log('=== RESULTADOS ===');
  console.log(`Screenshots: /tmp/control-app-01-loaded.png, /tmp/control-app-02-after-15s.png`);

  await browser.close();
}

main().catch(e => {
  console.error('ERROR:', e.message);
  process.exit(1);
});
