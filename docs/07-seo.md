# 07 · SEO

Target: Lighthouse SEO 100, Performance ≥ 95 (mobile), all pages
indexable with rich previews on Google, LinkedIn, X, WhatsApp, Slack.

## 1. Server-side rendering (mandatory)

- `config/inertia.php` → `ssr.enabled = true` (already), bundle
  `bootstrap/ssr/ssr.js` built by `npm run build:ssr`.
- `resources/js/ssr.tsx` uses `createServer` with the same page resolver as
  `app.tsx`; `vite.config.ts` gets `laravel({ ssr: 'resources/js/ssr.tsx' })`.
- Components must be SSR-safe: no `window`/`document`/`localStorage` at module
  scope or during render — move to `useEffect` (reference hooks
  `useLocalTime`, `useReveal`, `PreferencesProvider`, command palette, and
  `activation.ts` need review).
- Production: run `php artisan inertia:start-ssr` under Supervisor; health
  check `php artisan inertia:check-ssr`. If SSR is down, Inertia falls back to
  CSR — monitor it.
- Verify with `curl -s https://site/ | grep '<h1'` → content must be in HTML.

## 2. Head tags — `SeoData`

`App\Support\Seo\SeoData` (built in controllers, passed as `seo` prop):

```php
final readonly class SeoData
{
    public function __construct(
        public string $title,            // page title (without site suffix)
        public string $description,      // ≤ 160 chars
        public string $path,             // canonical path
        public string $type = 'website', // website | profile | article
        public ?array $image = null,     // ['url','width','height','alt'] og conversion 1200×630
        public bool $noindex = false,
        public ?string $publishedTime = null,
        public ?string $modifiedTime = null,
        public array $jsonLd = [],       // schema.org graphs
    ) {}
}
```

`<SeoHead seo>` renders with Inertia `<Head>` (each tag with `head-key` to
avoid duplicates):

- `<title>` = `"{title} {separator} {site_name}"` (home = site name + role).
- `description`, `canonical` (absolute, `APP_URL` + path, no query except
  `page`), `robots` (`noindex,nofollow` when page noindex, preview, or
  `site_settings.indexable = false`).
- Open Graph: `og:title`, `og:description`, `og:type`, `og:url`,
  `og:site_name`, `og:image` (+ `:width`, `:height`, `:alt`), `og:locale=en_US`,
  `article:published_time`/`modified_time`/`tag` for articles,
  `profile:first_name/last_name` for home.
- Twitter: `summary_large_image`, `twitter:site/creator` from settings.
- JSON-LD `<script type="application/ld+json">`.

Fallbacks: description → model `meta_description` → `summary`/`excerpt` →
site default. Image → model `cover` `og` conversion → profile `og_image` →
`default_og_image`.

Titles/descriptions per page:

| Page       | Title                  | Description      | JSON-LD                                                                                                                                      |
| ---------- | ---------------------- | ---------------- | -------------------------------------------------------------------------------------------------------------------------------------------- |
| Home       | `{name} — {role}`      | profile headline | `Person` (name, jobTitle, image, email, sameAs = socials, worksFor = current company, alumniOf = education, knowsAbout = skills) + `WebSite` |
| Projects   | `Projects`             | static + count   | `CollectionPage` + `ItemList` + `BreadcrumbList`                                                                                             |
| Case study | `{title} — case study` | summary          | `CreativeWork` (name, description, image, dateCreated=year, author=Person, keywords=stack) + `BreadcrumbList`                                |
| Writing    | `Writing`              |                  | `Blog` + `BreadcrumbList`                                                                                                                    |
| Article    | `{title}`              | excerpt          | `BlogPosting` (headline, datePublished, dateModified, image, author, keywords, wordCount, timeRequired) + `BreadcrumbList`                   |
| Books      | `Bookshelf`            |                  | `CollectionPage` (+ `Book` items)                                                                                                            |
| Uses / Now | `Uses` / `Now`         |                  | `WebPage` + `BreadcrumbList`                                                                                                                 |
| 404        | `Not found`            |                  | `noindex`                                                                                                                                    |

Builders live in `App\Support\Seo\JsonLd` (static methods returning arrays).

## 3. Crawling endpoints

- `GET /sitemap.xml` — Blade XML view generated from published projects,
  articles and enabled pages with `<lastmod>` from `updated_at`; cached,
  busted by model observers. (Own implementation — no extra package.)
- `GET /robots.txt` — dynamic route (delete `public/robots.txt`):
  `Allow: /`, `Disallow: /admin`, `Sitemap: {APP_URL}/sitemap.xml`; returns
  `Disallow: /` when `indexable` is false.
- `GET /rss.xml` — RSS 2.0 feed of published articles (Blade view), linked
  via `<link rel="alternate" type="application/rss+xml">` in the layout; the
  `rss` social link points here.
- Filtered archive URLs (`/projects?tech=Go`) keep canonical `/projects`.

## 4. Semantics & accessibility (SEO-relevant)

- One `<h1>` per page; logical heading order (templates already mostly do).
- Every image has `alt` (enforced by required admin fields), `width`,
  `height`.
- Internal links are real `<a href>` (Inertia `Link` renders anchors).
- `lang="en"` on `<html>`, `<meta name="theme-color">` per theme.

## 5. Performance (Core Web Vitals)

- LCP image (hero portrait / project cover) rendered with
  `fetchpriority="high"`, no `loading="lazy"`, responsive `srcset`/`sizes`,
  WebP/AVIF conversions.
- Everything else `loading="lazy" decoding="async"`.
- Template JS/CSS code-split per template (only the active template ships).
- Fonts: `preconnect` + `display=swap`; later self-host with
  `laravel-vite-plugin/fonts` for the active template.
- `motion`: respect reduced motion (`MotionConfig reducedMotion="user"`,
  already in reference); avoid layout-shifting reveal animations above the fold.
- HTTP: `Cache-Control` for built assets (Vite hashed) = immutable; enable
  gzip/brotli at the web server; optionally `spatie/laravel-responsecache`
  for public pages (bust on content change).
- Search index for the command palette is loaded as a deferred prop, not in
  initial HTML.

## 6. Verification checklist (per release)

- [ ] `curl` each route: content + all meta present in raw HTML.
- [ ] Google Rich Results Test passes for Home (Person), Article (BlogPosting), Breadcrumbs.
- [ ] OG preview correct (opengraph.xyz / LinkedIn Post Inspector).
- [ ] `sitemap.xml` valid and lists only published, visible content.
- [ ] Lighthouse mobile: SEO 100, Perf ≥ 95, A11y ≥ 95, Best Practices 100.
- [ ] Preview mode pages are `noindex`.
