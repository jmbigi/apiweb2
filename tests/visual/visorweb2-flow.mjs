// visorweb2-flow.mjs - Prueba de flujo completo de visorweb2
// Simula: login → listar scores → browse por estilo/instrumento → detalle score faristol
// Nota: No puede probar la UI Flutter (canvas en headless), pero prueba toda la capa API
// que la app consume, incluyendo los datos que renderiza.

import https from 'https';

const BASE = 'https://web2.faristol.net/api';

function api(path, opts = {}) {
  return new Promise((resolve, reject) => {
    const u = new URL(BASE + path);
    const options = {
      hostname: u.hostname, port: 443, path: u.pathname + u.search,
      method: opts.method || 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...opts.headers,
      },
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

let ok = 0;
let fail = 0;
const p = (name, cond) => { if (cond) { ok += 1; } else { fail += 1; } console.log((cond ? '  ✅' : '  ❌'), name); };

console.log('visorweb2 flow test (API layer)');
console.log('================================\n');

try {
  // 1. Login
  console.log('1. Login');
  let r = await api('/auth/login', {
    method: 'POST',
    body: { email: 'composer_test@email.com', password: 'Sinclave1!' },
  });
  p('login 200', r.status === 200);
  p('status true', r.body?.status === true);
  const token = r.body?.token;
  p('token received', !!token);
  console.log('   user:', r.body?.user_name,
    '| member:', r.body?.subscription_data?.is_ensemble_member,
    '| composer:', r.body?.composer || '(none)');
  if (!token) throw new Error('Login failed');
  const auth = { Authorization: 'Bearer ' + token };

  // 2. List all scores (what the app shows in the score list)
  console.log('\n2. List scores');
  r = await api('/music-score/list', { headers: auth });
  p('list 200', r.status === 200);
  const scores = r.body?.data || [];
  p('total scores >= 20', scores.length >= 20);
  const faristolScores = scores.filter(s => s.owner_id === 109);
  p('faristol scores exist', faristolScores.length >= 3);

  // 3. Browse by music style (como el usuario haría click en "Browse by genre")
  console.log('\n3. Browse by style');
  r = await api('/music-score/allmusic?type=musicStyle', { headers: auth });
  p('styles 200', r.status === 200);
  const styles = r.body?.data || [];
  p('styles count > 0', styles.length > 0);
  const stylesWithScores = styles.filter(s => (s.music_scores || []).length > 0);
  p('styles have scores', stylesWithScores.length > 0);
  console.log('   first style:', stylesWithScores[0]?.music_style_name,
    '->', stylesWithScores[0]?.music_scores?.length, 'scores');

  // 4. Browse by instrument
  console.log('\n4. Browse by instrument');
  r = await api('/music-score/allmusic?type=instrument', { headers: auth });
  p('instruments 200', r.status === 200);
  const instruments = r.body?.data || [];
  p('instruments count > 0', instruments.length > 0);

  // 5. Score detail (cuando el usuario hace clic en una partitura)
  console.log('\n5. Score detail (Faristol)');
  const fid = faristolScores[0].id;
  r = await api(`/music-score/get/${fid}`, { headers: auth });
  p('detail 200', r.status === 200);
  const detail = r.body?.data?.[0];
  p('has name', !!detail?.name);
  p('owner_id = 109 (faristol)', detail?.owner_id === 109);
  p('has files (PDF)', (detail?.files || []).length > 0);
  p('has composers', (detail?.composers || []).length > 0);
  p('has styles', (detail?.style_musics || []).length > 0);
  p('has instruments', (detail?.instruments || []).length > 0);
  if (detail) {
    console.log('   name:', detail.name);
    console.log('   composers:', detail.composers?.map(c => c.public_name).join(', '));
    console.log('   styles:', detail.style_musics?.map(s => s.name).join(', '));
    console.log('   instruments:', detail.instruments?.map(i => i.name).join(', '));
    console.log('   files:', detail.files?.length, 'PDF entries');
  }

  // 6. Ensemble status
  console.log('\n6. Ensemble');
  r = await api('/user/ensemble-status', { headers: auth });
  p('ensemble-status 200', r.status === 200);
  p('is_ensemble_member = true', r.body?.data?.is_ensemble_member === true);

  // 7. Get ensemble by listing them
  console.log('\n7. Ensemble list');
  r = await api('/ensembles', { headers: auth });
  p('ensembles list 200', r.status === 200);
  const ensembles = r.body?.data || [];
  p('has ensembles', ensembles.length > 0);
  const ensemble = ensembles[0];
  if (ensemble) {
    console.log('   name:', ensemble.name, '| id:', ensemble.id, '| owner:', ensemble.owner_id);

    // 8. Ensemble members
    console.log('\n8. Ensemble members');
    r = await api(`/ensembles/${ensemble.id}/members`, { headers: auth });
    p('members 200', r.status === 200);
    const members = r.body?.data?.data || r.body?.data || [];
    p('has members', members.length > 0);
    console.log('   count:', members.length);

    // 9. Ensemble rehearsals
    console.log('\n9. Ensemble rehearsals');
    r = await api(`/ensembles/${ensemble.id}/rehearsals`, { headers: auth });
    p('rehearsals 200', r.status === 200);
    const rehearsals = r.body?.data?.data || r.body?.data || [];
    p('has rehearsals', rehearsals.length > 0);
    if (rehearsals.length > 0) console.log('   next:', rehearsals[0].title, rehearsals[0].date);

    // 10. Ensemble folders
    console.log('\n10. Ensemble folders');
    r = await api(`/ensembles/${ensemble.id}/folders`, { headers: auth });
    p('folders 200', r.status === 200);
    const folders = r.body?.data || [];
    p('has folders', folders.length > 0);
    console.log('   names:', folders.map(f => f.name).join(', '));
  }

  console.log('\n================================');
  console.log(`Resultados: ${ok} ✅, ${fail} ❌`);
  process.exit(fail > 0 ? 1 : 0);

} catch (e) {
  console.log('\nERROR:', e.message);
  process.exit(1);
}
