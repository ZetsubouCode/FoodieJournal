import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const machine = '[data-machine]';
async function ready(page) {
    await page.goto('/'); await expect(page.locator('html')).toHaveAttribute('data-ui-ready', 'true');
}
async function on(page) {
    await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-socket]').click();
    await expect(page.locator(machine)).toHaveAttribute('data-power', 'on');
}
const center = async locator => { const r = await locator.boundingBox(); return {x:r.x+r.width/2,y:r.y+r.height/2}; };
async function beginDrag(page, destination, offset = {x:0,y:0}) {
    const from = await center(page.locator('[data-vending-lamp]'));
    const target = await center(page.locator(destination));
    await page.mouse.move(from.x, from.y); await page.mouse.down();
    await page.mouse.move(target.x+offset.x, target.y+offset.y, {steps:12});
}

test('fresh machine is dark and inert; holding at expanded socket edge waits for release', async ({ page }) => {
    await ready(page); await page.locator(machine).scrollIntoViewIfNeeded();
    await expect(page.locator(machine)).toHaveAttribute('data-power', 'off');
    await expect(page.locator('[data-vending-print]')).toBeDisabled();
    await expect(page.locator('[data-memory-reset]')).toBeDisabled();
    expect(await page.locator('[data-memory-slots]').evaluate(n=>n.inert)).toBe(true);
    expect(await page.locator('[data-vending-screen] .memory-showcase').evaluate(n=>getComputedStyle(n).opacity)).toBe('0');
    await beginDrag(page, '[data-lamp-socket]', {x:31,y:0});
    await expect(page.locator(machine)).toHaveAttribute('data-drop-target','socket');
    await page.waitForTimeout(350);
    await expect(page.locator(machine)).toHaveAttribute('data-power','off');
    await page.mouse.up(); await expect(page.locator(machine)).toHaveAttribute('data-lamp','animating');
    await expect(page.locator(machine)).toHaveAttribute('data-power','on');
});

test('invalid drops, Escape, pointercancel and lost capture return to the committed origin', async ({ page }) => {
    await ready(page); await page.locator(machine).scrollIntoViewIfNeeded();
    await beginDrag(page,'[data-vending-screen]'); await page.mouse.up();
    await expect(page.locator(machine)).toHaveAttribute('data-lamp','cradle');
    await expect(page.locator(machine)).toHaveAttribute('data-power','off');
    await on(page);
    for(const mode of ['release','escape','cancel','capture']) {
        await beginDrag(page,'[data-vending-screen]');
        if(mode==='release') await page.mouse.up();
        else if(mode==='escape') { await page.keyboard.press('Escape'); await page.mouse.up(); }
        else { await page.locator('[data-vending-lamp]').dispatchEvent(mode==='cancel'?'pointercancel':'lostpointercapture',{pointerId:1}); await page.mouse.up(); }
        await expect(page.locator(machine)).toHaveAttribute('data-lamp','socket');
        await expect(page.locator(machine)).toHaveAttribute('data-power','on');
    }
});

test('valid reverse drop shuts down and session refresh restores the correct physical state', async ({ page }) => {
    await ready(page); await on(page);
    // Delay the interaction chunk to inspect the restored server/CSS first paint.
    await page.route('**/build/assets/archive-*.js',async route=>{await new Promise(resolve=>setTimeout(resolve,1000));await route.continue();});
    await page.reload({waitUntil:'domcontentloaded'});
    const firstPaint=await page.locator(machine).evaluate(n=>({screen:getComputedStyle(n.querySelector('.memory-showcase')).opacity,light:getComputedStyle(n.querySelector('.vending-lamp__light')).opacity}));
    expect(firstPaint.screen).toBe('1'); expect(firstPaint.light).toBe('1');
    await expect(page.locator(machine)).toHaveAttribute('data-power','on');
    await page.unroute('**/build/assets/archive-*.js');
    await expect(page.locator(machine)).toHaveAttribute('data-lamp','socket');
    expect(await page.locator(machine).evaluate(n=>n.getAnimations({subtree:true}).some(a=>a.animationName==='vending-ignition'))).toBe(false);
    await page.locator(machine).scrollIntoViewIfNeeded();
    await beginDrag(page,'[data-lamp-cradle]');
    await expect(page.locator(machine)).toHaveAttribute('data-power','on');
    await page.mouse.up(); await expect(page.locator(machine)).toHaveAttribute('data-power','off');
    await page.reload(); await expect(page.locator(machine)).toHaveAttribute('data-power','off');
    await expect(page.locator(machine)).toHaveAttribute('data-lamp','cradle');
});

test('rotary angular drag crosses wrap boundary and keyboard reverses a real six-visit page', async ({ page }) => {
    await ready(page); await on(page); await page.locator(machine).scrollIntoViewIfNeeded();
    const first = await page.locator('[data-memory-slot]').first().getAttribute('href');
    const knob=page.locator('[data-vending-knob]');
    if(await knob.isDisabled()) return; // Single-page journal is verified by the feature/helper tests.
    const c=await center(knob), r=22;
    const at=async deg=>page.mouse.move(c.x+Math.cos(deg*Math.PI/180)*r,c.y+Math.sin(deg*Math.PI/180)*r);
    await at(155); await page.mouse.down();
    for(const angle of [168,179,190,202]) await at(angle);
    await page.mouse.up(); await expect(page.locator(machine)).toHaveAttribute('data-page','2');
    expect(await page.locator('[data-memory-slot]').first().getAttribute('href')).not.toBe(first);
    await knob.focus(); await page.keyboard.press('ArrowLeft'); await expect(page.locator(machine)).toHaveAttribute('data-page','1');
    await page.keyboard.press('ArrowLeft'); const pages=await page.locator(machine).getAttribute('data-pages');
    await expect(page.locator(machine)).toHaveAttribute('data-page',pages);
    const last = await page.locator('[data-memory-slot]').count(); expect(last).toBeGreaterThan(0); expect(last).toBeLessThanOrEqual(6);
    await page.locator('[data-memory-reset]').click(); await expect(page.locator(machine)).toHaveAttribute('data-page','1');
    await expect(page.locator(machine)).toHaveAttribute('data-power','on');
});

test('rapid page requests discard stale fragments; failure retains committed selection and retries', async ({ page }) => {
    await ready(page); await on(page);
    if(await page.locator('[data-vending-knob]').isDisabled()) return;
    await page.route('**/*machine_page=*', async route=>{
        if(route.request().url().includes('machine_page=2')) await new Promise(resolve=>setTimeout(resolve,400));
        await route.continue();
    });
    await page.locator('[data-vending-knob]').focus();
    await page.keyboard.press('ArrowRight'); await page.keyboard.press('ArrowRight');
    await expect(page.locator(machine)).toHaveAttribute('data-page-busy','false');
    const expected=Number(await page.locator(machine).getAttribute('data-pages'))>2?'3':'1';
    await expect(page.locator(machine)).toHaveAttribute('data-page',expected);
    await page.waitForTimeout(500); await expect(page.locator(machine)).toHaveAttribute('data-page',expected);
    await page.unroute('**/*machine_page=*'); let fail=true;
    await page.route('**/*machine_page=*',route=>fail?route.fulfill({status:503,body:'QA unavailable'}):route.continue());
    const before=await page.locator('[data-memory-slot]').first().getAttribute('href');
    await page.keyboard.press('ArrowRight'); await expect(page.locator('[data-discovery-error]')).toBeVisible();
    expect(await page.locator('[data-memory-slot]').first().getAttribute('href')).toBe(before);
    fail=false; await page.locator('[data-discovery-retry]').click(); await expect(page.locator('[data-discovery-error]')).toBeHidden();
    await expect(page.locator(machine)).toHaveAttribute('data-page-busy','false');
});

test('selection prints exactly one matching photo and retrieval opens its actual visit', async ({ page }) => {
    await ready(page); await on(page);
    const slot=page.locator('[data-memory-slot]').nth(1); await slot.click();
    const href=await slot.getAttribute('href'), id=await slot.getAttribute('data-memory-slot');
    await expect(page.locator('[data-vending-screen] a')).toHaveAttribute('href',href);
    await page.locator('[data-vending-print]').click();
    await expect(page.locator('[data-vending-print]')).toBeDisabled();
    await expect(page.locator('[data-memory-reset]')).toBeDisabled();
    await expect(page.locator(machine)).toHaveAttribute('data-dispenser','ready');
    await expect(page.locator('[data-paper-path] a')).toHaveCount(1);
    await expect(page.locator('[data-paper-path] a')).toHaveAttribute('data-printed-visit',id);
    await page.locator('[data-paper-path] a').focus(); await page.keyboard.press('Enter');
    await expect(page).toHaveURL(href); await expect(page.locator('.visit-dossier')).toBeVisible();
    await page.goBack(); await expect(page.locator(machine)).toHaveAttribute('data-power','on');
    await expect(page.locator('[data-vending-print]')).toBeEnabled();
});

test('reset and a new selection retract the ready card; push-button face depresses without rotating', async ({ page }) => {
    await ready(page); await on(page);
    await page.locator('[data-vending-print]').click(); await expect(page.locator(machine)).toHaveAttribute('data-dispenser','ready');
    await page.locator('[data-memory-slot]').nth(1).click(); await expect(page.locator('[data-paper-path] a')).toHaveCount(0);
    const button=page.locator('[data-memory-reset]'); const c=await center(button);
    await page.mouse.move(c.x,c.y); await page.mouse.down(); await page.waitForTimeout(100);
    const transform=await button.locator('.vending-reset__face').evaluate(n=>getComputedStyle(n).transform);
    expect(transform).not.toBe('none'); expect(transform).toMatch(/matrix\([^,]+, 0, 0,/);
    await page.mouse.up(); await expect(page.locator(machine)).toHaveAttribute('data-dispenser','idle');
    await expect(page.locator('[data-memory-slot]').first()).toHaveAttribute('aria-current','true');
});

test('touch uses lamp then target; reduced-motion runtime changes keep power and print usable', async ({ browser, baseURL }) => {
    const context=await browser.newContext({hasTouch:true,isMobile:true,viewport:{width:390,height:844},baseURL});
    const page=await context.newPage(); await ready(page);
    await page.locator('[data-vending-lamp]').tap(); await page.locator('[data-lamp-socket]').tap();
    await expect(page.locator(machine)).toHaveAttribute('data-power','on');
    const rects=await page.locator(machine).evaluate(n=>{
        const a=n.querySelector('[data-vending-lamp]').getBoundingClientRect(), b=n.querySelector('[data-lamp-socket]').getBoundingClientRect();
        return {dx:Math.abs(a.x+a.width/2-b.x-b.width/2),dy:Math.abs(a.y+a.height/2-b.y-b.height/2)};
    });
    expect(rects.dx).toBeLessThan(2); expect(rects.dy).toBeLessThan(2);
    await page.emulateMedia({reducedMotion:'reduce'});
    await page.locator('[data-vending-print]').tap(); await expect(page.locator(machine)).toHaveAttribute('data-dispenser','ready');
    await page.locator('[data-memory-reset]').tap();
    await page.locator('[data-vending-lamp]').tap(); await page.locator('[data-lamp-cradle]').tap();
    await expect(page.locator(machine)).toHaveAttribute('data-power','off'); await context.close();
});

test('keyboard lamp actions and blocked sessionStorage remain usable', async ({ page }) => {
    await page.addInitScript(()=>{Object.defineProperty(window,'sessionStorage',{get(){throw new Error('Storage blocked')}})});
    await page.emulateMedia({reducedMotion:'reduce'}); await ready(page);
    await page.locator('[data-vending-lamp]').focus(); await page.keyboard.press('Enter');
    await expect(page.locator('[data-lamp-socket]')).toBeFocused(); await page.keyboard.press('Enter');
    await expect(page.locator(machine)).toHaveAttribute('data-power','on');
    await expect(page.locator('[data-vending-lamp]')).toBeFocused();
    await page.locator('[data-vending-lamp]').focus(); await page.keyboard.press('Space');
    await expect(page.locator('[data-lamp-cradle]')).toBeFocused(); await page.keyboard.press('Enter');
    await expect(page.locator(machine)).toHaveAttribute('data-power','off');
    await expect(page.locator('[data-vending-lamp]')).toBeFocused();
    await page.keyboard.press('Enter'); await page.keyboard.press('Escape');
    await expect(page.locator(machine)).toHaveAttribute('data-lamp','cradle');
    await expect(page.locator('[data-vending-lamp]')).toBeFocused();
});

test('responsive geometry, LED alignment, missing images and WCAG scans in off/on/printed states', async ({ page }) => {
    test.setTimeout(150000);
    await page.emulateMedia({reducedMotion:'reduce'});
    for(const width of [1920,1440,1024,768,375,320]) {
        await page.setViewportSize({width,height:1000}); await ready(page);
        if(await page.locator(machine).getAttribute('data-power')==='off') await on(page);
        expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth)).toBe(true);
        if(width>=960) {
            const lights=await page.locator(machine).evaluate(n=>{const b=n.getBoundingClientRect();return [...n.querySelectorAll('.memory-machine__step i')].map((l,i)=>{const r=l.getBoundingClientRect();return {ratio:r.width/r.height,dx:(r.x+r.width/2-b.x)/b.width*1634-1012,dy:(r.y+r.height/2-b.y)/b.height*963-[267,405,546][i]}})});
            for(const l of lights){expect(l.ratio).toBeCloseTo(1,2);expect(Math.abs(l.dx)).toBeLessThan(1);expect(Math.abs(l.dy)).toBeLessThan(1);}
        }
    }
    for(const width of [1440,375]) {
        await page.setViewportSize({width,height:1000}); await ready(page);
        for(const state of ['on','ready','off']) {
            if(state==='ready'){await page.locator('[data-vending-print]').click();await expect(page.locator(machine)).toHaveAttribute('data-dispenser','ready');}
            if(state==='off'){await page.locator('[data-vending-lamp]').click();await page.locator('[data-lamp-cradle]').click();await expect(page.locator(machine)).toHaveAttribute('data-power','off');}
            const result=await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21aa','wcag22aa']).analyze();
            expect(result.violations.map(v=>({id:v.id,targets:v.nodes.map(n=>n.target)}))).toEqual([]);
        }
        await on(page);
    }
    await page.route('**/media/**',route=>route.abort()); await ready(page);
    await expect(page.locator('[data-vending-screen] [data-photo-fallback]')).toBeVisible();
    await page.locator('[data-vending-print]').click(); await expect(page.locator(machine)).toHaveAttribute('data-dispenser','ready');
    await expect(page.locator('[data-paper-path] [data-photo-fallback]')).toBeVisible();
});

test('no JavaScript leaves semantic visit and Explore navigation available', async ({ browser, baseURL }) => {
    const context=await browser.newContext({javaScriptEnabled:false,baseURL}); const page=await context.newPage(); await page.goto('/');
    const slot=page.locator('[data-memory-slot]').first(), href=await slot.getAttribute('href');
    await slot.click(); await expect(page).toHaveURL(href); await context.close();
});

test('touch dragging snaps only after release on the compact socket', async ({ browser, baseURL }) => {
    const context=await browser.newContext({hasTouch:true,isMobile:true,viewport:{width:390,height:844},baseURL});
    const page=await context.newPage(); await ready(page); await page.locator('[data-lamp-socket]').scrollIntoViewIfNeeded();
    const client=await context.newCDPSession(page), from=await center(page.locator('[data-vending-lamp]')), to=await center(page.locator('[data-lamp-socket]'));
    await client.send('Input.dispatchTouchEvent',{type:'touchStart',touchPoints:[{x:from.x,y:from.y}]});
    for(let i=1;i<=10;i++) await client.send('Input.dispatchTouchEvent',{type:'touchMove',touchPoints:[{x:from.x+(to.x-from.x)*i/10,y:from.y+(to.y-from.y)*i/10}]});
    await expect(page.locator(machine)).toHaveAttribute('data-drop-target','socket');
    await expect(page.locator(machine)).toHaveAttribute('data-power','off');
    await client.send('Input.dispatchTouchEvent',{type:'touchEnd',touchPoints:[]});
    await expect(page.locator(machine)).toHaveAttribute('data-power','on'); await context.close();
});

test('resize, a second pointer, scroll and visibility interruptions preserve a valid lamp pose', async ({ page }) => {
    await ready(page); await page.locator(machine).scrollIntoViewIfNeeded();
    await beginDrag(page,'[data-lamp-socket]');
    await page.locator('[data-vending-lamp]').dispatchEvent('pointerdown',{pointerId:7,button:0,isPrimary:false});
    await page.setViewportSize({width:768,height:900}); await page.mouse.up();
    await expect(page.locator(machine)).toHaveAttribute('data-lamp','cradle');
    await expect(page.locator(machine)).toHaveAttribute('data-power','off');
    await on(page); await page.locator('[data-vending-lamp]').scrollIntoViewIfNeeded();
    await beginDrag(page,'[data-lamp-cradle]');
    await page.evaluate(()=>scrollBy(0,30)); await page.mouse.up();
    await expect(page.locator(machine)).toHaveAttribute('data-lamp','socket');
    await page.locator('[data-vending-lamp]').scrollIntoViewIfNeeded();
    await beginDrag(page,'[data-lamp-cradle]');
    await page.evaluate(()=>{
        Object.defineProperty(document,'hidden',{value:true,configurable:true});
        document.dispatchEvent(new Event('visibilitychange')); delete document.hidden;
    }); await page.mouse.up();
    await expect(page.locator(machine)).toHaveAttribute('data-lamp','socket');
    await expect(page.locator(machine)).toHaveAttribute('data-power','on');
});

test('the one-time contextual hint disappears after discovery across session reloads', async ({ page }) => {
    await page.addInitScript(()=>{
        const original=window.setTimeout.bind(window);
        window.setTimeout=(fn,delay,...args)=>original(fn,delay===7200?80:delay,...args);
    });
    await ready(page); await expect(page.locator('[data-vending-hint]')).toBeVisible();
    await on(page); await expect(page.locator('[data-vending-hint]')).toBeHidden();
    await page.locator('[data-vending-lamp]').click(); await page.locator('[data-lamp-cradle]').click();
    await expect(page.locator(machine)).toHaveAttribute('data-power','off');
    await ready(page); await page.waitForTimeout(150); await expect(page.locator('[data-vending-hint]')).toBeHidden();
});

test('a now-empty server page clears the old preview and disables printing', async ({ page }) => {
    await ready(page); await on(page);
    if(await page.locator('[data-vending-knob]').isDisabled()) return;
    await page.route('**/*machine_page=*',route=>route.fulfill({status:200,contentType:'text/html',body:'<div data-vending-page data-page="1" data-pages="1" data-total="0"><nav data-memory-slots></nav><template data-vending-empty><article class="memory-showcase"><h2>Arsip masih kosong.</h2></article></template></div>'}));
    await page.locator('[data-vending-next]').click(); await expect(page.locator(machine)).toHaveAttribute('data-page-busy','false');
    await expect(page.locator('[data-vending-screen]')).toContainText('Arsip masih kosong.');
    await expect(page.locator('[data-vending-screen] a')).toHaveCount(0);
    await expect(page.locator('[data-vending-print]')).toBeDisabled(); await expect(page.locator('[data-vending-knob]')).toBeDisabled();
});

test('compiled CSS durations drive real snap, ignition and paper-feed timings', async ({ page }) => {
    await page.addInitScript(()=>{
        window.vendingTimings=[]; const animate=Element.prototype.animate;
        Element.prototype.animate=function(frames,timing){window.vendingTimings.push({name:this.className,duration:timing.duration});return animate.call(this,frames,timing)};
    });
    await page.emulateMedia({reducedMotion:'no-preference'}); await ready(page); await on(page);
    await page.locator('[data-vending-print]').click(); await expect(page.locator(machine)).toHaveAttribute('data-dispenser','printing');
    await page.waitForTimeout(300); await expect(page.locator(machine)).toHaveAttribute('data-dispenser','printing');
    await expect(page.locator(machine)).toHaveAttribute('data-dispenser','ready');
    const timings=await page.evaluate(()=>window.vendingTimings);
    expect(timings.find(t=>t.name==='vending-lamp').duration).toBe(270);
    expect(timings.find(t=>t.name.includes('vending-machine')).duration).toBe(960);
    expect(timings.find(t=>t.name==='vending-print').duration).toBe(820);
});
