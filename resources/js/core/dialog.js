const triggers = new WeakMap();

export function openDialog(dialog, trigger = document.activeElement) {
    if (!dialog || dialog.open) return;
    triggers.set(dialog, trigger);
    dialog.showModal();
    document.body.classList.add('dialog-open');
    dialog.dispatchEvent(new Event('dialog:open'));
}

export function closeDialog(dialog) {
    dialog?.close();
}

export function initializeDialogs(root = document) {
    root.querySelectorAll('dialog:not([data-dialog-ready])').forEach(dialog => {
        dialog.dataset.dialogReady = 'true';
        dialog.addEventListener('keydown', event => {
            if (event.key !== 'Tab') return;
            const controls = [...dialog.querySelectorAll('button:not(:disabled), a[href], input:not(:disabled):not([type=hidden]), select:not(:disabled), textarea:not(:disabled), summary, [tabindex]:not([tabindex="-1"])')].filter(node => node.getClientRects().length && !node.closest('[hidden]'));
            const first = controls[0], last = controls.at(-1);
            if (!first) { event.preventDefault(); dialog.focus(); return; }
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });
        dialog.addEventListener('close', () => {
            if (!document.querySelector('dialog[open]')) document.body.classList.remove('dialog-open');
            triggers.get(dialog)?.focus();
        });
        dialog.addEventListener('click', event => {
            if (event.target.closest('[data-dialog-close]')) closeDialog(dialog);
            if (event.target === dialog) {
                const box = dialog.getBoundingClientRect();
                if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) closeDialog(dialog);
            }
        });
    });
    root.querySelectorAll('[data-dialog-open]:not([data-trigger-ready])').forEach(trigger => {
        trigger.dataset.triggerReady = 'true';
        trigger.addEventListener('click', () => openDialog(document.getElementById(trigger.dataset.dialogOpen), trigger));
    });
}

export function confirmAction(message, action) {
    const dialog = document.getElementById('confirm-dialog');
    dialog.querySelector('[data-confirm-message]').textContent = message;
    const button = dialog.querySelector('[data-confirm-accept]');
    button.onclick = () => { closeDialog(dialog); action(); };
    openDialog(dialog);
}
