# 08 · Conventions

## Commands

| Purpose                     | Command                                                                 |
| --------------------------- | ----------------------------------------------------------------------- |
| Dev (server + queue + vite) | `composer dev`                                                          |
| PHP format                  | `composer lint` (Pint, `laravel` preset) · check: `composer lint:check` |
| PHP static analysis         | `composer types:check` (Larastan level 7)                               |
| PHP tests                   | `php artisan test` (Pest 5) — `composer test` runs lint + types + tests |
| JS lint/format              | `npm run check` / `npm run check:fix` (vite-plus)                       |
| TS types                    | `npm run types:check`                                                   |
| Everything CI runs          | `composer ci:check`                                                     |
| Build                       | `npm run build` · with SSR `npm run build:ssr`                          |
| Filament resource           | `php artisan make:filament-resource Experience --generate --view`       |
| Filament page               | `php artisan make:filament-page EditProfile`                            |

A task is **not done** until `composer ci:check` passes.

## PHP / Laravel

- Follow the style of the starter kit: PHP 8 attributes on models
  (`#[Fillable]`, `#[Hidden]`), typed properties, `casts()` method, docblock
  `@property` annotations for Larastan.
- Enums in `app/Enums`, string-backed, implementing Filament `HasLabel`,
  `HasColor`, `HasIcon` where shown as badges.
- Models: `HasFactory`; every model gets a factory (used by tests).
  Mirror every DB column default in the model `$attributes` (a freshly
  `create()`d model is not refreshed from the DB); `json` columns have no DB
  default at all (MySQL, D15) — their default lives only in `$attributes`.
  Month-precision dates are stored as the first day of the month.
- **Morph map is enforced** (`AppServiceProvider::configureMorphMap`): every
  new model must be added with a short snake_case alias, or media uploads /
  pivots / notifications on it throw.
- Reusable model concerns live in `app/Models/Concerns`: `IsSingleton`,
  `GeneratesSlug`, `HasSortOrder`, `HasVisibilityAndOrder`, `HasSkills`,
  `RegistersImageConversions`. Prefer them over re-implementing.
  Scopes: `visible()`, `ordered()`, `published()`.
- Controllers stay thin: query → resource → `Inertia::render`. Put derived
  computations (career entries, book stats, reading time, search index) in
  small classes under `app/Support/Content/*` or model accessors.
- Validation in Form Requests. No `$request->all()` mass assignment.
- Cache busting via model observers (`app/Observers`) registered with the
  `#[ObservedBy]` attribute.
- Filament resources: keep form schema in `Schemas/*Form.php` and table in
  `Tables/*Table.php` (Filament 5 default layout).
- Filament form rules learned the hard way: every `Repeater` gets
  `->defaultItems(0)` (otherwise create pages start with an empty required
  item); stacks use `Fields::stack()` + the `SyncsStack` page trait (keeps
  order); images use `Fields::image()` (Spatie upload) + `Fields::alt()`;
  month dates use `Fields::month()`; "current" roles/studies use
  `Fields::currentToggle()`. Never call external services (e.g. avatar APIs)
  from the panel — use `InitialsAvatar`.
- Resource forms default to 2 columns: the top-level layout `Grid`/`Tabs` must
  call `->columnSpanFull()` or the whole form is squeezed into half the width.
- Panel avatars use `App\Filament\AvatarProviders\InitialsAvatarProvider`
  (local SVG) instead of Filament's default ui-avatars.com provider.
- Visually check new screens (Playwright screenshot against `php artisan serve`
  with the demo seed) — tests don't catch layout problems.
- Don't add a package when a small class does the job; when adding one,
  record it in [09-decisions.md](09-decisions.md).

## TypeScript / React

- Formatting per `vite.config.ts` `fmt` (4 spaces, single quotes, semicolons,
  width 80). The reference code uses 2 spaces — reformat on port with
  `npm run check:fix`.
- Path alias `@/*` → `resources/js/*` (same as reference `@/*` → `src/*`,
  so imports port 1:1).
- Types for server data live in `resources/js/types/content.ts` and must
  match the PHP API Resources exactly.
- No data fetching in components; everything arrives as Inertia props.
- SSR-safe code only (no browser globals during render).

## Empty content

Every list the owner manages can be empty on a fresh install. Home pages render a
section only when its list has items (see each template's `HomePage`), and
components must never index into a list without a guard (`list[0]`, `list[active]`).
Before a release, load every page against an empty database (`php artisan
migrate:fresh --seed` without the demo seeder) and check the browser console.

## Testing (Pest)

- Feature test per public route × per template (the `templates`
  dataset in `tests/Pest.php`, one entry per `template.json`): status 200, component name, required props, SEO prop.
- Filament tests with `livewire()` helpers: list/create/edit/delete per
  resource, validation rules (e.g. end date ≥ start date), media upload
  (`UploadedFile::fake()->image()`), singleton pages save.
- Unit tests for derived logic: experience branch/version/commit
  derivation, reading time, book stats, company period label, `TemplateManager`
  preview rules, sitemap content.

## Git workflow

- Branch per phase or task (`feature/p1-data-layer`), small commits, message
  style `feat(admin): experience resource` / `fix(seo): ...` / `docs: ...`.
- Update the task status in `docs/tasks/*.md` **in the same commit** as the
  work.
- Never commit `.env`, `database/database.sqlite`, `storage/app/public/*`
  (media), `public/build`, `bootstrap/ssr`.

## Definition of done (every task)

1. Acceptance criteria in the task file are met.
2. Tests added/updated; `composer ci:check` green.
3. Docs updated if behaviour or schema changed (docs are the contract).
4. Task checkbox ticked + status board in `docs/tasks/README.md` updated.
