export function initializeVisitCards() {
    for (const card of document.querySelectorAll('[data-visit-card]')) {
        const image = card.querySelector('img[data-visit-card-image]');
        const fallback = card.querySelector('[data-visit-card-image-fallback]');
        if (image && fallback) {
            const showFallback = () => { image.hidden = true; fallback.hidden = false; };
            image.addEventListener('error', showFallback, { once: true });
            if (image.complete && image.naturalWidth === 0) showFallback();
        }
    }
    const overflows = [...document.querySelectorAll('[data-card-overflow]')];
    if (!overflows.length) return;

    const close = (root, restoreFocus = false) => {
        const button = root.querySelector('[data-card-overflow-button]');
        const panel = root.querySelector('[data-card-overflow-panel]');
        if (panel.hidden) return;
        panel.hidden = true;
        button.setAttribute('aria-expanded', 'false');
        if (restoreFocus) button.focus();
    };

    for (const root of overflows) {
        const button = root.querySelector('[data-card-overflow-button]');
        const panel = root.querySelector('[data-card-overflow-panel]');
        button.addEventListener('click', () => {
            const opening = panel.hidden;
            overflows.forEach(other => close(other));
            panel.hidden = !opening;
            button.setAttribute('aria-expanded', String(opening));
            if (opening) panel.querySelector('a')?.focus();
        });
        root.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !panel.hidden) {
                event.preventDefault();
                close(root, true);
            }
        });
        root.addEventListener('focusout', event => {
            if (!root.contains(event.relatedTarget)) close(root);
        });
    }
    document.addEventListener('pointerdown', event => {
        overflows.forEach(root => { if (!root.contains(event.target)) close(root); });
    });
}
