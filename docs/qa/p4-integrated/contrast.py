import json
import math
import re
import sys
from pathlib import Path
from PIL import Image


def luminance(color):
    values = [c / 255 for c in color]
    values = [c / 12.92 if c <= .04045 else ((c + .055) / 1.055) ** 2.4 for c in values]
    return sum(c * w for c, w in zip(values, [.2126, .7152, .0722]))


output = Path('docs/qa/p4-integrated')
prefix = sys.argv[1] if len(sys.argv) > 1 else 'contrast'
layouts = json.loads((output / f'{prefix}-input.json').read_text())
failures = []
for layout in layouts:
    background = Image.open(layout['background']).convert('RGB')
    actual = Image.open(layout['actual']).convert('RGB')
    for sample in layout['samples']:
        channels = list(map(float, re.findall(r'[\d.]+', sample['color'])))
        alpha = (channels[3] if len(channels) > 3 else 1) * sample['opacity']
        ratios = []
        rectangle_ratios = []
        for rect in sample.pop('rects'):
            box = (max(0, math.floor(rect['x'])), max(0, math.floor(rect['y'])), min(background.width, math.ceil(rect['x'] + rect['width'])), min(background.height, math.ceil(rect['y'] + rect['height'])))
            if box[2] <= box[0] or box[3] <= box[1]:
                continue
            for pixel, rendered in set(zip(background.crop(box).get_flattened_data(), actual.crop(box).get_flattened_data())):
                foreground = luminance([c * alpha + b * (1 - alpha) for c, b in zip(channels[:3], pixel)])
                value = luminance(pixel)
                ratio = (max(foreground, value) + .05) / (min(foreground, value) + .05)
                rectangle_ratios.append(ratio)
                difference = [alpha * (c - b) for c, b in zip(channels[:3], pixel)]
                delta = [a - b for a, b in zip(rendered, pixel)]
                norm = sum(d * d for d in difference)
                coverage = sum(a * d for a, d in zip(delta, difference)) / norm if norm else 0
                residual = max(abs(a - coverage * d) for a, d in zip(delta, difference))
                if .75 <= coverage <= 1.1 and residual <= 12:
                    ratios.append(ratio)
        if not ratios:
            sample['unmeasured'] = 'No high-coverage glyph pixels identified; not a contrast pass'
            continue
        sample['rectangleMinimum'] = round(min(rectangle_ratios), 3)
        sample['glyphPixelPairs'] = len(ratios)
        sample['minimum'] = round(min(ratios), 3)
        sample['threshold'] = 3 if sample['size'] >= 24 or (sample['size'] >= 18.667 and int(sample['weight']) >= 700) else 4.5
        if sample['minimum'] < sample['threshold']:
            failures.append({'width': layout['width'], 'route': layout['route'], **sample})
report = {'method': 'Compare actual/background PNGs with only glyph fill transparent, retaining text shadow/material. Minimum background contrast at pixels fitting the expected foreground blend with >=75% glyph coverage and <=12-channel residual; includes alpha/ancestor opacity. Rectangle minima retained separately. Exclude hidden/inert/disabled/collapsed text. Not sitewide certification.', 'layouts': layouts, 'needsReview': failures}
(output / f'{prefix}.json').write_text(json.dumps(report, indent=2) + '\n')
print(f"Sampled {sum(len(l['samples']) for l in layouts)} text runs; {len(failures)} glyph contrast failures require inspection.")
assert any('minimum' in s for layout in layouts for s in layout['samples']), 'No measured glyph evidence'
assert not failures, 'Rendered text contrast failed; inspect the saved diagnostic before changing source'
