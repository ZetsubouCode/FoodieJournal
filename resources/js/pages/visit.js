export function initializeVisit() {
    const toggle = document.querySelector('[data-visit-motion-toggle]');
    const state = toggle.querySelector('[data-visit-motion-state]');
    const preference = matchMedia('(prefers-reduced-motion: reduce)');
    const setMotion = enabled => {
        document.documentElement.dataset.visitMotion = enabled ? 'on' : 'off';
        toggle.setAttribute('aria-checked', String(enabled));
        state.textContent = enabled ? 'aktif' : 'nonaktif';
    };
    setMotion(!preference.matches);
    preference.addEventListener('change', event => setMotion(!event.matches));
    toggle.addEventListener('click', () => setMotion(toggle.getAttribute('aria-checked') === 'false'));
    toggle.hidden = false;

    const rail = document.querySelector('[data-chapter-rail]');
    if (rail && 'IntersectionObserver' in window) initializeChapterRail(rail);
}

function initializeChapterRail(rail) {
    const links = [...rail.querySelectorAll('a')];
    const sections = links.map(link => document.getElementById(link.hash.slice(1)));
    let observer;
    let idle;
    const hideRail = () => {
        clearTimeout(idle);
        rail.removeAttribute('data-active');
    };
    const showOnScroll = () => {
        if (document.hidden) return;
        rail.setAttribute('data-active', '');
        clearTimeout(idle);
        idle = setTimeout(hideRail, 1800);
    };

    rail.hidden = false;
    const desktop = matchMedia('(min-width: 85rem) and (min-height: 24rem) and (hover: hover)');
    const update = () => {
        observer?.disconnect();
        if (desktop.matches) {
            const visible = new Set();
            const top = Math.round(innerHeight * .22);
            const bottom = Math.round(innerHeight * .66);
            observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) visible.add(entry.target);
                    else visible.delete(entry.target);
                });
                const active = sections.findLastIndex(section => visible.has(section));
                if (active < 0) return;
                links.forEach((link, i) => {
                    if (i === active) link.setAttribute('aria-current', 'location');
                    else link.removeAttribute('aria-current');
                });
            }, { rootMargin: `-${top}px 0px -${bottom}px 0px` });
            sections.forEach(section => observer.observe(section));
            window.addEventListener('scroll', showOnScroll, { passive: true });
        } else {
            window.removeEventListener('scroll', showOnScroll);
            hideRail();
        }
    };
    desktop.addEventListener('change', update);
    window.addEventListener('resize', update);
    window.addEventListener('blur', hideRail);
    document.addEventListener('visibilitychange', () => { if (document.hidden) hideRail(); });
    update();
}
