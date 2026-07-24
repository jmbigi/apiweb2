// visorweb2-features-e2e.mjs - E2E nuevas features (favoritos, offline, ensemble)
import { chromium } from 'playwright';
import https from 'https';

let ok = 0, fail = 0;
const p = (name, cond) => { ok += cond ? 1 : 0; fail += cond ? 0 : 1; console.log((cond ? '  ✅' : '  ❌'), name); };
const sum = (arr, fn) => arr.reduce((a, e) => a + (fn(e) ? 1 : 0), 0);

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

console.log('visorweb2 nuevas features E2E');
console.log('=============================\n');

// 1. Login
console.log('1. Login');
const r = await api('/auth/login', {
  method: 'POST',
  body: { email: 'composer_test@email.com', password: 'Sinclave1!' },
});
const token = r.body?.token;
p('API login 200', r.status === 200);
p('token recibido', !!token);
const auth = { Authorization: 'Bearer ' + token };

// 2. Favorites API
console.log('\n2. Favorites API');
let favRes = await api('/music-score/user-fav-music-score', { headers: auth });
p('lista favoritos 200', favRes.status === 200);
const favBefore = (favRes.body?.data || []).length;

const scoresRes = await api('/music-score/list', { headers: auth });
const scores = scoresRes.body?.data || [];
const testScoreId = scores[0]?.id;
if (testScoreId) {
  let addRes = await api('/music-score/fav-music-score?music_score_id=' + testScoreId, { headers: auth });
  p('add favorite 200', addRes.status === 200);

  favRes = await api('/music-score/user-fav-music-score', { headers: auth });
  p('favorito aparece en lista', (favRes.body?.data || []).length > favBefore);

  let delRes = await api('/music-score/remove-fav-music-score?music_score_id=' + testScoreId, { headers: auth });
  p('remove favorite 200', delRes.status === 200);
}

// 3. Ensemble API
console.log('\n3. Ensemble API');
const ensRes = await api('/my-ensembles', { headers: auth });
const ensembles = ensRes.body?.data || [];
p('my-ensembles ok', ensRes.status === 200);
if (ensembles.length > 0) {
  const eid = ensembles[0].id;
  let scoresResE = await api('/ensembles/' + eid + '/scores', { headers: auth });
  p('ensemble scores 200', scoresResE.status === 200);
  p('ensemble scores data ok', Array.isArray(scoresResE.body?.data));

  let membersRes = await api('/ensembles/' + eid + '/members', { headers: auth });
  p('ensemble members 200', membersRes.status === 200);
} else {
  p('my-ensembles has data', false);
}

// 4. Carga app canvas
console.log('\n4. Carga app y canvas');
await page.goto('https://web2.faristol.net/visorweb2/', { waitUntil: 'networkidle', timeout: 90000 });
await page.waitForTimeout(10000);

const loadInfo = await page.evaluate(() => {
  const gp = document.querySelector('flt-glass-pane');
  const shadow = gp?.shadowRoot;
  const canvas = shadow?.querySelector('canvas');
  return { glassPane: !!gp, canvas: !!canvas, errors: document.body.innerText.substring(0, 200) };
});
p('glass-pane con shadow root', loadInfo.glassPane);
p('canvas en shadow DOM', loadInfo.canvas);
p('0 page errors', pageErrors === 0);
p('0 console errors', consoleErrors === 0);

console.log('\n=============================');
console.log(`Resultado: ${ok} ok, ${fail} fail${fail > 0 ? ' ❌' : ' ✅'}`);
await browser.close();
process.exit(fail > 0 ? 1 : 0);
