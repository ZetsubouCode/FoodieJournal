import { openDialog } from '../core/dialog';

export function initializeViewer() {
    const dialog = document.querySelector('[data-photo-viewer]'); const buttons = [...document.querySelectorAll('[data-view-photo]')];
    const image = dialog.querySelector('[data-viewer-image]'); const status = dialog.querySelector('[data-viewer-status]');
    const retry = dialog.querySelector('[data-viewer-retry]'); const prev = dialog.querySelector('[data-viewer-prev]'); const next = dialog.querySelector('[data-viewer-next]');
    let index = 0, fallback = false;
    const load = () => {
        const photo = buttons[index]; fallback = false; retry.hidden = true; image.hidden = true; image.classList.remove('viewer-zoom');
        status.textContent = `Memuat original ${index + 1} dari ${buttons.length}…`; image.alt = photo.dataset.alt; image.src = photo.dataset.original;
        prev.disabled = index === 0; next.disabled = index === buttons.length - 1;
    };
    image.addEventListener('load', () => { image.hidden = false; status.textContent = fallback ? 'Original tidak dapat dimuat. Menampilkan pratinjau; coba lagi atau tutup.' : `Foto ${index + 1} dari ${buttons.length}`; });
    image.addEventListener('error', () => {
        retry.hidden = false;
        if (!fallback) { fallback = true; image.src = buttons[index].dataset.fallback; }
        else { image.hidden = true; status.textContent = 'Foto tidak tersedia. Akses mungkin telah dicabut. Coba lagi atau tutup.'; }
    });
    buttons.forEach((button, i) => button.addEventListener('click', () => { index = i; openDialog(dialog, button); load(); }));
    prev.addEventListener('click', () => { if (index > 0) { index--; load(); } }); next.addEventListener('click', () => { if (index < buttons.length - 1) { index++; load(); } });
    retry.addEventListener('click', load);
    dialog.querySelector('[data-viewer-zoom]').addEventListener('click', () => image.classList.toggle('viewer-zoom'));
    dialog.addEventListener('keydown', event => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)) return;
        if (event.key === 'ArrowLeft') { event.preventDefault(); prev.click(); }
        if (event.key === 'ArrowRight') { event.preventDefault(); next.click(); }
    });
    dialog.addEventListener('close', () => { image.removeAttribute('src'); image.hidden = true; });
}
