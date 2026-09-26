# 11 · Template kit (building a new template)

> Status: **planned** (phase [P7](tasks/phase-7-template-kit.md)). This
> document is the target design; sections marked _today_ describe the
> current code.

The goal: anyone who clones the repository can add a template in an
afternoon, with **one command, one contract and a set of shared building
blocks**, and never has to touch Laravel code to do it.

## Today (before P7)

- Templates are hard-coded in `App\Enums\Template` (label, description,
  preload fonts, screenshot). `SiteSetting.active_template` is cast to that
  enum, so a new template means editing the enum, `styles/main.css`,
  `public/templates/` and nine page files by hand
  (see [06 § Adding a new template later](06-frontend-templates.md#adding-a-new-template-later)).
- Behaviour that every template needs (contact form submission, archive
  filters, search) is re-implemented inside each template.

## Target design

### 1. Template registry (replaces the enum)

Two kinds of templates share one registry:

| Kind       | Where it lives                                                    | Who makes it                                                                  |
| ---------- | ----------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| **Code**   | `resources/js/templates/<id>/` + `template.json` manifest         | Developers (PRs to this repo, or local forks)                                 |
| **Studio** | Database row (`studio_templates`) rendered by the `studio` engine | The owner, from the panel (by hand or with AI) — see [12](12-ai-templates.md) |

- `App\Support\Templates\TemplateRegistry` returns `TemplateDefinition`
  value objects (`id`, `label`, `description`, `preloadFonts`,
  `screenshotUrl`, `kind`, `inertiaNamespace`).
    - Code templates are discovered from
      `resources/js/templates/*/template.json` (cached with
      `php artisan optimize`; the cache is rebuilt on deploy).
    - Studio templates are read from the database, id format
      `studio:<ulid>`, Inertia namespace `studio`.
- `site_settings.active_template` stays a string column; the enum cast is
  removed and the value is validated against the registry. An unknown value
  (deleted template) falls back to the default code template.
- `TemplateManager`, `Appearance`, `app.blade.php` and the Pest template
  matrix read from the registry instead of `Template::cases()`.

`template.json` (one per code template):

```json
{
    "id": "terminal",
    "name": "Terminal",
    "description": "Dark developer-terminal look with mono type, a project slider and services.",
    "author": "Mahmoud Slameh",
    "preloadFonts": [
        "node_modules/@fontsource/dm-mono/files/dm-mono-latin-400-normal.woff2"
    ],
    "screenshot": "templates/terminal.webp"
}
```

### 2. Scaffold command

`php artisan make:template <id> [--from=minimal]` creates:

```
resources/js/templates/<id>/
  template.json            # manifest (above)
  styles.css               # scoped to [data-template="<id>"]
  layout/<Id>Layout.tsx    # receives LayoutProps
  pages/HomePage.tsx … NotFoundPage.tsx   # one per contract page
resources/js/pages/<id>/
  Home.tsx ProjectArchive.tsx CaseStudy.tsx WritingArchive.tsx
  Article.tsx Books.tsx Uses.tsx Now.tsx NotFound.tsx   # thin Inertia wrappers
public/templates/<id>.webp  # placeholder screenshot to replace
```

The Inertia wrappers are generated and never edited by hand (same shape as
today: `SeoHead` + page component + `withTemplateLayout(Layout)`).

### 3. Shared building blocks (`resources/js/kit`)

Templates own **how things look**; the kit owns **how things work**.

| Export                                                                                          | What it does                                                                   |
| ----------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------ |
| `useContactForm()`                                                                              | State, client validation, `POST /contact`, server error mapping, success state |
| `useArchiveFilters(route, only)`                                                                | Wraps `useSearchChange`: current filters, `set(patch)`, `reset()`              |
| `useSiteSearch()`                                                                               | Fuzzy search over the deferred `searchIndex` (what the command palettes use)   |
| `useTheme()`                                                                                    | Light/dark with the SSR-safe cookie (D19)                                      |
| `ResponsiveImage`, `CompanyLogo`, `BrandIcon`, `CountUp`, `RotatingText`, `ArchitectureDiagram` | Existing `shared/*` components, re-exported from one place                     |
| `ArticleBlocks` (headless)                                                                      | Walks article blocks and calls a render function per block type                |
| `formatDate`, `formatRange`, `cn`, …                                                            | Existing `lib/utils` helpers                                                   |

The three existing templates are migrated onto the kit (no visual change) so
they double as real-world examples.

### 4. The `minimal` starter template

A deliberately plain template (system fonts, one CSS file, no animation)
whose files are heavily commented: which props each page receives, which kit
hook to use, what must not be done. `make:template` copies it by default.

### 5. Template gallery for development

`/dev/templates` (registered only when `app()->isLocal()`): every page of
every template rendered with the demo seeder data, side by side, light and
dark. Used while building a template and for screenshots.

### 6. Rules for template authors

1. Render only the props you receive; never `fetch` and never hard-code
   personal content (hard rule 1). UI micro-copy is fine.
2. Every list can be empty; render nothing (not an empty section) when it is
   (see [08 § Empty content](08-conventions.md)).
3. Scope all CSS under `[data-template="<id>"]`; use Fontsource packages for
   fonts (no third-party requests, D20).
4. Use `m.*` components from `motion`, never `motion.*` (D22).
5. Images go through `ResponsiveImage` (`ImageData | null`).
6. `composer ci:check` must pass; the template test matrix picks the new
   template up automatically from the registry.

### 7. Documentation deliverable

`docs/templates/building-a-template.md`: a step-by-step guide (scaffold →
layout → pages → screenshot → tests → PR) with the prop reference for every
page generated from `resources/js/templates/types.ts`.
