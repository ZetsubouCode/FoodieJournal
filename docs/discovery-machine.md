> Current art direction and interaction revision: [7 November refinement](vending-machine-refinement.md). It supersedes the older outboard dock and pagination-button descriptions below.

# Homepage Memory Archive

The playable cabinet lives on Home only. Its current implementation is documented in [Vending machine interactions](vending-machine-interactive.md). The approved native 1634×963 shell and shared public chrome remain; the lamp, socket, cradle, rotary dial, reset face, LEDs and printed photo are independent layers.

Fresh sessions start OFF. A valid release installs the tube, then boots the cabinet. Six actual published visits form each page, the upper dial turns both ways, the lower push-button resets, and **Ambil memori** prints the selected visit's photo. Touch and keyboard can select the lamp and then its target. Site navigation and semantic visit links remain available without playing or without JavaScript.

Explore remains the Curiosity Index and retains its existing Visit/Food/Place filters, history, photo matching and domain semantics. `DiscoveryMachine`, `ExploreState`, `ExploreQuery` and `PublicOptions` continue to support existing collection and surprise clients. `X-Archive-Fragment` returns `archive-collection-fragment.blade.php`; it does not instantiate another game or expose unfiltered vending records. The homepage now uses `VendingMachine` and `X-Vending-Fragment` on the existing home route. No new route, database schema or anonymous server writes were introduced.

The earlier envelope, random-draw knobs and lamp-pause reports are historical QA. Their interaction descriptions are superseded by the supplied [implementation package](../codex_instruction/vending-machine/CODEX-INSTRUCTIONS-vending-machine-interactive.md). Asset provenance, motion values, state diagrams, control mapping and current evidence are in the focused interaction document.
