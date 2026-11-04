import { initializeDialogs } from './core/dialog';
import { initializeForms, enableControls } from './core/forms';
import { ToastService } from './core/toast';

initializeDialogs();
initializeForms();
const container = document.querySelector('[data-toast-container]');
export const toast = new ToastService(container);
if (container?.dataset.message) toast.show(container.dataset.message);
const pending = [];
if (document.querySelector('[data-combobox]')) pending.push(import('./components/combobox').then(m => m.initializeComboboxes()));
if (document.querySelector('[data-editor]')) pending.push(import('./admin/editor').then(m => m.initializeEditor()));
if (document.querySelector('[data-filter-form]')) pending.push(import('./pages/explore').then(m => m.initializeExplore()));
if (document.querySelector('[data-photo-viewer]')) pending.push(import('./components/viewer').then(m => m.initializeViewer()));
Promise.all(pending).then(() => { document.documentElement.dataset.uiReady = 'true'; enableControls(); }).catch(() => {
    const error = document.createElement('p'); error.className = 'alert alert--error'; error.role = 'alert';
    error.textContent = 'Kontrol interaktif gagal dimuat. Simpan atau salin isian sebelum memuat ulang halaman.'; document.querySelector('main').prepend(error);
});
