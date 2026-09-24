# Phase 3 — Frontend (templates on Inertia)

Docs: [06-frontend-templates](../06-frontend-templates.md) (mapping table of
what replaces what), [02-architecture](../02-architecture.md),
[05-media](../05-media.md#getting-images-to-react--appsupportmediaimagedata).

Porting rule: **copy the reference file, then change only what the mapping
table in 06 requires** (router, data access, i18n, images). Do not redesign.
Compare visually against `Reference-Frontend` (`npm run dev` there) page by
page, light + dark theme, mobile + desktop.

---

## P3-01 · Frontend deps, structure, shared code — `todo`

- [ ] Add deps (`lucide-react`, `motion`, `sugar-high`, `zod`) to root.
- [ ] Port `types/content.ts` (English-only, `ImageData`), `lib/utils.ts`
      (drop `imageUrl`/`imageSources`, drop locale param from formatters →
      fixed `en-GB`), `lib/fuzzy.ts`, `hooks/*`, `providers/ToastProvider`,
      `providers/CommandPaletteProvider`, `PreferencesProvider` (theme only),
      `i18n/dictionary.ts` (`en` only; `useTranslation` keeps `t()`, drop
      `l()`/`isRtl`/`locale`), `shared/*`, `styles/base.css`.
- [ ] `ResponsiveImage` takes `ImageData` + `priority` prop.
- [ ] Remove all `ar`/RTL code paths as you port (grep `isRtl`, `locale`,
      `dir=`, `'ar'`).

**Acceptance**: `npm run types:check` and `npm run check` pass.

---

## P3-02 · TemplateManager, shared props, Blade shell — `todo`

- [ ] `App\Support\Templates\TemplateManager` (active, current with preview
      rules from 06, `page()`), bound as singleton.
- [ ] `HandleInertiaRequests::share()`: `site`, `profile`, `socials`,
      `template`, `searchIndex` (once/deferred), `flash`. Remove `auth.user`
      from public share (don't leak admin data).
- [ ] `app.blade.php`: `data-template`, `data-theme` pre-hydration script,
      template fonts `<link>`, theme-color metas, RSS alternate link,
      `lang="en"`.

**Acceptance**: unit tests for preview rules (admin vs guest, invalid id,
reset, public toggle).

---

## P3-03 · Routes & controllers — `todo`

- [ ] `routes/web.php`: `/`, `/projects`, `/projects/{project:slug}`,
      `/writing`, `/writing/{article:slug}`, `/books`, `/uses`, `/now`,
      `POST /contact` (throttle 5/min), fallback → template `NotFound` with
      404 status.
- [ ] Controllers in `App\Http\Controllers\Site` using P1-07/P1-08 classes;
      Form Requests for archive filters (`q`, `tech`, `category`, `sort`,
      `view`, `tag`, `status`) — invalid values silently dropped (like zod
      `.catch(undefined)`).
- [ ] Disabled pages (`enabled_pages`) → 404; unpublished/invisible → 404.
- [ ] Wayfinder generation works (`@/routes/...`).

**Acceptance**: feature tests per route × template (dataset) assert
component name + prop keys.

---

## P3-04 · Inertia & SSR entries — `todo`

- [ ] `app.tsx`: `createInertiaApp` with providers (Preferences, Toast,
      CommandPalette), `MotionConfig reducedMotion="user"`, title callback
      (SeoHead provides full title → callback returns as-is).
- [ ] `ssr.tsx` + vite `ssr` input; `npm run build:ssr` builds.
- [ ] `shared/seo/SeoHead.tsx` minimal (title + description) — completed in P4-02.

**Acceptance**: a placeholder `terminal/Home` page renders via SSR (`curl`
shows markup).

---

## P3-05 · Port Terminal template — `todo`

- [ ] Copy `Reference-Frontend/src/templates/terminal/**` →
      `resources/js/templates/terminal/`; create the 9 pages in
      `resources/js/pages/terminal/` with `TerminalLayout` as persistent layout.
- [ ] Replace TanStack `Link`/`useRouterState` with Inertia equivalents +
      Wayfinder URLs.
- [ ] Archive pages use the `useFilters` helper (server filtering).
- [ ] Remove Arabic font from `fontsHref` and any RTL code.

**Acceptance**: pixel-comparable to the reference on all 9 pages with demo
seed; no console errors; SSR works.

## P3-06 · Port Changelog template — `todo`

Same as P3-05 for `changelog` (note: `features/` folder structure, career
commit graph uses derived branch/version/commit).

## P3-07 · Port Playground template — `todo`

Same as P3-05 for `playground`.

---

## P3-08 · Contact form end-to-end — `todo`

- [ ] `ContactMessageRequest` (rules mirror `lib/contactSchema.ts`: name
      required, email valid, topic enum, message ≥ 20 chars) + honeypot field + throttle.
- [ ] Controller stores message, dispatches notification, redirects back with
      flash success.
- [ ] All three templates' contact components use Inertia `useForm`; server
      errors map to the existing error-key UI.

**Acceptance**: feature test (valid, invalid, throttled, honeypot); message
appears in the panel inbox.

## P3-09 · Command palette & search index — `todo`

Port `shared/command/CommandPalette.tsx`; data from the `searchIndex` shared
prop; actions: theme toggle, copy email (language action removed).
**Acceptance**: ⌘K works on every template; navigation uses Inertia router.

## P3-10 · Preview bar & template switching — `todo`

Floating admin-only preview bar (Exit / Activate); `?template=` handling
end-to-end; `noindex` while previewing.
**Acceptance**: logged-in admin can preview each template; guest sees active
template and `?template=` is ignored for them.

---

## Notes

_(add dated notes here)_
