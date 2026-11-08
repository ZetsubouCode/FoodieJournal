# Reset-height lamp holder · 7 November 2026

Latest user correction implemented: resting lamp stays outside LEFT, centred beside RESET rather than beside the socket. Desktop resting centre is 81.55% of cabinet height, while socket centre stays 41.35%; compact resting centre is 134px in the hardware panel, matching the reset button centre. Reserved gutters remain. A new local `side-mount.svg` provides a vertical bolted sidewall plate, two tapered arms and gusset webs behind the cradle bed. The single thick CSS pipe is disabled.

All visible pickup copy, its DOM node, CSS and delayed label timer were removed. Accessible button names and screen-reader instructions remain. Implicit discovery uses finite metal-cap edge catches, a glass specular sweep, and a small hover/focus pickup response. The cue does not illuminate the core. Reduced motion retains a static material highlight. No tutorial box, tooltip or idle rectangular UI outline is used.

Executed against the isolated cinematic SQLite/demo media server (8012):

- `npm run build`: passed. [Build log](build.txt).
- `FOODIE_BROWSER_URL=http://127.0.0.1:8012 npx playwright test tests/Browser/vending-machine.spec.js --reporter=list`: **19 passed (2.0 minutes)**. [Log](browser-tests.txt).
- Final CSS visual/accessibility recheck with `--grep 'off-state pickup|responsive geometry'`: **2 passed**. [Log](visual-recheck.txt).
- `git diff --check`: passed.

The suite checks release-only snap/removal, cancel/interruption, touch/keyboard, persistence, detent pagination, stale requests, print identity/retrieval, reset, missing images, JavaScript-free navigation, compiled timing and reduced motion. New placement assertions require the tube centre to align with reset within 2px and be clearly separated vertically from the socket on desktop. The hint test asserts no visible label node. Automated axe scans passed for off/on/printed states.

Screenshots inspected at [1440px](off-1440.png), [375px](off-375.png) and [320px](off-320.png); no horizontal overflow or page errors in capture. [Geometry observations](evidence.json). Physical Safari/iOS and actual screen-reader testing were not performed. No owner data, content visibility rules, dependencies or bitmap artwork changed.
