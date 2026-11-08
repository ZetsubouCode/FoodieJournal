import { chromium } from '@playwright/test';
import fs from 'node:fs';
import assert from 'node:assert/strict';

const base = 'http://127.0.0.1:8012';
const out = 'docs/qa/frontend-experience';
fs.mkdirSync(out, { recursive: true });
const browser = await chromium.launch();
const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
const page = await context.newPage();
const evidence = { date: '2026-11-08', baseline: 'b56db2c584cbfb2dcd7a55592d8a3a5d3dd7fcbd', browser: browser.version(), fixture: 'Existing isolated cinematic.sqlite and cinematic-media; fictional labeled demo content. No seed or owner DB access.', pages: [], states: [], pageErrors: [] };
page.on('pageerror', e => evidence.pageErrors.push(e.message));
async function load(url) {
  const response = await page.goto(base + url);
  if (response.status() === 200) await page.waitForSelector('html[data-ui-ready=true]');
  await page.evaluate(() => document.fonts.ready);
  await page.locator('body').evaluate(async () => Promise.all([...document.images].filter(n => n.loading !== 'lazy').map(n => n.decode().catch(() => {}))));
  return response.status();
}
async function shot(name, locator = page) {
  await locator.screenshot({ path: `${out}/${name}.jpg`, type: 'jpeg', quality: 68, animations: 'allow' });
}
await load('/');
const visit = new URL(await page.locator('[data-memory-slot]').first().getAttribute('href')).pathname;
await load(visit);
const place = new URL(await page.locator('.dossier-hero__place a').getAttribute('href')).pathname;
assert(visit.startsWith('/visit/') && place.startsWith('/place/'));
evidence.routes = { home: '/', explore: '/explore', visit, place, error: '/missing-error-page-preview' };
for (const width of [320, 375, 390, 768, 1024, 1440, 1920]) {
  await page.setViewportSize({ width, height: 1000 });
  for (const [name, url] of Object.entries(evidence.routes)) {
    const status = await load(url);
    const measurement = await page.evaluate(() => {
      const rect = selector => { const n = document.querySelector(selector); if (!n) return null; const r = n.getBoundingClientRect(); return { x: r.x, width: r.width }; };
      return { viewport: innerWidth, scrollWidth: document.documentElement.scrollWidth, header: rect('.site-header'), main: rect('main'), footer: rect('.site-footer'), footerInner: rect('.site-footer__inner') };
    });
    evidence.pages.push({ name, url, width, status, ...measurement });
    await shot(`${name}-${width}`);
    if ([375,1440,1920].includes(width)) { await page.locator('.site-footer').scrollIntoViewIfNeeded(); await shot(`${name}-footer-${width}`); }
  }
}
await page.setViewportSize({ width: 1440, height: 1000 }); await load('/');
const nav = page.locator('.site-header nav a').nth(1);
await page.mouse.move(0,0); await shot('nav-start',page.locator('.site-header'));
await nav.hover(); await page.waitForTimeout(110); await shot('nav-mid',page.locator('.site-header'));
await page.waitForTimeout(250); await shot('nav-end',page.locator('.site-header'));
evidence.nav = await nav.evaluate(n => ({ after: getComputedStyle(n,'::after').content, transform: getComputedStyle(n,'::after').transform, transition: getComputedStyle(n,'::after').transitionDuration }));
await nav.focus(); await shot('nav-keyboard-focus',page.locator('.site-header'));
const machine = page.locator('[data-machine]');
async function state(name) { evidence.states.push({name,...await machine.evaluate(n=>({...n.dataset}))}); await shot(name,page.locator('[data-discovery-scene]')); }
await state('machine-off');
await page.locator('[data-vending-lamp]').focus(); await page.keyboard.press('Enter'); await page.keyboard.press('Enter');
await page.waitForFunction(()=>document.querySelector('[data-machine]').dataset.power==='booting'); await state('machine-boot');
await page.waitForFunction(()=>document.querySelector('[data-machine]').dataset.power==='on');
await page.locator('[data-memory-slot]').nth(1).click(); await state('machine-selected');
await page.locator('[data-vending-print]').click();
await page.waitForTimeout(220); await state('machine-printing');
await page.waitForFunction(()=>document.querySelector('[data-machine]').dataset.dispenser==='ready'); await state('machine-ready');
await page.route('**/visit/**',async route=>{await new Promise(r=>setTimeout(r,800));await route.continue();});
await page.locator('[data-paper-path] a').click({noWaitAfter:true}); await page.waitForTimeout(30);
await state('machine-retrieving'); await page.waitForURL('**/visit/**');
await page.unroute('**/visit/**');
await load('/explore');
await page.route('**/explore?**',async route=>{await new Promise(r=>setTimeout(r,800)); await route.continue();});
await page.getByRole('link',{name:'Hidangan',exact:true}).click();
await page.waitForFunction(()=>document.querySelector('[data-discovery-results]').getAttribute('aria-busy')==='true');
await shot('explore-loading',page.locator('[data-discovery]'));
await page.waitForFunction(()=>document.querySelector('[data-discovery]').dataset.mode==='food');
await page.unroute('**/explore?**'); await shot('explore-food',page.locator('[data-discovery]'));
await page.getByRole('button',{name:/^Filter/}).click(); await shot('explore-filter'); await page.keyboard.press('Escape');
await load('/explore?q=zzzz-audit-zero-results'); await shot('explore-empty',page.locator('[data-discovery]'));
await load('/explore');
await page.route('**/explore?**',route=>route.fulfill({status:503,body:'Controlled audit failure'}));
await page.getByRole('link',{name:'Hidangan',exact:true}).click(); await page.locator('[data-discovery-error]:visible').waitFor(); await shot('explore-error',page.locator('[data-discovery]'));
await page.unroute('**/explore?**'); await page.locator('[data-discovery-retry]').click(); await page.waitForFunction(()=>document.querySelector('[data-discovery]').dataset.mode==='food');
evidence.exploreRetry = true;
await page.route('**/media/**',route=>route.abort()); await load(visit); await shot('visit-failed-images'); await load(place); await shot('place-failed-images'); await page.unroute('**/media/**');
await page.setViewportSize({width:375,height:1000}); await page.emulateMedia({reducedMotion:'reduce'}); await load('/'); await shot('home-reduced-375');
await load('/explore'); await page.evaluate(()=>document.documentElement.style.fontSize='200%'); await shot('explore-text-200-375');
evidence.text200 = await page.evaluate(()=>({viewport:innerWidth,scrollWidth:document.documentElement.scrollWidth, method:'root font-size 200%; not browser zoom or OS text enlargement'}));
fs.writeFileSync(`${out}/evidence.json`,JSON.stringify(evidence,null,2)+'\n');
await browser.close();
console.log(JSON.stringify({routes:evidence.routes,checks:evidence.pages.length,overflow:evidence.pages.filter(x=>x.scrollWidth>x.viewport),nav:evidence.nav,states:evidence.states.map(x=>[x.name,x.power,x.dispenser]),pageErrors:evidence.pageErrors,text200:evidence.text200}));
