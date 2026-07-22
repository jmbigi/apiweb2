// wasm-e2e.mjs - Prueba E2E completa con flutter build web --wasm
import { chromium } from 'playwright';
import https from 'https';

let ok = 0, fail = 0;
const p = (name, cond) => { if (cond) ok += 1; else fail += 1; console.log((cond ? '  ✅' : '  ❌'), name); };

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

// ----- API helper -----
function api(path, opts = {}) {
  return new Promise((resolve, reject) => {
    const url = new URL('https://web2.faristol.net/api' + path);
    const options = {
      hostname: url.hostname, port: 443, path: url.pathname + url.search,
      method: opts.method || 'GET',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...opts.headers },
      rejectUnauthorized: false,
    };
    const req = https.request(options, res => {
      let data = '';
      res.on('data', c => data += c);
      res.on('end', () => { try { resolve({ status: res.statusCode, body: JSON.parse(data) }); } catch { resolve({ status: res.statusCode, body: data }); } });
    });
    req.on('error', reject);
    if (opts.body) req.write(JSON.stringify(opts.body));
    req.end();
  });
}

// ========== 1. CONTROL-APP ==========
console.log('\n=== CONTROL-APP ===\n');

console.log('1. Carga');
await page.goto('https://web2.faristol.net/control-app/', { waitUntil: 'networkidle', timeout: 90000 });
await page.waitForTimeout(8000);

// Canvas dentro de shadow DOM
const canvasInfo = await page.evaluate(() => {
  const gp = document.querySelector('flt-glass-pane');
  const shadow = gp?.shadowRoot;
  const canvas = shadow?.querySelector('canvas');
  const container = shadow?.querySelector('flt-canvas-container');
  return {
    glassPane: !!gp,
    shadowRoot: !!shadow,
    canvasExists: !!canvas,
    canvasW: canvas?.width || 0,
    canvasH: canvas?.height || 0,
    containerW: container?.style?.width,
    containerH: container?.style?.height,
    fltView: !!document.querySelector('flutter-view'),
  };
});
p('glass-pane con shadow root', canvasInfo.shadowRoot);
p('canvas existe', canvasInfo.canvasExists);
p('canvas dimensiones > 0', canvasInfo.canvasW > 0 && canvasInfo.canvasH > 0);
console.log('   canvas:', canvasInfo.canvasW + 'x' + canvasInfo.canvasH);

// Login via API
console.log('\n2. Login');
const r = await api('/auth/login', {
  method: 'POST',
  body: { id_card: 'superadmin', email: 'superadmin_test@email.com', password: 'Sinclave1!' },
});
const token = r.body?.token;
p('API login', !!token && r.body?.status === true);

// Guardar sesión en localStorage y recargar
await page.evaluate((t) => {
  localStorage.setItem('token', t);
  localStorage.setItem('session', JSON.stringify({ token: t, userID: 1, userName: 'superadmin', userEmail: 'superadmin_test@email.com', userCIF: 'superadmin', ensembleID: null, userType: 'superadmin' }));
}, token);
await page.reload({ waitUntil: 'networkidle' });
await page.waitForTimeout(8000);

const afterLogin = await page.evaluate(() => {
  const gp = document.querySelector('flt-glass-pane');
  const shadow = gp?.shadowRoot;
  const canvas = shadow?.querySelector('canvas');
  return {
    canvas: !!canvas,
    canvasW: canvas?.width,
    canvasH: canvas?.height,
    fltView: !!document.querySelector('flutter-view'),
    text: document.body.innerText.substring(0, 500),
  };
});
p('canvas tras login', afterLogin.canvas);
p('flutter-view presente', afterLogin.fltView);
console.log('   texto visible:', afterLogin.text.substring(0, 200));

// Screenshot
await page.screenshot({ path: '/tmp/control-app-login.png' });
p('screenshot tomado', true);

// API endpoints
console.log('\n3. API endpoints');
const auth = { Authorization: 'Bearer ' + token };
let res = await api('/dashboard', { headers: auth });
p('dashboard', res.status === 200);

res = await api('/music-score/list', { headers: auth });
const scores = res.body?.data || [];
p('scores list (' + scores.length + ')', scores.length > 0);

res = await api('/ensembles', { headers: auth });
const ensembles = res.body?.data || [];
p('ensembles list (' + ensembles.length + ')', ensembles.length > 0);
if (ensembles.length > 0) {
  const eid = ensembles[0].id;
  res = await api('/ensembles/' + eid + '/members', { headers: auth });
  p('members', res.status === 200);
  res = await api('/ensembles/' + eid + '/rehearsals', { headers: auth });
  p('rehearsals', res.status === 200);
}

// ========== 2. VISORWEB2 ==========
console.log('\n=== VISORWEB2 ===\n');

console.log('4. Carga');
await page.goto('https://web2.faristol.net/visorweb2/', { waitUntil: 'networkidle', timeout: 90000 });
await page.waitForTimeout(8000);

const vwInfo = await page.evaluate(() => {
  const gp = document.querySelector('flt-glass-pane');
  const shadow = gp?.shadowRoot;
  const canvas = shadow?.querySelector('canvas');
  const iframe = document.querySelector('iframe');
  return {
    canvas: !!canvas,
    canvasW: canvas?.width,
    canvasH: canvas?.height,
    fltView: !!document.querySelector('flutter-view'),
    iframeSrc: iframe?.src?.substring(0, 100),
    semanticsChildren: document.querySelector('flt-semantics-host')?.childElementCount || 0,
  };
});
p('canvas visorweb2', vwInfo.canvas);
p('flutter-view presente', vwInfo.fltView);
if (vwInfo.canvas) console.log('   canvas:', vwInfo.canvasW + 'x' + vwInfo.canvasH);

// Login visorweb2 via API
console.log('\n5. Login visorweb2');
const r2 = await api('/auth/login', {
  method: 'POST',
  body: { email: 'composer_test@email.com', password: 'Sinclave1!' },
});
const token2 = r2.body?.token;
p('API login', !!token2 && r2.body?.status === true);

const auth2 = { Authorization: 'Bearer ' + token2 };
res = await api('/music-score/list', { headers: auth2 });
const scores2 = res.body?.data || [];
const faristol = scores2.filter(s => s.owner_id === 109);
p('scores (' + scores2.length + ')', scores2.length > 0);
p('faristol scores (' + faristol.length + ')', faristol.length >= 3);

// PDF content
if (faristol.length > 0) {
  console.log('\n6. PDF content');
  res = await api('/music-score/getPdfContent', {
    method: 'POST',
    headers: { Authorization: 'Bearer ' + token2, 'Content-Type': 'application/json' },
    body: { scoreId: faristol[0].id, id: faristol[0].files?.[0]?.id || 1, password: '' },
  });
  p('PDF content (' + (r2.body?.status ? 'OK' : 'FAIL') + ')', res.status === 200 && res.body?.status === true);
  console.log('   score:', faristol[0].name, '| PDF:', res.body?.status ? 'disponible' : 'no disponible');
}

// Ensemble
res = await api('/user/ensemble-status', { headers: auth2 });
p('ensemble member', res.body?.data?.is_ensemble_member === true);

// Screenshot visorweb2
await page.screenshot({ path: '/tmp/visorweb2-loaded.png' });
p('screenshot visorweb2', true);

// ========== SUMMARY ==========
console.log('\n================================');
console.log('Resultados: ' + ok + ' ✅, ' + fail + ' ❌');

await browser.close();
process.exit(fail > 0 ? 1 : 0);
