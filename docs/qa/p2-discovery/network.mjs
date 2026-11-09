import { chromium, expect } from '@playwright/test';
import { build } from 'esbuild';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import assert from 'node:assert/strict';
const old=await build({stdin:{contents:execFileSync('git',['show','HEAD:resources/js/pages/explore.js'],{encoding:'utf8'}),resolveDir:process.cwd()+'/resources/js/pages'},bundle:true,format:'esm',write:false});
const browser=await chromium.launch(), records=[];
for(const width of [1440,390])for(const client of ['baseline','p2']){
 const context=await browser.newContext({viewport:{width,height:1000},reducedMotion:'no-preference'}),page=await context.newPage();
 if(client==='baseline')await page.route('**/build/assets/explore-*.js',r=>r.fulfill({body:old.outputFiles[0].text,contentType:'text/javascript'}));
 const response=await page.goto('http://127.0.0.1:8015/explore');assert.equal(response.headers()['x-foodie-visual-fixture'],'photo-v1');await expect(page.locator('html')).toHaveAttribute('data-ui-ready','true');
 await page.evaluate(async()=>{await document.fonts.ready;await Promise.all([...document.images].filter(n=>n.src&&n.getBoundingClientRect().top<innerHeight).map(n=>n.decode()))});
 await page.waitForTimeout(300);
 records.push({width,client,...await page.evaluate(()=>({images:[...document.querySelectorAll('.curiosity-surface img')].map(n=>({loading:n.loading,currentSrc:n.currentSrc,sizes:n.sizes,width:n.width})),media:performance.getEntriesByType('resource').filter(e=>e.name.includes('/media/')).map(e=>({url:e.name,bytes:e.transferSize})).sort((a,b)=>a.url.localeCompare(b.url))}))});
 await context.close();
}
for(const width of [1440,390]){const [a,b]=records.filter(r=>r.width===width);assert.deepEqual(a.images,b.images);assert.deepEqual(a.media,b.media)}
fs.writeFileSync('docs/qa/p2-discovery/network-investigation.json',JSON.stringify({method:'Controlled same-fixture/current markup/style audit with old versus P2 Explore client. Decode every visible native image before resource accounting; no eager promotion. This is a diagnostic, not a replacement for the real before/after lab captures.',records},null,2)+'\n');await browser.close();console.log('Baseline and P2 clients select identical derivatives and completed media bytes at both widths.');
