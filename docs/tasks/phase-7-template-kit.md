# Phase 7 — Template kit

Goal: adding a template is one command plus React work, with a clear
contract and shared hooks. Design: [11 · Template kit](../11-template-kit.md).
Decision: D26.

---

## P7-01 · Template registry replaces the `Template` enum — `done`

- [x] `TemplateDefinition` value object + `TemplateRegistry` (code templates
      from `resources/js/templates/*/template.json`, cached).
- [x] `template.json` for changelog, playground, terminal (values moved from
      the enum).
- [x] `SiteSetting.active_template` → plain string validated by the
      registry; unknown value falls back to the default template.
- [x] `TemplateManager`, `Appearance`, `app.blade.php`, `HandleInertiaRequests`
      and the Pest datasets use the registry. Delete `App\Enums\Template`.

**Acceptance**: no visible change; all existing tests green; a test adds a
fake manifest and sees it in the registry.

## P7-02 · Kit: shared hooks & components — `done`

- [x] `resources/js/kit/index.ts` exporting `useContactForm`,
      `useArchiveFilters`, `useSiteSearch`, `useTheme`, `ArticleBlocks`
      (headless) and the existing shared components and helpers.
- [x] Migrate the three templates' contact forms, archive filters and search
      onto the kit (no visual change).

**Acceptance**: `npm run types:check`, `npm run check` pass; contact and
filter feature tests still pass for every template.

## P7-03 · `minimal` starter template — `done`

- [x] Plain, heavily commented template implementing all 9 pages with the kit.
- [x] Screenshot `public/templates/minimal.webp`.

## P7-04 · `php artisan make:template` — `todo`

- [ ] Copies `minimal` (or `--from=<id>`) into `resources/js/templates/<id>`,
      generates `resources/js/pages/<id>/*.tsx` wrappers and a placeholder
      screenshot, rewrites ids/class scopes.
- [ ] Refuses existing ids and invalid slugs.

**Acceptance**: `make:template demo && npm run build:ssr && php artisan test`
passes with the new template in the matrix (test in CI with a temp dir).

## P7-05 · `/dev/templates` gallery (local only) — `todo`

- [ ] Route registered only in the local environment; renders any page of any
      template with demo data; light/dark toggle.

## P7-06 · Guide: `docs/templates/building-a-template.md` — `todo`

- [ ] Step-by-step guide + per-page prop reference + rules from 11 §6.
- [ ] Update 06 § "Adding a new template later" to point to it.

---

## Notes

- 2026-09-26 — P7-01: `TemplateRegistry` + `TemplateDefinition` read
  `resources/js/templates/<id>/template.json`; `App\Enums\Template` deleted.
  `active_template` is a plain string; unknown ids fall back to the default
  (config `portfolio.templates.default`). New `template:cache` /
  `template:clear` commands run with `optimize` / `optimize:clear`. The
  shared `template` prop now also carries `name`, so `PreviewBar` no longer
  hard-codes labels. Tests: `tests/Unit/TemplateRegistryTest.php`
  (discovery, fallback, invalid manifests, cache, nine page files per
  template), fallback feature test, commands test; the `templates` dataset
  lives in `tests/Pest.php` (`templateIds()`). `composer ci:check` green
  (185 tests).
- 2026-09-26 — P7-02: `resources/js/kit` (`@/kit`): `useContactForm`,
  `CONTACT_FIELDS`/`CONTACT_TOPICS`, `useArchiveFilters` (moved from
  `shared/inertia/useSearchChange.ts`), `useSiteSearch` + `rankByQuery`
  (now also used by the command palette), `useTheme`, headless
  `ArticleBlocks` with exhaustive `ArticleBlockRenderers`, and re-exports of
  the shared hooks, components and helpers. The three templates' contact
  forms (~70 duplicated lines each), archive wrappers, theme toggles and
  article bodies now use the kit. **Bug fixed:** article `image` blocks
  (supported by the panel's block builder) were rendered by no template;
  the exhaustive renderer type surfaced it and each template now renders a
  figure with caption. Checked in Chromium on all three templates: contact
  validation summary, clearing while typing, submission stored in the
  inbox, success state; image blocks with caption. `composer ci:check`
  green.
- 2026-09-26 — P7-03: `resources/js/templates/minimal` (manifest, styles,
  layout, components, nine pages, README) and `resources/js/pages/minimal`
  wrappers, built only on `@/kit`; system fonts, so `preloadFonts` is empty
  (the registry test now allows that and still checks listed files exist).
  The nav hides pages switched off in the panel. Screenshot
  `public/templates/minimal.webp` (960×600) taken from the running app with
  the demo content. Checked in Chromium: every page renders (404 for
  unknown URLs), project search filter, theme toggle, contact form
  validation and submission, light/dark and mobile.
