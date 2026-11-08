# Vending hardware source

Current geometry: vertical resting tube OUTSIDE LEFT at RESET height, on the two-gusset bolted sidewall mount; original socket stays on the left rail. The integrated dock described in the earlier refinement is superseded. See [current motion contract](../../docs/motion-interaction.md#home-mechanical-contract) and [ADR 001](../../docs/adr/001-experience-authority.md).

The runtime parts are generated deterministically by `node tools/build-vending-assets.mjs`. Edit that source, regenerate, then build Vite and review desktop/compact screenshots. Do not edit the outputs independently.

The brass rail is a complete replacement instrument panel, incorporating the existing `public/assets/explore/archive/bronze.webp` as embedded texture. It covers the entire old lamp and two misshapen knobs in the approved source. Ventilation slots, rail screws, recessed socket, padded cradle with two retainers, tube caps/glass/inner wire/reflections, 60-segment knurled dial and reset casing/face are authored as separate SVG geometry. Lighting consistently comes from the upper left. No bitmap was recolored, erased, stretched or generated; the approved cabinet source remains intact.

All hardware SVGs have explicit canvases. The tube and dial rotate around 50% 50%; the tube's upright pose uses the socket button's measured centre, resting pose uses cradle centre at 0°. Reset face moves 3px into its fixed casing without rotation. Tube glow is deliberately outside the SVG so the unpowered glass never emits light. The print layer is actual visit media in a semantic paper link, masked at the feed edge and occluded by a separate roller.

The composition reference and original implementation instructions are retained together in `codex_instruction/vending-machine/`. The older knob raster crops and archive-light module are historical sources and are no longer used by the homepage module. See `docs/motion-interaction.md` for maintained state and input contracts; vending-machine-interactive.md is historical evidence.

Latest side mount: `side-mount.svg` (88×280) contains a bolted vertical sidewall plate, two tapered arms and gusset webs. It sits behind the existing cradle bed; no CSS pipe remains. The resting anchor is at reset height, and the original socket is unchanged.
