// visorweb2-e2e.mjs - Prueba E2E completa de visorweb2 con navegador
// Verifica: canvas shadow DOM, APIs, datos, PDF, screenshots
import { chromium } from 'playwright';
import https from 'https';

let ok = 0, fail = 0;
const p = (name, cond) => { if (cond) ok += 1; else fail += 1; console.log((cond ? '  ✅' : '  ❌'), name); };

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

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

let pageErrors = 0, consoleErrors = 0;
page.on('pageerror', () => pageErrors++);
page.on('console', msg => { if (msg.type() === 'error') consoleErrors++; });

console.log('visorweb2 E2E');
console.log('==============\n');

// 1. Carga inicial
console.log('1. Carga de la app');
await page.goto('https://web2.faristol.net/visorweb2/', { waitUntil: 'networkidle', timeout: 90000 });
await page.waitForTimeout(10000);

const loadInfo = await page.evaluate(() => {
  const gp = document.querySelector('flt-glass-pane');
  const shadow = gp?.shadowRoot;
  const canvas = shadow?.querySelector('canvas');
  return {
    fltView: !!document.querySelector('flutter-view'),
    glassPane: !!gp,
    shadowRoot: !!shadow,
    canvas: !!canvas,
    canvasW: canvas?.width || 0,
    canvasH: canvas?.height || 0,
    text: document.body.innerText.substring(0, 200),
  };
});
p('flutter-view presente', loadInfo.fltView);
p('glass-pane con shadow root', loadInfo.shadowRoot);
p('canvas en shadow DOM', loadInfo.canvas);
p('canvas 1280x800', loadInfo.canvasW === 1280 && loadInfo.canvasH === 800);
p('0 page errors', pageErrors === 0);
p('0 console errors', consoleErrors === 0);

// 2. Login via API
console.log('\n2. Login');
const r = await api('/auth/login', {
  method: 'POST',
  body: { email: 'composer_test@email.com', password: 'Sinclave1!' },
});
const token = r.body?.token;
p('API login 200', r.status === 200);
p('token recibido', !!token);
console.log('  user:', r.body?.user_name, '| member:', r.body?.subscription_data?.is_ensemble_member);

// 3. API: scores
console.log('\n3. Scores');
const auth = { Authorization: 'Bearer ' + token };
let res = await api('/music-score/list', { headers: auth });
const scores = res.body?.data || [];
p('list scores (' + scores.length + ')', scores.length >= 20);
const faristol = scores.filter(s => s.owner_id === 109);
p('faristol scores (' + faristol.length + ')', faristol.length >= 3);

// 4. Browse por estilo
console.log('\n4. Browse por estilo');
res = await api('/music-score/allmusic?type=musicStyle', { headers: auth });
const styles = res.body?.data || [];
p('estilos (' + styles.length + ')', styles.length > 0);
const withScores = styles.filter(s => (s.music_scores || []).length > 0);
p('estilos con scores', withScores.length > 0);

// 5. Browse por instrumento
console.log('\n5. Browse por instrumento');
res = await api('/music-score/allmusic?type=instrument', { headers: auth });
const instruments = res.body?.data || [];
p('instrumentos (' + instruments.length + ')', instruments.length > 0);

// 6. Detalle de score faristol
console.log('\n6. Detalle faristol');
const fid = faristol[0].id;
res = await api('/music-score/get/' + fid, { headers: auth });
const detail = res.body?.data?.[0];
p('detalle 200', res.status === 200);
p('owner_id = 109', detail?.owner_id === 109);
p('tiene nombre', !!detail?.name);
p('tiene compositores', (detail?.composers || []).length > 0);
p('tiene estilos', (detail?.style_musics || []).length > 0);
p('tiene instrumentos', (detail?.instruments || []).length > 0);
p('tiene files (PDF)', (detail?.files || []).length > 0);
if (detail) console.log('  ' + detail.name);

// 7. PDF content
console.log('\n7. PDF content');
res = await api('/music-score/getPdfContent', {
  method: 'POST',
  headers: { Authorization: 'Bearer ' + token, 'Content-Type': 'application/json' },
  body: { scoreId: faristol[0].id, id: faristol[0].files?.[0]?.id || 1, password: '' },
});
p('PDF 200', res.status === 200);
p('PDF status true', res.body?.status === true);
p('PDF data presente', !!res.body?.data && res.body.data.length > 100);
console.log('  PDF base64 length:', res.body?.data?.length || 0);

// 8. Ensemble
console.log('\n8. Ensemble');
res = await api('/user/ensemble-status', { headers: auth });
p('ensemble status', res.body?.data?.is_ensemble_member === true);

res = await api('/ensembles', { headers: auth });
const ensembles = res.body?.data || [];
p('ensembles list (' + ensembles.length + ')', ensembles.length > 0);
if (ensembles.length > 0) {
  const eid = ensembles[0].id;
  console.log('  ensemble:', ensembles[0].name);
  res = await api('/ensembles/' + eid + '/members', { headers: auth });
  p('members 200', res.status === 200);
  res = await api('/ensembles/' + eid + '/rehearsals', { headers: auth });
  p('rehearsals 200', res.status === 200);
  res = await api('/ensembles/' + eid + '/folders', { headers: auth });
  p('folders 200', res.status === 200);
}

// 9. Screenshot
await page.screenshot({ path: '/tmp/visorweb2-e2e.png' });
p('screenshot tomado', true);

console.log('\n================================');
console.log('Total: ' + ok + ' ✅, ' + fail + ' ❌');

await browser.close();
process.exit(fail > 0 ? 1 : 0);
