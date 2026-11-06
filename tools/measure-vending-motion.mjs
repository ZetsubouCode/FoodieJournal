import { chromium } from '@playwright/test';
import fs from 'node:fs';

// No video or screenshots during timing. Isolated, already-warmed demo photography.
const browser=await chromium.launch();
const page=await browser.newPage({viewport:{width:1440,height:1100},reducedMotion:'no-preference'});
await page.goto('http://127.0.0.1:8012'); await page.waitForSelector('html[data-ui-ready=true]');
await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-socket]').click();
await page.waitForFunction(()=>document.querySelector('[data-machine]').dataset.power==='on');
await page.evaluate(async()=>{await document.fonts.ready;await Promise.all([...document.images].map(n=>n.decode().catch(()=>{})))});
const result={environment:'Chromium, unthrottled localhost 8012, isolated fictional demo media, 1440×1100. Single warm sample per variant; no recording, no field INP claim.',browser:browser.version(),samples:[]};
for(const variant of ['retired-cabinet-filter','current-static-shell-filter']) {
    await page.locator('[data-machine]').evaluate((n,old)=>{
        n.style.filter=old?'drop-shadow(0 20px 20px rgb(0 0 0 / .65))':'none';
        n.querySelector('.memory-machine__shell').style.filter=old?'none':'drop-shadow(0 20px 20px rgb(0 0 0 / .65))';
    },variant==='retired-cabinet-filter');
    await page.evaluate(()=>{
        window.frameDone=false;window.frameIntervals=[];let previous=0,start=performance.now();
        const frame=now=>{if(previous)window.frameIntervals.push(now-previous);previous=now;if(now-start<1800)requestAnimationFrame(frame);else window.frameDone=true}; requestAnimationFrame(frame);
    });
    await page.locator('[data-vending-print]').click(); await page.waitForFunction(()=>window.frameDone);
    result.samples.push({variant,...await page.evaluate(()=>{const a=window.frameIntervals.toSorted((x,y)=>x-y);return {count:a.length,medianMs:a[Math.floor(a.length*.5)],p95Ms:a[Math.floor(a.length*.95)],maximumMs:a.at(-1)}})});
    await page.locator('[data-memory-reset]').click();
}
await browser.close();fs.writeFileSync('docs/qa/vending-machine/motion-measurements.json',JSON.stringify(result,null,2));console.log(JSON.stringify(result,null,2));
