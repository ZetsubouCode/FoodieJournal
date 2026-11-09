import { initializeDialogs } from '../core/dialog';
import { initializeForms, enableControls } from '../core/forms';
import { initializeVisitCards } from '../components/visit-card';
import { initializeFilterDrawer } from './filter-drawer';
import { initializeImageFallbacks } from '../components/image-fallback';
import { durationMilliseconds } from '../components/vending-state.mjs';

export function initializeExplore() {
    const root = document.querySelector('[data-discovery]');
    const shell = root.closest('[data-explore-shell]');
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    const entrances = new Set(), photos = new Map();
    const photoObserver = window.IntersectionObserver ? new IntersectionObserver(entries => {
        for (const entry of entries) if (entry.isIntersecting) photos.get(entry.target)?.start();
    }) : null;
    let controller, sequence = 0, drawer, committed = location.href, retry, pendingLink;
    const status = message => { root.querySelector('[data-browse-status]').textContent = message; };
    function watchPhotos(scope) {
        for (const image of scope.querySelectorAll('img.media-image, img[data-visit-card-image]')) {
            if (image.complete || photos.has(image) || (!photoObserver && image.loading === 'lazy')) continue;
            let timer;
            const stop = () => {
                clearTimeout(timer); photoObserver?.unobserve(image); photos.delete(image);
                image.removeAttribute('data-photo-waiting');
                image.removeEventListener('load', stop); image.removeEventListener('error', stop);
            };
            const start = () => {
                photoObserver?.unobserve(image);
                if (image.complete) { stop(); return; }
                // Native visible-image loading keeps responsive sizes="auto" eligible.
                timer = setTimeout(() => { if (image.isConnected && !image.complete) image.dataset.photoWaiting = 'true'; }, 200);
            };
            photos.set(image, { start, stop });
            image.addEventListener('load', stop, { once: true }); image.addEventListener('error', stop, { once: true });
            if (photoObserver) photoObserver.observe(image); else start();
        }
    }
    function reflectBoard(values) {
        for (const link of shell.querySelectorAll('[data-curiosity-key]')) {
            const active = (values[link.dataset.curiosityKey] ?? []).includes(link.dataset.curiosityValue);
            if (active) link.dataset.selected = 'true'; else link.removeAttribute('data-selected');
            link.setAttribute('aria-label', link.dataset.curiosityLabel + (active ? ' · Filter aktif' : ''));
        }
    }
    function cancelEntrances() { entrances.forEach(a => a.cancel()); entrances.clear(); }
    function revealResults() {
        if (reduced.matches || document.hidden) return;
        const duration = durationMilliseconds(getComputedStyle(root).getPropertyValue('--motion-standard'));
        const visible = [...root.querySelectorAll('.results > article, .memory-reel .empty-state')].filter(node => {
            const rect = node.getBoundingClientRect(); return rect.top < innerHeight && rect.bottom > 0;
        }).slice(0, 4);
        for (const node of visible) {
            if (!node.animate) continue;
            const animation = node.animate([{ opacity: .7, transform: 'translateY(4px)' }, { opacity: 1, transform: 'translateY(0)' }], { duration, easing: 'cubic-bezier(.2,.72,.22,1)' });
            entrances.add(animation); animation.finished.catch(() => {}).finally(() => entrances.delete(animation));
        }
    }
    function bindDrawer() {
        drawer?.destroy();
        drawer = initializeFilterDrawer(root.querySelector('[data-filter-drawer]'), url => navigate(url));
    }
    async function navigate(url, restore = false, scroll = false, source = null) {
        url = new URL(url, location.href).href;
        controller?.abort(); controller = new AbortController();
        const current = controller, token = ++sequence;
        cancelEntrances(); pendingLink?.removeAttribute('data-pending');
        pendingLink = source?.closest('[data-curiosity-key]');
        if (pendingLink) pendingLink.dataset.pending = 'true';
        retry = () => navigate(url, restore, scroll, source);
        const results = root.querySelector('[data-discovery-results]');
        results.setAttribute('aria-busy', 'true');
        status('Memuat catatan…');
        root.querySelector('[data-discovery-error]').hidden = true;
        const timeout = setTimeout(() => current.abort(), 15000);
        const focused = document.activeElement;
        const focusId = focused?.id;
        const wasTab = focused?.closest('.discovery-tabs');
        const wasView = focused?.closest('.discovery-view');
        const wasInside = root.contains(focused);
        try {
            const response = await fetch(url, { headers: { 'X-Explore-Fragment': '1' }, signal: current.signal });
            if (!response.ok) throw new Error('Browse failed');
            const next = new DOMParser().parseFromString(await response.text(), 'text/html').querySelector('[data-discovery]');
            const selectors = ['[data-discovery-console]', '[data-discovery-results]', '[data-filter-drawer]'];
            if (!next || !['visit', 'food', 'place'].includes(next.dataset.mode) || selectors.some(selector => !next.querySelector(selector)) || !next.querySelector('.memory-reel__head > p') || !next.querySelector('[data-filter-form]')) throw new Error('Incomplete browse fragment');
            const values = JSON.parse(next.dataset.browseState);
            if (values?.tab !== next.dataset.mode || [...shell.querySelectorAll('[data-curiosity-key]')].some(link => values[link.dataset.curiosityKey] !== undefined && !Array.isArray(values[link.dataset.curiosityKey]))) throw new Error('Invalid browse state');
            if (token !== sequence || current.signal.aborted) return;
            const restoreFocus = focused === document.activeElement || document.activeElement === document.body;
            photos.forEach((entry, image) => { if (root.contains(image)) entry.stop(); });
            drawer?.destroy();
            for (const selector of selectors) {
                root.querySelector(selector).replaceWith(document.importNode(next.querySelector(selector), true));
            }
            root.dataset.mode = next.dataset.mode;
            root.dataset.browseState = next.dataset.browseState;
            reflectBoard(values);
            initializeDialogs(root); initializeForms(root); enableControls(root); initializeVisitCards(root); initializeImageFallbacks(root); bindDrawer();
            if (restore) history.replaceState({}, '', url);
            else if (url !== committed) history.pushState({}, '', url);
            committed = url;
            document.querySelector('meta[name=robots]')?.setAttribute('content', new URL(url).search ? 'noindex,follow' : 'index,follow');
            status(root.querySelector('.memory-reel__head > p').textContent.trim() + ' tersedia.');
            if (scroll) root.querySelector('[data-discovery-console]').scrollIntoView({ block: 'start', behavior: 'instant' });
            if (restoreFocus) {
                const target = scroll ? root.querySelector('#memory-reel-title')
                    : (focusId && document.getElementById(focusId)) || (wasTab && root.querySelector('.discovery-tabs [aria-current=page]')) || (wasView && root.querySelector('.discovery-view [aria-current=page]')) || (wasInside && root.querySelector('#memory-reel-title'));
                target?.focus({ preventScroll: true });
            }
            watchPhotos(root); revealResults();
        } catch {
            if (token !== sequence) return;
            if (restore) history.replaceState({}, '', committed);
            root.querySelector('[data-discovery-error]').hidden = false;
            status('Pembaruan gagal. Hasil sebelumnya tetap tersedia.');
        } finally {
            clearTimeout(timeout);
            if (token === sequence) {
                root.querySelector('[data-discovery-results]').setAttribute('aria-busy', 'false');
                pendingLink?.removeAttribute('data-pending'); pendingLink = null;
            }
        }
    }
    shell.addEventListener('click', event => {
        if (event.target.closest('[data-discovery-retry]')) { retry?.(); return; }
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') return;
        const url = new URL(link.href);
        if (url.origin !== location.origin || url.pathname !== '/explore' || url.hash) return;
        event.preventDefault(); navigate(url.href, false, !!link.closest('.curiosity-index'), link);
    });
    root.addEventListener('submit', event => {
        if (!event.target.matches('[data-discovery-search]')) return;
        event.preventDefault();
        const url = new URL(event.target.action); url.search = new URLSearchParams(new FormData(event.target));
        navigate(url.href);
    });
    root.addEventListener('change', event => { if (event.target.matches('#discovery-sort')) event.target.form.requestSubmit(); });
    window.addEventListener('popstate', () => navigate(location.href, true));
    window.addEventListener('pagehide', () => {
        sequence++; controller?.abort(); drawer?.destroy(); cancelEntrances();
        pendingLink?.removeAttribute('data-pending'); root.querySelector('[data-discovery-results]').setAttribute('aria-busy', 'false');
        photos.forEach(entry => entry.stop()); photoObserver?.disconnect();
    });
    window.addEventListener('pageshow', event => { if (event.persisted) { watchPhotos(shell); navigate(location.href, true); } });
    reduced.addEventListener('change', () => { if (reduced.matches) cancelEntrances(); });
    document.addEventListener('visibilitychange', () => { if (document.hidden) cancelEntrances(); });
    root.dataset.enhanced = 'true'; bindDrawer(); watchPhotos(shell);
}
