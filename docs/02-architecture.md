# 02 · Architecture

## High-level picture

```
                ┌──────────────── Filament panel (/admin) ────────────────┐
  Owner ──────► │ Resources · Singleton pages · Spatie media uploads      │
                └───────────────┬──────────────────────────────────────────┘
                                │ Eloquent (SQLite dev / MySQL|Postgres prod)
                                ▼
 Visitor ─► Laravel route ─► Controller ─► Query/Action ─► API Resources (arrays)
                                │
                                ▼
                  Inertia::render(Template::page('Home'), props)
                                │  e.g. component = "terminal/Home"
                                ▼
          Inertia SSR server (Node) renders React → full HTML + <head>
                                │
                                ▼
                  Browser hydrates → SPA navigation via Inertia
```

## Key design choices

1. **Server owns data, templates own presentation.** Controllers build the
   exact props of the *template contract* (see
   [06](06-frontend-templates.md#the-template-contract)). Templates only render.
2. **Template = Inertia page namespace.** The active template id prefixes the
   Inertia component name: `terminal/Home`, `changelog/CaseStudy`, … Each
   template lives in its own folder, so Vite code-splits it and SSR only loads
   what is needed.
3. **Filtering happens on the server.** Archive filters (`?q=&tech=&category=`)
   are validated with Form Requests and applied in Eloquent queries; the page
   receives the filtered list and the current filters. Inertia partial reloads
   (`only: ['projects']`) keep it snappy. This replaces the zod search schemas
   + TanStack Router loaders of the reference app.
4. **Singletons as Eloquent models.** `Profile`, `SiteSetting` and `NowPage`
   are single-row models (not a settings package) so they can own Spatie media
   (portrait, OG image, CV…).
5. **English only.** No locale middleware, no translated columns.

## Target folder layout

```
app/
  Enums/                      # PHP enums implementing Filament HasLabel/HasColor/HasIcon
  Filament/
    Pages/                    # EditProfile, SiteSettings, Appearance, EditNowPage, Dashboard
    Resources/<Name>/         # Filament 5 structure: <Name>Resource.php, Pages/, Schemas/, Tables/
    Widgets/
  Http/
    Controllers/Site/         # HomeController, ProjectController, ArticleController, ...
    Requests/                 # ProjectArchiveRequest, ContactMessageRequest, ...
    Resources/                # JsonResource classes = the frontend shape
    Middleware/HandleInertiaRequests.php
  Models/                     # Eloquent models (HasMedia where needed)
  Support/
    Templates/TemplateManager.php   # resolves active/preview template, page names
    Seo/SeoData.php, Seo/JsonLd.php
    Media/ImageData.php             # Media → { src, srcSet, width, height, alt }
database/
  migrations/ factories/ seeders/ (DemoContentSeeder imports Reference-Frontend data)
resources/
  js/
    app.tsx  ssr.tsx
    pages/<template>/<Page>.tsx      # thin Inertia pages per template (Home, ProjectArchive, ...)
    templates/<template>/...         # ported template components/styles
    shared/                          # template-agnostic components (Seo head, ResponsiveImage, CommandPalette...)
    lib/ hooks/ providers/ types/
  views/app.blade.php, feeds/rss.blade.php, sitemap.blade.php
routes/web.php
docs/                               # this documentation
```

## Request lifecycle (public page)

1. `routes/web.php` → e.g. `GET /projects/{project:slug}` →
   `Site\ProjectController@show`.
2. Controller loads the model with eager-loaded relations + media, aborts 404
   if unpublished.
3. Wraps it in `ProjectDetailResource` and builds `SeoData`.
4. `Inertia::render($templates->page('CaseStudy'), ['project' => ..., 'seo' => ...])`.
5. `HandleInertiaRequests::share()` adds global props: `site`, `profile`
   (layout subset), `socials`, `template` (`id`, `fontsHref`, `isPreview`),
   `searchIndex` (deferred/once prop), `flash`.
6. `app.blade.php` injects the template font stylesheet and the pre-hydration
   theme script, then `<x-inertia::head>` (SSR head) and `<x-inertia::app>`.
7. SSR node server renders the page; browser hydrates.

## TemplateManager (`app/Support/Templates/TemplateManager.php`)

```php
enum Template: string { case Changelog = 'changelog'; case Playground = 'playground'; case Terminal = 'terminal'; }

final class TemplateManager
{
    public function active(): Template;            // SiteSetting::current()->active_template, cached
    public function current(Request $r): Template; // preview (?template= / session) if allowed, else active
    public function isPreview(Request $r): bool;
    public function page(string $name): string;    // "{$this->current()->value}/{$name}"
}
```

- Preview rules and SEO safety (noindex while previewing) → see
  [06 · Preview](06-frontend-templates.md#preview).
- Cache key `site.active_template`; flushed by the `SiteSetting` observer.

## Caching

- Site-wide shared props (`profile`, `socials`, `searchIndex`) are cached
  (`Cache::rememberForever`) and busted by model observers on save/delete.
- Full-page HTTP caching is optional later (Phase 4).

## Environments

- Dev: SQLite, `composer dev` (serve + queue + vite).
- Prod: MySQL/Postgres, queue worker (media conversions run on the queue),
  Inertia SSR process under Supervisor/systemd, `php artisan optimize`.
