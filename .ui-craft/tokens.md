# Foodie tokens

Maintained 9 November 2026. Actual sources: `resources/css/tokens.css`, `memory-dispenser.css`, `public-chrome.css`, `vending-machine.css`. [Art direction](../docs/art-direction.md) and [motion](../docs/motion-interaction.md) describe approved targets; [status](../docs/implementation-status.md) records gaps.

## Current implementation

Global primitives: charcoal #111211/#191b19/#242724, ivory #f5f0e7/#d5cec2, copper #e2a76b. Semantic surfaces/text/action/focus/status/border consume these; archive scope overrides surfaces to #100e0b/#211b15/#32271d and text to #f0e2ce/#d0c1ac/#bbab94. Archive brass/highlight/glass/paper roles live in memory-dispenser.css. This cascade exists today; do not treat global values as the only rendered palette.

Public fonts: self-hosted Archive Garamond (Cormorant Garamond) + Archive Sans (DM Sans). Admin currently inherits those public overrides through the shared layout; P0-2 preserves its existing fonts rather than changing typography. Fluid headings; body 1rem; tabular numeric data. Spacing is 4px-based, section rhythm 24/32/48/64/96px. Radii 6px controls / 12px cards / 20px dialogs. Control height 44px, action-group min inline 7.5rem; local controls still require hit-area measurement. Focus 2px #f2c788 with 3px offset. Shared narrative measure 68ch.

Current shells (P0-2, 9 November): --public-content-max 112rem, --public-gutter 1rem ≤480px / 1.5rem tablet / clamp(2rem,3.333vw,4rem) ≥1024px; public chrome applies the width once to header/main/footer inner and error main. Global --content-max stays 80rem for admin, excluded via public-page--admin. Separate --reading-max stays 68ch. public-chrome owns nav/footer/fonts including standalone errors; cinematic owns atmosphere/crop, dossier owns story geometry. --surface-footer mixes charcoal with measured copper. Public footer has two-line fluid 2.4–5.2rem statement, vertical desktop/row mobile navigation; admin keeps its compact footer. No duplicate per-page chrome.

Current motion fast/standard 140/220ms. Cabinet snap/boot/off/print 270/960/240/820ms, native CSS/WAAPI; easing cubic-bezier(.2,.72,.22,1). Rotary 32° commitments, residual <9°, 160ms settle. Page exit 110ms; column entrance 190/212/234ms with shallow perspective and explicit wrap direction. Reset 85/150ms depression/return; retrieval 180ms. Some literals remain in JS/CSS: semantic motion consolidation is pending, not already complete.

Resting tube vertical outside LEFT at RESET height; socket original coordinates 2.8%/27%/4.25%/28.7%. Gutter 48–80px; compact 48px. Two-gusset bolted side mount. Pickup cap/glass cue: .6s delay, 2s ×3 cycles, dismissed on interaction; static reduced-motion highlight, electrical core OFF. No visible hint/pagination widgets. Final cascade overrides earlier integrated-dock definitions. Keep LED source centres/circular geometry.

Primary-nav hover line (P0-1 implemented 9 November): semantic --action-primary-bg copper, left origin, --motion-standard 220ms enter / --motion-fast 140ms exit, linear fill and interruption-safe native transitions. Active and keyboard focus show full line immediately; shared focus outline remains. Reduced motion has no transition, touch does not depend on hover. public-chrome.css owns the rule for Vite and generated standalone errors; no new tokens. [Evidence](../docs/qa/navigation-hover-progress/verification.md).

P0-2 wide shell and warm shared footer are now implemented, not pending tokens: measured 1792px at 1920. Scene fade is 90rem, with Home .12 / Explore .32 / Visit .5 / Place .42 opacity and deliberate crop variants; admin keeps .7 / 75rem. Cabinet maximum is source 1634px plus reserved gutter. See [verification](../docs/qa/wide-shell/verification.md).

P0-3 readability (9 November): Home excerpts 1rem; Home/card/date/location/filter metadata normally .875rem; .75rem secondary engraving remains. Shared .btn minimum width and height use --control-height; card tags/overflow/recommendation links and Explore filter/view/search/chip actions measure at least 44px in tested layouts. Explore heading scales with root text; 34rem/18rem component queries reflow captions by available text space, not device assumptions. No new global color/motion tokens. [Verification](../docs/qa/readability-states/verification.md).

P0-4 (9 November) adds testing only, with no token changes. The pinned Windows Chromium baseline gate uses 40 reviewed PNGs, threshold .15 / 30 differing pixels maximum (nav midpoint zero), explicit updates only, and geometry/state/network assertions. [Review/negative control](../docs/qa/visual-baselines/verification.md).

## Approved targets, not implemented tokens

Home 5/Explore 3/Detail 3 qualitative motion ambition; no blanket ban on 3D. Add semantic enter/exit/easing/responsive-density roles only as required by scoped implementation. Reuse existing material tokens; avoid more one-off hex/spacing/motion overrides. Contrast must be measured on final rendered backgrounds; no global conformance claim follows from this inventory.
