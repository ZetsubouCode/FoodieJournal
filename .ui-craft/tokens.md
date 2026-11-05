# Foodie tokens

Source: `resources/css/tokens.css`, from approved design-system-specification.md.

Primitive charcoal 950/900/800, ivory 100/300, copper 400, functional red/green/blue/yellow. Semantic page/raised/hover, primary/secondary/muted text, action/focus/status/border. Components consume semantic tokens only.

System Georgia display; system Segoe UI/Arial body; tabular numeric data. Fluid display and heading; body minimum 16px. 4px spacing scale; 24/32/48/64/96 section rhythm. 6px controls, 12px cards, 20px dialogs. Controls >=44px; action group minimum 7.5rem. Focus 2px copper with 3px offset. Short opacity/color transitions, reduced motion supported. Contrast measurements recorded with verification evidence.

Visit Card F Refined adds `--visit-card-paper`, `--visit-card-star`, `--visit-card-chip-border`, and `--visit-card-photo-fallback` in `resources/css/tokens.css`. The card consumes these alongside shared surface, text, action, focus, shadow, and motion tokens; the SVG edge files carry their own fixed paper/stroke colors to remain standalone assets.

The homepage-specific composition tokens live together in `resources/css/home.css`: `--home-ink`, `--home-copper` (mapped to the existing action accent), `--home-outline`, `--home-section-gap`, and `--home-carousel-duration`. They cover the signature footer, quiet borders, section rhythm and rail animation; shared text, focus, surface and spacing continue to come from `resources/css/tokens.css`. Desktop rail cards retain the full F Refined typography; the visible item count adapts through clipping and partial next-card exposure rather than scaling the card down.
