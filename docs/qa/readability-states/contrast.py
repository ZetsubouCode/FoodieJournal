import json
import math
import re
from pathlib import Path
from PIL import Image


def luminance(color):
    channels = [c / 255 for c in color]
    linear = [c / 12.92 if c <= .04045 else ((c + .055) / 1.055) ** 2.4 for c in channels]
    return sum(c * w for c, w in zip(linear, [.2126, .7152, .0722]))


layouts = []
for width, scale in [(1440, 1), (390, 2)]:
    prefix = Path(f'tools/.runtime/readability-contrast-{width}-{scale}')
    layout = json.loads(prefix.with_suffix('.json').read_text(encoding='utf-8'))
    background = Image.open(prefix.with_suffix('.png')).convert('RGB')
    for sample in layout['samples']:
        foreground = luminance(tuple(map(int, re.findall(r'\d+', sample['color'])[:3])))
        contrasts = []
        for rect in sample['rects']:
            box = (math.floor(rect['x']), math.floor(rect['y']), math.ceil(rect['x'] + rect['width']), math.ceil(rect['y'] + rect['height']))
            for pixel in set(background.crop(box).get_flattened_data()):
                value = luminance(pixel)
                contrasts.append((max(foreground, value) + .05) / (min(foreground, value) + .05))
        sample['minimumContrastAcrossTextRect'] = round(min(contrasts), 2)
        assert sample['minimumContrastAcrossTextRect'] >= 4.5, sample
    layouts.append(layout)

report = {
    'method': 'Minimum contrast across complete text rectangles with only sampled text hidden; actual images, gradient, caption backing and panels retained. Rectangles include spaces; this is a conservative bounded sample, not per-glyph or sitewide conformance.',
    'layouts': layouts,
}
Path('docs/qa/readability-states/contrast.json').write_text(json.dumps(report, indent=2) + '\n', encoding='utf-8')
print('Actual-photo/panel contrast passed at 1440px normal and 390px root-text 200%.')
