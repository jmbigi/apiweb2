// control-app-e2e.mjs - Prueba E2E de control-app con navegador
// Requiere: flutter build web --wasm
import { chromium } from 'playwright';

let ok = 0;
let fail = 0;
const p = (name, cond) => {
  if (cond) { ok += 1; console.log('  ✅', name); }
  else { fail += 1; console.log('  ❌', name); }
};

async function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

const errors = [];
page.on('pageerror', err => { errors.push({ type: 'pageerror', msg: err.message, stack: err.stack }); });
page.on('console', msg => {
  if (msg.type() === 'error') errors.push({ type: 'console', msg: msg.text() });
});

console.log('control-app E2E');
console.log('================\n');

// 1. Cargar app
console.log('1. Carga inicial');
await page.goto('https://web2.faristol.net/control-app/', { waitUntil: 'networkidle', timeout: 90000 });
await page.waitForTimeout(5000);

const dom = await page.evaluate(() => ({
  canvas: !!document.querySelector('flt-canvas'),
  glassPane: !!document.querySelector('flt-glass-pane'),
  semanticsChildren: document.querySelector('flt-semantics-host')?.childElementCount || 0,
  fltView: !!document.querySelector('flutter-view'),
  scripts: Array.from(document.querySelectorAll('script[src]')).map(s => s.src.split('/').pop()),
}));
p('flt-canvas presente', dom.canvas);
p('glass-pane presente', dom.glassPane);
p('flutter-view presente', dom.fltView);
p('semantics con hijos', dom.semanticsChildren > 0);
console.log('   scripts:', dom.scripts.join(', '));

// 2. Obtener árbol semántico
console.log('\n2. Árbol semántico');
const semantics = await page.evaluate(() => {
  const sh = document.querySelector('flt-semantics-host');
  if (!sh) return [];
  const walker = document.createTreeWalker(sh, NodeFilter.SHOW_ELEMENT);
  const items = [];
  let n;
  while (n = walker.nextNode()) {
    const label = n.getAttribute('aria-label') || '';
    const role = n.getAttribute('role') || n.tagName;
    const text = n.textContent?.trim() || '';
    const actions = n.getAttribute('aria-actions') || '';
    if ((label || text.length > 1) && role !== 'FLT-SEMANTICS-PLACEHOLDER') {
      items.push({ role: role.substring(0, 20), label: label.substring(0, 60), text: text.substring(0, 60), actions: actions.substring(0, 40) });
    }
  }
  return items.slice(0, 30);
});
p('elementos semánticos encontrados', semantics.length > 0);
console.log('   elementos:', semantics.length);
semantics.forEach((s, i) => console.log(`   [${i}] ${s.role} "${s.label || s.text}" actions=${s.actions}`));

// 3. Login
console.log('\n3. Login flow');
// Buscar campo email o CIF
let loginInput = semantics.find(s => s.label.toLowerCase().includes('email') || s.label.toLowerCase().includes('cif') || s.label.toLowerCase().includes('correo'));
p('campo email/CIF visible', !!loginInput);

// Intentar login via API + inyectar sesión
const https = require('https');
function api(path, opts = {}) {
  return new Promise((resolve, reject) => {
    const u = new URL('https://web2.faristol.net/api' + path);
    const options = {
      hostname: u.hostname, port: 443, path: u.pathname + u.search,
      method: opts.method || 'GET',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...opts.headers },
      rejectUnauthorized: false,
    };
    const req = https.request(options, res => {
      let data = '';
      res.on('data', c => data += c);
      res.on('end', () => {
        try { resolve({ status: res.statusCode, body: JSON.parse(data) }); }
        catch { resolve({ status: res.statusCode, body: data }); }
      });
    });
    req.on('error', reject);
    if (opts.body) req.write(JSON.stringify(opts.body));
    req.end();
  });
}

let r = await api('/auth/login', {
  method: 'POST',
  body: { id_card: 'superadmin', email: 'superadmin_test@email.com', password: 'Sinclave1!' }
});
const token = r.body?.token;
p('API login exitoso', !!token && r.body?.status === true);
console.log('   user:', r.body?.user_name);

// Inyectar token en localStorage si está disponible
if (token) {
  await page.evaluate((t) => {
    try { localStorage.setItem('token', t); } catch(e) {}
    try { localStorage.setItem('session', JSON.stringify({ token: t, user: 'superadmin' })); } catch(e) {}
  }, token);
  await page.reload({ waitUntil: 'networkidle' });
  await page.waitForTimeout(5000);

  const afterLogin = await page.evaluate(() => ({
    canvas: !!document.querySelector('flt-canvas'),
    semanticsChildren: document.querySelector('flt-semantics-host')?.childElementCount || 0,
    text: document.body.innerText.substring(0, 500),
  }));
  p('canvas tras login', afterLogin.canvas);
  p('semantics tras login', afterLogin.semanticsChildren > 0);
  console.log('   texto visible:', afterLogin.text.substring(0, 200));
}

// 4. Dashboard endpoints
console.log('\n4. Dashboard API');
const auth = { Authorization: 'Bearer ' + token };
r = await api('/dashboard', { headers: auth });
p('dashboard API 200', r.status === 200);
p('dashboard status true', r.body?.status === true || r.body?.message?.includes('success'));
console.log('   response:', JSON.stringify(r.body).substring(0, 200));

r = await api('/music-score/list', { headers: auth });
const scores = r.body?.data || [];
p('scores list OK', scores.length > 0);
console.log('   total scores:', scores.length);

// 5. Ensemble
console.log('\n5. Ensemble');
r = await api('/ensembles', { headers: auth });
const ensembles = r.body?.data || [];
p('ensembles list OK', ensembles.length > 0);
if (ensembles.length > 0) {
  const eid = ensembles[0].id;
  console.log('   ensemble:', ensembles[0].name);
  r = await api(`/ensembles/${eid}/members`, { headers: auth });
  p('members OK', r.status === 200);
  r = await api(`/ensembles/${eid}/rehearsals`, { headers: auth });
  p('rehearsals OK', r.status === 200);
  r = await api(`/ensembles/${eid}/folders`, { headers: auth });
  p('folders OK', r.status === 200);
}

// 6. Errores
console.log('\n6. Errores en consola');
if (errors.length === 0) {
  console.log('  ✅ 0 errores');
} else {
  errors.forEach(e => console.log(`  ❌ [${e.type}] ${e.msg}`));
}
p('sin page errors', !errors.some(e => e.type === 'pageerror'));
p('sin console errors', !errors.some(e => e.type === 'console'));

console.log('\n================================');
console.log(`Resultados: ${ok} ✅, ${fail} ❌`);

await browser.close();
process.exit(fail > 0 ? 1 : 0);
