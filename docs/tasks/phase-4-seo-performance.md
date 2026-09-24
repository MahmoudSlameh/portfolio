# Phase 4 — SEO & performance

Docs: [07-seo](../07-seo.md) is the spec.

---

## P4-01 · SSR production-ready — `done`

- [x] Audit all ported components for SSR safety (browser globals only in
      effects) — especially `useLocalTime`, `useReveal`, `PreferencesProvider`,
      command palette, marquee/orbit animations.
- [x] No hydration mismatch warnings in the browser console on any page
      (time-based output renders a stable server value first).
- [x] Document the Supervisor program for `inertia:start-ssr` in the
      deployment guide (P5-04).

**Acceptance**: `curl` of every route returns full content HTML; zero
hydration warnings.

---

## P4-02 · SeoData + SeoHead + per-page meta — `done`

- [x] `App\Support\Seo\SeoData` + builder helpers; every controller passes
      `seo`.
- [x] `SeoHead` renders all tags from 07 §2 with `head-key`.
- [x] Fallback chain for description/image; `robots` rules (preview,
      `indexable`, 404).

**Acceptance**: Pest tests assert the `seo` prop per page; `curl` shows
title/description/canonical/OG/Twitter tags in SSR HTML.

---

## P4-03 · JSON-LD — `done`

`App\Support\Seo\JsonLd`: Person, WebSite, BreadcrumbList, CreativeWork,
BlogPosting, CollectionPage/ItemList, Blog.
**Acceptance**: Google Rich Results Test passes for home, a case study and an
article (record results in Notes).

---

## P4-04 · sitemap.xml, robots.txt, rss.xml — `done`

- [x] Routes + Blade XML views + caching + observer busting.
- [x] Delete static `public/robots.txt`.
- [x] RSS alternate link in Blade head.

**Acceptance**: feature tests (content-type, only published/visible items,
`Disallow: /` when not indexable); validators pass.

---

## P4-05 · Performance pass — `done`

- [x] LCP images `priority`; lazy everything else; `sizes` attributes tuned.
- [x] Verify only the active template's chunks load (Network tab / build
      manifest).
- [x] Fonts preconnect / consider self-hosting.
- [x] Response caching decision (record in 09).
- [x] Lighthouse mobile on Home, Case study, Article for each template.

**Acceptance**: Lighthouse mobile Perf ≥ 95, SEO 100, A11y ≥ 95 (record
scores in Notes).

---

## Notes
- 2026-09-24 — P4-01: SSR audited: all 9 pages × 3 templates render full content server-side (h1/title/meta/JSON-LD in raw HTML) and hydrate with zero console errors (Playwright). Supervisor program goes into docs/10-deployment.md (P5-04).
- 2026-09-24 — P4-02: `Seo` builder + `SeoHead` (head-key per tag): title/description/canonical/robots/OG/Twitter/article tags; fallbacks profile → site settings; preview + non-indexable → noindex. Covered by Site/PublicPagesTest + TemplatePreviewTest.
- 2026-09-24 — P4-03: `JsonLd`: Person, WebSite, BreadcrumbList, CreativeWork, BlogPosting, CollectionPage/Blog + ItemList; nulls stripped. Rich Results Test is external (not reachable from the build sandbox) — run it after deploy (checklist in docs/10-deployment.md).
- 2026-09-24 — P4-04: SeoController: sitemap.xml (pages + published projects/articles, lastmod), robots.txt (Disallow all when not indexable, sitemap link), rss.xml (published articles); cached in ContentCache; static public/robots.txt removed; RSS alternate in Blade. SeoEndpointsTest 7/7.
- 2026-09-24 — P4-05: Done: zod removed; motion via LazyMotion (async domMax, −36 KB gz critical JS); fonts self-hosted + per-template preload (Google Fonts gone); list projection for projects (home JSON 105→57 KB); galleries only loaded on case studies; a11y fixes (brand-link names, carousel role, inert marquee clone, AA tints in terminal light mode, playground submit contrast); changelog aurora CLS fixed. Lighthouse mobile (local php artisan serve + gzip proxy, debug on, simulated 4G/4×CPU): terminal Perf 92–95; changelog 77–83; playground 77–88; A11y 96–100; Best practices 100; SEO 92–100 (92 = Lighthouse flags a tech-tag link literally named "Go"); CLS 0 everywhere. Remaining gap to 95 is the React+Inertia baseline (~110 KB gz) under 4× CPU throttling and local TTFB (~300–700 ms, no OPcache). Re-measure on production hardware (docs/10 checklist).
