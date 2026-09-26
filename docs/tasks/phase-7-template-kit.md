# Phase 7 — Template kit

Goal: adding a template is one command plus React work, with a clear
contract and shared hooks. Design: [11 · Template kit](../11-template-kit.md).
Decision: D26.

---

## P7-01 · Template registry replaces the `Template` enum — `todo`

- [ ] `TemplateDefinition` value object + `TemplateRegistry` (code templates
      from `resources/js/templates/*/template.json`, cached).
- [ ] `template.json` for changelog, playground, terminal (values moved from
      the enum).
- [ ] `SiteSetting.active_template` → plain string validated by the
      registry; unknown value falls back to the default template.
- [ ] `TemplateManager`, `Appearance`, `app.blade.php`, `HandleInertiaRequests`
      and the Pest datasets use the registry. Delete `App\Enums\Template`.

**Acceptance**: no visible change; all existing tests green; a test adds a
fake manifest and sees it in the registry.

## P7-02 · Kit: shared hooks & components — `todo`

- [ ] `resources/js/kit/index.ts` exporting `useContactForm`,
      `useArchiveFilters`, `useSiteSearch`, `useTheme`, `ArticleBlocks`
      (headless) and the existing shared components and helpers.
- [ ] Migrate the three templates' contact forms, archive filters and search
      onto the kit (no visual change).

**Acceptance**: `npm run types:check`, `npm run check` pass; contact and
filter feature tests still pass for every template.

## P7-03 · `minimal` starter template — `todo`

- [ ] Plain, heavily commented template implementing all 9 pages with the kit.
- [ ] Screenshot `public/templates/minimal.webp`.

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
