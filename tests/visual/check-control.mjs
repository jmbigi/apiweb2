import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

page.on('pageerror', err => console.log('PAGE:', err.message));
page.on('console', msg => {
  const t = msg.text();
  if (t.includes('error') || t.includes('Error') || t.includes('FAIL') || t.includes('canva') || t.includes('skwasm') || t.includes('SKIA') || t.includes('ck'))
    console.log('CONSOLE:', t.substring(0, 200));
});

console.log('Loading control-app...');
await page.goto('https://web2.faristol.net/control-app/', { waitUntil: 'networkidle', timeout: 90000 });
await page.waitForTimeout(10000);

const info = await page.evaluate(() => {
  const all = document.querySelectorAll('*');
  const tags = {};
  all.forEach(el => { const t = el.tagName.toLowerCase(); tags[t] = (tags[t] || 0) + 1; });
  return {
    tags: tags,
    fltCanvas: !!document.querySelector('flt-canvas'),
    canvas: !!document.querySelector('canvas'),
    glassPane: !!document.querySelector('flt-glass-pane'),
    semanticsChildren: document.querySelector('flt-semantics-host')?.childElementCount || 0,
    bodyText: document.body.innerText.substring(0, 500),
    hasMainDotJs: !!document.querySelector('script[src*="main.dart.js"]'),
    hasMainDotMjs: !!document.querySelector('script[src*="main.dart.mjs"]'),
  };
});
console.log('TAGS:', JSON.stringify(info.tags, null, 2));
console.log('CANVAS:', 'flt-canvas=' + info.fltCanvas, 'canvas=', info.canvas);
console.log('GLASS:', info.glassPane);
console.log('SEMANTICS children:', info.semanticsChildren);
console.log('main.dart.js:', info.hasMainDotJs);
console.log('main.dart.mjs:', info.hasMainDotMjs);
console.log('TEXT:', info.bodyText.substring(0, 300));

await browser.close();
