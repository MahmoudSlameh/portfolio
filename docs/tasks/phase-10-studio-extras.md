# Phase 10 — Studio extras

Goal: iterate on generated templates, share them and graduate them to code.
Design: [12](../12-ai-templates.md).

---

## P10-01 · Refine & versions — `todo`

- [ ] **Refine** action: instruction → new version from the current one.
- [ ] **Versions** slide-over: list, preview, activate/roll back a version.

## P10-02 · Export / import JSON — `todo`

- [ ] Export a version as `<name>.studio.json` (spec + metadata, no media).
- [ ] Import validates and sanitises exactly like AI output (`source=import`).
- [ ] `docs/templates/sharing-studio-templates.md`.

## P10-03 · Card screenshots — `todo`

- [ ] Optional automatic screenshot of the home page for the Appearance card
      (headless Chromium when available; otherwise a generated
      colour/typography swatch from the tokens).

## P10-04 · `php artisan template:eject` — `todo`

- [ ] Turns a studio template into a code template (`template.json`, layout,
      pages composed of the chosen sections, tokens → `styles.css`) for
      developers to continue in TSX.

---

## Notes
