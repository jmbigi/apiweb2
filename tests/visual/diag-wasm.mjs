import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

page.on('pageerror', err => console.log('PAGE:', err.message));
page.on('console', msg => console.log('CONSOLE(' + msg.type() + '):', msg.text().substring(0, 300)));
page.on('requestfailed', req => console.log('FAILED:', req.url().substring(0, 120), req.failure()?.errorText));
page.on('response', resp => {
  if (resp.status() >= 400) console.log('HTTP' + resp.status() + ':', resp.url().substring(0, 120));
});

console.log('Loading control-app with --wasm...');
await page.goto('https://web2.faristol.net/control-app/', { waitUntil: 'networkidle', timeout: 90000 });
await page.waitForTimeout(15000);

const resources = await page.evaluate(() => {
  const perf = performance.getEntriesByType('resource');
  return perf.map(r => r.name.split('/').pop() + '=' + (r.duration || 0).toFixed(0) + 'ms ' + r.entryType);
});
console.log('Resources loaded:', resources.join(', '));

const state = await page.evaluate(() => ({
  fltView: !!document.querySelector('flutter-view'),
  canvas: !!document.querySelector('canvas'),
  fltCanvas: !!document.querySelector('flt-canvas'),
  glassPane: !!document.querySelector('flt-glass-pane'),
  semantics: document.querySelector('flt-semantics-host')?.childElementCount || 0,
  text: document.body.innerText.substring(0, 300),
  scripts: Array.from(document.querySelectorAll('script')).map(s => s.src || s.textContent?.substring(0, 50)).filter(Boolean),
}));
console.log('STATE:', JSON.stringify(state, null, 2));

await browser.close();
