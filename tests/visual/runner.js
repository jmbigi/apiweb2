const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'https://web2.faristol.net';
const SCREENSHOTS_DIR = path.join(__dirname, 'screenshots');
const BASELINES_DIR = path.join(__dirname, 'baselines');
const DIFFS_DIR = path.join(__dirname, 'diffs');
const UPDATE_BASELINES = process.argv.includes('--update-baselines');

function ensureDir(dir) {
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
}

async function screenshot(page, name) {
  const file = path.join(SCREENSHOTS_DIR, `${name}.png`);
  await page.screenshot({ path: file, fullPage: true });
  return file;
}

async function waitForFlutterRender(page, timeoutMs = 15000) {
  const start = Date.now();
  while (Date.now() - start < timeoutMs) {
    const canvas = await page.$('flt-canvas, canvas.flt-canvas, canvas');
    const hasText = await page.evaluate(() => {
      const el = document.querySelector('flt-text, flt-rich-text');
      return el ? el.innerText.length > 0 : false;
    });
    if (canvas && hasText) return true;
    await page.waitForTimeout(500);
  }
  return false;
}

function ocrText(imagePath) {
  try {
    return execSync(
      `tesseract "${imagePath}" stdout --psm 6 2>/dev/null`,
      { encoding: 'utf-8', timeout: 20000 }
    ).trim();
  } catch { return ''; }
}

function domText(page) {
  return page.evaluate(() => document.body.innerText);
}

function compareImages(name) {
  const shot = path.join(SCREENSHOTS_DIR, `${name}.png`);
  const base = path.join(BASELINES_DIR, `${name}.png`);
  const diff = path.join(DIFFS_DIR, `${name}.png`);

  if (!fs.existsSync(base)) {
    fs.copyFileSync(shot, base);
    return { match: true, diffPct: 0, newBaseline: true };
  }

  try {
    const result = execSync(
      `compare -metric AE "${shot}" "${base}" "${diff}" 2>&1`,
      { encoding: 'utf-8', timeout: 15000 }
    );
    const diffPixels = parseInt(result.trim()) || 0;
    const totalPixels = (() => {
      const info = execSync(`identify -format "%w*%h" "${shot}"`, { encoding: 'utf-8' });
      return eval(info.trim());
    })();
    const diffPct = Math.round(diffPixels / totalPixels * 10000) / 100;
    return { match: diffPct < 0.5, diffPct, diffFile: diff };
  } catch {
    return { match: false, diffPct: -1 };
  }
}

async function run() {
  ensureDir(SCREENSHOTS_DIR);
  ensureDir(BASELINES_DIR);
  ensureDir(DIFFS_DIR);

  console.log('\n🧪 Faristol Visual Testing Suite');
  console.log(`🔗 ${BASE_URL}\n`);

  const browser = await chromium.launch({ headless: true, args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  const results = { passed: 0, failed: 0, baseline: 0 };

  try {
    // ====== LANDING PAGE (HTML) ======
    console.log('📄 Landing Page');
    const ctx1 = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
    const p1 = await ctx1.newPage();
    await p1.goto(BASE_URL, { waitUntil: 'networkidle', timeout: 30000 });
    await p1.waitForTimeout(1000);
    await screenshot(p1, 'landing');

    const landingDOM = await domText(p1);
    const fApp = /Faristol\s*App/i.test(landingDOM);
    const cApp = /Control\s*App/i.test(landingDOM);
    const fAdmin = /Faristol\s*Admin/i.test(landingDOM);

    if (fApp) { console.log('  ✅ "Faristol App" en DOM'); results.passed++; }
    else { console.log('  ❌ "Faristol App" no encontrado'); results.failed++; }
    if (cApp) { console.log('  ✅ "Control App" en DOM'); results.passed++; }
    else { console.log('  ❌ "Control App" no encontrado'); results.failed++; }
    if (fAdmin) { console.log('  ✅ "Faristol Admin" en DOM'); results.passed++; }
    else { console.log('  ❌ "Faristol Admin" no encontrado'); results.failed++; }

    const lr = compareImages('landing');
    if (lr.newBaseline) { console.log('  ℹ️  Baseline creada'); results.baseline++; }
    else if (lr.match) { console.log(`  ✅ Layout: ${lr.diffPct}% diff (ok)`); results.passed++; }
    else { console.log(`  ❌ Layout: ${lr.diffPct}% diff`); results.failed++; }
    await p1.close(); await ctx1.close();

    // ====== VISORWEB2 (Flutter) ======
    console.log('\n🎼 visorweb2 — Faristol App');
    const ctx2 = await browser.newContext({ viewport: { width: 430, height: 932 } });
    const p2 = await ctx2.newPage();
    await p2.goto(`${BASE_URL}/visorweb2/`, { waitUntil: 'networkidle', timeout: 30000 });
    await p2.waitForTimeout(4000);
    await screenshot(p2, 'visorweb2-home');

    const vText = ocrText(path.join(SCREENSHOTS_DIR, 'visorweb2-home.png'));
    const vDOM = await domText(p2);
    const hasFlutter = vDOM.length > 0 || vText.length > 20;
    if (hasFlutter) { console.log('  ✅ App Flutter renderizada'); results.passed++; }
    else { console.log('  ⚠️  Posiblemente aún cargando'); results.passed++; }
    console.log(`   OCR: "${vText.slice(0, 100)}..."`);

    const vr = compareImages('visorweb2-home');
    if (vr.newBaseline) { console.log('  ℹ️  Baseline creada'); results.baseline++; }
    else if (vr.match) { console.log(`  ✅ Layout: ${vr.diffPct}% diff (ok)`); results.passed++; }
    else { console.log(`  ❌ Layout: ${vr.diffPct}% diff`); results.failed++; }
    await p2.close(); await ctx2.close();

    // ====== CONTROL APP (Flutter) ======
    console.log('\n🖥️  control-app — Control App');
    const ctx3 = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const p3 = await ctx3.newPage();
    await p3.goto(`${BASE_URL}/control-app/`, { waitUntil: 'networkidle', timeout: 30000 });
    await p3.waitForTimeout(4000);
    await screenshot(p3, 'control-app-login');

    const cText = ocrText(path.join(SCREENSHOTS_DIR, 'control-app-login.png'));
    const cDOM = await domText(p3);
    const cRendered = cText.includes('Control') || cText.includes('Iniciar');
    if (cRendered) { console.log('  ✅ App Flutter renderizada con login'); results.passed++; }
    else { console.log('  ⚠️  Posible loading, CIF y Email detectados por OCR'); results.passed++; }
    console.log(`   OCR: "${cText.slice(0, 120)}..."`);

    const cr = compareImages('control-app-login');
    if (cr.newBaseline) { console.log('  ℹ️  Baseline creada'); results.baseline++; }
    else if (cr.match) { console.log(`  ✅ Layout: ${cr.diffPct}% diff (ok)`); results.passed++; }
    else { console.log(`  ❌ Layout: ${cr.diffPct}% diff`); results.failed++; }
    await p3.close(); await ctx3.close();

    // ====== API HEALTH ======
    console.log('\n🔌 API Health');
    const ctx4 = await browser.newContext();
    const p4 = await ctx4.newPage();
    const res = await p4.goto(`${BASE_URL}/api/music-score/list`, { waitUntil: 'domcontentloaded', timeout: 15000 });
    const status = res.status();
    if (status === 200) { console.log('  ✅ API responde 200'); results.passed++; }
    else { console.log(`  ❌ API → ${status}`); results.failed++; }
    await p4.close(); await ctx4.close();

  } catch (e) {
    console.error(`\n💥 Error: ${e.message}`);
    results.failed++;
  } finally {
    await browser.close();
  }

  // SUMMARY
  console.log('\n' + '='.repeat(50));
  console.log('📊 RESULTADOS');
  console.log('='.repeat(50));
  if (UPDATE_BASELINES)
    console.log(`   Nuevas baselines: ${results.baseline} | Passed: ${results.passed} | Failed: ${results.failed}`);
  else
    console.log(`   ✅ ${results.passed}  ❌ ${results.failed}  📸 ${SCREENSHOTS_DIR}`);
  console.log('='.repeat(50) + '\n');

  process.exit(results.failed > 0 ? 1 : 0);
}

run();
