# Phase 8 — Studio engine (spec-driven templates)

Goal: a template can be stored in the database as a **Template Spec** (JSON +
scoped CSS) and rendered by one React engine with SSR. No AI yet — specs are
written by hand or seeded. Design: [12 §§ 1–4, 6](../12-ai-templates.md).
Decision: D25.

---

## P8-01 · Spec schema `studio/v1` + validator — `done`

- [x] `resources/studio/schema/v1.json` and `App\Support\Studio\SpecValidator`
      (sections, variants, props, copy keys and lengths, fonts allow-list,
      colour formats); precise error messages (path + reason).
- [x] TS types `resources/js/templates/studio/spec.ts` kept in sync (test
      compares the schema's enums with the section catalogue).
- [x] `config/studio.php`: fonts allow-list, limits.

**Acceptance**: unit tests with valid and invalid fixtures.

## P8-02 · CSS sanitiser — `done`

- [x] `App\Support\Studio\CssSanitizer` per 12 §6 (allow-list, scope
      prefixing, 40 KB cap).

**Acceptance**: unit tests with malicious fixtures (`@import`, `url(http…)`,
`</style>`, unscoped selectors, `expression()`).

## P8-03 · Models, migrations, enums — `done`

- [x] `StudioTemplate`, `StudioTemplateVersion` (+ factories, morph map
      aliases), enums `StudioSource`, `StudioStatus`, media collections
      `reference` and `screenshot`.
- [x] Registry (P7-01) lists ready studio templates as `studio:<ulid>`.

## P8-04 · `studio` React engine — `done`

- [x] `resources/js/templates/studio/` layout + 9 pages rendering the spec;
      `resources/js/pages/studio/*.tsx` wrappers.
- [x] Tokens → CSS custom properties emitted server-side in `app.blade.php`
      (light + dark, no flash); scoped CSS injected after base styles.
- [x] Initial section library and variants from 12 §3 with documented class
      hooks (`.st-*`), built on the kit.
- [x] `TemplateManager::page()` returns `studio/<Page>` for studio templates;
      shared `studio` prop carries the active version's spec.

**Acceptance**: a seeded fixture spec renders every public page with SSR;
the template test matrix includes it.

## P8-05 · Appearance: studio templates without AI — `done`

- [x] Studio cards on Appearance (preview, activate, duplicate, delete),
      "New studio template" (from an example) and a JSON spec editor with
      validation errors shown inline.

---

## Notes

- 2026-09-26 — P8-01: `SpecCatalogue` (single source of truth) +
  `config/studio.php` (fonts that ship with the app, limits);
  `SpecValidator` with path + reason errors (closed objects, unique home
  sections, typed props with ranges, plain-text copy, hex colours, mono
  fonts only for `mono`), `SpecValidationResult`, `InvalidSpecException`;
  `SpecSchema` → `resources/studio/schema/v1.json` and the engine catalogue
  `resources/js/templates/studio/catalogue.ts`, both written by
  `php artisan studio:generate` (checked by a test and `--check`);
  `spec.ts` types derived from the catalogue. The design example used
  `inter`, which the app does not ship; it now uses `geist`. Tests:
  `tests/Feature/Studio/SpecValidatorTest.php` (36 cases).
- 2026-09-26 — P8-02: `CssSanitizer` + `CssSanitizeResult` (allow-list
  parser, no new dependency). Scope simplified to `[data-template="studio"]`
  (one template per page; survives duplicate/refine). Tests:
  `tests/Feature/Studio/CssSanitizerTest.php` (27 cases: @import/@font-face/
  @charset/@namespace/@page, external and escaped url(), image-set(),
  expression(), javascript:, behavior, -moz-binding, `</style>` in selectors
  and values, unbalanced braces, nested rules, HTML comments, size cap,
  stability, the example spec). Sanitising on save is wired in P8-03/P8-05.
- 2026-09-26 — P8-03: migration (`studio_templates` with ULID key,
  `studio_template_versions` with `notes`), enums `StudioStatus`,
  `StudioSource`, models + factory (`ready()`, `failed()`, `generating()`),
  morph aliases, media collections. `addVersion()` validates and sanitises
  before storing; `activate()` switches/rolls back. `TemplateDefinition`
  gains `kind`, `namespace()`, nullable `screenshot` + `screenshotUrl()`,
  `forStudio()`; the registry merges ready studio templates (not cached,
  refreshed on save/delete); `page()` and `data-template` use the
  namespace; Appearance keys are colon-free and cards without a screenshot
  show a note. Tests: `tests/Feature/Studio/StudioTemplateTest.php`.
  The registry catches a missing table (deployed before `migrate`) and keeps serving the code templates. Studio templates only render once the engine exists (P8-04).
- 2026-09-26 — P8-04: the studio engine (see 12 §3 "As built"): shared
  `studio` prop, `StudioStyles` tokens → CSS in `<head>`, layout with 4
  headers/3 footers, 12 home sections, 8 pages, class hooks with a guard
  test, four example specs covering every variant + `StudioDemoSeeder`.
  Checked in Chromium: all 9 pages × 4 demo templates render (404 page
  included) with no console errors and no horizontal scroll at 390 px;
  light and dark. Tests: `tests/Feature/Studio/StudioEngineTest.php`.
- 2026-09-26 — P8-05: Appearance lists studio templates with status,
  version and source; header action **New studio template** starts from an
  example (a blank spec would render an empty site, so "blank" became
  "from an example"); **Edit** slide-over with a JSON `CodeEditor`, all
  validation problems in one field message, save = new version + activate +
  cache flush, persistent warning with the sanitiser notes; **Duplicate**;
  **Delete** disabled while active. Cards are rebuilt after each action
  (the page schema was built before the action ran, so the response showed
  the old version). Checked in Chromium: editor loads, invalid spec shows
  the errors, a save with an external `url()` shows "Saved as version N"
  and the CSS warning. Note: after `composer install --no-scripts`, run
  `php artisan filament:assets`, otherwise the editor does not load.
  Tests: `tests/Feature/Studio/AppearanceStudioTest.php` (9 cases).
