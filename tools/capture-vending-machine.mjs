import { chromium } from '@playwright/test';
import fs from 'node:fs';

const output = 'docs/qa/vending-machine';
const base = 'http://127.0.0.1:8012'; // Explicitly isolated, bundled fictional demo journal.
fs.mkdirSync(output, { recursive: true });
const browser = await chromium.launch();
const context = await browser.newContext({ viewport: { width: 1440, height: 1300 }, reducedMotion: 'no-preference' });
const page = await context.newPage();
const evidence = { fixture: 'Dedicated cinematic.sqlite/media. Labeled fictional demo visits and illustrative bundled photography; no owner data.', browser: browser.version(), screenshots: [], frames: [], errors: [], checks: [] };
page.on('pageerror', e => evidence.errors.push(e.message));
page.on('console', m => { if (m.type() === 'error') evidence.errors.push(m.text()); });
const power = state => page.waitForFunction(state => document.querySelector('[data-machine]').dataset.power === state, state);
const dispenser = state => page.waitForFunction(state => document.querySelector('[data-machine]').dataset.dispenser === state, state);
const centre = async selector => { const r = await page.locator(selector).boundingBox(); return { x: r.x+r.width/2, y: r.y+r.height/2 }; };
async function load() {
    await page.goto(base); await page.waitForSelector('html[data-ui-ready=true]'); await page.evaluate(() => document.fonts.ready);
    await page.evaluate(async () => { await Promise.all([...document.querySelectorAll('[data-machine] img')].map(img => img.decode().catch(()=>{}))); scrollTo(0,0); });
}
async function shot(name) {
    const state=await page.locator('[data-machine]').evaluate(n=>({power:n.dataset.power,dispenser:n.dataset.dispenser,screenOpacity:getComputedStyle(n.querySelector('.memory-showcase')).opacity}));
    await page.locator('[data-discovery-scene]').screenshot({ path: `${output}/${name}.png`, animations: 'allow' }); evidence.screenshots.push(name); evidence.frames.push({name,...state});
}
await load(); await shot('after-off-desktop');
let from=await centre('[data-vending-lamp]'), to=await centre('[data-lamp-socket]');
await page.mouse.move(from.x,from.y); await page.mouse.down(); await page.mouse.move(to.x+24,to.y,{steps:20});
if(await page.locator('[data-machine]').getAttribute('data-power')!=='off') throw new Error('Power changed before release');
await shot('magnetic-preview-held'); await page.mouse.up(); await power('booting');
// Observe real boot time; do not pause the coordinator's bounded stage clock.
await page.waitForTimeout(160); await shot('post-ignition');
await page.waitForTimeout(180); await shot('displays-awake');
await power('on'); await shot('after-on-desktop');
await page.locator('[data-memory-slot]').nth(1).click(); await page.locator('[data-vending-print]').click();
for(const [name, fraction] of [['print-feed-early',.25],['print-feed-late',.7]]) {
    await page.locator('[data-paper-path] a').evaluate((n,f)=>n.getAnimations().forEach(a=>{a.pause();a.currentTime=Number(a.effect.getTiming().duration)*f}),fraction); await shot(name);
}
await page.locator('[data-paper-path] a').evaluate(n=>n.getAnimations().forEach(a=>a.finish())); await dispenser('ready'); await shot('after-print-desktop');
const c=await centre('[data-memory-reset]'); await page.mouse.move(c.x,c.y); await page.mouse.down(); await page.waitForTimeout(100); await shot('reset-pressed'); await page.mouse.up();
await page.locator('[data-vending-knob]').focus(); await page.keyboard.press('ArrowRight'); await page.waitForFunction(()=>document.querySelector('[data-machine]').dataset.pageBusy==='false'); await shot('page-two');
await page.locator('[data-memory-reset]').click(); await page.waitForFunction(()=>document.querySelector('[data-machine]').dataset.page==='1');
for(const width of [1920,1440,1024,768,375,320]) {
    await page.setViewportSize({width,height:width<960?1200:1400}); await load();
    const data = await page.locator('[data-machine]').evaluate(n=>{
        const a=n.querySelector('[data-vending-lamp]').getBoundingClientRect(), b=n.querySelector('[data-lamp-socket]').getBoundingClientRect();
        return {overflow:document.documentElement.scrollWidth>innerWidth,lampAlignment:{dx:Math.abs(a.x+a.width/2-b.x-b.width/2),dy:Math.abs(a.y+a.height/2-b.y-b.height/2)}};
    }); evidence.checks.push({width,state:'on',...data});
    if(data.overflow || data.lampAlignment.dx>2 || data.lampAlignment.dy>2) throw new Error('Invalid layout at '+width);
    await shot('after-on-'+width); await page.locator('[data-vending-print]').click(); await dispenser('ready'); await shot('after-print-'+width);
    await page.locator('[data-memory-reset]').click();
}
evidence.assetBytes=fs.readdirSync('public/assets/vending').filter(x=>x.endsWith('.svg')).reduce((n,file)=>n+fs.statSync('public/assets/vending/'+file).size,0);
evidence.bundleNote='No added npm/composer dependencies. 2D SVG hardware and existing cabinet raster.';
await context.close();
// A short uninterrupted real-time motion recording, without screenshot pauses.
const videoContext=await browser.newContext({viewport:{width:1280,height:960},reducedMotion:'no-preference',recordVideo:{dir:'tools/.runtime/vending-video',size:{width:1280,height:960}}});
const videoPage=await videoContext.newPage();
await videoPage.addInitScript(()=>{
    window.vendingPowerTimeline=[];
    new MutationObserver(records=>{for(const r of records){if(r.type!=='attributes'||!r.target.matches('[data-machine]'))continue;const power=r.target.dataset.power;if(window.vendingPowerTimeline.at(-1)?.power!==power)window.vendingPowerTimeline.push({power,ms:performance.now()})}}).observe(document,{subtree:true,attributes:true,attributeFilter:['data-power']});
});
await videoPage.goto(base); await videoPage.waitForSelector('html[data-ui-ready=true]'); await videoPage.locator('[data-machine]').scrollIntoViewIfNeeded();
const vc=async selector=>{const r=await videoPage.locator(selector).boundingBox();return {x:r.x+r.width/2,y:r.y+r.height/2}};
async function videoDrag(target){const a=await vc('[data-vending-lamp]'),b=await vc(target);await videoPage.mouse.move(a.x,a.y);await videoPage.mouse.down();for(let i=1;i<=16;i++){await videoPage.mouse.move(a.x+(b.x-a.x)*i/16,a.y+(b.y-a.y)*i/16);await videoPage.waitForTimeout(16)}await videoPage.mouse.up();}
await videoDrag('[data-lamp-socket]'); await videoPage.waitForFunction(()=>document.querySelector('[data-machine]').dataset.power==='on');
await videoPage.locator('[data-vending-knob]').focus(); await videoPage.keyboard.press('ArrowRight'); await videoPage.waitForFunction(()=>document.querySelector('[data-machine]').dataset.pageBusy==='false');
await videoPage.locator('[data-vending-print]').click(); await videoPage.waitForFunction(()=>document.querySelector('[data-machine]').dataset.dispenser==='ready'); await videoPage.waitForTimeout(300);
await videoDrag('[data-lamp-cradle]'); await videoPage.waitForFunction(()=>document.querySelector('[data-machine]').dataset.power==='off'); await videoPage.waitForTimeout(250);
evidence.videoPowerTimeline=await videoPage.evaluate(()=>window.vendingPowerTimeline);
await videoContext.close(); const video=await videoPage.video().path(); fs.copyFileSync(video,output+'/interaction-review.webm');
await browser.close(); fs.writeFileSync(output+'/evidence.json',JSON.stringify(evidence,null,2));
console.log(JSON.stringify({screenshots:evidence.screenshots.length,errors:evidence.errors,checks:evidence.checks,frames:evidence.frames.slice(0,8),assetBytes:evidence.assetBytes},null,2));
