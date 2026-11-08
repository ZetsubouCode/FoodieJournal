import { angleDelta, pointerAngle } from './vending-state.mjs';

/** Indexed selector: each 32° commitment changes one page; sub-step travel resists. */
export function bindRotary(knob, { step, enabled, reduced, signal }) {
    const rotor = knob.querySelector('img');
    const detent = 32;
    let drag = null, index = 0, visual = 0, suppress = false, wheel = 0, wheelTime = 0, animation;
    function draw(angle) { visual = angle; rotor.style.transform = `rotate(${angle}deg)`; }
    function settle() {
        const from = visual, target = index * detent;
        animation?.cancel(); draw(target);
        if (!reduced.matches && !signal.aborted) {
            animation = rotor.animate([{transform:`rotate(${from}deg)`},{transform:`rotate(${target}deg)`}], {duration:160,easing:'cubic-bezier(.18,.82,.25,1)'});
        }
        knob.dataset.detent = String(index); knob.dataset.angle = String(target);
    }
    function commit(direction) { index += direction; step(direction); }
    const cancel = () => {
        if (!drag) return;
        const id = drag.id; suppress ||= drag.moved > 5; drag = null;
        if (knob.hasPointerCapture(id)) knob.releasePointerCapture(id);
        settle();
    };
    knob.addEventListener('pointerdown', e => {
        if (!enabled() || drag || e.button !== 0 || !e.isPrimary) return;
        animation?.cancel();
        const r = knob.getBoundingClientRect(), center = {x:r.x+r.width/2,y:r.y+r.height/2};
        drag = {id:e.pointerId,center,last:pointerAngle({x:e.clientX,y:e.clientY},center),remainder:0,moved:0};
        suppress = false; knob.setPointerCapture(e.pointerId);
    }, {signal});
    knob.addEventListener('pointermove', e => {
        if (!drag || drag.id !== e.pointerId) return;
        const next = pointerAngle({x:e.clientX,y:e.clientY},drag.center), delta = angleDelta(drag.last,next); drag.last = next;
        if (Math.hypot(e.clientX-drag.center.x,e.clientY-drag.center.y)<9) return;
        drag.moved += Math.abs(delta); drag.remainder += delta;
        while(Math.abs(drag.remainder)>=detent) { const d=Math.sign(drag.remainder);drag.remainder-=d*detent;commit(d); }
        // Less than 9° visual compliance, followed by a firm indexed click.
        draw(index*detent + Math.tanh(drag.remainder/detent)*9);
        suppress = drag.moved > 5;
    }, {signal});
    for(const event of ['pointerup','pointercancel','lostpointercapture']) knob.addEventListener(event,cancel,{signal});
    function turn(d) { if(!enabled())return; commit(d);settle(); }
    knob.addEventListener('click', e => {
        if(suppress){suppress=false;return;}
        const r=knob.getBoundingClientRect();turn(e.detail===0 || e.clientX>=r.x+r.width/2?1:-1);
    }, {signal});
    knob.addEventListener('keydown',e=>{
        if(e.key==='Escape'){cancel();return;}
        if(!['ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(e.key)||!enabled())return;
        e.preventDefault();turn(['ArrowRight','ArrowUp'].includes(e.key)?1:-1);
    },{signal});
    knob.addEventListener('wheel',e=>{
        if(document.activeElement!==knob||!enabled()||e.ctrlKey)return;
        e.preventDefault();const now=performance.now();if(now-wheelTime>180)wheel=0;wheelTime=now;wheel+=e.deltaY;
        if(Math.abs(wheel)>=45){turn(Math.sign(wheel));wheel=0;}
    },{passive:false,signal});
    reduced.addEventListener('change',()=>{if(reduced.matches){animation?.cancel();draw(index*detent)}},{signal});
    signal.addEventListener('abort',()=>{cancel();animation?.cancel()},{once:true});
    return cancel;
}
