# Homepage Memory Archive runtime artwork

Source master: resources/artwork/archive-cabinet-v2.png, edited from the supplied
empty cabinet with built-in image generation. It repairs hardware regions while
preserving the approved cabinet identity. The exact generation brief is saved
beside the source master. No food photograph or dynamic text is baked into it.

- cabinet-v2.webp: complete repaired shell, 1634×963.
- knob-upper.webp / knob-lower.webp: exact knob crop layers for local rotation.
- pickup-lip.webp: actual lower rim pixels for foreground occlusion.
- pickup-compact.webp / slot-compact.webp: same artwork for readable compact layout.
- atmosphere.webp / bronze.webp: optimized supplied background and material.

Reproduce with python tools/prepare-archive-assets.py from the repository root.
npm run build compiles live bounds from config/archive.php into external CSS.
Pillow is a development-only dependency; no image runtime is installed.

V1 cabinet.webp and shell-mask.svg are obsolete. The original source package and
historical evidence remain outside runtime. All culinary photographs still use
protected, published application media URLs; no uploads are copied here.

Current composition: native 1634×963 Homepage cabinet. Explore uses Curiosity Index; the asset path is retained for compatibility. Small CSS lamp aperture modulation and one centered lens per existing housing do not modify the raster. See docs/cinematic-redesign.md.
