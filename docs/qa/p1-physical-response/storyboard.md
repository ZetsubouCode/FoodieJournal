# P1 — Weighted socket seating

9 November 2026, Asia/Jakarta. Baseline `21e443a7254ae3a3e72989a83aa06584dba0c005`, clean working tree. Read completed P0-1–4 and P0-H evidence, design memory, design-system §1 and the locked motion contract before choosing this refinement.

Hypothesis: a short final seating stroke, with the existing socket plate flexing at contact, makes installation read as a latch accepting a physical tube. Keep the existing 270ms snap and 960ms boot, with no additional wait or legal state.

| Beat | Ownership / visible response | Interruption and reduced behavior |
| --- | --- | --- |
| Contact / lift | Existing CSS responds by the next frame with the lifted glass/material treatment; archive input dispatches LIFT. No power commitment while held over the target. | Existing invalid release, Escape, capture loss, scroll and resize return to origin. No latch flex on cancellation. |
| Valid release / approach | Existing SNAP owns the locked 270ms animation. Tube approaches a point 4px above the unchanged socket centre at 72% of eased progress. | Controls remain locked; no new input window, timers or state flags. Reverse/return retains its existing direct path. |
| Seat / plate flex | Tube reaches its exact anchor at 86%; socket image flexes to scaleX(1.035), scaleY(.985) at the same progress, then recovers by 100%. Both use the existing snap duration/easing and lifecycle animation set. | Only a committed cradle→socket installation flexes the plate. Hidden/resize/runtime reduction finishes registered animations together; pagehide cancels and aborts their owner. |
| Commit / electrical response | COMMIT_LAMP starts the unchanged boot choreography only after seating; 960ms later controls become usable. | Reduced motion at load skips both transforms and reaches the same semantic state immediately. Missing WAAPI uses the existing immediate fallback. No additional announcement. |

Scope audit: clearPrint is local to archive.js (selection, page request, reset, committed power change and initialization); motion is shared there by preview, snap, boot/off, page exit/entrance, print and retrieval. settle is reached by release, tap/keyboard targets and cancellation. loadPage is reached by rotary, reset and retry; bindRotary has one archive caller. Read their consumers, reducer and machine browser cases before editing. No changes to those consumers, reducer events, print clearing, templates, CSS geometry, rotary, storage or domain are needed.

Validation: existing ten reducer cases and all nineteen cabinet browser cases; new seating/contact/interruption assertions; three same-fixture real-time trace samples per width before/after; paused local start/contact/end frames at 390/1440; real-time complete forward/reverse journeys; keyboard, touch, reduced load/runtime, no-JS, enlarged root text, exact print identity, and unchanged reviewed P0-4 gate with snapshot updates disabled. New retraction is optional and deliberately omitted.
