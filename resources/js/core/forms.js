import { confirmAction } from './dialog';

export function initializeForms(root = document) {
    if (document.documentElement.dataset.uiReady === 'true') enableControls(root);
    root.querySelectorAll('form:not([data-form-ready])').forEach(form => {
        form.dataset.formReady = 'true';
        form.addEventListener('input', () => { if (form.hasAttribute('data-unsaved')) form.dataset.dirty = 'true'; });
        form.addEventListener('change', () => { if (form.hasAttribute('data-unsaved')) form.dataset.dirty = 'true'; });
        form.addEventListener('submit', event => {
            if (form.dataset.submitting === 'true') { event.preventDefault(); return; }
            const otherDirty = document.querySelector('form[data-dirty=true]');
            if (otherDirty && otherDirty !== form && !form.hasAttribute('data-async')) {
                event.preventDefault(); const submitter = event.submitter;
                confirmAction('Ada isian editor yang belum disimpan. Lanjutkan tindakan ini dan tinggalkan perubahan?', () => { otherDirty.dataset.dirty = 'false'; form.requestSubmit(submitter); });
                return;
            }
            const message = event.submitter?.dataset.confirm || form.dataset.confirm;
            if (message && form.dataset.confirmed !== 'true') {
                event.preventDefault(); const submitter = event.submitter;
                confirmAction(message, () => { form.dataset.confirmed = 'true'; form.requestSubmit(submitter); });
                return;
            }
            if (form.method.toLowerCase() !== 'get' && !form.hasAttribute('data-async')) {
                form.dataset.submitting = 'true'; form.dataset.dirty = 'false';
                if (event.submitter?.name) {
                    const value = document.createElement('input'); value.type = 'hidden'; value.name = event.submitter.name; value.value = event.submitter.value; form.append(value);
                }
                form.querySelectorAll('[type=submit]').forEach(button => { button.disabled = true; button.setAttribute('aria-busy', 'true'); });
            }
        });
    });
    document.querySelector('[data-error-summary]')?.focus();
    root.querySelectorAll('a[href]:not([data-link-ready])').forEach(link => {
        link.dataset.linkReady = 'true';
        link.addEventListener('click', event => {
            const dirty = document.querySelector('form[data-dirty=true]');
            if (dirty && !link.getAttribute('href').startsWith('#') && !event.ctrlKey && !event.metaKey) {
                event.preventDefault(); confirmAction('Ada perubahan yang belum disimpan. Tinggalkan halaman?', () => { dirty.dataset.dirty = 'false'; location.assign(link.href); });
            }
        });
    });
}

export function enableControls(root = document) {
    root.querySelectorAll('[data-js-control]:not([data-preserve-disabled])').forEach(button => { button.disabled = false; });
}
