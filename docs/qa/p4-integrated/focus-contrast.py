import json
import math
import re
from pathlib import Path
from PIL import Image


def luminance(color):
    v = [c / 255 for c in color]
    return sum((c / 12.92 if c <= .04045 else ((c + .055) / 1.055) ** 2.4) * w for c, w in zip(v, [.2126, .7152, .0722]))


output = Path('docs/qa/p4-integrated')
samples = json.loads((output / 'focus-input.json').read_text())
for sample in samples:
    actual = Image.open(sample['actual']).convert('RGB')
    background = Image.open(sample['background']).convert('RGB')
    foreground = tuple(map(int, re.findall(r'\d+', sample['color'])[:3]))
    offset, thickness = sample['offset'], sample['thickness']
    left, top = sample['x'] - offset, sample['y'] - offset
    right, bottom = sample['x'] + sample['width'] + offset, sample['y'] + sample['height'] + offset
    ratios = []
    for y in range(max(0, math.floor(top - thickness)), min(actual.height, math.ceil(bottom + thickness))):
        for x in range(max(0, math.floor(left - thickness)), min(actual.width, math.ceil(right + thickness))):
            if left <= x < right and top <= y < bottom:
                continue
            a, b = actual.getpixel((x, y)), background.getpixel((x, y))
            if max(abs(c - f) for c, f in zip(a, foreground)) <= 3 and a != b:
                v, w = luminance(foreground), luminance(b)
                ratios.append((max(v, w) + .05) / (min(v, w) + .05))
    sample['measuredPixels'] = len(ratios)
    sample['minimum'] = round(min(ratios), 3) if ratios else None
(output / 'focus-contrast.json').write_text(json.dumps({'method': 'Actual solid outline pixels in the computed ring perimeter versus the same focused viewport with only outline color transparent. Bounded visible ring samples, not all imagery or focus certification.', 'samples': samples}, indent=2) + '\n')
print([(s['name'], s['viewportWidth'], s['minimum']) for s in samples])
assert all(s['minimum'] is not None and s['minimum'] >= 3 for s in samples), 'Inspect saved focus contrast evidence'
