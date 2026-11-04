import { openDialog } from '../core/dialog';

export function initializeExplore() {
    const form = document.querySelector('[data-filter-form]'); const dialog = form.closest('dialog');
    const apply = form.querySelector('[data-filter-apply]'); const status = form.querySelector('[data-filter-status]');
    let snapshot, applied = false, controller, timer, sequence = 0;
    const fields = [...form.querySelectorAll('input:not([type=hidden]), select')];
    dialog.addEventListener('dialog:open', () => {
        snapshot = fields.map(field => ({ field, value: field.value, checked: field.checked })); applied = false;
    });
    dialog.addEventListener('close', () => {
        clearTimeout(timer); controller?.abort(); sequence++;
        if (!applied) snapshot?.forEach(saved => { saved.field.value = saved.value; saved.field.checked = saved.checked; });
        form.removeAttribute('aria-busy'); status.textContent = '';
    });
    const count = async () => {
        controller?.abort(); controller = new AbortController(); const current = ++sequence;
        status.textContent = 'Menghitung hasil…'; form.setAttribute('aria-busy', 'true');
        try {
            const url = new URL(form.dataset.countUrl, location.origin); url.search = new URLSearchParams(new FormData(form));
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
            if (!response.ok) throw new Error(); const data = await response.json(); if (current !== sequence) return;
            apply.textContent = `Tampilkan ${data.total} hasil`; status.textContent = '';
        } catch (error) { if (error.name !== 'AbortError') { status.textContent = 'Jumlah hasil belum dapat dimuat. Tetap dapat menerapkan filter.'; apply.textContent = 'Tampilkan hasil'; } }
        finally { if (current === sequence) form.removeAttribute('aria-busy'); }
    };
    form.addEventListener('change', () => { clearTimeout(timer); timer = setTimeout(count, 220); });
    form.addEventListener('submit', () => { applied = true; });
    form.querySelector('[data-filter-reset]').addEventListener('click', () => {
        fields.forEach(field => { if (field.type === 'checkbox') field.checked = false; else field.value = ''; });
        form.querySelectorAll('[data-filter-option]').forEach(node => node.hidden = false); count();
    });
    form.querySelectorAll('[data-filter-search]').forEach(input => input.addEventListener('input', () => {
        input.closest('details').querySelectorAll('[data-filter-option]').forEach(option => { option.hidden = !option.textContent.toLocaleLowerCase('id').includes(input.value.toLocaleLowerCase('id')); });
    }));
    document.querySelectorAll('[data-filter-quick]').forEach(button => button.addEventListener('click', () => {
        openDialog(dialog, button); const group = form.querySelector(`[data-filter-dimension="${button.dataset.filterQuick}"]`);
        if (group) { group.open = true; group.querySelector('summary').focus(); }
    }));
}
