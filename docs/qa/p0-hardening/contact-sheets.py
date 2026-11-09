from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

root = Path(__file__).resolve().parent
font = ImageFont.truetype('C:/Windows/Fonts/arial.ttf', 18)

def sheet(name, pairs, width=660):
    rows = []
    for label, left, right in pairs:
        images = []
        for path in (left, right):
            image = Image.open(path).convert('RGB')
            image.thumbnail((width, 900))
            images.append(image)
        height = max(image.height for image in images) + 65
        row = Image.new('RGB', (width * 2 + 30, height), '#17140f')
        draw = ImageDraw.Draw(row)
        draw.text((10, 8), label, font=font, fill='#f0e2ce')
        draw.text((10, 33), 'Before', font=font, fill='#e2a76b')
        draw.text((width + 20, 33), 'After', font=font, fill='#e2a76b')
        for image, x in zip(images, (10, width + 20)):
            row.paste(image, (x, 60))
        rows.append(row)
    result = Image.new('RGB', (width * 2 + 30, sum(row.height for row in rows)), '#17140f')
    y = 0
    for row in rows:
        result.paste(row, (0, y))
        y += row.height
    result.save(root / name, quality=85)

for width in (390, 1440, 1920, 2560):
    sheet(f'public-{width}.jpg', [
        (f'{name.title()} {width}px: matched fixture / reduced motion / arrival', root / f'before-{name}-{width}.jpg', root / f'after-{name}-{width}.jpg')
        for name in ('home', 'explore', 'visit', 'place', 'error')
    ])
    sheet(f'footers-{width}.jpg', [
        (f'{name.title()} footer {width}px: same content and crop', root / f'before-{name}-footer-{width}.jpg', root / f'after-{name}-footer-{width}.jpg')
        for name in ('home', 'explore', 'visit', 'place', 'error')
    ])
sheet('place-admin.jpg', [
    ('Photo-free Place 1440: title and real facts, no reserved photo track', root / 'before-photo-free-place-1440.jpg', root / 'after-photo-free-place-1440.jpg'),
    ('Admin login 1440: system fonts and plain semantic background', root / 'before-admin-login-1440.jpg', root / 'after-admin-login-1440.jpg'),
    ('Admin form 1440: same workflow; practical type and controls', root / 'before-admin-form-1440.jpg', root / 'after-admin-form-1440.jpg'),
])
sheet('mood-reference.jpg', [
    ('Historical cabinet-on-Explore / current Explore: mood only; different IA', root.parent / 'memory-archive-v2/explore-1440.png', root / 'after-explore-1440.jpg'),
])
print('Generated matched public/footer sheets, Place/admin comparison and historical mood comparison.')

lineage = Image.new('RGB', (2010, 550), '#17140f')
draw = ImageDraw.Draw(lineage)
for index, (label, path) in enumerate([
    ('Historical 801cc57 intermediate Explore', root.parent / 'memory-archive-v2/explore-1440.png'),
    ('Current P0 before hardening, 1440px', root / 'before-explore-1440.jpg'),
    ('Repaired P0-H, 1440px (mood retained)', root / 'after-explore-1440.jpg'),
]):
    image = Image.open(path).convert('RGB')
    image.thumbnail((650, 470))
    draw.text((index * 670 + 10, 12), label, font=font, fill='#e2a76b')
    lineage.paste(image, (index * 670 + 10, 60))
lineage.save(root / 'mood-lineage.jpg', quality=85)
