import { openDialog } from '../core/dialog';

export function initializeViewer() {
    const dialog = document.querySelector('[data-photo-viewer]');
    if (typeof dialog.showModal !== 'function') return;
    const photos = [...document.querySelectorAll('[data-view-photo]')];
    const photoIndexes = new Map(photos.map((photo, i) => [photo.dataset.viewPhoto, i]));
    let image = dialog.querySelector('[data-viewer-image]');
    const status = dialog.querySelector('[data-viewer-status]'); const stage = dialog.querySelector('[data-viewer-stage]');
    const retry = dialog.querySelector('[data-viewer-retry]'); const prev = dialog.querySelector('[data-viewer-prev]'); const next = dialog.querySelector('[data-viewer-next]');
    const zoom = dialog.querySelector('[data-viewer-zoom]');
    const fullscreen = dialog.querySelector('[data-viewer-fullscreen]');
    const fullscreenRoot = document.documentElement;
    const setExpanded = expanded => {
        dialog.toggleAttribute('data-viewer-expanded', expanded);
        fullscreen.setAttribute('aria-pressed', String(expanded));
    };
    const exitFullscreen = () => {
        if (document.fullscreenElement === fullscreenRoot) document.exitFullscreen().catch(() => {});
        setExpanded(false);
    };
    fullscreenRoot.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) setExpanded(false);
    });
    fullscreen.addEventListener('click', async () => {
        if (dialog.hasAttribute('data-viewer-expanded')) {
            exitFullscreen();
            return;
        }
        setExpanded(true);
        if (!document.fullscreenEnabled || !fullscreenRoot.requestFullscreen) return;
        try {
            await fullscreenRoot.requestFullscreen();
            if (!dialog.open || !dialog.hasAttribute('data-viewer-expanded')) exitFullscreen();
        } catch {
            // Keep the viewport-sized viewer when browser fullscreen is unavailable.
        }
    });
    let index = 0;
    let attempt = 0;
    const load = () => {
        const photo = photos[index]; let fallback = false;
        const attemptId = ++attempt;
        if (document.activeElement === retry) dialog.querySelector('[data-dialog-close]').focus();
        retry.hidden = true; zoom.disabled = true; zoom.setAttribute('aria-pressed', 'false');
        // A detached previous request must never commit into the current photo.
        image.onload = image.onerror = null;
        image.removeAttribute('src');
        const pending = image.cloneNode(false);
        image.replaceWith(pending); image = pending;
        image.hidden = true; image.classList.remove('viewer-zoom'); image.alt = photo.dataset.alt;
        stage.scrollTo(0, 0);
        stage.setAttribute('aria-busy', 'true');
        photos.forEach(link => link.removeAttribute('aria-current')); photo.setAttribute('aria-current', 'true');
        status.textContent = `Memuat original ${index + 1} dari ${photos.length}…`;
        image.onload = () => {
            if (image !== pending || !dialog.open) return;
            image.hidden = false; zoom.disabled = false; stage.setAttribute('aria-busy', 'false');
            status.textContent = fallback ? `Foto ${index + 1} dari ${photos.length}. Original tidak dapat dimuat. Menampilkan pratinjau; coba lagi atau tutup.` : `Foto ${index + 1} dari ${photos.length}`;
        };
        image.onerror = () => {
            if (image !== pending || !dialog.open) return;
            retry.hidden = false;
            if (!fallback) {
                fallback = true;
                status.textContent = 'Original tidak dapat dimuat. Memuat pratinjau…';
                // Recheck authorization instead of reusing the decoded thumbnail in this document.
                const derivative = new URL(photo.href);
                derivative.searchParams.set('viewer', attemptId);
                image.src = derivative.href;
            } else {
                image.hidden = true;
                stage.setAttribute('aria-busy', 'false');
                status.textContent = 'Foto tidak tersedia. Akses mungkin telah dicabut. Coba lagi atau tutup.';
            }
        };
        image.src = photo.dataset.original;
        const focused = document.activeElement;
        prev.disabled = index === 0; next.disabled = index === photos.length - 1;
        if (focused === prev && prev.disabled) next.focus();
        else if (focused === next && next.disabled) prev.focus();
    };
    const bind = (link, i) => {
        if (i === undefined) return;
        link.setAttribute('aria-haspopup', 'dialog');
        link.addEventListener('click', event => {
            if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            index = i;
            openDialog(dialog, link);
            load();
        });
    };
    photos.forEach(bind);
    document.querySelectorAll('[data-item-photo]').forEach(link => bind(link, photoIndexes.get(link.dataset.itemPhoto)));
    prev.addEventListener('click', () => { if (index > 0) { index--; load(); } }); next.addEventListener('click', () => { if (index < photos.length - 1) { index++; load(); } });
    retry.addEventListener('click', load);
    zoom.addEventListener('click', () => zoom.setAttribute('aria-pressed', String(image.classList.toggle('viewer-zoom'))));
    dialog.addEventListener('keydown', event => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName) || event.target.isContentEditable) return;
        if (event.key === 'ArrowLeft') { event.preventDefault(); prev.click(); }
        if (event.key === 'ArrowRight') { event.preventDefault(); next.click(); }
    });
    dialog.addEventListener('close', () => {
        exitFullscreen();
        image.onload = image.onerror = null; image.removeAttribute('src'); image.hidden = true;
        stage.setAttribute('aria-busy', 'false'); photos.forEach(link => link.removeAttribute('aria-current'));
    });
}
