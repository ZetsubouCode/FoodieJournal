# UX principles in Foodie decisions

These are decision aids, not formulas or proof of usability. Validate them through [page journeys](page-experience.md), observed failures and acceptance tests.

| Principle | Concrete Foodie application | What to verify |
| --- | --- | --- |
| Hick–Hyman / choice complexity | Simple Beranda/Jelajah navigation; Explore exposes common controls and stages advanced dimensions in the drawer | First-time reader can choose a path without reading every taxonomy; do not hide necessary filters merely to lower choice count |
| Fitts / target acquisition | ≥44px internal touch goal for lamp/target/index/reset and primary actions; forgiving release zone uses tube centre | Drag/tap/keyboard alternatives and narrow-width targets remain reachable without accidental actions |
| Miller / chunking | Group location, food, rating/cost and editorial criteria in the drawer; group dossier facts separately from prose | Labels explain groups; no literal seven-item cap or invented scientific limit |
| Gestalt grouping | Material/depth joins lamp/socket and tray/paper; type and spacing join dated price/rating facts | Readers distinguish one visit's facts from the place aggregate and unrelated recommendations |
| Jakob / familiar conventions | Real links, GET forms, browser Back/Forward, Escape and native dialogs retain predictable behavior | Modified click/no JS works, filters survive refresh, viewer closes and restores focus |
| Recognition over recall | Named visits, preview photo, selected window, chips and pending selections remain visible | Readers need not memorize code A1, a prior filter or which story a printed photo opens |
| Nielsen / visibility and recovery | Actual busy state and live status, committed results retained on error, retry and clear filter actions | Failures explain impact, offer a working recovery and avoid false success; labels match action |
| Tesler / complexity placement | Shared backend owns price context, same-item matches, visibility and null aggregates | Visitor sees truthful simple labels; presentation never reimplements domain rules or hides uncertainty |
| Doherty / responsiveness | Press/contact feedback occurs promptly; data requests start immediately, with real busy feedback | No fake wait or long animation gate; measure interaction latency rather than claiming one universal threshold |
| Peak–end | Causal print/retrieval is the memorable moment; footer offers a coherent quiet ending | Photo opens the exact visit, repeated use remains quick, footer never blocks reading/navigation |
| Cognitive load | One cabinet on Home; practical Explore work area; text and photo variants omit unavailable content honestly | Richness supports finding/remembering stories; reduce repeated tiny labels and needless motion before adding decoration |

Use these rationales when choosing between maintainable alternatives. A richer bento or 3D treatment succeeds only if the visitor can still recognize content, operate controls and recover reliably.

P0-H applies these concretely: a Place cover stays recognizable across history pages; unavailable photography yields real facts rather than an empty column; ultrawide grids gain useful columns while prose/cabinet retain their limits; admin uses system fonts/plain surfaces for repeatable work. Saved success is announced once, and actual Explore waits retain committed results/URL with retry on failure. New skeletons stay P2; no artificial waiting is introduced. [Decisions and evidence](adr/002-p0-hardening.md).
