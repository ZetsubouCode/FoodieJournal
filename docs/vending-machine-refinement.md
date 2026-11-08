> Latest lamp correction: [side-hung lamp and pickup cues](hanging-lamp-correction.md). The former inside-panel dock described below is superseded.

# Vending machine refinement · 7 November 2026

This revision supersedes the 6 November outboard cradle, analog dial travel and visible page-arrow fallbacks. The underlying release-only power reducer, published-visit source, printing/retrieval and standard site navigation remain.

Implemented:

- Removed both visible pagination buttons and their handlers/styles. The rotary selector retains keyboard arrows, focused wheel, left/right click halves and angular touch/pointer input.
- Indexed 32° selector: partial movement has less than 9° visual compliance, each crossed detent issues exactly one page action, and release returns to the committed index over 160ms. Interrupted gestures settle without generating another action. Motion preference changes cancel the settling animation cleanly.
- New vertical 66×280 SVG dock with two retainers sits beside the socket within the instrument rail. Desktop anchors are dock centre 2.85% / socket centre 7.35% of cabinet width, both at 41.35% height. Both tube poses are upright. Compact layout retains the vertical system with the knob/reset on its right. The tube no longer breaks the cabinet silhouette.
- Dark glass/material layers suppress baked tray illumination when off; the print path, roller and foreground lip remain independent. Warmth returns during boot. No dimming filter is animated across the full cabinet.
- “Ambil lampu” appears only for an undiscovered off session after 7.2 seconds. Three restrained dock highlights stop automatically; meaningful interaction dismisses the label and cue. Reduced motion retains a static label/outline.
- Selected windows have a thin amber edge, restrained frame lift, lifted photograph contrast and a brighter code badge. Right LED controls retain their original source centres, with more readable labels and visible hover/focus/press feedback on their existing hit areas.
- Reset assets now use a square recessed casing and separate sprung face, distinctly different from the textured circular rotary selector. The face still depresses 3px without rotating.
- Marquee, number plate, small naming plaques and window labels use low-light engraving shadows and restrained highlight edges. Preview title size, metadata spacing and excerpt length were refined to keep the photo dominant.
- Page changes use a short mechanical exit followed by column-wise shallow-perspective entrances. The requested turn direction travels through the async request, so last→first remains forward and first→last remains backward. Generation checks still reject stale exits and responses.

Asset source remains `tools/build-vending-assets.mjs`; regenerate with Node and run `npm run build`. Runtime geometry/material treatments are in `resources/css/vending-machine.css`; input is `vending-input.js`, lifecycle and content choreography are `pages/archive.js`. No packages, migrations or content rules changed.

Evidence: [off desktop](qa/vending-refinement/off-desktop.png), [powered desktop](qa/vending-refinement/on-desktop.png), [off compact](qa/vending-refinement/off-mobile.png), [powered compact](qa/vending-refinement/on-mobile.png). The original reference/cabinet source is retained. Tests and remaining limitations are recorded in [refinement QA](qa/vending-refinement/verification.md).
