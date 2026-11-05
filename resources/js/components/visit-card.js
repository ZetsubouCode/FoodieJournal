const overflowRoots = new Set();
let outsideListenerReady = false;

function closeOverflow(root, restoreFocus = false) {
    const button = root.querySelector('[data-card-overflow-button]');
    const panel = root.querySelector('[data-card-overflow-panel]');
    if (panel.hidden) return;
    panel.hidden = true;
    button.setAttribute('aria-expanded', 'false');
    if (restoreFocus) button.focus();
}

export function initializeVisitCards(scope = document) {
    for (const card of scope.querySelectorAll('[data-visit-card]:not([data-visit-card-ready])')) {
        card.dataset.visitCardReady = 'true';
        const image = card.querySelector('img[data-visit-card-image]');
        const fallback = card.querySelector('[data-visit-card-image-fallback]');
        if (image && fallback) {
            const showFallback = () => { image.hidden = true; fallback.hidden = false; };
            image.addEventListener('error', showFallback, { once: true });
            if (image.complete && image.naturalWidth === 0) showFallback();
        }
    }
    const overflows = [...scope.querySelectorAll('[data-card-overflow]:not([data-overflow-ready])')];
    if (!overflows.length) return;

    for (const root of overflows) {
        root.dataset.overflowReady = 'true';
        overflowRoots.add(root);
        const button = root.querySelector('[data-card-overflow-button]');
        const panel = root.querySelector('[data-card-overflow-panel]');
        button.addEventListener('click', () => {
            const opening = panel.hidden;
            overflowRoots.forEach(other => { if (!other.isConnected) overflowRoots.delete(other); else closeOverflow(other); });
            panel.hidden = !opening;
            button.setAttribute('aria-expanded', String(opening));
            if (opening) panel.querySelector('a')?.focus();
        });
        root.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !panel.hidden) {
                event.preventDefault();
                closeOverflow(root, true);
            }
        });
        root.addEventListener('focusout', event => {
            if (!root.contains(event.relatedTarget)) closeOverflow(root);
        });
    }
    if (!outsideListenerReady) {
        outsideListenerReady = true;
        document.addEventListener('pointerdown', event => {
            overflowRoots.forEach(root => { if (!root.isConnected) overflowRoots.delete(root); else if (!root.contains(event.target)) closeOverflow(root); });
        });
    }
}
