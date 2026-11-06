import { closeDialog } from '../core/dialog';

export function initializeFilterDrawer(dialog, navigate) {
    const form = dialog.querySelector('[data-filter-form]');
    const apply = form.querySelector('[data-filter-apply]');
    const status = form.querySelector('[data-filter-status]');
    const fields = [...form.querySelectorAll('input[type=checkbox], input[type=number]')];
    let snapshot, applied = false, timer, controller, generation = 0;
    const initialApply = apply.textContent;
    const selectGroup = key => {
        form.querySelectorAll('[data-filter-group]').forEach(panel => { panel.hidden = panel.dataset.filterGroup !== key; });
        form.querySelectorAll('[data-filter-group-open]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.filterGroupOpen === key)));
        form.querySelector('.archive-filter__content').scrollTop = 0;
    };
    const refresh = () => {
        const selected = form.querySelector('[data-filter-selected]');
        selected.replaceChildren();
        fields.filter(field => field.type === 'checkbox' ? field.checked : field.value !== '').forEach(field => {
            const label = field.type === 'checkbox' ? field.closest('label').textContent.trim()
                : form.querySelector('label[for="' + field.id + '"]')?.textContent + ': ' + field.value;
            const button = document.createElement('button');
            button.type = 'button'; button.className = 'archive-filter__chip';
            button.textContent = label + ' ×'; button.setAttribute('aria-label', 'Hapus pilihan ' + label);
            button.addEventListener('click', () => { if (field.type === 'checkbox') field.checked = false; else field.value = ''; changed(); });
            selected.append(button);
        });
        form.querySelectorAll('[data-filter-group]').forEach(panel => {
            const total = [...panel.querySelectorAll('input[type=checkbox], input[type=number]')].filter(field => field.type === 'checkbox' ? field.checked : field.value !== '').length;
            form.querySelector('[data-group-count="' + panel.dataset.filterGroup + '"]').textContent = total || '—';
        });
        form.querySelector('.archive-filter__pending').textContent = selected.childElementCount ? selected.childElementCount + ' pilihan belum diterapkan' : 'Belum ada kriteria tambahan';
    };
    const count = async () => {
        controller?.abort(); controller = new AbortController(); const current = ++generation;
        form.setAttribute('aria-busy', 'true'); status.textContent = 'Menghitung hasil…';
        try {
            const url = new URL(form.dataset.countUrl, location.origin);
            url.search = new URLSearchParams(new FormData(form));
            const response = await fetch(url, { signal: controller.signal, headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error();
            const data = await response.json(); if (current !== generation) return;
            apply.textContent = 'Tampilkan ' + data.total + ' hasil'; status.textContent = '';
        } catch (error) {
            if (current === generation && error.name !== 'AbortError') { status.textContent = 'Jumlah belum dapat dimuat. Filter tetap dapat diterapkan.'; apply.textContent = 'Tampilkan hasil'; }
        } finally { if (current === generation) form.removeAttribute('aria-busy'); }
    };
    function changed() { refresh(); clearTimeout(timer); timer = setTimeout(count, 200); }
    dialog.addEventListener('dialog:open', () => {
        applied = false;
        snapshot = fields.map(field => ({ field, checked: field.checked, value: field.value }));
        refresh();
    });
    dialog.addEventListener('close', () => {
        clearTimeout(timer); controller?.abort(); generation++;
        if (!applied) {
            snapshot?.forEach(saved => { saved.field.checked = saved.checked; saved.field.value = saved.value; });
            apply.textContent = initialApply;
        }
        status.textContent = ''; form.removeAttribute('aria-busy'); refresh();
    });
    form.addEventListener('change', changed);
    form.addEventListener('click', event => {
        const group = event.target.closest('[data-filter-group-open]');
        if (group) selectGroup(group.dataset.filterGroupOpen);
        const reset = event.target.closest('[data-filter-reset], [data-filter-group-reset]');
        if (reset) {
            const scope = reset.hasAttribute('data-filter-reset') ? form : form.querySelector('[data-filter-group="' + reset.dataset.filterGroupReset + '"]');
            scope.querySelectorAll('input[type=checkbox], input[type=number]').forEach(field => { field.checked = false; if (field.type === 'number') field.value = ''; });
            changed();
        }
    });
    form.addEventListener('input', event => {
        if (!event.target.matches('[data-filter-search]')) return;
        const group = event.target.closest('[data-filter-dimension]');
        const value = event.target.value.trim().toLocaleLowerCase('id');
        const choices = [...group.querySelectorAll('[data-filter-option]')];
        choices.forEach(option => { option.hidden = !option.textContent.toLocaleLowerCase('id').includes(value); });
        group.querySelector('[data-filter-no-options]').hidden = choices.some(option => !option.hidden);
    });
    form.addEventListener('submit', event => {
        event.preventDefault(); applied = true;
        const url = new URL(form.action); url.search = new URLSearchParams(new FormData(form));
        closeDialog(dialog); navigate(url.href);
    });
    refresh();
    return { destroy() { clearTimeout(timer); controller?.abort(); generation++; } };
}
