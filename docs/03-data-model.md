# 03 · Data model

Derived from the reference design's `src/types/content.ts` and
`src/data/*.ts` (the `Reference-Frontend/` folder, removed in P5-03), extended with the owner's requirements and
flattened to **English only** (every `Localized<T>` becomes `T`).

Conventions for every content table unless stated otherwise:

- `id` bigint PK, `timestamps()`.
- `is_visible` boolean default `true` — hide without deleting.
- `sort_order` unsigned int default `0` — used by Filament `->reorderable()`.
- Month-precision dates (`start`, `end`, `finishedAt` = `"2024-02"` in the
  reference) are stored as `date` (first day of month) and serialized back to
  `YYYY-MM`.
- Long free text = `text`; lists of strings / small structured lists =
  `json` cast to `array` (edited with Filament `Repeater`/`TagsInput`).
- Media never lives in columns — see [05-media.md](05-media.md). Alt-text
  columns (`*_alt`) live next to the model where needed.

## Entity overview

```
Profile (singleton) ─┐       SiteSetting (singleton)      NowPage (singleton) ─┬─< book_now_page >── Book
                     │
Company ─┬─< Experience >─┬─< skillables >── Skill >── SkillCategory
         │                └─< Project >──┬─< skillables
         ├─< Project                     ├─< ProjectGalleryItem (media)
         └─< Testimonial                 └─< article_project >── Article
Education   Certification   Social   UsesGroup ─< UsesItem   ContactMessage   User
```

---

## Enums (`app/Enums`)

All enums are string-backed and implement Filament `HasLabel` (+ `HasColor` /
`HasIcon` where useful for badges).

| Enum                   | Cases                                                                                                               | Source                               |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------- | ------------------------------------ |
| `CompanyKind`          | `employer`, `client`                                                                                                | `CompanyKind`                        |
| `WordmarkStyle`        | `serif`, `serif-italic`, `mono`, `sans-bold`, `sans-light`, `spaced`                                                | used when no logo is uploaded        |
| `EmploymentType`       | `full-time`, `part-time`, `contract`, `freelance`, `open-source`, `internship`                                      | `EmploymentType` (+ `part-time`)     |
| `WorkMode`             | `on-site`, `remote`, `hybrid`                                                                                       | **new** (owner requirement)          |
| `CareerBranch`         | `main`, `freelance`, `oss`                                                                                          | `Branch` — auto-derived, overridable |
| `ProjectStatus`        | `live`, `maintained`, `archived`, `in-progress`                                                                     |                                      |
| `ProjectCategory`      | `platform`, `product`, `open-source`, `design-system`                                                               |                                      |
| `ProjectLinkKind`      | `live`, `source`, `writeup`, `talk`                                                                                 |                                      |
| `ArchitectureNodeKind` | `client`, `service`, `store`, `queue`, `external`                                                                   |                                      |
| `ReadingStatus`        | `reading`, `read`, `to-read`                                                                                        |                                      |
| `BookCategory`         | `engineering`, `design`, `systems`, `fiction`, `history`, `philosophy`                                              |                                      |
| `BookCoverStyle`       | `band`, `block`, `rule`, `circle`, `split`                                                                          |                                      |
| `UsesKind`             | `hardware`, `software`, `development`                                                                               |                                      |
| `SocialPlatform`       | `github`, `linkedin`, `x`, `mastodon`, `rss`, `read-cv`, `youtube`, `dribbble`, `stackoverflow`, `email`, `website` | `SocialIcon` (+ extras)              |
| `AvailabilityStatus`   | `open`, `limited`, `closed`                                                                                         |                                      |
| `StatusTone`           | `signal`, `neutral`                                                                                                 |                                      |
| `ContactTopic`         | `role`, `advisory`, `speaking`, `hello`                                                                             |                                      |
| `ArticleStatus`        | `draft`, `published`                                                                                                | **new**                              |

Countries: store ISO-3166 alpha-2 in `country_code` (`char(2)`); list +
names come from `symfony/intl` (`Symfony\Component\Intl\Countries`) — add the
package in P1.

---

## Singletons

### `profiles` → `App\Models\Profile` (HasMedia)

One row, accessed via `Profile::current()` (creates if missing). Maps to TS
`Profile`.

| Column              | Type                                                           | Notes / TS field                       |
| ------------------- | -------------------------------------------------------------- | -------------------------------------- |
| name                | string                                                         | `name`                                 |
| initials            | string(4)                                                      | `initials` (auto from name if blank)   |
| role                | string                                                         | `role` — job title shown in hero       |
| headline            | string(300)                                                    | `headline`                             |
| focus_areas         | json `string[]`                                                | `focusAreas` (rotating text)           |
| summary             | text                                                           | `summary` (short bio)                  |
| story               | json `string[]`                                                | `story` (long bio paragraphs)          |
| location            | string                                                         | `location` e.g. "Damascus, Syria"      |
| timezone            | string                                                         | IANA, `timezone`                       |
| timezone_label      | string nullable                                                | `timezoneLabel`                        |
| email               | string                                                         | `email` (public contact)               |
| phone               | string nullable                                                | new                                    |
| current_version     | string nullable                                                | `currentVersion` (changelog flavour)   |
| availability_status | enum `AvailabilityStatus`                                      | `availability.status`                  |
| availability_label  | string                                                         | `availability.label`                   |
| availability_note   | string nullable                                                | `availability.note`                    |
| latest_release      | json `{added: string[], changed: string[], removed: string[]}` | `latestRelease`                        |
| stats               | json `[{value, label}]`                                        | `stats` (id generated from label slug) |
| status              | json `[{label, value, tone}]`                                  | `status` strip                         |
| principles          | json `[{title, body}]`                                         | `principles`                           |
| portrait_alt        | string nullable                                                | `portrait.alt`                         |
| updated_at          | timestamp                                                      | `updatedAt`                            |

Media: `portrait` (single image), `resume` (single PDF, optional),
`og_image` (optional personal share image).

### `site_settings` → `App\Models\SiteSetting` (HasMedia)

| Column                   | Type                                     | Notes                                                        |
| ------------------------ | ---------------------------------------- | ------------------------------------------------------------ |
| active_template          | string (template id) default `changelog` | **the switch the owner uses**                                |
| site_name                | string                                   | e.g. "Mahmoud Slameh" / brand title used in `<title>` suffix |
| title_separator          | string default `—`                       |                                                              |
| meta_description         | string(300)                              | default description                                          |
| twitter_handle           | string nullable                          |                                                              |
| google_site_verification | string nullable                          |                                                              |
| bing_site_verification   | string nullable                          |                                                              |
| analytics_snippet        | text nullable                            | (optional, e.g. Plausible)                                   |
| enabled_pages            | json `{writing, books, uses, now}` bools | hides nav + returns 404 when off                             |
| contact_recipient        | string nullable                          | where contact notifications go (defaults to profile email)   |
| indexable                | bool default `true`                      | global noindex switch (staging)                              |

Media: `default_og_image`, `favicon` (optional; falls back to `public/favicon.*`).

### `now_pages` → `App\Models\NowPage`

| Column       | Type                   | TS             |
| ------------ | ---------------------- | -------------- |
| location     | string                 | `location`     |
| availability | string                 | `availability` |
| focus        | json `[{title, body}]` | `focus`        |
| learning     | json `[{title, body}]` | `learning`     |
| updated_at   | timestamp              | `updatedAt`    |

Relation: `readingBooks()` belongsToMany `Book` via `book_now_page`
(`now_page_id`, `book_id`, `sort_order`) → `readingBookIds`. Default: when the
pivot is empty, fall back to books with status `reading`.

---

## Career

### `companies` → `App\Models\Company` (HasMedia) — "Clients & companies"

| Column                 | Type                                     | Notes / TS `Company`                                                               |
| ---------------------- | ---------------------------------------- | ---------------------------------------------------------------------------------- |
| name                   | string                                   | `name`                                                                             |
| slug                   | string unique                            | `id` in TS                                                                         |
| kind                   | enum `CompanyKind`                       | `kind` — employer vs client                                                        |
| website_url            | string nullable                          | `url` (**link to their site/page**)                                                |
| industry               | string nullable                          | `industry`                                                                         |
| city                   | string nullable                          | → `location`                                                                       |
| country_code           | char(2) nullable                         | → `location`                                                                       |
| period_label           | string nullable                          | `period` — if blank, derived from linked experiences ("2021 — 2024", "2024 — now") |
| engagement             | text nullable                            | `engagement` (one-liner about the work)                                            |
| wordmark_style         | enum `WordmarkStyle` default `sans-bold` | fallback rendering when there is no logo                                           |
| is_featured            | bool                                     | show on clients wall / cooperation section                                         |
| is_visible, sort_order |                                          |                                                                                    |

Media: `logo` (single; svg/png/webp; light variant) and `logo_dark`
(optional). Frontend gets `logo: ImageData | null`.

### `experiences` → `App\Models\Experience` — "Work experience"

| Column                 | Type                                   | Notes / TS `Experience`                                                                                                                                                        |
| ---------------------- | -------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| company_id             | FK nullable → companies (nullOnDelete) | `companyId`                                                                                                                                                                    |
| organization_name      | string nullable                        | `organization` — required when no company (e.g. open source) ; else defaults to company name                                                                                   |
| role                   | string                                 | `role` — **job title**                                                                                                                                                         |
| employment_type        | enum `EmploymentType`                  | `type`                                                                                                                                                                         |
| work_mode              | enum `WorkMode`                        | **new** — remote / on-site / hybrid                                                                                                                                            |
| country_code           | char(2) nullable                       | **new**                                                                                                                                                                        |
| city                   | string nullable                        | **new**                                                                                                                                                                        |
| address                | string nullable                        | **new** (optional street address, never required)                                                                                                                              |
| start_date             | date                                   | `start` (YYYY-MM)                                                                                                                                                              |
| end_date               | date nullable                          | `end` — `null` = **currently working here**                                                                                                                                    |
| summary                | text                                   | `summary` — short description                                                                                                                                                  |
| highlights             | json `string[]`                        | `highlights` — **achievements**                                                                                                                                                |
| branch                 | enum `CareerBranch` nullable           | `branch` — if null: open-source→`oss`, freelance/contract→`freelance`, else `main` (`CareerBranch::forEmploymentType`)                                                         |
| version                | string nullable                        | `version` — if null: counted per branch from the oldest role: `v1.0.0`, `v2.0.0`… on `main`; `oss/1.0.0`, `freelance/1.0.0`… elsewhere                                         |
| commit_hash            | string(7) nullable                     | `commit` — if null `substr(sha1("{id}:{role}:{organization}"), 0, 7)`                                                                                                          |
| commit_message         | string nullable                        | `message` — if null: oldest `main` role → `init: first commit`; other `main` roles → `feat(career): join {org} as {role}`; other branches → `chore({branch}): {role} at {org}` |
| is_visible, sort_order |                                        |                                                                                                                                                                                |

Version and message need the whole career, so they are computed by
`App\Support\Content\ChangelogMetadata::for($experiences)`; `branch` and `commit`
are model accessors (`resolved_branch`, `resolved_commit`).

Relations: `company()`, `skills()` (morphToMany `Skill` via `skillables` →
`stack`), `projects()` hasMany (→ `projectIds`).
Computed `location` string for TS: `"{city}, {country} · {work mode}"`
(e.g. `"Amsterdam, Netherlands · Remote"`), omitting empty parts.

### `education` → `App\Models\Education` (HasMedia)

| Column                 | Type             | Notes / TS `Education`                                                           |
| ---------------------- | ---------------- | -------------------------------------------------------------------------------- |
| degree                 | string           | `degree` — **qualification name**, e.g. "Diploma in Software Engineering", "BSc" |
| institution            | string           | `institution` — university / institute                                           |
| institution_url        | string nullable  | new                                                                              |
| field_of_study         | string nullable  | `field` — **specialization**                                                     |
| grade                  | string nullable  | **new** — "Distinction", "3.7 / 4.0", "Very good"                                |
| country_code           | char(2) nullable | → `location`                                                                     |
| city                   | string nullable  | → `location`                                                                     |
| start_date             | date             | `start` (TS changes from `number` year → `YYYY-MM`)                              |
| end_date               | date nullable    | `end` — `null` = **currently studying** (TS becomes `string \| null`)            |
| description            | text nullable    | new                                                                              |
| achievements           | json `string[]`  | `notes` — **achievements**                                                       |
| is_visible, sort_order |                  |                                                                                  |

Media: `logo` (institution logo, optional), `certificate` (optional image/PDF).

### `certifications` → `App\Models\Certification` (HasMedia)

| Column                 | Type            | TS                      |
| ---------------------- | --------------- | ----------------------- |
| name                   | string          | `name`                  |
| issuer                 | string          | `issuer`                |
| issued_at              | date            | `year` (serialize year) |
| expires_at             | date nullable   | new                     |
| credential_id          | string nullable | `credentialId`          |
| credential_url         | string nullable | `url`                   |
| is_visible, sort_order |                 |                         |

Media: `badge` (optional).

### `testimonials` → `App\Models\Testimonial` (HasMedia)

| Column                 | Type            | TS                                    |
| ---------------------- | --------------- | ------------------------------------- |
| quote                  | text            | `quote`                               |
| author_name            | string          | `author`                              |
| author_role            | string          | `role`                                |
| company_id             | FK nullable     | `companyId`                           |
| relation               | string nullable | `relation` ("Managed me, 2024 — now") |
| is_visible, sort_order |                 |                                       |

Media: `avatar` (optional).

---

## Skills

### `skill_categories` → `SkillCategory`

`name` (TS `label`), `slug` (TS `id`), `description`, `sort_order`.

### `skills` → `Skill` (HasMedia)

| Column                 | Type                   | TS `Skill`                                                                              |
| ---------------------- | ---------------------- | --------------------------------------------------------------------------------------- |
| name                   | string unique          | `name`                                                                                  |
| slug                   | string unique          | `id`                                                                                    |
| skill_category_id      | FK nullable            | `categoryId` — null = technology used only as "stack" tag, not listed in skills section |
| proficiency            | tinyint nullable (1–5) | `proficiency`                                                                           |
| years                  | tinyint nullable       | `years`                                                                                 |
| icon                   | string nullable        | brand icon key for `BrandIcon` (simple-icons slug)                                      |
| is_visible, sort_order |                        |                                                                                         |

Media: `icon` (optional custom SVG).

### `skillables` (polymorphic pivot)

`skill_id`, `skillable_type`, `skillable_id`, `sort_order`. Used by
`Experience::skills()` and `Project::skills()` → TS `stack: string[]`
(ordered skill names).

---

## Work

### `projects` → `App\Models\Project` (HasMedia, SoftDeletes)

| Column           | Type                            | TS `Project`                                              |
| ---------------- | ------------------------------- | --------------------------------------------------------- |
| slug             | string unique                   | `slug` / `id`                                             |
| title            | string                          | `title`                                                   |
| tagline          | string                          | `tagline`                                                 |
| summary          | text                            | `summary` (also meta description fallback)                |
| year             | smallint                        | `year`                                                    |
| status           | enum `ProjectStatus`            | `status`                                                  |
| category         | enum `ProjectCategory`          | `category`                                                |
| is_featured      | bool                            | `featured` (home page)                                    |
| version          | string nullable                 | `version`                                                 |
| company_id       | FK nullable                     | `companyId` / client                                      |
| experience_id    | FK nullable                     | `experienceId`                                            |
| role             | string nullable                 | `role`                                                    |
| team             | string nullable                 | `team`                                                    |
| timeline         | string nullable                 | `timeline`                                                |
| overview         | json `string[]`                 | `overview`                                                |
| problem          | json `string[]`                 | `problem`                                                 |
| approach         | json `[{title, description}]`   | `approach`                                                |
| architecture     | json `Architecture` nullable    | `architecture` (caption, columns, rows, nodes[], edges[]) |
| features         | json `[{title, description}]`   | `features`                                                |
| challenges       | json `[{title, description}]`   | `challenges`                                              |
| metrics          | json `[{value, label, detail}]` | `metrics`                                                 |
| links            | json `[{label, url, kind}]`     | `links`                                                   |
| cover_alt        | string                          | `cover.alt`                                               |
| meta_title       | string nullable                 | SEO override                                              |
| meta_description | string nullable                 | SEO override                                              |
| is_published     | bool default false              |                                                           |
| published_at     | timestamp nullable              |                                                           |
| sort_order       |                                 |                                                           |

Media: `cover` (single, 16:9). Relations: `skills()` (→ `stack`),
`galleryItems()` hasMany, `articles()` belongsToMany.

### `project_gallery_items` → `ProjectGalleryItem` (HasMedia)

`project_id` (cascade), `alt`, `caption`, `sort_order`; media collection
`image` (single). → TS `gallery: (ImageData & {caption})[]`. Implemented as a
Filament relationship `Repeater` so each image has its own alt + caption.

---

## Content

### `articles` → `App\Models\Article` (HasMedia, SoftDeletes)

| Column                       | Type                 | TS `Article`                         |
| ---------------------------- | -------------------- | ------------------------------------ |
| slug                         | string unique        | `slug` / `id`                        |
| title                        | string               | `title`                              |
| excerpt                      | string(400)          | `excerpt`                            |
| body                         | json TipTap document | `body` (`ArticleBlock[]`), see below |
| tags                         | json `string[]`      | `tags`                               |
| status                       | enum `ArticleStatus` |                                      |
| published_at                 | timestamp nullable   | `publishedAt`                        |
| cover_alt                    | string nullable      |                                      |
| meta_title, meta_description | string nullable      | SEO overrides                        |

Media: `cover` (optional; used as OG image). Relation: `projects()`
belongsToMany via `article_project` (→ `projectIds`).
`readingMinutes` computed server-side (220 wpm, same algorithm as
`lib/content.ts`).

The body is written in Filament's rich editor and stored as a **TipTap
document** (`{"type":"doc","content":[…]}`, since P11-01).
`App\Support\Content\ArticleDocument` flattens it into the `ArticleBlock`
union templates receive (mapping table in
[P11-01](tasks/phase-11-content-tools.md)); `ArticleBody::toBlocks()` adds
heading ids and resolves images.

| Block     | Fields                                                                                                                                 |
| --------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| paragraph | `text` — inline Markdown subset: `**bold**`, `*italic*`, `` `code` ``, `[text](url)`, `\` escapes (render with the kit's `InlineText`) |
| heading   | `text` (plain), `id` (slug of the text, unique in the article)                                                                         |
| code      | `language` (from the pasted `language-*` class or the Code block; `text` when unknown), `filename?` (Code block only), `code`          |
| quote     | `text` (inline Markdown), `cite?` (a last line starting with `—` or `--`)                                                              |
| list      | `items[]` (inline Markdown; nested lists are flattened)                                                                                |
| callout   | `title`, `text` (inline Markdown) — the **Callout** custom block                                                                       |
| image     | `image` (`ImageData` with the alt text given on upload), `caption?` (the image's `title`)                                              |

Article media collections: `cover` (single) and `body_images` (multiple).
Images added in the editor are stored in `body_images` by Filament's
media-library attachment provider and referenced by uuid from the image
node; images removed from the body are deleted on save.

### `books` → `App\Models\Book` (HasMedia)

| Column                                    | Type                  | TS `Book`                   |
| ----------------------------------------- | --------------------- | --------------------------- |
| slug                                      | string unique         | `slug` / `id`               |
| title, author                             | string                |                             |
| published_year                            | smallint nullable     | `publishedYear`             |
| category                                  | enum `BookCategory`   |                             |
| status                                    | enum `ReadingStatus`  |                             |
| finished_at                               | date nullable         | `finishedAt` (YYYY-MM)      |
| rating                                    | tinyint nullable 1–5  |                             |
| pages                                     | smallint nullable     |                             |
| note                                      | text nullable         |                             |
| cover_background, cover_ink, cover_accent | string (hex)          | `cover.*` — generated cover |
| cover_style                               | enum `BookCoverStyle` | `cover.style`               |
| is_visible                                |                       |                             |

Media: `cover_image` (optional real cover; templates fall back to the
generated one).

### `uses_groups` / `uses_items`

- `uses_groups`: `kind` (enum `UsesKind`), `title`, `sort_order`.
- `uses_items`: `uses_group_id` (cascade), `name`, `description`, `url`
  nullable, `sort_order`; media `image` optional.

### `socials` → `Social`

`platform` (enum `SocialPlatform` → TS `icon`), `label`, `handle`, `url`,
`is_visible`, `sort_order`.

### `contact_messages` → `ContactMessage`

`name`, `email`, `topic` (enum `ContactTopic`), `message` (text), `ip_address`,
`user_agent`, `read_at` nullable, `replied_at` nullable, `created_at`.
Never exposed to the frontend.

---

## Studio templates

`studio_templates` and `studio_template_versions` (templates stored as a
Template Spec, rendered by the `studio` engine): see
[12 §4](12-ai-templates.md#4-data-model-p8p9--built-in-p8-03).

## Frontend shapes (API Resources)

Each model has a `JsonResource` in `app/Http/Resources` returning **exactly**
the TS interface from `resources/js/types/content.ts` (ported from the
reference, `Localized` removed). Key derived shapes:

```ts
// replaces the reference ImageAsset { base, widths, ... }
export interface ImageData {
    src: string; // largest conversion URL
    srcSet: string; // Spatie responsive images srcset
    width: number;
    height: number;
    alt: string;
    placeholder?: string; // tiny blurred base64 (Spatie responsive images placeholder)
}

export interface CareerEntry extends Experience {
    // unchanged idea
    company: Company | null;
    projects: { slug: string; title: string }[];
}
```

Every other derived type from the reference `src/lib/content.ts`
(`SkillGroup`, `ProjectDetail`, `ArticleSummary`, `ArticleDetail`,
`BookStats`, `TestimonialEntry`, `NowDetail`, `SearchIndex`, `ProjectFacets`)
is computed **in PHP** with the same algorithm and returned as props.

## Seeders

- `DatabaseSeeder` → `AdminUserSeeder` (from `ADMIN_EMAIL` / `ADMIN_PASSWORD`
  env) + `SiteSettingSeeder`.
- `DemoContentSeeder` (optional, `php artisan db:seed --class=DemoContentSeeder`)
  imports the reference portfolio from `database/seeders/data/*.json` (a one-off
  export of the reference design's `src/data/*.ts`, English values only; the
  export script was removed with the reference in P5-03) and attaches images from `database/seeders/images/*.webp`
  (largest width of each reference image). Idempotent: `updateOrCreate` on natural
  keys (slug, credential id, role + start month…); images only added to empty
  collections.
