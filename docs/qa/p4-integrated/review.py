from pathlib import Path
import sys
from PIL import Image, ImageOps, ImageDraw

output = Path('docs/qa/p4-integrated')
baselines = Path('tests/Browser/__screenshots__/public-visual-win32-chromium')
if '--candidates' in sys.argv:
    baselines = output / 'candidates'


def sheet(files, name, cell=(400, 650), columns=3):
    image = Image.new('RGB', (columns * cell[0], ((len(files) + columns - 1) // columns) * cell[1]), '#191b19')
    draw = ImageDraw.Draw(image)
    for i, path in enumerate(files):
        x, y = i % columns * cell[0], i // columns * cell[1]
        picture = Image.open(path).convert('RGB')
        picture.thumbnail((cell[0] - 16, cell[1] - 36))
        image.paste(picture, (x + 8, y + 28))
        draw.text((x + 8, y + 6), path.name, fill='#f5f0e7')
    image.save(output / name, quality=86)


for route in ['home', 'explore', 'visit', 'place', 'error']:
    files = sorted(baselines.glob(f'{route}-*-hero.png')) + sorted(baselines.glob(f'{route}-*-footer.png'))
    if files:
        sheet(files, f'review-baseline-{route}.jpg')
states = [p for p in sorted(baselines.glob('*.png')) if not p.name.endswith(('-hero.png', '-footer.png'))]
sheet(states, 'review-baseline-states.jpg')
errors = sorted(output.glob('http-*.jpg'))
if errors:
    sheet(errors, 'review-http.jpg')
details = [output / f for f in ['toolbar-before.png', 'toolbar-after.png', 'drawer-focus.jpg', 'busy.jpg', 'viewer-focus.jpg'] if (output / f).exists()]
if details:
    sheet(details, 'review-repairs-states.jpg', (600, 650), 2)
for width in [375, 1440]:
    crops = []
    for path in sorted(output.glob(f'spacing-{width}-*.jpg')) + sorted(output.glob(f'text-{width}-*.jpg')):
        image = Image.open(path)
        crop = output / f'review-{path.stem}-top.jpg'
        image.crop((0, 0, image.width, min(image.height, 1400))).save(crop, quality=86)
        crops.append(crop)
    if crops:
        sheet(crops, f'review-text-spacing-{width}.jpg')
