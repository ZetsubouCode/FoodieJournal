import { initializeDialogs } from './core/dialog';
import { initializeForms, enableControls } from './core/forms';
import { ToastService } from './core/toast';
import { initializeImageFallbacks } from './components/image-fallback';

initializeDialogs();
initializeForms();
initializeImageFallbacks();
const container = document.querySelector('[data-toast-container]');
export const toast = new ToastService(container);
if (container?.dataset.message) toast.show(container.dataset.message);
const pending = [];
if (document.querySelector('[data-combobox]')) pending.push(import('./components/combobox').then(m => m.initializeComboboxes()));
if (document.querySelector('[data-editor]')) pending.push(import('./admin/editor').then(m => m.initializeEditor()));
if (document.querySelector('[data-home-archive]')) pending.push(import('./pages/archive').then(m => m.initializeArchive()));
if (document.querySelector('[data-discovery]')) pending.push(import('./pages/explore').then(m => m.initializeExplore()));
if (document.querySelector('[data-photo-viewer]')) pending.push(import('./components/viewer').then(m => m.initializeViewer()));
if (document.querySelector('[data-visit-card]')) pending.push(import('./components/visit-card').then(m => m.initializeVisitCards()));
if (document.querySelector('[data-visit-carousel]')) pending.push(import('./components/visit-carousel').then(m => m.initializeVisitCarousels()));
Promise.all(pending).then(() => { document.documentElement.dataset.uiReady = 'true'; enableControls(); }).catch(() => {
    const error = document.createElement('p'); error.className = 'alert alert--error'; error.role = 'alert';
    error.textContent = 'Kontrol interaktif gagal dimuat. Simpan atau salin isian sebelum memuat ulang halaman.'; document.querySelector('main').prepend(error);
});
