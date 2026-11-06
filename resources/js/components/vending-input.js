import { angleDelta, pointerAngle } from './vending-state.mjs';

/** Knurled dial: continuous angles, 32° detents, remainder retained across frames. */
export function bindRotary(knob, { step, enabled, reduced, signal }) {
    const rotor = knob.querySelector('img');
    let drag = null, angle = 0, suppress = false, wheel = 0, wheelTime = 0;
    const draw = () => { rotor.style.transform = `rotate(${angle}deg)`; };
    const cancel = () => {
        if (!drag) return;
        const id = drag.id; drag = null;
        if (knob.hasPointerCapture(id)) knob.releasePointerCapture(id);
    };
    knob.addEventListener('pointerdown', e => {
        if (!enabled() || drag || e.button !== 0 || !e.isPrimary) return;
        const r = knob.getBoundingClientRect();
        const center = { x: r.x + r.width / 2, y: r.y + r.height / 2 };
        drag = { id: e.pointerId, center, last: pointerAngle({x:e.clientX,y:e.clientY}, center), remainder: 0, moved: 0 };
        suppress = false; knob.setPointerCapture(e.pointerId);
    }, { signal });
    knob.addEventListener('pointermove', e => {
        if (!drag || drag.id !== e.pointerId) return;
        const next = pointerAngle({x:e.clientX,y:e.clientY}, drag.center);
        const delta = angleDelta(drag.last, next); drag.last = next;
        // Tiny radial noise near the centre cannot spuriously turn a page.
        if (Math.hypot(e.clientX - drag.center.x, e.clientY - drag.center.y) < 9) return;
        drag.moved += Math.abs(delta); drag.remainder += delta; angle += delta; draw();
        while (Math.abs(drag.remainder) >= 32) {
            const direction = Math.sign(drag.remainder); drag.remainder -= direction * 32; step(direction);
        }
        suppress = drag.moved > 7;
    }, { signal });
    knob.addEventListener('pointerup', cancel, { signal });
    knob.addEventListener('pointercancel', () => { suppress = true; cancel(); }, { signal });
    knob.addEventListener('lostpointercapture', cancel, { signal });
    knob.addEventListener('click', e => {
        if (suppress) { suppress = false; return; }
        if (!enabled()) return;
        const r = knob.getBoundingClientRect();
        const direction = e.detail === 0 || e.clientX >= r.x + r.width / 2 ? 1 : -1;
        angle += direction * 32; draw(); step(direction);
    }, { signal });
    knob.addEventListener('keydown', e => {
        if (e.key === 'Escape') { cancel(); return; }
        if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key) || !enabled()) return;
        e.preventDefault(); const direction = ['ArrowRight', 'ArrowUp'].includes(e.key) ? 1 : -1;
        angle += direction * 32; draw(); step(direction);
    }, { signal });
    // Only a focused dial consumes wheel input; ordinary page scrolling stays native.
    knob.addEventListener('wheel', e => {
        if (document.activeElement !== knob || !enabled() || e.ctrlKey) return;
        e.preventDefault(); const now = performance.now();
        if (now - wheelTime > 180) wheel = 0;
        wheelTime = now; wheel += e.deltaY;
        if (Math.abs(wheel) >= 45) { const d = Math.sign(wheel); wheel = 0; angle += d * 32; draw(); step(d); }
    }, { passive: false, signal });
    signal.addEventListener('abort', cancel, { once: true });
    return cancel;
}
