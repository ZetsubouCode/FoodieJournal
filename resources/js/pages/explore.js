import { openDialog, initializeDialogs } from '../core/dialog';
import { initializeForms, enableControls } from '../core/forms';
import { initializeVisitCards } from '../components/visit-card';

const pause = ms => new Promise(resolve => setTimeout(resolve, ms));

export function initializeExplore() {
    const form = document.querySelector('[data-filter-form]');
    const dialog = form.closest('dialog');
    const apply = form.querySelector('[data-filter-apply]');
    const status = form.querySelector('[data-filter-status]');
    let snapshot, applied = false, countController, timer, countSequence = 0;
    const fields = [...form.querySelectorAll('input:not([type=hidden]), select')];

    dialog.addEventListener('dialog:open', () => {
        snapshot = fields.map(field => ({ field, value: field.value, checked: field.checked })); applied = false;
    });
    dialog.addEventListener('close', () => {
        clearTimeout(timer); countController?.abort(); countSequence++;
        if (!applied) snapshot?.forEach(saved => { saved.field.value = saved.value; saved.field.checked = saved.checked; });
        form.removeAttribute('aria-busy'); status.textContent = '';
    });
    const count = async () => {
        countController?.abort(); countController = new AbortController(); const current = ++countSequence;
        status.textContent = 'Menghitung hasil…'; form.setAttribute('aria-busy', 'true');
        try {
            const url = new URL(form.dataset.countUrl, location.origin); url.search = new URLSearchParams(new FormData(form));
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: countController.signal });
            if (!response.ok) throw new Error(); const data = await response.json(); if (current !== countSequence) return;
            apply.textContent = `Tampilkan ${data.total} hasil`; status.textContent = '';
        } catch (error) { if (error.name !== 'AbortError') { status.textContent = 'Jumlah hasil belum dapat dimuat. Tetap dapat menerapkan filter.'; apply.textContent = 'Tampilkan hasil'; } }
        finally { if (current === countSequence) form.removeAttribute('aria-busy'); }
    };
    form.addEventListener('change', () => { clearTimeout(timer); timer = setTimeout(count, 220); });
    form.addEventListener('submit', () => { applied = true; });
    form.querySelector('[data-filter-reset]').addEventListener('click', () => {
        fields.forEach(field => { if (field.type === 'checkbox') field.checked = false; else field.value = ''; });
        form.querySelectorAll('[data-filter-option]').forEach(node => node.hidden = false); count();
    });
    form.querySelectorAll('[data-filter-search]').forEach(input => input.addEventListener('input', () => {
        input.closest('details').querySelectorAll('[data-filter-option]').forEach(option => { option.hidden = !option.textContent.toLocaleLowerCase('id').includes(input.value.toLocaleLowerCase('id')); });
    }));

    let root = document.querySelector('[data-discovery]');
    let visualIndex = Number(root.dataset.selectedIndex);
    let wheelAngle = 60 - visualIndex * 60;
    let transitioning = false;
    let requestId = 0;
    let requestController;
    let wheelAnimation;
    let retryUrl = null;
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    const more = trigger => {
        openDialog(dialog, trigger);
        const group = form.querySelector('[data-filter-dimension="food_type"]');
        if (group) { group.open = true; group.querySelector('summary').focus(); }
    };
    const ambient = () => { root.dataset.ambient = document.hidden || !root.dataset.inView ? 'paused' : 'playing'; };
    const observer = new IntersectionObserver(entries => { root.dataset.inView = entries[0]?.isIntersecting ? 'yes' : ''; ambient(); }, { threshold: .05 });
    observer.observe(root.querySelector('[data-discovery-scene]'));
    document.addEventListener('visibilitychange', ambient);

    function syncFilter(url) {
        const params = new URL(url, location.origin).searchParams;
        form.querySelectorAll('[data-filter-dimension] input[type=checkbox]').forEach(input => {
            const chosen = [...params.entries()].filter(([key]) => key === input.name || key === input.name.replace('[]', '') || key.startsWith(input.name.replace('[]', '') + '[')).map(([, value]) => value);
            input.checked = chosen.includes(input.value);
        });
        form.querySelectorAll('input[type=number]').forEach(input => { input.value = params.get(input.name) ?? ''; });
        form.querySelectorAll('input[type=hidden]').forEach(input => {
            if (['tab', 'sort', 'view', 'q'].includes(input.name)) input.value = params.get(input.name) ?? '';
        });
    }

    async function navigate(url, targetIndex = null, category = false, historyRestore = false) {
        if (transitioning && !historyRestore) return;
        if (historyRestore) { requestController?.abort(); wheelAnimation?.cancel(); requestId++; transitioning = false; }
        const id = ++requestId;
        const previousIndex = visualIndex;
        const previousAngle = wheelAngle;
        const scene = root.querySelector('[data-discovery-scene]');
        const button = root.querySelector('[data-wheel-next]');
        retryUrl = url;
        transitioning = true;
        button.disabled = true;
        scene.setAttribute('aria-busy', 'true');
        root.querySelector('[data-discovery-results]').setAttribute('aria-busy', 'true');
        root.querySelector('[data-discovery-error]').hidden = true;
        root.querySelector('[data-discovery-status]').textContent = category ? 'Mengganti gulungan…' : 'Memuat hasil…';
        if (category && !reducedMotion.matches) scene.dataset.phase = 'shutting_down';
        requestController = new AbortController();
        const responsePromise = fetch(url, { headers: { 'X-Explore-Fragment': '1' }, signal: requestController.signal }).then(async response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const next = new DOMParser().parseFromString(await response.text(), 'text/html').querySelector('[data-discovery]');
            if (!next) throw new Error('Missing discovery fragment');
            return next;
        });
        try {
            let motionPromise = Promise.resolve();
            if (category && !reducedMotion.matches) {
                await pause(180);
                if (id !== requestId) return;
                scene.dataset.phase = 'changing';
                const steps = (targetIndex - visualIndex + 6) % 6;
                wheelAngle -= steps * 60; visualIndex = targetIndex;
                wheelAnimation = root.querySelector('[data-wheel-rotor]').animate([
                    { transform: `rotate(${previousAngle}deg)` }, { transform: `rotate(${wheelAngle}deg)` },
                ], { duration: 620, easing: 'cubic-bezier(.17,.73,.2,1)', fill: 'forwards' });
                motionPromise = wheelAnimation.finished;
            }
            const [next] = await Promise.all([responsePromise, motionPromise]);
            if (id !== requestId) return;
            visualIndex = Number(next.dataset.selectedIndex);
            if (!category || reducedMotion.matches) wheelAngle = 60 - visualIndex * 60;
            else if (visualIndex !== targetIndex) wheelAngle += (targetIndex - visualIndex) * 60;
            if (category && !reducedMotion.matches) next.querySelector('[data-discovery-scene]').dataset.phase = 'changing';
            observer.disconnect(); root.replaceWith(next); root = next;
            observer.observe(root.querySelector('[data-discovery-scene]'));
            initializeDialogs(root); initializeForms(root); enableControls(root); initializeVisitCards(root);
            syncFilter(url);
            if (!historyRestore) history.pushState({}, '', url);
            if (category && !reducedMotion.matches) {
                await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
                root.querySelector('[data-discovery-scene]').dataset.phase = 'reigniting';
                await pause(280);
                root.querySelector('[data-discovery-scene]').dataset.phase = 'idle';
            }
            root.querySelector('[data-discovery-status]').textContent = `${root.dataset.selectedLabel}. ${root.querySelector('.memory-reel__head > p')?.textContent || 'Hasil diperbarui'}.`;
            if (category) root.querySelector('[data-wheel-next]').focus({ preventScroll: true });
            ambient();
        } catch (error) {
            if (id !== requestId || error.name === 'AbortError') return;
            visualIndex = previousIndex; wheelAngle = previousAngle;
            wheelAnimation?.cancel();
            root.querySelector('[data-wheel]').dataset.position = String(previousIndex);
            scene.dataset.phase = 'idle';
            root.querySelector('[data-discovery-error]').hidden = false;
            root.querySelector('[data-discovery-status]').textContent = 'Jelajah belum dapat diperbarui. Coba lagi.';
        } finally {
            if (id === requestId) {
                transitioning = false;
                root.querySelector('[data-wheel-next]').disabled = root.querySelector('[data-wheel-next]').hasAttribute('data-preserve-disabled');
                root.querySelector('[data-discovery-scene]').setAttribute('aria-busy', 'false');
                root.querySelector('[data-discovery-results]').setAttribute('aria-busy', 'false');
            }
        }
    }

    document.addEventListener('click', event => {
        const quick = event.target.closest('[data-filter-quick]');
        if (quick && root.contains(quick)) {
            openDialog(dialog, quick);
            const group = form.querySelector(`[data-filter-dimension="${quick.dataset.filterQuick}"]`);
            if (group) { group.open = true; group.querySelector('summary').focus(); }
            return;
        }
        const next = event.target.closest('[data-wheel-next]');
        if (next && root.contains(next)) {
            let index = (visualIndex + 1) % 6;
            for (let skipped = 0; skipped < 6 && root.querySelector(`[data-wheel-slot="${index}"]`)?.dataset.kind === 'empty'; skipped++) index = (index + 1) % 6;
            const slot = root.querySelector(`[data-wheel-slot="${index}"]`);
            if (slot?.dataset.kind === 'more') {
                const previous = wheelAngle;
                wheelAngle -= 60; visualIndex = index;
                wheelAnimation = root.querySelector('[data-wheel-rotor]').animate([
                    { transform: `rotate(${previous}deg)` }, { transform: `rotate(${wheelAngle}deg)` },
                ], { duration: reducedMotion.matches ? 0 : 620, easing: 'cubic-bezier(.17,.73,.2,1)', fill: 'forwards' });
                wheelAnimation.finished.then(() => { root.querySelector('[data-wheel]').dataset.position = String(index); wheelAnimation.cancel(); });
                more(next);
            } else if (slot?.href) navigate(slot.href, index, true);
            return;
        }
        const slot = event.target.closest('[data-wheel-slot][data-kind="more"]');
        if (slot && root.contains(slot)) { more(slot); return; }
        const retry = event.target.closest('[data-discovery-retry]');
        if (retry && root.contains(retry) && retryUrl) { navigate(retryUrl); return; }
        const link = event.target.closest('a[href]');
        if (!link || !root.contains(link) || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        if (link.closest('.discovery-tabs')) return;
        const url = new URL(link.href, location.origin);
        if (url.origin !== location.origin || url.pathname !== '/explore') return;
        event.preventDefault();
        const index = link.dataset.wheelSlot === undefined ? null : Number(link.dataset.wheelSlot);
        navigate(url.href, index, index !== null);
    });
    window.addEventListener('popstate', () => navigate(location.href, null, false, true));
}
