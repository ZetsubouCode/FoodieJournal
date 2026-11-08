# Vending hardware source

7 November update: the dock is now a vertical 66×280 inset with two transverse retainers, wholly inside the left rail beside the socket. Tube rest angle is 0°. Reset uses a rounded-square recessed bezel and a separate square press face; the upper selector alone retains circular knurling. Current geometry and evidence are in `docs/vending-machine-refinement.md`.

The runtime parts are generated deterministically by `node tools/build-vending-assets.mjs`. Edit that source, regenerate, then build Vite and review desktop/compact screenshots. Do not edit the outputs independently.

The brass rail is a complete replacement instrument panel, incorporating the existing `public/assets/explore/archive/bronze.webp` as embedded texture. It covers the entire old lamp and two misshapen knobs in the approved source. Ventilation slots, rail screws, recessed socket, padded cradle with two retainers, tube caps/glass/inner wire/reflections, 60-segment knurled dial and reset casing/face are authored as separate SVG geometry. Lighting consistently comes from the upper left. No bitmap was recolored, erased, stretched or generated; the approved cabinet source remains intact.

All hardware SVGs have explicit canvases. The tube and dial rotate around 50% 50%; the tube's upright pose uses the socket button's measured centre, resting pose uses cradle centre at 0°. Reset face moves 3px into its fixed casing without rotation. Tube glow is deliberately outside the SVG so the unpowered glass never emits light. The print layer is actual visit media in a semantic paper link, masked at the feed edge and occluded by a separate roller.

The composition reference and original implementation instructions are retained together in `codex_instruction/vending-machine/`. The older knob raster crops and archive-light module are historical sources and are no longer used by the homepage module. See `docs/vending-machine-interactive.md` for complete state and input contracts.

Latest side mount: `side-mount.svg` (88×280) contains a bolted vertical sidewall plate, two tapered arms and gusset webs. It sits behind the existing cradle bed; no CSS pipe remains. The resting anchor is at reset height, and the original socket is unchanged.
