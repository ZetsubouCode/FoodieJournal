// The cabinet stays mounted, so this scheduler is independent of collection updates.
export function initializeArchiveLight(root) {
    const lamp = root.querySelector('[data-lamp-toggle]');
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    const compact = matchMedia('(max-width: 959px)');
    let timer, animations = [], stopped = false;
    const enabled = () => !stopped && !document.hidden && !reduced.matches && !compact.matches && lamp.getAttribute('aria-pressed') !== 'true';
    function cancel() {
        clearTimeout(timer);
        animations.forEach(animation => animation.cancel());
        animations = [];
    }
    function schedule() {
        if (!enabled()) return;
        // Fresh idle time for every cycle, rather than one repeating CSS cadence.
        timer = setTimeout(flicker, 6000 + Math.random() * 16000);
    }
    async function flicker() {
        if (!enabled()) return;
        const depth = .32 + Math.random() * .3;
        const shades = [0, depth, depth * .12, depth * .45, 0];
        const timing = { duration: 1100 + Math.random() * 500, easing: 'ease-in-out' };
        animations = [
            lamp.querySelector('.archive-lamp__shade').animate(shades.map(opacity => ({ opacity })), timing),
            lamp.querySelector('.archive-lamp__emission').animate(shades.map(value => ({ opacity: 1 - value * 1.2 })), timing),
        ];
        try { await Promise.all(animations.map(animation => animation.finished)); }
        catch { return; } // Paused, hidden or reduced-motion preference changed.
        animations = [];
        schedule();
    }
    function refresh() { cancel(); schedule(); }
    lamp.addEventListener('click', () => {
        const paused = lamp.getAttribute('aria-pressed') !== 'true';
        lamp.setAttribute('aria-pressed', String(paused));
        lamp.setAttribute('aria-label', paused ? 'Lanjutkan animasi lampu' : 'Jeda animasi lampu');
        root.dataset.lightPaused = String(paused);
        refresh();
    });
    document.addEventListener('visibilitychange', refresh);
    reduced.addEventListener('change', refresh);
    compact.addEventListener('change', refresh);
    window.addEventListener('pagehide', () => { stopped = true; cancel(); });
    window.addEventListener('pageshow', () => { stopped = false; refresh(); });
    schedule();
}
