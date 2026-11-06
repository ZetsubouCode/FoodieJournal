# Foodie — Memory Archive Asset Pack

This package is intended as a *visual source kit* for rebuilding Foodie's Explore page, not a complete editable Figma document or a running frontend.

## Start here

1. Open `reference_full_page.png` to see the approved cinematic direction.
2. Open `memory_machine_empty_cropped.png` to see the **empty realistic machine** for dynamic content.
3. Read `machine_geometry.json` for initial slot, screen, and pickup-area coordinates (source canvas 1448×1086; cropped canvas 1351×796 starting at x=32,y=138).
4. Use `memory_machine_front_bezel_cropped.png` as an upper metal overlay if animating content in the compartments **behind** the frame. The empty base and front bezel share the same crop dimensions.
5. Read `CODEX_IMPLEMENTATION_INSTRUCTIONS.md` and execute it in the Laravel repository.

## File classification

### Suggested runtime base assets
- `memory_machine_empty_cropped.png`: transparent-background horizontal machine, empty slot windows, no baked-in collection text or story; custom-reconstructed from the illustrated metal cabinet.
- `memory_machine_front_bezel_cropped.png`: corresponding metal upper shell with transparent slot/screen cutouts. Optional, requires precise compositing.
- `background_warm_foliage.png`: atmospheric wall with warm light and foliage; source is portrait-oriented, so responsive background positioning must be tuned.
- `texture_brushed_bronze.png`: bronze grain for selective overlay.
- `memory_envelope_extracted.png`: photo-cut extract of the original envelope, **rough and optional**; rebuild it if its edges look imperfect during animation.

### Source/design references (NOT runtime UI)
- `reference_full_page.png`: approved full-page look.
- `machine_filled_reference.png`: cabinet as originally illustrated, but contains baked-in food and copy.
- `console_filled_reference.png`: UI style illustration; actual input controls should be HTML.
- `memory_card_filled_reference.png`: card style illustration; actual photo/title/metadata should be data-driven.

### Supporting files
- `memory_machine_empty_base.png`: uncropped original source canvas.
- `memory_machine_front_bezel.png`: uncropped bezel overlay.
- `machine_geometry.json`: initial coordinates for Codex; verify in browser.
- `machine_geometry_overlay.svg`: visual construction map.
- `machine_slot_content_mask_cropped.png`: slot cutout mask.
- `preview_empty_on_dark.jpg`: quick preview only, not intended as runtime asset.

## Important limitations

The empty machine was produced by compositing over the prior generated illustration, not by recovering native source layers. Its blank compartments and screen are neutral material reconstructions rather than fully re-rendered photorealistic compartments. Preserve the cabinet's visual language but improve or regenerate portions where their realism or exact alignment is insufficient. The ideal final UI uses **real published Foodie photos and dynamic text** in accurately layered slots and screen. A raster design reference with embedded wording must not be mistaken for functional UI.

There are **no font files** in this package. Obtain appropriately licensed fonts using normal font-provider workflows; suggested pairings are Cormorant Garamond + DM Sans/Manrope, with EB Garamond as a comparison candidate. Avoid hardcoding a claim of exact font identity from an AI mockup.

No extra paid plugins are required. You may use GSAP core via npm for coordinated timelines where it adds value, or browser-native WAAPI for simple motion.
