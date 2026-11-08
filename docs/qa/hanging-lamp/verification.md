# Hanging lamp QA · 7 November 2026

[Current correction](../../hanging-lamp-correction.md). Original socket coordinates are restored and the resting tube hangs vertically outside the left side on a mounting arm. Reserved gutters keep the holder visible without horizontal page scrolling.

Executed: `npm run build` passed; `git diff --check` passed. The machine suite initially passed 18 existing scenarios and exposed one new reduced-motion cue defect: changing the preference dismissed the hint instead of preserving its static form. Corrected that lifecycle handling, then ran:

```powershell
$env:FOODIE_BROWSER_URL='http://127.0.0.1:8012'
npx playwright test tests/Browser/vending-machine.spec.js --grep 'off-state pickup|touch uses lamp|responsive geometry|resting tube' --reporter=list
```

**4 passed (32.5 seconds)**. [Final log](final-browser-checks.txt). Covers touch pickup/placement, power and printing under reduced motion, viewport/LED geometry, image failure, off/on/printed axe scans, outside-left dock alignment/containment in the viewport, finite pickup cycles even after prior discovery, zero electrical emission while off, static reduced-motion outline and dismissal on pickup.

Visual capture at 1440, 375 and 320px recorded no horizontal overflow and no page errors. [Evidence](evidence.json), [desktop](off-1440.png), [compact](off-375.png), [320px](off-320.png), [power on](on-desktop.png), [real-time pickup reflection/outline recording](pickup-cue.webm). The finite cue begins after 600ms and runs three times; it is not an endless lamp animation. The short text remains a secondary, delayed first-discovery hint.

Only the isolated cinematic.sqlite/demo-media server on port 8012 was used. No owner database, content rules, dependencies or bitmap source changed. Physical-device Safari and real screen-reader checks were not performed in this pass.
