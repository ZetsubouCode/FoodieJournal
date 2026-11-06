import { initialState, transition, controlsAvailable, lampAvailable, inDropZone, wrapIndex, durationMilliseconds } from '../components/vending-state.mjs';
import { bindRotary } from '../components/vending-input';

export function initializeArchive() {
    const root = document.querySelector('[data-home-archive]');
    if (!root) return;
    root.vendingCleanup?.();
    const machine = root.querySelector('[data-machine]');
    const lamp = root.querySelector('[data-vending-lamp]');
    const socket = root.querySelector('[data-lamp-socket]');
    const cradle = root.querySelector('[data-lamp-cradle]');
    const screen = root.querySelector('[data-vending-screen]');
    const paper = root.querySelector('[data-paper-path]');
    const hint = root.querySelector('[data-vending-hint]');
    const errorPanel = root.querySelector('[data-discovery-error]');
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    const lifecycle = new AbortController(), { signal } = lifecycle;
    const animations = new Set();
    const settings = getComputedStyle(machine);
    const timing = name => durationMilliseconds(settings.getPropertyValue('--vending-' + name));
    let state = initialState(document.documentElement.dataset.vendingSession === 'on', Number(machine.dataset.page) - 1, root.querySelector('[data-memory-slot]')?.dataset.memorySlot ?? null);
    let pose, drag = null, anchors, frame = 0, tapArmed = false, suppressClick = false;
    let request = null, sequence = 0, hintTimer, retryPage = null;
    const slots = () => [...root.querySelectorAll('[data-memory-slot]')];
    const selected = () => slots().find(slot => slot.dataset.memorySlot === state.selectedVisitId);
    const announce = text => { root.querySelector('[data-machine-status]').textContent = text; };
    const persist = on => {
        document.documentElement.dataset.vendingSession = on ? 'on' : 'off';
        if (on) document.documentElement.dataset.vendingDiscovered = 'true';
        try {
            sessionStorage.setItem('foodie.vending.power', on ? 'on' : 'off');
            if (on) sessionStorage.setItem('foodie.vending.discovered', 'true');
        } catch { /* Storage can be disabled. */ }
    };
    function render() {
        machine.dataset.power = state.power;
        machine.dataset.lamp = state.lampLocation;
        machine.dataset.lampOrigin = state.lampOrigin ?? '';
        machine.dataset.dispenser = state.dispenser;
        machine.dataset.selectedVisit = state.selectedVisitId ?? '';
        machine.dataset.page = state.pageIndex + 1;
        machine.dataset.lampArmed = String(tapArmed);
        machine.dataset.pageBusy = String(state.pageBusy);
        machine.setAttribute('aria-busy', String(state.pageBusy || ['booting', 'shutting_down'].includes(state.power) || state.dispenser === 'printing'));
        const available = controlsAvailable(state);
        root.querySelector('[data-memory-slots]').inert = !available || state.pageBusy;
        screen.inert = !available || state.pageBusy;
        paper.inert = !available || state.dispenser !== 'ready';
        for (const selector of ['[data-vending-choose]', '[data-vending-print]', '[data-vending-story]']) {
            root.querySelector(selector).disabled = !available || state.pageBusy || state.selectedVisitId === null || (selector === '[data-vending-print]' && state.dispenser !== 'idle');
        }
        root.querySelector('[data-memory-reset]').disabled = !available;
        for (const selector of ['[data-vending-knob]', '[data-vending-previous]', '[data-vending-next]']) {
            root.querySelector(selector).disabled = !available || Number(machine.dataset.pages) < 2;
        }
        lamp.disabled = !lampAvailable(state) || state.lampLocation === 'animating';
        socket.disabled = lamp.disabled || state.lampLocation === 'socket' || state.lampOrigin === 'socket';
        cradle.disabled = lamp.disabled || state.lampLocation === 'cradle' || state.lampOrigin === 'cradle';
        lamp.setAttribute('aria-pressed', String(tapArmed));
        lamp.setAttribute('aria-label', state.power === 'on' ? 'Angkat lampu, lalu pilih dudukan untuk mematikan mesin' : 'Angkat lampu, lalu pilih soket untuk menyalakan mesin');
        root.querySelector('[data-page-number]').textContent = String(state.pageIndex + 1).padStart(2, '0');
        for (const slot of slots()) {
            if (slot.dataset.memorySlot === state.selectedVisitId) slot.setAttribute('aria-current', 'true');
            else slot.removeAttribute('aria-current');
        }
    }
    function dispatch(event) { const previous = state; state = transition(state, event); render(); return previous !== state; }
    function media(scope = machine) {
        scope.querySelectorAll('[data-vending-photo]').forEach(img => {
            if (img.dataset.bound) return;
            img.dataset.bound = 'true';
            const cleanup = () => { img.removeEventListener('error', failed); img.removeEventListener('load', cleanup); signal.removeEventListener('abort', cleanup); };
            const failed = () => { cleanup(); const fallback = img.parentElement?.querySelector('[data-photo-fallback]'); if (fallback) { fallback.hidden = false; fallback.textContent = 'Foto belum tersedia'; } img.remove(); };
            if (img.complete) { if (!img.naturalWidth) failed(); return; }
            img.addEventListener('error', failed, { once: true });
            img.addEventListener('load', cleanup, { once: true });
            signal.addEventListener('abort', cleanup, { once: true });
        });
    }
    function dismissHint() { clearTimeout(hintTimer); hint.hidden = true; }
    function measure() {
        const parent = lamp.offsetParent, base = parent.getBoundingClientRect();
        const target = node => { const r = node.getBoundingClientRect(); return { x: r.x + r.width / 2 - base.x - parent.clientLeft, y: r.y + r.height / 2 - base.y - parent.clientTop, width: r.width, height: r.height }; };
        anchors = { socket: { ...target(socket), angle: 0 }, cradle: { ...target(cradle), angle: -96 } };
    }
    // Cache dimensions for pointer frames: no layout reads during follow-pointer.
    let lampSize;
    const poseTransform = p => `translate(${p.x - lampSize.width / 2}px, ${p.y - lampSize.height / 2}px) rotate(${p.angle}deg)`;
    function drawLamp() { frame = 0; lamp.style.transform = poseTransform(pose); }
    function refreshPose() {
        measure(); lampSize = { width: lamp.offsetWidth, height: lamp.offsetHeight };
        if (['cradle', 'socket'].includes(state.lampLocation)) { pose = { ...anchors[state.lampLocation] }; drawLamp(); }
    }
    async function motion(node, frames, duration, easing = 'cubic-bezier(.2,.72,.22,1)') {
        if (signal.aborted) return false;
        if (reduced.matches || !node.animate) return true;
        const a = node.animate(frames, { duration, easing, fill: 'none' }); animations.add(a);
        try { await a.finished; return !signal.aborted; }
        catch { return false; }
        finally { animations.delete(a); }
    }
    function clearPrint() { paper.replaceChildren(); }
    function preview(slot, animate = true) {
        const template = slot?.querySelector('[data-visit-preview]');
        if (template) screen.replaceChildren(template.content.cloneNode(true));
        media(screen);
        if (animate) motion(screen.firstElementChild, [{ opacity: .55, transform: 'translateY(4px)' }, { opacity: 1, transform: 'translateY(0)' }], 160);
    }
    function choose(slot) {
        if (!slot || !dispatch({ type: 'SELECT', id: slot.dataset.memorySlot })) return;
        clearPrint(); preview(slot); announce('Kunjungan dipilih. ' + slot.querySelector('.memory-slot__name').textContent + '. Foto siap dicetak.');
    }
    async function settle(target = null) {
        if (state.lampLocation !== 'dragging') return;
        const restoreKeyboardFocus = [socket, cradle].includes(document.activeElement) && document.activeElement.matches(':focus-visible');
        tapArmed = false; machine.dataset.dropTarget = ''; dismissHint();
        const origin = state.lampOrigin;
        dispatch(target ? { type: 'SNAP', target } : { type: 'RETURN' });
        const destination = target ?? origin;
        const from = poseTransform(pose), toPose = { ...anchors[destination] };
        const to = poseTransform(toPose);
        // Commit the final transform before WAAPI finishes; fill never leaves stale layers.
        lamp.style.transform = to;
        const complete = await motion(lamp, [{ transform: from }, { transform: to }], timing('snap'));
        if (!complete || signal.aborted) return;
        pose = toPose; dispatch({ type: 'COMMIT_LAMP' });
        if (destination === origin) { if (restoreKeyboardFocus) lamp.focus({ preventScroll: true }); return; }
        if (destination === 'socket') motion(socket.querySelector('img'), [{ transform: 'scaleX(1.035)' }, { transform: 'scaleX(1)' }], 125);
        clearPrint(); request?.abort(); sequence++; dispatch({ type: 'PAGE_CANCEL' });
        persist(destination === 'socket');
        announce(destination === 'socket' ? 'Lampu terkunci. Mesin menyala…' : 'Lampu kembali ke dudukan. Mesin dimatikan.');
        // A bounded, cancellable clock drives the CSS boot choreography.
        await motion(machine, [{ opacity: 1 }, { opacity: 1 }], timing(destination === 'socket' ? 'boot' : 'off'));
        if (signal.aborted) return;
        dispatch({ type: 'POWER_STABLE' });
        if (restoreKeyboardFocus) lamp.focus({ preventScroll: true });
        announce(state.power === 'on' ? 'Mesin siap. Pilih kunjungan atau putar kenop untuk halaman berikutnya.' : 'Mesin mati. Lampu berada di dudukan.');
    }
    function releaseCapture() {
        if (!drag) return;
        const id = drag.id; drag = null;
        if (lamp.hasPointerCapture(id)) lamp.releasePointerCapture(id);
    }
    function cancelDrag() {
        if (state.lampLocation !== 'dragging') return;
        releaseCapture(); if (frame) cancelAnimationFrame(frame); frame = 0;
        suppressClick = true; settle();
    }
    lamp.addEventListener('pointerdown', e => {
        if (e.button !== 0 || !e.isPrimary || drag || tapArmed || !lampAvailable(state) || !['socket', 'cradle'].includes(state.lampLocation)) return;
        dismissHint(); refreshPose();
        if (!dispatch({ type: 'LIFT' })) return;
        drag = { id: e.pointerId, x: e.clientX, y: e.clientY, scrollX: window.scrollX, scrollY: window.scrollY, pose: { ...pose }, moved: false };
        suppressClick = false; lamp.setPointerCapture(e.pointerId);
    }, { signal });
    lamp.addEventListener('pointermove', e => {
        if (!drag || e.pointerId !== drag.id) return;
        if (window.scrollX !== drag.scrollX || window.scrollY !== drag.scrollY) { cancelDrag(); return; }
        const dx = e.clientX - drag.x, dy = e.clientY - drag.y;
        drag.moved ||= Math.hypot(dx, dy) > 5;
        pose = { ...pose, x: drag.pose.x + dx, y: drag.pose.y + dy };
        const target = state.lampOrigin === 'cradle' ? 'socket' : 'cradle';
        machine.dataset.dropTarget = inDropZone(pose, anchors[target]) ? target : '';
        if (!frame) frame = requestAnimationFrame(drawLamp);
    }, { signal });
    lamp.addEventListener('pointerup', e => {
        if (!drag || e.pointerId !== drag.id) return;
        // Scroll dispatch may lag pointerup by a frame; stale geometry never commits.
        if (window.scrollX !== drag.scrollX || window.scrollY !== drag.scrollY) { cancelDrag(); return; }
        const moved = drag.moved; releaseCapture(); if (frame) cancelAnimationFrame(frame); drawLamp();
        if (!moved) {
            // A stationary release selects the lamp; a second tap chooses its destination.
            tapArmed = true; render(); suppressClick = true; return;
        }
        suppressClick = true;
        const target = state.lampOrigin === 'cradle' ? 'socket' : 'cradle';
        settle(inDropZone(pose, anchors[target]) ? target : null);
    }, { signal });
    lamp.addEventListener('pointercancel', cancelDrag, { signal });
    lamp.addEventListener('lostpointercapture', () => { if (drag) cancelDrag(); }, { signal });
    lamp.addEventListener('click', e => {
        if (suppressClick) { suppressClick = false; return; }
        dismissHint();
        if (tapArmed) { cancelDrag(); return; }
        refreshPose(); tapArmed = dispatch({ type: 'LIFT' }); render();
        if (tapArmed && e.detail === 0) (state.lampOrigin === 'cradle' ? socket : cradle).focus({ preventScroll: true });
    }, { signal });
    socket.addEventListener('click', () => { if (tapArmed && state.lampOrigin === 'cradle') settle('socket'); }, { signal });
    cradle.addEventListener('click', () => { if (tapArmed && state.lampOrigin === 'socket') settle('cradle'); }, { signal });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { cancelDrag(); cancelKnob(); } }, { signal });

    async function loadPage(index) {
        if (!dispatch({ type: 'PAGE_REQUEST', page: index })) return;
        clearPrint(); request?.abort(); request = new AbortController();
        const current = request, token = ++sequence;
        retryPage = index; errorPanel.hidden = true;
        const timeout = setTimeout(() => current.abort(), 12000);
        const url = new URL(root.dataset.pageUrl); url.searchParams.set('machine_page', index + 1);
        try {
            const response = await fetch(url, { headers: { 'X-Vending-Fragment': '1' }, signal: current.signal });
            if (!response.ok) throw new Error('Page unavailable');
            const next = new DOMParser().parseFromString(await response.text(), 'text/html').querySelector('[data-vending-page]');
            if (!next) throw new Error('Invalid cabinet page');
            if (signal.aborted || current.signal.aborted || sequence !== token) return;
            const direction = index >= state.pageIndex ? 1 : -1;
            const nav = root.querySelector('[data-memory-slots]');
            const focusWasSlot = nav.contains(document.activeElement);
            nav.replaceChildren(...document.importNode(next.querySelector('[data-memory-slots]'), true).childNodes);
            machine.dataset.pages = next.dataset.pages; machine.dataset.total = next.dataset.total;
            dispatch({ type: 'PAGE_COMMIT', page: Number(next.dataset.page) - 1, id: slots()[0]?.dataset.memorySlot ?? null });
            if (selected()) preview(selected(), false);
            else screen.replaceChildren(next.querySelector('[data-vending-empty]').content.cloneNode(true));
            media();
            if (focusWasSlot) selected()?.focus({ preventScroll: true });
            for (const [i, slot] of slots().entries()) motion(slot, [{ opacity: .5, transform: `translateX(${direction * 9}px)` }, { opacity: 1, transform: 'translateX(0)' }], 175 + i * 12);
            announce(`Halaman ${state.pageIndex + 1} dari ${machine.dataset.pages}. ${slots().length} kunjungan tersedia.`);
        } catch {
            if (sequence !== token || signal.aborted) return;
            dispatch({ type: 'PAGE_CANCEL' }); errorPanel.hidden = false;
            announce('Halaman belum dapat dimuat. Pilihan sebelumnya tetap tersedia.');
        } finally { clearTimeout(timeout); }
    }
    function step(direction) {
        if (!controlsAvailable(state) || Number(machine.dataset.pages) < 2) return;
        loadPage(wrapIndex(state.requestedPage + direction, Number(machine.dataset.pages)));
    }
    const cancelKnob = bindRotary(root.querySelector('[data-vending-knob]'), { step, enabled: () => controlsAvailable(state) && Number(machine.dataset.pages) > 1, reduced, signal });
    root.querySelector('[data-vending-previous]').addEventListener('click', () => step(-1), { signal });
    root.querySelector('[data-vending-next]').addEventListener('click', () => step(1), { signal });
    root.querySelector('[data-memory-reset]').addEventListener('click', async () => {
        if (!dispatch({ type: 'RESET' })) return;
        clearPrint(); errorPanel.hidden = true;
        if (state.pageIndex !== 0 || state.pageBusy) loadPage(0);
        else { choose(slots()[0]); announce('Pilihan kembali ke awal. Mesin tetap menyala.'); }
    }, { signal });
    root.querySelector('[data-vending-choose]').addEventListener('click', () => {
        const list = slots(), index = list.indexOf(selected()); choose(list[wrapIndex(index + 1, list.length)]);
    }, { signal });
    root.querySelector('[data-vending-story]').addEventListener('click', () => { if (controlsAvailable(state) && !state.pageBusy) { const link = screen.querySelector('a'); if (link) location.assign(link.href); } }, { signal });
    root.querySelector('[data-discovery-retry]').addEventListener('click', () => { if (retryPage !== null) loadPage(retryPage); }, { signal });
    root.querySelector('[data-vending-print]').addEventListener('click', async () => {
        const template = selected()?.querySelector('[data-visit-print]');
        if (!template || !dispatch({ type: 'PRINT' })) return;
        paper.replaceChildren(template.content.cloneNode(true)); media(paper); announce('Impresi foto sedang dicetak…');
        const card = paper.firstElementChild;
        const to = 'translateY(30%) rotate(-2deg)'; card.style.transform = to;
        await motion(card, [{ transform: 'translateY(-112%) rotate(-2deg)' }, { transform: 'translateY(-65%) rotate(-2deg)', offset: .25 }, { transform: 'translateY(-23%) rotate(-2deg)', offset: .72 }, { transform: to }], timing('print'), 'cubic-bezier(.26,.36,.28,1)');
        if (signal.aborted) return;
        dispatch({ type: 'PRINT_READY' }); announce('Foto siap diambil. Tekan foto untuk membuka cerita kunjungan.');
    }, { signal });
    paper.addEventListener('click', async e => {
        const link = e.target.closest('a');
        if (!link || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
        e.preventDefault(); if (!dispatch({ type: 'RETRIEVE' })) return;
        const href = link.href;
        await motion(link, [{ transform: 'translateY(30%) rotate(-2deg)', opacity: 1 }, { transform: 'translateY(74%) rotate(-6deg)', opacity: 0 }], 180);
        if (!signal.aborted) location.assign(href);
    }, { signal });
    root.addEventListener('click', e => {
        const link = e.target.closest('[data-memory-slot]');
        if (!link || e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
        e.preventDefault(); if (controlsAvailable(state) && !state.pageBusy) choose(link);
    }, { signal });
    root.addEventListener('keydown', e => { if (e.code === 'Space' && e.target.matches('[data-memory-slot]')) { e.preventDefault(); e.target.click(); } }, { signal });
    function interrupt() { cancelDrag(); cancelKnob(); animations.forEach(a => { try { a.finish(); } catch { a.cancel(); } }); }
    document.addEventListener('visibilitychange', () => { if (document.hidden) interrupt(); }, { signal });
    const relayout = () => {
        cancelKnob();
        if (drag || tapArmed) cancelDrag();
        if (state.lampLocation === 'animating') animations.forEach(a => { try { a.finish(); } catch { a.cancel(); } });
        requestAnimationFrame(() => { if (!signal.aborted) refreshPose(); });
    };
    window.addEventListener('resize', relayout, { signal });
    window.addEventListener('scroll', () => { if (drag) cancelDrag(); cancelKnob(); }, { passive: true, signal });
    reduced.addEventListener('change', () => { if (reduced.matches) { dismissHint(); interrupt(); } }, { signal });
    root.vendingCleanup = () => {
        sequence++; request?.abort(); dismissHint(); releaseCapture();
        if (frame) cancelAnimationFrame(frame);
        animations.forEach(a => a.cancel()); lifecycle.abort();
    };
    window.addEventListener('pagehide', () => {
        root.vendingCleanup();
        window.addEventListener('pageshow', e => { if (e.persisted) initializeArchive(); }, { once: true });
    }, { once: true, signal });
    [lamp, socket, cradle].forEach(node => { node.hidden = false; });
    machine.dataset.enhanced = 'true'; clearPrint(); refreshPose(); render(); media();
    if (state.power === 'on') announce(state.selectedVisitId ? 'Mesin siap. Pilih kunjungan atau cetak foto kenangan.' : 'Mesin menyala. Belum ada kunjungan yang dipublikasikan.');
    if (state.power === 'off' && !reduced.matches && document.documentElement.dataset.vendingDiscovered !== 'true') hintTimer = setTimeout(() => { if (state.power === 'off' && state.lampLocation === 'cradle') hint.hidden = false; }, 7200);
}
