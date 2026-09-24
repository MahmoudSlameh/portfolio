# Phase 1 — Data layer

Docs: [03-data-model](../03-data-model.md) (the spec — follow column names
exactly), [05-media](../05-media.md).

General for every model: migration + model (fillable attrs, `casts()`,
`@property` docblocks, relations, scopes `visible()`/`ordered()`), factory,
Pest unit test for casts/relations/derived values.

---

## P1-01 · Enums — `todo`

- [ ] Create every enum from the table in 03 under `app/Enums`, string-backed.
- [ ] Implement `HasLabel` (+ `HasColor`/`HasIcon` for: ProjectStatus,
      ArticleStatus, ReadingStatus, WorkMode, EmploymentType, CompanyKind,
      AvailabilityStatus, ContactTopic, SocialPlatform).
- [ ] `Template` enum: `label()`, `description()`, `fontsHref()` (copy URLs
      from `Reference-Frontend/src/templates/*/index.ts`; drop Arabic font
      families), `screenshot()` path.

**Acceptance**: `Template::cases()` = 3; labels human-readable; Pest test
covers `fontsHref()` non-empty for each case.

---

## P1-02 · Singletons: Profile, SiteSetting, NowPage — `todo`

- [ ] Migrations per 03 (json columns default `[]`/`{}`).
- [ ] `Profile::current()`, `SiteSetting::current()`, `NowPage::current()`
      (`firstOrCreate` with sensible defaults; cached; observer flushes cache).
- [ ] `Profile` & `SiteSetting` implement `HasMedia` with collections from 05.
- [ ] `NowPage::readingBooks()` pivot migration can wait for P1-06 (books).

**Acceptance**: calling `current()` twice returns the same row; media
collections registered; saving flushes the cache key.

---

## P1-03 · Career models — `todo`

Companies, Experiences, Education, Certifications, Testimonials.

- [ ] Migrations with FKs (`nullOnDelete` for company links) and indexes on
      `slug`, `sort_order`, `start_date`.
- [ ] `Experience` accessors: `is_current`, `location_label`,
      `resolved_branch`, `resolved_version`, `resolved_commit`,
      `resolved_message` (derivation rules in 03).
- [ ] `Company::period_label` fallback derived from experiences.
- [ ] `Education::is_current`, `location_label`.
- [ ] Media collections (logo, certificate, badge, avatar).
- [ ] Factories with realistic states (`current()`, `remote()`, `client()`).

**Acceptance**: unit tests for every derivation rule, including
"end_date null ⇒ current" and branch derivation from employment type.

---

## P1-04 · Skills — `todo`

- [ ] `skill_categories`, `skills`, `skillables` (morph pivot with
      `sort_order`).
- [ ] `HasSkills` trait (`skills()` morphToMany ordered by pivot) for
      Experience and Project.

**Acceptance**: attaching skills keeps order; `stack` array returns names in
order.

---

## P1-05 · Projects — `todo`

- [ ] `projects` (SoftDeletes) + `project_gallery_items` (HasMedia `image`).
- [ ] Casts for json fields; `Architecture` json validated shape (value
      object or array shape docblock).
- [ ] Scopes `published()`, `featured()`; route key `slug`.

**Acceptance**: factory creates a complete project incl. architecture,
metrics, links, 2 gallery items with fake images.

---

## P1-06 · Content models — `todo`

- [ ] `articles` (SoftDeletes, `body` json, `tags` json) + `article_project`.
- [ ] `books`, `uses_groups`, `uses_items`, `socials`, `contact_messages`,
      `now_page_book`.
- [ ] `Article::readingMinutes()` — same algorithm as
      `Reference-Frontend/src/lib/content.ts#countWords` (220 wpm).

**Acceptance**: reading time test matches the reference result for the
`ledgers-are-just-logs` article.

---

## P1-07 · Derived-data support classes — `todo`

Port `Reference-Frontend/src/lib/content.ts` to PHP (`app/Support/Content`):

- [ ] `CareerQuery` → `CareerEntry[]` (sorted by start desc, company, project refs).
- [ ] `ProjectQuery` (filters: featured, search, category, tech, sort) +
      `ProjectFacets`; `ProjectDetail` (prev/next by year desc, related articles).
- [ ] `ArticleQuery` (search, tag) + tags list; `ArticleDetail` (prev/next,
      related projects).
- [ ] `BookQuery` + `BookStats` (per-year counts, averages).
- [ ] `SkillGroups`, `SearchIndex`, `NowDetail`.

**Acceptance**: Pest tests replicate the reference behaviour (sorting,
filters, prev/next, stats) using DemoContent-like factories.

---

## P1-08 · API Resources + `ImageData` — `todo`

- [ ] `app/Support/Media/ImageData.php` (see 05).
- [ ] One `JsonResource` per TS interface in `resources/js/types/content.ts`
      (port the TS file first, removing `Localized`, changing `ImageAsset` →
      `ImageData`, `Education.start/end` → `string`/`string|null`).
- [ ] Dates serialized `YYYY-MM` (month fields) / ISO date (publishedAt).
- [ ] Resource keys are **camelCase** exactly like the TS interfaces.

**Acceptance**: snapshot-style tests assert key sets of each resource equal
the TS interface keys (keep a PHP array of expected keys per resource).

---

## P1-09 · Seeders — `todo`

- [ ] `AdminUserSeeder` (from env), `SiteSettingSeeder`, `ProfileSeeder`
      (empty-but-valid defaults).
- [ ] `DemoContentSeeder`: convert every `Reference-Frontend/src/data/*.ts`
      record to PHP arrays in `database/seeders/data/*.php` (English values
      only) and import idempotently; attach images from
      `Reference-Frontend/public/images` (largest width) with alt texts from
      the data files. **Copy the image files into
      `database/seeders/images/`** so the seeder survives deletion of
      `Reference-Frontend/` (P5-03).

**Acceptance**: `php artisan migrate:fresh --seed && php artisan db:seed --class=DemoContentSeeder`
twice → no duplicates; every template page later renders like the reference.

---

## Notes

_(add dated notes here)_
