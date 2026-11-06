export const wrapIndex = (index, count) => count > 0 ? ((index % count) + count) % count : 0;
export const angleDelta = (from, to) => ((to - from + 540) % 360) - 180;
export const pointerAngle = (point, center) => Math.atan2(point.y - center.y, point.x - center.x) * 180 / Math.PI;
// CSS optimizers may serialize 960ms as .96s. WAAPI always expects milliseconds.
export function durationMilliseconds(value) {
    const text = value.trim();
    const number = Number.parseFloat(text) || 0;
    return text.endsWith('ms') ? number : text.endsWith('s') ? number * 1000 : number;
}
// A rotated tube is judged at its centre, independent of where it was gripped.
export function inDropZone(point, target, expansion = 1.8) {
    const rx = Math.max(40, target.width * expansion / 2);
    const ry = Math.max(42, target.height * expansion / 2);
    return ((point.x - target.x) / rx) ** 2 + ((point.y - target.y) / ry) ** 2 <= 1;
}
export function initialState(on = false, page = 0, selected = null) {
    return { power: on ? 'on' : 'off', lampLocation: on ? 'socket' : 'cradle', lampOrigin: null,
        lampTarget: null, pageIndex: page, requestedPage: page, selectedVisitId: selected,
        dispenser: 'idle', printedVisitId: null, pageBusy: false };
}
export const lampAvailable = s => ['off', 'on'].includes(s.power) && !['printing', 'retrieving'].includes(s.dispenser);
export const controlsAvailable = s => s.power === 'on' && s.lampLocation === 'socket' && !['printing', 'retrieving'].includes(s.dispenser);
export function transition(s, event) {
    switch (event.type) {
    case 'LIFT':
        return lampAvailable(s) && ['socket', 'cradle'].includes(s.lampLocation)
            ? { ...s, lampOrigin: s.lampLocation, lampLocation: 'dragging' } : s;
    case 'RETURN':
        return s.lampLocation === 'dragging' ? { ...s, lampLocation: 'animating', lampTarget: s.lampOrigin } : s;
    case 'SNAP':
        return s.lampLocation === 'dragging' && ['socket', 'cradle'].includes(event.target) && event.target !== s.lampOrigin
            ? { ...s, lampLocation: 'animating', lampTarget: event.target } : s;
    case 'COMMIT_LAMP':
        if (s.lampLocation !== 'animating') return s;
        if (s.lampTarget === s.lampOrigin) return { ...s, lampLocation: s.lampOrigin, lampOrigin: null, lampTarget: null };
        return { ...s, lampLocation: s.lampTarget, lampTarget: null, lampOrigin: null,
            power: s.lampTarget === 'socket' ? 'booting' : 'shutting_down', dispenser: 'idle', printedVisitId: null };
    case 'POWER_STABLE':
        return ['booting', 'shutting_down'].includes(s.power) ? { ...s, power: s.power === 'booting' ? 'on' : 'off' } : s;
    case 'SELECT':
        return controlsAvailable(s) && !s.pageBusy ? { ...s, selectedVisitId: event.id, dispenser: 'idle', printedVisitId: null } : s;
    case 'PAGE_REQUEST':
        return controlsAvailable(s) ? { ...s, requestedPage: event.page, pageBusy: true, dispenser: 'idle', printedVisitId: null } : s;
    case 'PAGE_COMMIT':
        return s.pageBusy ? { ...s, pageBusy: false, pageIndex: event.page, requestedPage: event.page, selectedVisitId: event.id } : s;
    case 'PAGE_CANCEL':
        return { ...s, pageBusy: false, requestedPage: s.pageIndex };
    case 'PRINT':
        return controlsAvailable(s) && !s.pageBusy && s.selectedVisitId !== null && s.dispenser === 'idle'
            ? { ...s, dispenser: 'printing', printedVisitId: s.selectedVisitId } : s;
    case 'PRINT_READY':
        return s.dispenser === 'printing' ? { ...s, dispenser: 'ready' } : s;
    case 'RETRIEVE':
        return s.dispenser === 'ready' && controlsAvailable(s) ? { ...s, dispenser: 'retrieving' } : s;
    case 'RESET':
        return controlsAvailable(s) ? { ...s, dispenser: 'idle', printedVisitId: null } : s;
    default: return s;
    }
}
