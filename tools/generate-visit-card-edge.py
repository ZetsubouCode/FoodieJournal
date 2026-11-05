"""Rebuild the two photo masks and fiber strips from the approved F Refined contour.

Coordinates below are hand traced from docs/design/references/visit-card-f-refined.png
(556 x 360). Only edge geometry is derived from the reference; no screenshot pixels
are shipped as card content. Keep photo masks and fiber strips on the same path.
"""

from pathlib import Path

OUT = Path(__file__).resolve().parents[1] / "public/assets/visit-card"

# The reference seam starts near x=178, swells toward x=199 through the middle,
# then recedes to x=160 at the lower edge. Uneven local nicks follow its deckle.
vertical = [
    (0, 177), (8, 178), (15, 177), (22, 181), (29, 180), (37, 185),
    (44, 184), (51, 188), (59, 187), (66, 190), (74, 187), (81, 190),
    (89, 189), (97, 191), (104, 187), (111, 193), (119, 189), (126, 194),
    (134, 192), (141, 197), (149, 194), (156, 198), (164, 195), (171, 200),
    (179, 197), (186, 199), (194, 195), (201, 197), (209, 192), (216, 194),
    (224, 188), (231, 190), (239, 183), (246, 185), (254, 179), (261, 182),
    (269, 176), (276, 178), (284, 173), (291, 175), (299, 169), (306, 172),
    (314, 166), (321, 168), (329, 163), (336, 165), (344, 160), (351, 163),
    (360, 159),
]

# Mobile turns the traced vertical silhouette through ninety degrees, retaining
# its slow middle swell and nonperiodic nicks at a shallower depth.
horizontal = [(y, round(220 + (x - 180) * .45)) for y, x in vertical]


def path(points):
    first, *rest = points
    return f"M{first[0]} {first[1]} " + " ".join(f"L{x} {y}" for x, y in rest)


def write(name, content):
    (OUT / name).write_text(content + "\n", encoding="utf-8")


v_strip = path([(x - 168, y) for y, x in vertical])
h_strip = path([(x, y - 188) for x, y in horizontal])

write("photo-mask.svg", f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 220 360" preserveAspectRatio="none">
  <path fill="white" d="M0 0 H177 {' '.join(f'L{x} {y}' for y, x in vertical[1:])} H0 Z"/>
</svg>''')
write("mobile-photo-mask.svg", f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 360 240" preserveAspectRatio="none">
  <path fill="white" d="M0 0 H360 V{horizontal[-1][1]} {' '.join(f'L{x} {y}' for x, y in reversed(horizontal[:-1]))} Z"/>
</svg>''')

filter_defs = '<defs><filter id="fiber" x="-70%" y="-70%" width="240%" height="240%"><feTurbulence type="fractalNoise" baseFrequency=".09 .38" numOctaves="3" seed="7" result="grain"/><feDisplacementMap in="SourceGraphic" in2="grain" scale="1.8"/></filter></defs>'


def strip(view_box, geometry):
    return f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="{view_box}" preserveAspectRatio="none">
  {filter_defs}
  <path d="{geometry}" fill="none" stroke="#050403" stroke-opacity=".44" stroke-width="8" filter="url(#fiber)"/>
  <path d="{geometry}" fill="none" stroke="#e6c29a" stroke-opacity=".84" stroke-width="4.5" filter="url(#fiber)"/>
  <path d="{geometry}" fill="none" stroke="#f4dec0" stroke-opacity=".7" stroke-width="1.2"/>
</svg>'''


write("vertical-tear.svg", strip("0 0 52 360", v_strip))
write("horizontal-tear.svg", strip("0 0 360 52", h_strip))
