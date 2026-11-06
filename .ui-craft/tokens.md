# Foodie tokens

Source: `resources/css/tokens.css`, from approved design-system-specification.md.

Primitive charcoal 950/900/800, ivory 100/300, copper 400, functional red/green/blue/yellow. Semantic page/raised/hover, primary/secondary/muted text, action/focus/status/border. Components consume semantic tokens only.

Public Cormorant Garamond display and DM Sans body (self-hosted OFL); admin retains system Georgia / Segoe UI; tabular numeric data. Fluid display and heading; body minimum 16px. 4px spacing scale; 24/32/48/64/96 section rhythm. 6px controls, 12px cards, 20px dialogs. Controls >=44px; action group minimum 7.5rem. Focus 2px copper with 3px offset. Short opacity/color transitions, reduced motion supported. Contrast measurements recorded with verification evidence.

Archive material tokens remain in memory-dispenser.css; the global public environment uses the same charcoal/ivory/copper meanings. public-chrome.css owns self-hosted font faces and the sole header/footer geometry, including standalone errors. cinematic.css owns page atmosphere variants, editorial headings, Curiosity Index and the quiet Explore console; dossier.css owns Visit Detail. .container always uses --content-max (80rem) and shared mobile gutters.

Motion categories: ambient (local tube/rail emission, one short cradle glint), mechanical (release-only lamp travel, rotary detents and button depression), reveal (real visit photo feeds through the roller), interface (--motion-fast / --motion-standard). Vending tokens in vending-machine.css: --vending-snap 270ms, --vending-boot 960ms, --vending-off 240ms, --vending-print 820ms; easing cubic-bezier(.2,.72,.22,1). No moving full-viewport layer. Reduced motion commits informative states immediately. Native cabinet aspect ratio preserves circular lenses; their source centers are unchanged. Vending-specific glass and print-ink colors are component tokens.

Homepage editorial sections use the shared spacing/type/color tokens, with asymmetric lead/supporting entries and an explicit recommendation gallery. No carousel counters or autoplay remain on Home. Existing Visit Card tokens continue to own archive result cards.
