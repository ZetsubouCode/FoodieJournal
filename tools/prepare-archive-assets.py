"""Optimize source artwork and derive exact physical crop layers; never touches uploads."""
from pathlib import Path
from PIL import Image

target = Path('public/assets/explore/archive')
target.mkdir(parents=True, exist_ok=True)
cabinet = Image.open('resources/artwork/archive-cabinet-v2.png')
cabinet.save(target / 'cabinet-v2.webp', 'WEBP', quality=93, method=6)
for name, box, quality in [
    ('knob-upper.webp', (50, 560, 110, 620), 95),
    ('knob-lower.webp', (50, 690, 110, 750), 95),
    ('pickup-lip.webp', (315, 891, 810, 916), 95),
    ('pickup-compact.webp', (150, 775, 960, 929), 93),
    ('slot-compact.webp', (154, 194, 417, 476), 93),
]:
    cabinet.crop(box).save(target / name, 'WEBP', quality=quality)
for name, output, width, quality in [
    ('background_warm_foliage.png', 'atmosphere.webp', 1122, 85),
    ('texture_brushed_bronze.png', 'bronze.webp', 512, 82),
]:
    artwork = Image.open(Path('Foodie_Memory_Archive') / name)
    artwork.thumbnail((width, width * 2), Image.Resampling.LANCZOS)
    artwork.save(target / output, 'WEBP', quality=quality, method=6)
print('Archive V2 runtime artwork rebuilt from project-local sources.')
