# Motion and interaction contract

Approved target, 8 November 2026. Home **5/5**, Explore **3/5**, Detail **3/5** express relative creative ambition, not duration or a requirement to animate everything. These supersede the old blanket “motion 2” summary. Admin remains restrained. [Status](implementation-status.md) distinguishes existing sequences from planned improvements.

## Grammar and ownership

Prioritize physicality, then choreography, then distinct actionable state feedback. Input → anticipation/contact → mechanical response → data response → reveal → settled/ready. Ambient surprises are optional later work. Never delay a real request or navigation to satisfy a visual timer.

CSS owns material hover/focus/press and bounded local light; `archive.js` coordinates lifecycle and data; `vending-state.mjs` owns legal transitions; `vending-input.js` owns rotary input. Explore keeps its request generation and committed-result model. Reuse native Web Animations/CSS and shared dialog/form primitives. Add helpers only for real repetition.

Use semantic fast/standard/enter/exit/easing tokens plus cabinet component timings. Existing fast/standard are 140/220ms; cabinet snap/boot/off/print are 270/960/240/820ms. Current detent settle 160ms, page exit 110ms, column entrances 190/212/234ms and retrieval 180ms remain the baseline, not a mandate to lengthen motion. Parse compiled `ms` and `s` correctly. Exit should usually be shorter than entry. Render expensive textures/shadows statically; animate bounded transforms/opacity.

## Navigation hover progress

Apply consistently to primary public nav tabs, including standalone errors. Optional footer adoption requires an explicit scope decision; do not put progress decoration on every link.

| Event/state | Required behavior |
| --- | --- |
| Idle inactive | Thin warm copper/amber line has scaleX(0), anchored left; no layout movement |
| Pointer enter | Fill left → right from current scale to 1 over the standard token (220ms starting target) |
| Pointer leave | Retract toward left over fast token (140ms); no delayed completion |
| Re-enter mid-exit | Reverse smoothly from current visual scale; no jump or stacked timers |
| Selected route | Full line persists; pointer leaving does not erase it; `aria-current=page` retains semantic selection |
| Keyboard `:focus-visible` | Full line immediately plus the shared visible focus outline; line alone is not the focus indicator |
| Reduced motion | Full line immediately on hover/focus/active, no interpolated fill/retraction |
| Touch/no hover | Ordinary link navigation, active route and focus remain; no hover dependency |

Native CSS pseudo-element/transform is sufficient. No JavaScript navigation interception, fake network progress, artificial delay or SPA migration. History confirms the older hover fill in `4f6365b:resources/css/home.css`; the 801cc57 rewrite removed it. Start/mid/end evidence currently shows the absence, not a passing target.

## Home mechanical contract

Fresh session starts OFF; restored session may paint ON without replaying boot. Tube rests **vertically outside LEFT at RESET height**, on the bolted sidewall/two-gusset bracket. Socket stays at left 2.8%, top 27%, width 4.25%, height 28.7% of the 1634×963 cabinet. Desktop cradle centre is 81.55% high; compact centre 134px. See [ADR](adr/001-experience-authority.md) and final CSS rules, not superseded integrated-dock reports.

| Journey | Existing behavior to preserve | Improvement target / boundary |
| --- | --- | --- |
| Off discoverability | Three finite 2s cap-edge/reflection cycles after .6s, including discovered OFF sessions; dismissed on pickup; static reduced-motion highlight; core remains off | Material-only cue: no visible label, tooltip, tutorial or rectangular pickup UI; keep screen-reader help |
| Pickup/install | Pointer follows cached geometry; valid release commits snap then boot; expanded ellipse uses tube centre | Weighted contact and local latch response may improve; never activate while merely hovering/holding |
| Cancel/remove | Invalid release/Escape/lost capture/scroll/resize return to origin; valid reverse drop shuts down | No stray glow, paper or enabled control; retain tap lamp→target and keyboard alternatives |
| Rotary | 32° indexed steps, resistant residual <9°, 160ms settle, directional wrap, focused wheel, click halves, arrow keys | One request per crossed index; no extra visible pagination arrows/buttons; stale responses never overwrite latest intent |
| Select | Exact visit ID updates selected window and actual story preview | Distinct press, selected and unavailable states; no unrelated frame motion |
| Print/retrieve | One frozen selected-visit photo feeds behind roller, then a real link retrieves it; competing actions lock | Keep paper geometry causal; missing photo remains honest; disabled action must not suggest another print |
| Reset/new selection/page/power-off | Ready paper is cleared; reset preserves ON and returns to first selection/page | Current `clearPrint()` removes instantly. Consider a short local retraction before the next physical event, with immediate semantic clearing and cancellation guards |
| Failure | Keep committed page/selection, visible retry, status announcement | Separate page pending/failed from machine OFF; never fabricate or reveal an old print as a new success |

LED centres stay (1012,267), (1012,405), (1012,546); circular lenses do not stretch. Reset is square recessed and depresses without rotation. Six windows per actual published page; empty final apertures do not repeat filler. Home selection/page stays local, never mutates its URL. Ordinary site navigation works without playing or JavaScript.

## Explore and detail

Explore keeps the discovery board mounted while server fragments update console/results/drawer. Give immediate busy feedback, preserve committed results, abort obsolete requests, then reveal the committed group without taking focus away. Filter staging, Apply/Cancel, URL and Back/Forward outrank animation. Use layout-matched skeletons only for genuinely waiting geometry; [page-state rules](page-experience.md#state-contract) define their transitions.

Visit/Place use photo hover/focus, readable section reveals and gallery feedback as aids to storytelling. No mandatory scroll-jacking, long entrance gates or timeline scrubbing to read a visit. Bento ordering follows semantic DOM order on mobile. Meaningful CSS perspective/2.5D/3D is allowed; WebGL requires a measured advantage and equivalent fallback, not a decorative dependency.

## Accessibility, interruption and performance gates

Every applicable control has idle, hover, focus, press, selected/active and disabled feedback. Async flows additionally expose processing/loading, success, empty, failure/retry and interruption/cancellation. Do not add impossible boolean combinations; extend the existing reducer when state becomes nontrivial.

`prefers-reduced-motion` applies at load and changes at runtime: finish meaningful state immediately, cancel decorative travel, retain static cues and all actions. Hidden tabs/pagehide, resize, lost pointer capture, repeated requests and Back/Forward must settle safely. No uncaught animation rejection, duplicate listener, stuck `inert` or infinite idle loop. Shared native dialogs restore focus; announce real changes once with meaningful live regions.

Proposed gates: immediate visible press/busy response by the next rendered frame; no intentional artificial wait; no new continuously animated viewport filter; no new runtime dependency without profiling. Capture snap/boot/print production timings, frame trace, layout-shift and network evidence on the same fixture/build. LCP ≤2.5s, INP ≤200ms and CLS ≤0.1 are field targets, not measured claims here. Compare at least three comparable lab runs; investigate a >10% median deterioration or new layout shift before accepting a change. Lab input traces are not field INP. Keep originals on explicit viewer open, image dimensions stable and noncritical media lazy.
