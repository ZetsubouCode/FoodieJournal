const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

function makeClone(slide) {
    const clone = slide.cloneNode(true);
    clone.removeAttribute('data-carousel-slide');
    clone.setAttribute('aria-hidden', 'true');
    clone.inert = true;
    clone.querySelectorAll('[id]').forEach(node => node.removeAttribute('id'));
    clone.querySelectorAll('[aria-labelledby], [aria-controls]').forEach(node => {
        node.removeAttribute('aria-labelledby');
        node.removeAttribute('aria-controls');
    });
    return clone;
}

function initializeCarousel(root) {
    if (root.dataset.carouselReady) return;
    root.dataset.carouselReady = 'true';
    const viewport = root.querySelector('[data-carousel-viewport]');
    const rail = root.querySelector('.visit-carousel__rail');
    const track = root.querySelector('[data-carousel-track]');
    const originals = [...root.querySelectorAll('[data-carousel-slide]')];
    if (originals.length < 2) return;
    const count = originals.length;
    const previous = root.querySelector('[data-carousel-prev]');
    const next = root.querySelector('[data-carousel-next]');
    const toggle = root.querySelector('[data-carousel-toggle]');
    const countLabel = root.querySelector('[data-carousel-count]');
    const status = root.querySelector('[data-carousel-status]');
    const interval = Math.max(4000, Number(root.dataset.interval) || 6500);
    const before = originals.map(makeClone);
    const after = originals.map(makeClone);
    // Boundary copies must be ready before the first/last wrap reaches them.
    [...before[count - 1].querySelectorAll('img'), ...after[0].querySelectorAll('img')]
        .forEach(image => { image.loading = 'eager'; });
    track.prepend(...before);
    track.append(...after);
    const slides = [...track.children];
    let position = count;
    let moving = false;
    let queuedStep;
    let timer;
    let visible = false;
    let paused = false;
    let dragging = false;
    let pointerStart;
    let suppressClick = false;

    const current = () => ((position - count) % count + count) % count;
    const mayPlay = () => !paused && !dragging && visible && !document.hidden && !reduceMotion.matches && !navigator.connection?.saveData && !rail.matches(':hover') && !rail.contains(document.activeElement);
    const syncControls = () => {
        const index = current();
        countLabel.textContent = `${index + 1} / ${count}`;
        originals.forEach((slide, i) => {
            const inactive = i !== index;
            if (inactive) slide.setAttribute('aria-hidden', 'true');
            else slide.removeAttribute('aria-hidden');
            slide.querySelectorAll('a, button').forEach(control => {
                if (inactive) control.setAttribute('tabindex', '-1');
                else control.removeAttribute('tabindex');
            });
        });
        toggle.hidden = reduceMotion.matches || Boolean(navigator.connection?.saveData);
        toggle.textContent = paused ? 'Putar otomatis' : 'Jeda otomatis';
        toggle.setAttribute('aria-label', `${paused ? 'Putar' : 'Jeda'} putar otomatis: ${root.getAttribute('aria-label')}`);
    };
    const place = (animated) => {
        track.style.transition = animated && !reduceMotion.matches ? 'transform var(--home-carousel-duration) ease-in-out' : 'none';
        const slide = slides[position];
        const offset = slide.offsetLeft - Math.max(0, (viewport.clientWidth - slide.clientWidth) / 2);
        track.style.transform = `translate3d(${-offset}px, 0, 0)`;
    };
    const stopTimer = () => { clearTimeout(timer); timer = undefined; };
    const schedule = () => {
        stopTimer();
        if (mayPlay()) timer = window.setTimeout(() => advance(1, false), interval);
    };
    const settle = () => {
        if (!moving) return;
        moving = false;
        if (position < count || position >= 2 * count) {
            position = count + current();
            place(false);
            // The visible pixels are identical; restore transition on the next paint.
            requestAnimationFrame(() => { track.style.transition = ''; });
        }
        syncControls();
        if (queuedStep) {
            const step = queuedStep;
            queuedStep = undefined;
            requestAnimationFrame(() => advance(step));
        } else schedule();
    };
    const advance = (step, announce = true) => {
        if (moving) { queuedStep = step; return; }
        stopTimer();
        position += step;
        moving = !reduceMotion.matches;
        place(moving);
        syncControls();
        if (announce) status.textContent = `Kunjungan ${current() + 1} dari ${count}`;
        if (!moving) {
            if (position < count || position >= 2 * count) {
                position = count + current();
                place(false);
            }
            schedule();
        }
    };

    track.addEventListener('transitionend', event => {
        if (event.target === track && event.propertyName === 'transform') settle();
    });
    previous.addEventListener('click', () => advance(-1));
    next.addEventListener('click', () => advance(1));
    toggle.addEventListener('click', () => { paused = !paused; syncControls(); schedule(); });
    rail.addEventListener('mouseenter', stopTimer);
    rail.addEventListener('mouseleave', schedule);
    rail.addEventListener('focusin', stopTimer);
    root.addEventListener('focusout', () => requestAnimationFrame(schedule));
    viewport.addEventListener('pointerdown', event => {
        if (event.target.closest('button, .visit-card__tag, .visit-card__recommendation, .visit-card__popover a')) return;
        pointerStart = { x: event.clientX, y: event.clientY };
        dragging = true;
        stopTimer();
    });
    viewport.addEventListener('pointermove', event => {
        if (pointerStart && Math.abs(event.clientX - pointerStart.x) > 12 && Math.abs(event.clientX - pointerStart.x) > Math.abs(event.clientY - pointerStart.y)) {
            viewport.setPointerCapture(event.pointerId);
        }
    });
    viewport.addEventListener('dragstart', event => event.preventDefault());
    viewport.addEventListener('pointerup', event => {
        if (!pointerStart) return;
        const dx = event.clientX - pointerStart.x;
        const dy = event.clientY - pointerStart.y;
        pointerStart = undefined;
        dragging = false;
        if (Math.abs(dx) > 35 && Math.abs(dx) > Math.abs(dy)) {
            suppressClick = true;
            advance(dx < 0 ? 1 : -1);
            window.setTimeout(() => { suppressClick = false; }, 350);
        } else schedule();
    });
    viewport.addEventListener('pointercancel', () => { pointerStart = undefined; dragging = false; schedule(); });
    viewport.addEventListener('click', event => {
        if (suppressClick) { event.preventDefault(); event.stopPropagation(); suppressClick = false; }
    }, true);
    document.addEventListener('visibilitychange', schedule);
    reduceMotion.addEventListener('change', () => { if (moving) settle(); place(false); syncControls(); schedule(); });
    let viewportWidth = viewport.clientWidth;
    new ResizeObserver(() => {
        if (viewport.clientWidth === viewportWidth) return;
        viewportWidth = viewport.clientWidth;
        if (moving) settle();
        place(false);
    }).observe(viewport);
    new IntersectionObserver(entries => {
        visible = entries[0].isIntersecting;
        schedule();
    }, { threshold: .15 }).observe(root);
    place(false);
    syncControls();
}

export function initializeVisitCarousels() {
    document.querySelectorAll('[data-visit-carousel]').forEach(initializeCarousel);
}
