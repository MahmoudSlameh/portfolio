# 11 · Template kit (building a new template)

> Status: **planned** (phase [P7](tasks/phase-7-template-kit.md)). This
> document is the target design; sections marked _today_ describe the
> current code.

The goal: anyone who clones the repository can add a template in an
afternoon, with **one command, one contract and a set of shared building
blocks**, and never has to touch Laravel code to do it.

## Today

- **Done (P7-01):** templates are discovered from `template.json` manifests
  by `TemplateRegistry` (§1). The `Template` enum is gone; a new template
  still needs its nine page files written by hand
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

- `App\Support\Templates\TemplateRegistry` (singleton) returns
  `TemplateDefinition` value objects (`id`, `label`, `description`,
  `preloadFonts`, `screenshot`, `author`). _Built in P7-01._
    - Code templates are discovered from
      `resources/js/templates/<id>/template.json` (path and default id in
      `config/portfolio.php` → `templates`). A manifest whose `id` does not
      match its folder, or with a missing field, throws with the file name.
    - `php artisan template:cache` writes the manifests to
      `bootstrap/cache/templates.php` and `template:clear` removes it; both are
      hooked into `php artisan optimize` / `optimize:clear`.
    - _P8:_ studio templates are read from the database, id format
      `studio:<ulid>`, Inertia namespace `studio` (adds `kind` to the
      definition).
- `site_settings.active_template` is a plain string column (no cast). An
  unknown value (deleted template) falls back to the default template in
  `TemplateManager::active()`.
- `TemplateManager`, `Appearance`, `app.blade.php`, the shared `template`
  prop (`id`, `name`, `isPreview`) and the Pest `templates` dataset read from
  the registry.

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

### 2. Scaffold command — _built in P7-04_

```bash
php artisan make:template <id> [--from=minimal] [--name=] [--description=] [--author=] [--no-format]
```

`App\Support\Templates\TemplateScaffolder` copies the source template and
renames everything that ties the copy to it:

```
resources/js/templates/<id>/        # copy of the source folder
  template.json                     # rewritten: id, name, description, author, source's preloadFonts
  styles.css                        # [data-template='<from>'] → [data-template='<id>']
  layout/<Id>Layout.tsx             # <From>Layout renamed (file and identifiers)
  README.md                         # next steps (the source README is not copied)
resources/js/pages/<id>/*.tsx       # the nine Inertia wrappers, imports rewritten
public/templates/<id>.webp          # the source screenshot as a placeholder
resources/js/styles/main.css        # @import of the new stylesheet, after the source's
```

- Ids are lowercase slugs; `studio` and `reset` are reserved; existing ids
  and unknown sources are refused before anything is written.
- The generated files are formatted with `npx vp fmt` (skip with
  `--no-format`), because rewritten import paths change line lengths.
- A warm template cache (`template:cache`) is rebuilt so the new template
  appears right away.
- Paths come from `config/portfolio.php` → `templates` (`path`,
  `pages_path`, `stylesheet`, `screenshots_path`); the tests point them at a
  temporary copy.

The Inertia wrappers are thin (`SeoHead` + page component +
`withTemplateLayout(Layout)`) and rarely need editing.

### 3. Shared building blocks (`resources/js/kit`) — _built in P7-02_

Templates own **how things look**; the kit owns **how things work**. Import
everything from `@/kit` (`resources/js/kit/index.ts`).

| Export                                                                                                                                             | What it does                                                                                                                                                                                      |
| -------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `useContactForm({ onSuccess? })`                                                                                                                   | Values, client validation (errors show after the first submit and update while typing), `POST /contact`, toast on failure, `status`/`messageId`, `summaryRef`/`nameRef` focus handling, `reset()` |
| `CONTACT_FIELDS`, `CONTACT_TOPICS`                                                                                                                 | Field order for the error summary; the topics the server accepts                                                                                                                                  |
| `useArchiveFilters(search, only)`                                                                                                                  | Returns the `onSearchChange(patch)` the archive pages pass to their template: merges the patch, drops empty values, reloads only `only` + `search` + `seo`                                        |
| `useSiteSearch(query)` / `rankByQuery(items, q, text)`                                                                                             | Fuzzy search over the deferred `searchIndex` (projects, articles, books); `rankByQuery` is the same ranking the command palette uses                                                              |
| `useTheme()`                                                                                                                                       | `{ theme, toggleTheme }` with the SSR-safe cookie (D19)                                                                                                                                           |
| `ArticleBlocks` + `ArticleBlockRenderers`                                                                                                          | Headless article body: one render function per block type, typed exhaustively, so a new block type fails to compile until every template renders it. Context: `index`, `isFirst`, `headingIndex`  |
| `useToast`, `useClipboard`, `useNow`, `useReveal`, `useTranslation`, `useCommandPalette`                                                           | Existing hooks, re-exported                                                                                                                                                                       |
| `Link`, `useNavigate`, `useRouterState`                                                                                                            | Inertia navigation                                                                                                                                                                                |
| `ResponsiveImage`, `CompanyLogo`, `BrandIcon`, `CountUp`, `RotatingText`, `ArchitectureDiagram`, `CommandPalette`, `SeoHead`, `withTemplateLayout` | Shared components                                                                                                                                                                                 |
| `cn`, `formatDate`, `formatMonth`, `formatTime`, `formatNumber`, `yearRange`, …                                                                    | `lib/utils` helpers                                                                                                                                                                               |

The three existing templates use the kit for the contact form, archive
filters, theme toggle and article body, so they double as real-world
examples.

### 4. The `minimal` starter template — _built in P7-03_

A deliberately plain template (system fonts, one CSS file, no animation)
whose files are heavily commented: which props each page receives, which kit
hook to use, what must not be done. `make:template` copies it by default.

### 5. Template gallery for development — _built in P7-05_

`/dev/templates` (`App\Http\Controllers\Dev\TemplateGalleryController`,
Blade view `resources/views/dev/templates.blade.php`):

- **Compare all**: one page (Home, archives, a case study, an article, Books,
  Uses, Now, 404) rendered by every template side by side.
- **One template**: every page of that template.
- Light or dark, desktop (1280 × 800) or mobile (390 × 844), scaled down.
- Each frame is the real site at `<path>?_template=<id>&_theme=<light|dark>`:
  a **stateless** override (no session, so frames never affect each other or
  the owner's preview), without the preview bar, always `noindex`. The theme
  is rendered on the server (`TemplateManager::theme()`), so no cookie is
  written.
- Enabled by `portfolio.templates.dev_gallery`: `TEMPLATE_GALLERY` in `.env`,
  default **on only when `APP_ENV=local`**. Otherwise the route is a 404 and
  `?_template=` / `?_theme=` are ignored.
- With no published content it shows a hint to run `DemoContentSeeder`.
- `php artisan serve` handles one request at a time; set
  `PHP_CLI_SERVER_WORKERS=4` in `.env` so the frames load in parallel.

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
