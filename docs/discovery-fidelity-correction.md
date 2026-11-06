> Historical implementation record. The machine now lives on Homepage; Explore uses the Curiosity Index. Current architecture: [cinematic redesign](cinematic-redesign.md).

> Historical record — superseded on 5 November 2026 by the Memory Archive in [docs/discovery-machine.md](discovery-machine.md). Referenced production assets and capture scripts have been removed; this is not current implementation guidance.

# Discovery Machine fidelity correction — 5 November 2026

This records the earlier material/composition pass. The later optical, media-binding and animation reconstruction is authoritative in `docs/projection-system-reconstruction.md`.

The previous hero was an intermediate draft. This correction replaces its flat SVG spoke wheel with a photographic alpha reel: thick sculpted bronze dividers, recessed triangular windows, rivets, bevels, and a separate functional hub. The wheel, DOM links, published food photos, stationary button and active contour remain independent layers. No screenshot is used as functioning UI.

The hero is now a scene rather than three equal layout columns. The projector occupies 55% of the desktop scene, overlaps the projection space, and receives a slight perspective treatment. The projected screen has a separate aged celluloid frame, rotateY/rotateZ/skew perspective, a contrast scrim, and a beam aimed from the lens. Mobile stacks the same controls and projection; only decorative chassis edges may crop. The paper Memory Reel heading has imperfect edges and sits on a continuous film bed.

The image-generation tool created `public/assets/discovery/wheel-metal.webp` (260,580 bytes) and `projection-frame.webp` (184,812 bytes) from the approved concept as a style reference. The existing chassis remains because its material quality was adequate; its scale and relationship to the reel changed. The unused flat `wheel-frame.svg` was removed. Generated alpha was preserved, and the outputs were resized/encoded to WebP. No external asset service is required at runtime.

Generation prompts, using the built-in image tool:

- Reel: “ONLY a single vintage film projector FRONT REEL FRAME, straight-on orthographic circular view, perfectly round and centered, isolated on transparent background. Six equally spaced deep curved triangular wedge OPEN WINDOWS, at 12, 2, 4, 6, 8, 10 o'clock, six thick sculpted cast bronze dividers between them, heavy double concentric rim, blackened aged copper, tactile pitted and brushed metal, rivets around outer rim, warm amber specular edge light from right, deep dark bevels. Central round mounting surround with a transparent center hole for an independent functional button. The SIX triangular window interiors must be completely TRANSPARENT alpha so website food photos can show through; outside circle also transparent. No food, no pictures, no text, no button, no projector chassis, no beam.”
- Frame: “A single rectangular antique projected 35mm FILM FRAME BORDER only, landscape 4:3, straight on, centered fills canvas. Rough amber-brown celluloid film stock, genuine sprocket perforations along top and bottom, thin worn left/right edges, warm projector illumination bleeding through scratched edges, subtle dust and emulsion. The entire large rectangular CENTER aperture (85% width and 76% height) must be truly TRANSPARENT alpha for a dynamic website food image and real HTML text. Outside the border also transparent. No image in center, no food, no text, no numbers, no symbols, no scenery, no solid background. Photoreal macro material, imperfect tactile distressed edges, warm amber highlights, darker copper corners.”

## Evidence

`docs/qa/discovery-fidelity/` retains `before-desktop.png`, `before-mobile.png`, `after-{1440,1920,768,390,320}.png`, and `demo-{1440,1920,768,390,320}.png`. Before images are the previous isolated QA captures. After images use the same QA dataset. Demo images use a separate `tools/.runtime/discovery-visual.sqlite` and media directory, seeded with the repository's clearly fictional DemoJournalSeeder and bundled illustrative food photos. Neither fixture modifies owner data. The demo exists to inspect all six wedge windows with persisted published media, not to supply production content.

`node tools/capture-discovery-fidelity.mjs` captures the standard QA server on port 8011. Optional `FOODIE_CAPTURE_BASE` and `FOODIE_CAPTURE_PREFIX` select another loopback fixture and filename prefix. Captures wait for scene images to finish loading.

Validation: `npm run build`; `php artisan test --filter="DiscoveryMachineTest|PublicTest"` (12 passed, 125 assertions); `npx playwright test tests/Browser/discovery-machine.spec.js` (4 passed). The browser suite verifies rotation and forward wrap, shutdown/reveal, result/URL history, failed fetch retry, keyboard, reduced motion, axe, 320–1920 layouts, and 200% text reflow. It found an 8 px mobile gutter overflow during this pass; the scene now uses the matching mobile gutter and passes the same assertion.

Remaining differences from A1: the generated reel is more frontal and has different rivet/patina details; the beam lacks volumetric smoke; the projection uses one historical cover rather than a separately art-directed food cutout. Real taxonomy length and missing media determine wedge density. These are explicit fidelity limits, not a pixel-perfect approval claim. The next design review is `/critique` against the retained approved concept and these captures.
