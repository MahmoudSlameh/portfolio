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

## P10-03 · Card screenshots — `done`

- [x] **Swatch** (always available, no dependency):
      `App\Support\Studio\StudioSwatch::svg($spec)` draws the light and
      dark palettes (bg, surface, text, muted, accent, border), a heading in
      the display font's family, the radius, and the header/hero variants,
      as an SVG data URI. Studio cards show it when there is no screenshot.
- [x] **Screenshot** (optional): enabled when `STUDIO_SCREENSHOT_CHROME`
      points at a Chrome/Chromium binary (`config/studio.php` →
      `screenshots`). `App\Jobs\CaptureStudioScreenshot` runs the binary
      with `--headless --screenshot` (Symfony Process, no new package) at
      1280 × 800 on a **signed URL valid for 5 minutes** that renders that
      template (the gallery's stateless `?_template=` override, now also
      honoured when `RenderSignature` is valid), saves it to the template's
      `screenshot` media and deletes the temporary file. Failures are
      reported and leave the swatch in place.
- [x] Captured automatically (queued) whenever a template's active version
      changes; card action **Refresh screenshot** (when enabled) and
      `php artisan studio:screenshots` for existing templates.
- [x] Override renders (gallery or signed) are always `noindex` and never
      include the analytics snippet.

**Acceptance**: every studio card shows an image (swatch or screenshot);
screenshots never need a login or leak a public preview URL; tests fake the
process.

## P10-04 · `php artisan template:eject` — `done`

`php artisan template:eject <studio template> <new-id> [--name=] [--author=] [--no-format]`
(`<studio template>`: `studio:<ulid>`, the ulid or the exact name).

- [x] `App\Support\Templates\TemplateEjector` copies the whole studio
      engine (`resources/js/templates/studio`) and its nine Inertia pages to
      `<new-id>`, renamed like `make:template` does (import paths,
      `[data-template]` scope, `StudioLayout` → `<Id>Layout`). The copy is
      plain TSX the developer owns: sections, variants and markup can all
      change.
- [x] The active spec is frozen into `frozenSpec.ts`
      (`export const spec: TemplateSpec = {…}`, type-checked) and the copied
      `useSpec.ts` reads it instead of the shared `studio` prop.
- [x] Tokens and the spec's (already sanitised) CSS are written to the
      template's `styles.css` (`StudioStyles::render()`, rescoped), after
      the engine's base styles.
- [x] `template.json` (fonts to preload from the spec), a README with next
      steps, the stylesheet `@import`, and a screenshot: the studio
      template's screenshot when it has one, else its swatch as SVG.
- [x] Id checks shared with `make:template` (format, reserved, taken). The
      studio template itself is untouched; the new code template can be
      activated like any other.

**Acceptance**: an ejected template type-checks, builds and renders every
page like the studio template it came from (checked in the browser);
tests run against a temporary copy of the engine.

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
- 2026-09-27 — P10-03: `StudioSwatch`, `CaptureStudioScreenshot`,
  `RenderSignature`, `studio:screenshots`, **Refresh screenshot**.
  Findings: (1) Laravel's relative signed URLs never validate on `/`
  (`hasValidRelativeSignature()` compares against `'/'.path()`, i.e. `//`),
  and absolute ones break when Chrome reaches the site on another host; so
  the render uses its own HMAC over template id, theme and expiry. The
  first real capture had only looked right because `APP_ENV=local` enables
  the dev gallery; re-checked with `TEMPLATE_GALLERY=false`: unsigned
  `?_template=` falls back to the active template, the signed capture shows
  the studio template. (2) A job's static `queue()` method is called by the
  dispatcher to push it, so the helper is `captureIfEnabled()`. Captures
  take ~0.9 s each with the demo content. Tests:
  `tests/Feature/Studio/StudioScreenshotTest.php` (10 cases, Chrome faked
  with `Process::fake()`).
- 2026-09-27 — P10-04: `TemplateEjector`, `template:eject`;
  `TemplateScaffolder::assertNewId()` / `importStylesheet()` are now shared.
  The frozen spec is a TypeScript module rather than `spec.json`
  (`resolveJsonModule` is off, and a typed constant is checked by `tsc`).
  Checked for real: ejecting "Neon Brutalist" as `neon-eject` type-checked,
  passed lint and `npm run build`, the template test matrix picked it up
  (428 tests green), and all 7 pages rendered pixel-identical to the studio
  original in Chromium; the demo template was then removed. Tests:
  `tests/Feature/TemplateEjectCommandTest.php` (8 cases, temporary copy of
  the engine).
