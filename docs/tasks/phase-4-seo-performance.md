# Phase 4 — SEO & performance

Docs: [07-seo](../07-seo.md) is the spec.

---

## P4-01 · SSR production-ready — `todo`

- [ ] Audit all ported components for SSR safety (browser globals only in
      effects) — especially `useLocalTime`, `useReveal`, `PreferencesProvider`,
      command palette, marquee/orbit animations.
- [ ] No hydration mismatch warnings in the browser console on any page
      (time-based output renders a stable server value first).
- [ ] Document the Supervisor program for `inertia:start-ssr` in the
      deployment guide (P5-04).

**Acceptance**: `curl` of every route returns full content HTML; zero
hydration warnings.

---

## P4-02 · SeoData + SeoHead + per-page meta — `todo`

- [ ] `App\Support\Seo\SeoData` + builder helpers; every controller passes
      `seo`.
- [ ] `SeoHead` renders all tags from 07 §2 with `head-key`.
- [ ] Fallback chain for description/image; `robots` rules (preview,
      `indexable`, 404).

**Acceptance**: Pest tests assert the `seo` prop per page; `curl` shows
title/description/canonical/OG/Twitter tags in SSR HTML.

---

## P4-03 · JSON-LD — `todo`

`App\Support\Seo\JsonLd`: Person, WebSite, BreadcrumbList, CreativeWork,
BlogPosting, CollectionPage/ItemList, Blog.
**Acceptance**: Google Rich Results Test passes for home, a case study and an
article (record results in Notes).

---

## P4-04 · sitemap.xml, robots.txt, rss.xml — `todo`

- [ ] Routes + Blade XML views + caching + observer busting.
- [ ] Delete static `public/robots.txt`.
- [ ] RSS alternate link in Blade head.

**Acceptance**: feature tests (content-type, only published/visible items,
`Disallow: /` when not indexable); validators pass.

---

## P4-05 · Performance pass — `todo`

- [ ] LCP images `priority`; lazy everything else; `sizes` attributes tuned.
- [ ] Verify only the active template's chunks load (Network tab / build
      manifest).
- [ ] Fonts preconnect / consider self-hosting.
- [ ] Response caching decision (record in 09).
- [ ] Lighthouse mobile on Home, Case study, Article for each template.

**Acceptance**: Lighthouse mobile Perf ≥ 95, SEO 100, A11y ≥ 95 (record
scores in Notes).

---

## Notes

_(add dated notes here)_
