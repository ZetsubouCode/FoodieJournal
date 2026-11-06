import test from 'node:test';
import assert from 'node:assert/strict';
import { initialState, transition as send, controlsAvailable, angleDelta, wrapIndex, inDropZone, durationMilliseconds } from '../../resources/js/components/vending-state.mjs';

test('authored and minified CSS durations resolve to the same WAAPI milliseconds', () => {
    assert.equal(durationMilliseconds('270ms'),270); assert.equal(durationMilliseconds('.27s'),270);
    assert.equal(durationMilliseconds(' .96s '),960); assert.equal(durationMilliseconds('820ms'),820);
    assert.equal(durationMilliseconds('0s'),0); assert.equal(durationMilliseconds(''),0);
});

test('angles stay continuous through ±180° in both directions', () => {
    assert.equal(angleDelta(179, -179), 2); assert.equal(angleDelta(-179, 179), -2);
    assert.equal(angleDelta(45, 10), -35); assert.equal(angleDelta(-179, -170), 9);
});
test('wrap handles negative, zero, one and many pages', () => {
    assert.equal(wrapIndex(-1, 4), 3); assert.equal(wrapIndex(4, 4), 0);
    assert.equal(wrapIndex(5, 1), 0); assert.equal(wrapIndex(-3, 0), 0);
});
test('expanded elliptical drop zones accept edges without accepting distant slots', () => {
    const target = { x: 60, y: 250, width: 50, height: 210 };
    assert.equal(inDropZone({ x: 101, y: 250 }, target), true);
    assert.equal(inDropZone({ x: 125, y: 250 }, target), false);
    assert.equal(inDropZone({ x: 60, y: 438 }, target), true);
});
test('holding and snap travel cannot power the machine before commit', () => {
    let s = initialState(); s = send(s, { type: 'LIFT' });
    assert.equal(s.power, 'off'); assert.equal(controlsAvailable(s), false);
    s = send(s, { type: 'SNAP', target: 'socket' }); assert.equal(s.power, 'off');
    assert.equal(send(s, { type: 'LIFT' }), s);
    s = send(s, { type: 'COMMIT_LAMP' }); assert.equal(s.power, 'booting');
    assert.equal(controlsAvailable(s), false); s = send(s, { type: 'POWER_STABLE' });
    assert.equal(s.power, 'on'); assert.equal(controlsAvailable(s), true);
});
test('invalid removal returns to socket and never cycles power', () => {
    let s = send(initialState(true), { type: 'LIFT' });
    assert.equal(s.power, 'on'); s = send(s, { type: 'RETURN' });
    s = send(s, { type: 'COMMIT_LAMP' }); assert.equal(s.power, 'on'); assert.equal(s.lampLocation, 'socket');
});
test('valid removal commits shutdown only after travel', () => {
    let s = send(initialState(true), { type: 'LIFT' });
    s = send(s, { type: 'SNAP', target: 'cradle' }); assert.equal(s.power, 'on');
    s = send(s, { type: 'COMMIT_LAMP' }); assert.equal(s.power, 'shutting_down');
    s = send(s, { type: 'POWER_STABLE' }); assert.equal(s.power, 'off');
});
test('off controls and illegal transitions leave state unchanged', () => {
    const s = initialState();
    for (const event of [{type:'PRINT'}, {type:'RESET'}, {type:'SELECT',id:9}, {type:'PAGE_REQUEST',page:2}, {type:'COMMIT_LAMP'}, {type:'POWER_STABLE'}]) assert.equal(send(s,event), s);
});
test('one print is frozen to a concrete visit, locks competing actions and retrieves once', () => {
    let s = initialState(true, 0, '17'); s = send(s, {type:'PRINT'});
    assert.equal(s.printedVisitId, '17');
    for (const e of [{type:'PRINT'},{type:'SELECT',id:'22'},{type:'LIFT'},{type:'PAGE_REQUEST',page:2},{type:'RESET'}]) assert.equal(send(s,e),s);
    s=send(s,{type:'PRINT_READY'}); s=send(s,{type:'RETRIEVE'}); assert.equal(s.dispenser,'retrieving');
    assert.equal(send(s,{type:'RETRIEVE'}),s);
});
test('new selection or page clears a ready photo, reset leaves power on', () => {
    let s = send(send(initialState(true, 0, '17'), {type:'PRINT'}),{type:'PRINT_READY'});
    s=send(s,{type:'SELECT',id:'18'}); assert.equal(s.printedVisitId,null); assert.equal(s.dispenser,'idle');
    s=send(s,{type:'PAGE_REQUEST',page:3}); assert.equal(s.requestedPage,3); assert.equal(s.pageIndex,0);
    s=send(s,{type:'PAGE_COMMIT',page:3,id:'30'}); assert.equal(s.selectedVisitId,'30');
    s=send(s,{type:'RESET'}); assert.equal(s.power,'on');
});
