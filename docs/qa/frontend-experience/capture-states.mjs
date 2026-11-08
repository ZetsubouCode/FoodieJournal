import { chromium } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';
import assert from 'node:assert/strict';

// Run from the repository root, with only the isolated cinematic server on 8012.
const output = 'docs/qa/frontend-experience';
const baseline = JSON.parse(fs.readFileSync(`${output}/evidence.json`));
const browser = await chromium.launch();
const context = await browser.newContext();
const page = await context.newPage();
const result = { fixture: baseline.fixture, text200: [], reduced: [], axe: [], missingPhotoVisit: '/visit/demo-teh-tanpa-kamera' };
const shot = name => page.screenshot({ path: `${output}/${name}.jpg`, type:'jpeg', quality:68 });
async function load(url) {
  await page.goto('http://127.0.0.1:8012' + url);
  if (url !== baseline.routes.error) await page.waitForSelector('html[data-ui-ready=true]');
  await page.evaluate(() => document.fonts.ready);
}
for (const width of [375,1440]) {
  await page.setViewportSize({width,height:1000});
  for (const [name,url] of Object.entries(baseline.routes)) {
    await page.emulateMedia({reducedMotion:'reduce'}); await load(url);
    result.reduced.push({name,width,overflow:await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth)});
    await shot(`${name}-reduced-${width}`);
    await page.evaluate(()=>document.documentElement.style.fontSize='200%');
    result.text200.push({name,width,scrollWidth:await page.evaluate(()=>document.documentElement.scrollWidth)});
    await shot(`${name}-text200-${width}`);
  }
}
await page.setViewportSize({width:1440,height:1000});
for (const name of ['explore','visit','place']) {
  await load(baseline.routes[name]);
  const scan = await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21aa','wcag22aa']).analyze();
  result.axe.push({name,violations:scan.violations.map(v=>({id:v.id,impact:v.impact,targets:v.nodes.map(n=>n.target)}))});
}
await load(result.missingPhotoVisit);
assert.equal(await page.locator('.dossier-hero--text').count(),1);
result.missingPhoto = { title:await page.locator('h1').textContent(),gallery:await page.locator('.dossier-gallery').count(),rating:await page.locator('.dossier-facts').innerText() };
await shot('visit-no-photo');
const place = new URL(await page.locator('.dossier-hero__place a').getAttribute('href')).pathname;
await load(place); await shot('place-of-no-photo-visit');
result.missingPhotoPlace = place;
fs.writeFileSync(`${output}/states.json`,JSON.stringify(result,null,2)+'\n');
await browser.close();
console.log(JSON.stringify(result));
