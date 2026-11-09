import { openDialog } from '../core/dialog';

export function initializeViewer() {
    const dialog = document.querySelector('[data-photo-viewer]'); const buttons = [...document.querySelectorAll('[data-view-photo]')];
    let image = dialog.querySelector('[data-viewer-image]');
    const status = dialog.querySelector('[data-viewer-status]'); const stage = dialog.querySelector('[data-viewer-stage]');
    const retry = dialog.querySelector('[data-viewer-retry]'); const prev = dialog.querySelector('[data-viewer-prev]'); const next = dialog.querySelector('[data-viewer-next]');
    const zoom = dialog.querySelector('[data-viewer-zoom]');
    let index = 0;
    const load = () => {
        const photo = buttons[index]; let fallback = false;
        if (document.activeElement === retry) dialog.querySelector('[data-dialog-close]').focus();
        retry.hidden = true; zoom.disabled = true; zoom.setAttribute('aria-pressed', 'false');
        // A detached previous request must never commit into the current photo.
        const pending = image.cloneNode(false);
        image.removeAttribute('src'); image.replaceWith(pending); image = pending;
        image.hidden = true; image.classList.remove('viewer-zoom'); image.alt = photo.dataset.alt;
        stage.setAttribute('aria-busy', 'true');
        buttons.forEach(button => button.removeAttribute('aria-current')); photo.setAttribute('aria-current', 'true');
        status.textContent = `Memuat original ${index + 1} dari ${buttons.length}…`;
        image.onload = () => {
            if (image !== pending || !dialog.open) return;
            image.hidden = false; zoom.disabled = false; stage.setAttribute('aria-busy', 'false');
            status.textContent = fallback ? 'Original tidak dapat dimuat. Menampilkan pratinjau; coba lagi atau tutup.' : `Foto ${index + 1} dari ${buttons.length}`;
        };
        image.onerror = () => {
            if (image !== pending || !dialog.open) return;
            retry.hidden = false;
            if (!fallback) { fallback = true; status.textContent = 'Original tidak dapat dimuat. Memuat pratinjau…'; image.src = photo.dataset.fallback; }
            else { image.hidden = true; stage.setAttribute('aria-busy', 'false'); status.textContent = 'Foto tidak tersedia. Akses mungkin telah dicabut. Coba lagi atau tutup.'; }
        };
        image.src = photo.dataset.original;
        prev.disabled = index === 0; next.disabled = index === buttons.length - 1;
    };
    buttons.forEach((button, i) => button.addEventListener('click', () => { index = i; openDialog(dialog, button); load(); }));
    prev.addEventListener('click', () => { if (index > 0) { index--; load(); } }); next.addEventListener('click', () => { if (index < buttons.length - 1) { index++; load(); } });
    retry.addEventListener('click', load);
    zoom.addEventListener('click', () => zoom.setAttribute('aria-pressed', String(image.classList.toggle('viewer-zoom'))));
    dialog.addEventListener('keydown', event => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName) || event.target.isContentEditable) return;
        if (event.key === 'ArrowLeft') { event.preventDefault(); prev.click(); }
        if (event.key === 'ArrowRight') { event.preventDefault(); next.click(); }
    });
    dialog.addEventListener('close', () => {
        image.onload = image.onerror = null; image.removeAttribute('src'); image.hidden = true;
        stage.setAttribute('aria-busy', 'false'); buttons.forEach(button => button.removeAttribute('aria-current'));
    });
}
