import { openDialog, closeDialog } from '../core/dialog';

export function initializeComboboxes(root = document) {
    root.querySelectorAll('[data-combobox]:not([data-combo-ready])').forEach(element => {
        element.dataset.comboReady = 'true';
        const input = element.querySelector('[data-combo-input]');
        const list = element.querySelector('[data-combo-list]');
        const status = element.querySelector('[data-combo-status]');
        const container = element.querySelector('[data-combo-selected]');
        const multiple = element.dataset.multiple === 'true';
        const initial = JSON.parse(element.dataset.selected || '[]');
        let selected = (Array.isArray(initial) ? initial : (initial ? [initial] : [])).map(value => typeof value === 'object' ? value : { id: value, label: `Pilihan #${value}`, needsLabel: true });
        let options = [], active = -1, timer, controller, sequence = 0;
        const hide = () => { list.hidden = true; input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); };
        const renderSelected = (notify = true) => {
            container.replaceChildren();
            selected.forEach(option => {
                const value = document.createElement('input'); value.type = 'hidden'; value.name = element.dataset.name + (multiple ? '[]' : ''); value.value = option.id;
                const chip = document.createElement('button'); chip.type = 'button'; chip.className = 'chip'; chip.textContent = `${option.label} ×`; chip.setAttribute('aria-label', `Hapus pilihan ${option.label}`);
                chip.addEventListener('click', () => { selected = selected.filter(s => String(s.id) !== String(option.id)); renderSelected(); input.focus(); });
                container.append(value, chip);
            });
            if (notify) element.dispatchEvent(new CustomEvent('selectionchange', { bubbles: true, detail: selected }));
        };
        const choose = option => {
            if (!multiple) selected = [];
            if (!selected.some(s => String(s.id) === String(option.id))) selected.push(option);
            renderSelected(); input.value = ''; hide(); input.focus();
            element.dispatchEvent(new Event('change', { bubbles: true }));
        };
        const activate = index => {
            active = index; list.querySelectorAll('[role=option]').forEach((node, i) => node.setAttribute('aria-selected', String(i === active)));
            const node = list.children[active]; if (node) { input.setAttribute('aria-activedescendant', node.id); node.scrollIntoView({ block: 'nearest' }); }
        };
        const search = async () => {
            controller?.abort(); controller = new AbortController(); const current = ++sequence;
            status.textContent = 'Mencari…'; element.setAttribute('aria-busy', 'true');
            try {
                const url = new URL(element.dataset.endpoint, location.origin); url.searchParams.set('q', input.value.slice(0, 120));
                const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
                if (!response.ok) throw new Error('Pencarian gagal. Coba ketik lagi.');
                const data = await response.json(); if (current !== sequence) return;
                options = data.results; active = -1; list.replaceChildren();
                options.forEach((option, index) => {
                    const node = document.createElement('div'); node.id = `${list.id}-${index}`; node.className = 'combobox-option'; node.role = 'option'; node.setAttribute('aria-selected', 'false'); node.textContent = option.label;
                    node.addEventListener('pointerdown', event => { event.preventDefault(); choose(option); }); list.append(node);
                });
                status.textContent = options.length ? `${options.length} pilihan. Gunakan panah dan Enter.` : 'Tidak ada pilihan yang cocok.';
                list.hidden = options.length === 0; input.setAttribute('aria-expanded', String(options.length > 0));
            } catch (error) { if (error.name !== 'AbortError') { status.textContent = error.message; hide(); } }
            finally { if (current === sequence) element.removeAttribute('aria-busy'); }
        };
        input.addEventListener('focus', search);
        input.addEventListener('input', () => { clearTimeout(timer); controller?.abort(); sequence++; timer = setTimeout(search, 220); });
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') { hide(); event.stopPropagation(); }
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault(); if (list.hidden) search(); else activate(active < 0 ? (event.key === 'ArrowDown' ? 0 : options.length - 1) : (active + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length);
            }
            if (event.key === 'Enter' && !list.hidden && active >= 0) { event.preventDefault(); choose(options[active]); }
            if (event.key === 'Tab') hide();
        });
        element.addEventListener('focusout', () => setTimeout(() => { if (!element.contains(document.activeElement)) hide(); }, 0));
        renderSelected(false);
        if (selected.some(option => option.needsLabel)) {
            const url = new URL(element.dataset.endpoint, location.origin); selected.forEach(s => url.searchParams.append('ids[]', s.id));
            fetch(url, { headers: { Accept: 'application/json' } }).then(r => r.ok ? r.json() : Promise.reject()).then(data => { selected = selected.map(s => data.results.find(o => String(o.id) === String(s.id)) || s); renderSelected(false); }).catch(() => { status.textContent = 'Nama pilihan belum dimuat. Pilihan tersimpan tetap dipertahankan.'; });
        }
        element.querySelector('[data-combo-create]')?.addEventListener('click', () => {
            const dialog = document.getElementById('inline-create'); const form = dialog.querySelector('form');
            form.reset(); form.dataset.kind = element.dataset.kind; form._onCreated = choose;
            form.elements.namedItem('inline_name').value = input.value;
            form.querySelector('[data-inline-place]').hidden = element.dataset.kind !== 'place';
            form.querySelector('[data-inline-cuisine]').hidden = element.dataset.kind !== 'cuisine';
            form.querySelector('[data-inline-error]').hidden = true;
            openDialog(dialog);
        });
    });
    const form = document.querySelector('[data-inline-form]');
    if (form && !form.dataset.inlineReady) {
        form.dataset.inlineReady = 'true';
        form.addEventListener('submit', async event => {
            event.preventDefault(); const button = form.querySelector('[type=submit]'); if (button.disabled) return; button.disabled = true;
            const error = form.querySelector('[data-inline-error]'); error.hidden = true;
            try {
                const endpoint = form.dataset.kind === 'place' ? '/admin/place' : `/admin/taxonomy/${form.dataset.kind}`;
                const body = new FormData(form); body.set('name', body.get('inline_name')); body.set('city_name', body.get('inline_city_name'));
                const response = await fetch(endpoint, { method: 'POST', body, headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
                const data = await response.json(); if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || 'Tidak dapat menyimpan. Muat ulang sesi jika sudah kedaluwarsa.');
                closeDialog(form.closest('dialog')); form._onCreated(data);
            } catch (failure) { error.textContent = failure.message; error.hidden = false; error.tabIndex = -1; error.focus(); }
            finally { button.disabled = false; }
        });
    }
}
