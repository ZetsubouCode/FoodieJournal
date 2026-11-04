import { initializeComboboxes } from '../components/combobox';
import { initializeForms } from '../core/forms';
import { confirmAction } from '../core/dialog';

export function initializeEditor() {
    const form = document.querySelector('[data-editor]');
    const items = form.querySelector('[data-items]');
    let index = Math.max(-1, ...Array.from(items?.querySelectorAll('[data-item] input[name$="[id]"]') || []).map(node => Number(node.name.match(/items\[(\d+)\]/)?.[1] || 0))) + 1;
    const dirty = () => { form.dataset.dirty = 'true'; form.querySelector('[data-save-state]').textContent = 'Ada perubahan belum disimpan.'; };
    form.addEventListener('input', dirty); form.addEventListener('change', dirty); form.addEventListener('selectionchange', dirty);
    form.querySelector('[data-add-item]')?.addEventListener('click', () => {
        const html = form.querySelector('[data-item-template]').innerHTML.replaceAll('__INDEX__', String(index++));
        items.insertAdjacentHTML('beforeend', html); const item = items.lastElementChild;
        initializeComboboxes(item); initializeForms(item); wireSources(item); dirty(); item.querySelector('input:not([type=hidden])')?.focus();
    });
    form.addEventListener('click', event => {
        if (event.target.closest('[data-remove-item]')) confirmAction('Hapus hidangan ini dari isian? Foto galeri tetap disimpan.', () => { event.target.closest('[data-item]').remove(); dirty(); });
        if (event.target.closest('[data-add-evidence]')) {
            const item = event.target.closest('[data-item]'); const list = item.querySelector('[data-evidence-list]');
            const next = Number(list.dataset.next || list.children.length); list.dataset.next = String(next + 1);
            list.insertAdjacentHTML('beforeend', item.querySelector('[data-evidence-template]').innerHTML.replaceAll('__EVIDENCE__', String(next)));
            initializeComboboxes(list.lastElementChild); initializeForms(list.lastElementChild); dirty();
        }
        if (event.target.closest('[data-remove-evidence]')) { event.target.closest('[data-evidence]').remove(); dirty(); }
    });
    const source = form.querySelector('[data-slug-source]'); const slug = form.querySelector('[data-slug-input]');
    let manual = slug.value !== '';
    slug.addEventListener('input', () => { manual = true; });
    source.addEventListener('input', () => {
        if (!manual && slug.dataset.existing !== 'true') {
            const suggested = source.value.normalize('NFKD').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 155);
            form.querySelector('[data-slug-preview]').textContent = suggested ? `/visit/${suggested} (suffix ditentukan saat simpan)` : 'Slug otomatis dari tempat dan waktu.';
        }
    });
    wireSources(form);
}

function wireSources(root) {
    root.querySelectorAll('[data-ingredient-sources]:not([data-sources-ready])').forEach(container => {
        container.dataset.sourcesReady = 'true'; const combo = container.previousElementSibling;
        const sources = JSON.parse(container.dataset.sources || '{}');
        const render = selected => {
            container.querySelectorAll('input').forEach(input => { sources[input.dataset.id] = input.value; }); container.replaceChildren();
            selected.forEach(option => {
                const label = document.createElement('label'); label.className = 'field'; label.textContent = `Sumber bahan: ${option.label}`;
                const input = document.createElement('input'); input.name = `items[${container.dataset.index}][ingredient_sources][${option.id}]`; input.dataset.id = option.id; input.value = sources[option.id] || ''; input.maxLength = 500;
                label.append(input); container.append(label);
            });
        };
        combo.addEventListener('selectionchange', event => render(event.detail));
        const selected = JSON.parse(combo.dataset.selected || '[]'); render((Array.isArray(selected) ? selected : []).map(id => typeof id === 'object' ? id : { id, label: `#${id}` }));
    });
}
