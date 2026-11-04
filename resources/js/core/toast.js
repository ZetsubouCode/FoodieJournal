export class ToastService {
    constructor(container) { this.container = container; }
    show(message, timeout = 6500) {
        if (!this.container) return;
        while (this.container.children.length >= 3) this.container.firstElementChild.remove();
        const node = document.createElement('div'); node.className = 'toast';
        const text = document.createElement('p'); text.textContent = message;
        const dismiss = document.createElement('button'); dismiss.className = 'btn btn--tertiary';
        dismiss.type = 'button'; dismiss.textContent = '×'; dismiss.setAttribute('aria-label', 'Tutup pemberitahuan');
        let timer; const pause = () => clearTimeout(timer); const resume = () => { pause(); timer = setTimeout(() => node.remove(), timeout); };
        dismiss.addEventListener('click', () => { pause(); node.remove(); });
        node.addEventListener('mouseenter', pause); node.addEventListener('mouseleave', resume);
        node.addEventListener('focusin', pause); node.addEventListener('focusout', resume);
        node.append(text, dismiss); this.container.append(node); resume();
    }
}
