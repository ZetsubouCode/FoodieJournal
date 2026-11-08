# Foodie tokens

Maintained 8 November 2026. Actual sources: `resources/css/tokens.css`, `memory-dispenser.css`, `public-chrome.css`, `vending-machine.css`. [Art direction](../docs/art-direction.md) and [motion](../docs/motion-interaction.md) describe approved targets; [status](../docs/implementation-status.md) records gaps.

## Current implementation

Global primitives: charcoal #111211/#191b19/#242724, ivory #f5f0e7/#d5cec2, copper #e2a76b. Semantic surfaces/text/action/focus/status/border consume these; archive scope overrides surfaces to #100e0b/#211b15/#32271d and text to #f0e2ce/#d0c1ac/#bbab94. Archive brass/highlight/glass/paper roles live in memory-dispenser.css. This cascade exists today; do not treat global values as the only rendered palette.

Public fonts: self-hosted Archive Garamond (Cormorant Garamond) + Archive Sans (DM Sans); admin system Georgia/Segoe UI. Fluid headings; body 1rem; tabular numeric data. Spacing is 4px-based, section rhythm 24/32/48/64/96px. Radii 6px controls / 12px cards / 20px dialogs. Control height 44px, action-group min inline 7.5rem; local controls still require hit-area measurement. Focus 2px #f2c788 with 3px offset. Shared narrative measure 68ch.

Current shell: --content-max 80rem, width min(100% - 3rem, max); ≤480px width 100% - 2rem. public-chrome owns nav/footer/fonts including standalone errors; cinematic owns page atmosphere/crop; dossier owns story geometry. Do not duplicate chrome per page.

Current motion fast/standard 140/220ms. Cabinet snap/boot/off/print 270/960/240/820ms, native CSS/WAAPI; easing cubic-bezier(.2,.72,.22,1). Rotary 32° commitments, residual <9°, 160ms settle. Page exit 110ms; column entrance 190/212/234ms with shallow perspective and explicit wrap direction. Reset 85/150ms depression/return; retrieval 180ms. Some literals remain in JS/CSS: semantic motion consolidation is pending, not already complete.

Resting tube vertical outside LEFT at RESET height; socket original coordinates 2.8%/27%/4.25%/28.7%. Gutter 48–80px; compact 48px. Two-gusset bolted side mount. Pickup cap/glass cue: .6s delay, 2s ×3 cycles, dismissed on interaction; static reduced-motion highlight, electrical core OFF. No visible hint/pagination widgets. Final cascade overrides earlier integrated-dock definitions. Keep LED source centres/circular geometry.

## Approved targets, not implemented tokens

Wide public shell up to 112rem; gutters 16px ≤480, 24px through tablet, fluid clamp(32px,3.333vw,64px) ≥1024. At 1920 target 1792px, with separate 68ch prose and full-bleed scene/footer background. Implement as semantic shell/gutter roles after checking admin/error consumers.

Shared primary-nav hover line uses semantic copper/amber, origin left, standard 220ms entry / fast 140ms exit; keyboard/reduced-motion show immediate line with normal focus ring. Current CSS has only a static active line.

Home 5/Explore 3/Detail 3 qualitative motion ambition; no blanket ban on 3D. Add semantic enter/exit/easing/responsive-density roles only as required by scoped implementation. Reuse existing material tokens; avoid more one-off hex/spacing/motion overrides. Contrast must be measured on final rendered backgrounds; no global conformance claim follows from this inventory.
