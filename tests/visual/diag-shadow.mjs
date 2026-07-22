import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });

await page.goto('https://web2.faristol.net/control-app/', { waitUntil: 'networkidle', timeout: 90000 });
await page.waitForTimeout(10000);

const deepDOM = await page.evaluate(() => {
  const gp = document.querySelector('flt-glass-pane');
  let inside = null;
  if (gp && gp.shadowRoot) {
    inside = Array.from(gp.shadowRoot.querySelectorAll('*')).map(el => el.tagName.toLowerCase()).slice(0, 20);
  }

  const pvs = Array.from(document.querySelectorAll('flt-platform-view'));
  const pvInfo = pvs.map(pv => ({
    tag: pv.tagName,
    slot: pv.getAttribute('slot'),
    inner: pv.innerHTML?.substring(0, 100),
  }));

  const allEls = Array.from(document.querySelectorAll('*')).map(el =>
    el.tagName.toLowerCase() + (el.shadowRoot ? '[shadow]' : '')
  ).slice(0, 30);

  return { gpShadow: inside || 'no shadow', platformViews: pvInfo, allEls };
});

console.log('GP Shadow:', JSON.stringify(deepDOM.gpShadow, null, 2));
console.log('PlatformViews:', JSON.stringify(deepDOM.platformViews, null, 2));
console.log('All elements:', JSON.stringify(deepDOM.allEls, null, 2));

await browser.close();
