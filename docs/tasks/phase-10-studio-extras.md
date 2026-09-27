# Phase 10 — Studio extras

Goal: iterate on generated templates, share them and graduate them to code.
Design: [12](../12-ai-templates.md).

---

## P10-01 · Refine & versions — `done`

- [x] **Renderable = has an active version.** The registry and the manager
      render a studio template whenever it has an active version, whatever
      its generation status, so a queued, running or failed refine never
      takes a live template off the site. (Before: only `status = ready`.)
- [x] **Refine** card action (AI ready, template has a version): "What
      should change?" → `StudioGenerator::refine()` = a generation that
      starts from the template's own active spec (the job already sends it
      as `<start-spec>`), with the template's reference images. Counts
      towards the daily limit; Retry works the same.
- [x] Result: a new version whose parent is the version it started from.
      If the template is **live on the site**, the new version is saved but
      **not activated** (status back to `ready`, notification: preview and
      activate it); otherwise it becomes the template's active version.
- [x] **Version preview**: `?template=studio:<ulid>&version=<n>` (admin
      only), kept in the session with the template preview and cleared by
      `?template=reset` or another `?template=`; the preview bar shows
      "version n". Previewing another version of the live template counts
      as a preview (noindex, preview bar).
- [x] **Versions** page per template (`/admin/appearance/{template}/versions`,
      not in the navigation; a card action links to it): a table of
      versions (number, active badge, how it was made — prompt excerpt,
      model, tokens, CSS notes — and when) with **Preview** and
      **Activate** (confirmation; says when visitors will see it). A table
      instead of the planned slide-over: row actions need no custom modal
      markup.

**Acceptance**: refine creates version n+1 from version n without touching
the live site; any version can be previewed and activated (roll back);
tests for all of it.

## P10-02 · Export / import JSON — `done`

- [x] File format (`App\Support\Studio\StudioFile`), `<slug>.studio.json`:
      `{"format": "portfolio-studio-template", "formatVersion": 1, "name",
"description", "exportedAt", "spec"}`. No media (reference images and
      screenshots stay private), no prompt, provider or token data.
- [x] **Export**: card action (templates with a version) downloads the
      active version; the Versions page exports any version.
- [x] **Import** (Appearance header): upload a `.json` file (≤ 100 KB) or
      paste its text; name optional (defaults to the file's name). Accepts
      the wrapped format or a bare spec (`"$schema": "studio/v1"`).
      Validated with `SpecValidator` (errors shown in the form, nothing
      created) and stored with `addVersion()` like AI output, so CSS is
      sanitised (warning with what was removed); `source = import`. The
      imported template is never activated automatically.
- [x] `docs/templates/sharing-studio-templates.md`: export, import, the
      format, what is (not) included, safety, editing a file by hand.

**Acceptance**: export → import round-trips the spec; invalid files are
rejected with readable errors; unsafe CSS is removed on import.

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

- 2026-09-27 — P10-01: `StudioGenerator::refine()`, `TemplateBrief(refine:)`,
  `StudioTemplate::isLive()` / `markReady()`, version preview in
  `TemplateManager` (`studioVersion()`, `previewVersion()`, shared prop
  `template.version`), `StudioTemplateVersions` page, Refine and Versions
  card actions. Found in the browser: the versions table was oldest first
  because the `versions()` relation already orders by number (fixed with
  `reorder()`; the test now checks the order with row text that cannot
  match the other way). Preloaded fonts still follow the active version
  when previewing another one (preload only; the page renders correctly).
  The page overrides `getRelativeRouteName()` (`studio-template-versions`):
  Filament derives route names from the slug, and a name containing
  `{template}` breaks the generated Wayfinder helpers (TypeScript error).
  Tests: `tests/Feature/Studio/RefineAndVersionsTest.php` (11 cases).
- 2026-09-27 — P10-02: `StudioFile` (export, filename, parse),
  `InvalidStudioFile`, Export on cards and on the Versions page, Import on
  Appearance (file or paste, optional name). A refused import shows a
  persistent notification and keeps the modal open. Checked in Chromium: a
  card's Export downloads `mono-grid.studio.json`, and importing that file
  creates the template. Tests: `tests/Feature/Studio/StudioFileTest.php`
  (14 cases).
