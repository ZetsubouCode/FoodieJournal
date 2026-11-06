import { initializeDialogs } from '../core/dialog';
import { initializeForms, enableControls } from '../core/forms';
import { initializeVisitCards } from '../components/visit-card';
import { initializeFilterDrawer } from './filter-drawer';
import { initializeImageFallbacks } from '../components/image-fallback';

export function initializeExplore() {
    const root = document.querySelector('[data-discovery]');
    const shell = root.closest('[data-explore-shell]');
    let controller, sequence = 0, drawer, committed = location.href, retry;
    const status = message => { root.querySelector('[data-browse-status]').textContent = message; };
    function bindDrawer() {
        drawer?.destroy();
        drawer = initializeFilterDrawer(root.querySelector('[data-filter-drawer]'), url => navigate(url));
    }
    async function navigate(url, restore = false, scroll = false) {
        url = new URL(url, location.href).href;
        controller?.abort(); controller = new AbortController();
        const current = controller, token = ++sequence;
        retry = () => navigate(url, restore, scroll);
        const results = root.querySelector('[data-discovery-results]');
        results.setAttribute('aria-busy', 'true');
        status('Memuat catatan…');
        root.querySelector('[data-discovery-error]').hidden = true;
        const timeout = setTimeout(() => current.abort(), 15000);
        const focused = document.activeElement;
        const focusId = focused?.id;
        const wasTab = focused?.closest('.discovery-tabs');
        try {
            const response = await fetch(url, { headers: { 'X-Explore-Fragment': '1' }, signal: current.signal });
            if (!response.ok) throw new Error('Browse failed');
            const next = new DOMParser().parseFromString(await response.text(), 'text/html').querySelector('[data-discovery]');
            if (!next) throw new Error('Missing browse fragment');
            if (token !== sequence || current.signal.aborted) return;
            drawer?.destroy();
            for (const selector of ['[data-discovery-console]', '[data-discovery-results]', '[data-filter-drawer]']) {
                root.querySelector(selector).replaceWith(document.importNode(next.querySelector(selector), true));
            }
            root.dataset.mode = next.dataset.mode;
            initializeDialogs(root); initializeForms(root); enableControls(root); initializeVisitCards(root); initializeImageFallbacks(root); bindDrawer();
            if (!restore && url !== committed) history.pushState({}, '', url);
            committed = url;
            document.querySelector('meta[name=robots]')?.setAttribute('content', new URL(url).search ? 'noindex,follow' : 'index,follow');
            status(root.querySelector('.memory-reel__head > p').textContent.trim() + ' tersedia.');
            if (focusId) document.getElementById(focusId)?.focus({ preventScroll: true });
            else if (wasTab) root.querySelector('.discovery-tabs [aria-current=page]')?.focus({ preventScroll: true });
            if (scroll) root.querySelector('[data-discovery-console]').scrollIntoView({ block: 'start', behavior: 'instant' });
        } catch {
            if (token !== sequence) return;
            if (restore) history.replaceState({}, '', committed);
            root.querySelector('[data-discovery-error]').hidden = false;
            status('Pembaruan gagal. Hasil sebelumnya tetap tersedia.');
        } finally {
            clearTimeout(timeout);
            if (token === sequence) root.querySelector('[data-discovery-results]').setAttribute('aria-busy', 'false');
        }
    }
    shell.addEventListener('click', event => {
        if (event.target.closest('[data-discovery-retry]')) { retry?.(); return; }
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') return;
        const url = new URL(link.href);
        if (url.origin !== location.origin || url.pathname !== '/explore' || url.hash) return;
        event.preventDefault(); navigate(url.href, false, !!link.closest('.curiosity-index'));
    });
    root.addEventListener('submit', event => {
        if (!event.target.matches('[data-discovery-search]')) return;
        event.preventDefault();
        const url = new URL(event.target.action); url.search = new URLSearchParams(new FormData(event.target));
        navigate(url.href);
    });
    root.addEventListener('change', event => { if (event.target.matches('#discovery-sort')) event.target.form.requestSubmit(); });
    window.addEventListener('popstate', () => navigate(location.href, true));
    window.addEventListener('pagehide', () => { sequence++; controller?.abort(); drawer?.destroy(); });
    window.addEventListener('pageshow', event => { if (event.persisted) navigate(location.href, true); });
    bindDrawer();
}
