# 06 · Frontend & templates

## Source of truth

`Reference-Frontend/` was a standalone Vite + TanStack Router SPA with mock
data. It was ported into `resources/js` and removed in P5-03 (restore it with
`git checkout eb06a9a -- Reference-Frontend` if you need to compare). It contained three templates:

| Id           | Name       | Style                                                                | Fonts                                      |
| ------------ | ---------- | -------------------------------------------------------------------- | ------------------------------------------ |
| `changelog`  | Changelog  | Editorial "engineer's changelog", git-graph career, versions/commits | Bricolage Grotesque, Geist, JetBrains Mono |
| `playground` | Playground | Playful bento, stickers, tickets, dock navigation                    | see `templates/playground/index.ts`        |
| `terminal`   | Terminal   | Dark dev-terminal, mono type, slider, services                       | DM Mono (+ IBM Plex Sans Arabic → drop)    |

To look at the original designs, restore the folder as above, then run
`cd Reference-Frontend && npm install && npm run dev` and open
`/?template=terminal`, `/?template=playground`, `/?template=changelog`.

**The port must be visually identical** to the reference for every page of
every template (except the removed language toggle).

## How the reference works (what must be replaced)

| Reference mechanism                                             | Replacement in Laravel                                                                                                                                             |
| --------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| TanStack file routes `src/routes/*` with `loader`               | Laravel routes + controllers (`app/Http/Controllers/Site`) returning Inertia responses                                                                             |
| `lib/content.ts` (mock "API" over `data/*.ts`)                  | Eloquent queries + API Resources (same derived shapes computed in PHP)                                                                                             |
| `route.head()` + `buildHead()` in `lib/seo.ts`                  | PHP `SeoData` → `seo` prop → `<SeoHead>` using Inertia `<Head>` (SSR) — see [07](07-seo.md)                                                                        |
| `validateSearch` (zod) + `navigate({search})`                   | Form Request validation on the server; `onSearchChange` → `router.get(url, {...filters}, {preserveState: true, preserveScroll: true, replace: true, only: [...]})` |
| `@tanstack/react-router` `Link` (40 files)                      | `@inertiajs/react` `Link` with Wayfinder route helpers (`import { show } from '@/routes/projects'` → `href={show(slug).url}`)                                      |
| `useRouterState` (active path)                                  | `usePage().url`                                                                                                                                                    |
| Root loader `loadTemplate()` + `activateTemplate()`             | Server picks template → Inertia component `"{template}/{Page}"`; fonts link injected in Blade                                                                      |
| `useTemplatePages()`                                            | Not needed — each template has its own Inertia pages                                                                                                               |
| `submitContactMessage()` (fake latency)                         | Inertia `useForm().post('/contact')` (or Wayfinder form variant) → `ContactMessageController@store`, validation errors mapped to existing error keys               |
| `PreferencesProvider` locale + `useTranslation().l()` / `isRtl` | **Removed.** Keep theme (light/dark) only. `t()` stays as a tiny English dictionary for UI copy                                                                    |
| `i18n/dictionary.ts` `en` + `ar`                                | Keep `en` only                                                                                                                                                     |
| `ImageAsset` + `imageUrl()` (`/images/{base}-{w}.webp`)         | `ImageData` from Spatie media (see [05](05-media.md))                                                                                                              |
| `sessionStorage`/`localStorage` template keys                   | Server-side (session) — see Preview                                                                                                                                |

## As built (P3)

- Compatibility layer (decision D17): `resources/js/lib/router.tsx`,
  `resources/js/lib/content.ts`, English-only `hooks/useTranslation.ts`,
  `providers/PreferencesProvider.tsx` (theme only, cookie-backed — D19).
- Inertia pages are generated thin wrappers in `resources/js/pages/<template>/<Page>.tsx`
  (`SeoHead` + template component + `Page.layout = withTemplateLayout(Layout)`).
- `shared/inertia/withTemplateLayout.tsx` passes the shared `profile`, `socials` and the deferred
  `searchIndex` to the template Layout and shows the admin `PreviewBar` while previewing.
- `kit/useArchiveFilters.ts` (was `shared/inertia/useSearchChange.ts`) implements `onSearchChange` with `router.get(..., { only, preserveState, replace })`. The kit (`@/kit`) is described in [11 §3](11-template-kit.md#3-shared-building-blocks-resourcesjskit--built-in-p7-02).
- `shared/ui/CompanyLogo.tsx` renders uploaded company logos (dark variant aware) on the clients walls,
  falling back to the template wordmark.
- Images are `ImageData | null`; `ResponsiveImage` renders a neutral placeholder when nothing was uploaded.
- One stylesheet `resources/css/app.css` → `resources/js/styles/main.css` (D18).

## Target structure in `resources/js`

```
resources/js/
  app.tsx                 # createInertiaApp: resolve pages, title, progress, providers
  ssr.tsx                 # createServer(...) same resolver
  pages/
    changelog/Home.tsx ProjectArchive.tsx CaseStudy.tsx WritingArchive.tsx
              Article.tsx Books.tsx Uses.tsx Now.tsx NotFound.tsx
    playground/…same 9…
    terminal/…same 9…
  templates/
    types.ts              # the template contract (props per page)
    changelog/            # ported from Reference-Frontend/src/templates/changelog (minus index.ts registry)
    playground/
    terminal/
  shared/                 # command palette, ArchitectureDiagram, BrandIcon, CountUp, ResponsiveImage, RotatingText, SeoHead
  hooks/ lib/ providers/ i18n/ types/content.ts
  routes/ actions/        # generated by Wayfinder (gitignored or committed per Wayfinder docs)
```

Each Inertia page is thin:

```tsx
// resources/js/pages/terminal/Home.tsx
import type { HomePageProps } from '@/templates/types';
import { TerminalLayout } from '@/templates/terminal/layout/TerminalLayout';
import { HomePage } from '@/templates/terminal/home/HomePage';
import { SeoHead } from '@/shared/seo/SeoHead';

export default function Home(props: HomePageProps & PageSeoProps) {
    return (
        <>
            <SeoHead seo={props.seo} />
            <HomePage {...props} />
        </>
    );
}
Home.layout = (page: React.ReactNode) => (
    <TerminalLayout>{page}</TerminalLayout>
);
```

Layouts read global shared props (`profile`, `socials`, `searchIndex`) with
`usePage<SharedProps>().props` instead of receiving them from the root route.

`app.blade.php` `@vite([... "resources/js/pages/{$page['component']}.tsx"])`
already matches this naming (component `terminal/Home` → file
`pages/terminal/Home.tsx`).

## The template contract

`resources/js/templates/types.ts` keeps the reference's `HomePageProps`,
`ProjectArchivePageProps`, `CaseStudyPageProps`, `WritingArchivePageProps`,
`ArticlePageProps`, `BooksPageProps`, `UsesPageProps`, `NowPageProps`
(minus TanStack-specific bits), plus:

```ts
export interface SharedProps {
    site: {
        name: string;
        url: string;
        enabledPages: Record<'writing' | 'books' | 'uses' | 'now', boolean>;
    };
    profile: Profile;
    socials: Social[];
    searchIndex: SearchIndex; // Inertia once/deferred prop
    template: { id: TemplateId; isPreview: boolean };
    flash: { success?: string };
}
export interface PageSeoProps {
    seo: SeoData;
}
```

Controllers are the only producers of these props. A PHP test per page asserts
the prop keys (`assertInertia(fn ($page) => $page->component('terminal/Home')->has('profile')...)`)
for **each** template.

Search/filter props: archives receive `filters` (current validated query)
and the filtered list; `onSearchChange(patch)` becomes a helper
`useFilters(routeUrl)` that calls `router.get` with merged filters.

## Preview

Owner requirement: switch templates with `?template=<id>` for previewing,
while visitors see the one activated in the panel.

Rules (`TemplateManager::current()`):

**Preview is for the owner only** (decision Q01). Visitors always get the
active template and `?template=` is ignored for them.

1. `?template=<valid id>` → store in session `template.preview` **only if**
   the request has an authenticated user who can access the Filament panel
   (Filament uses the same `web` session guard, so being logged in to
   `/admin` is enough). `?template=` with an invalid value or
   `?template=reset` clears it.
2. If a preview is stored in session and the user is still an admin → use it.
3. Else → `SiteSetting::current()->active_template`.

While previewing: shared prop `template.isPreview = true` → render a small
floating "Previewing _Terminal_ · Exit preview · Activate" bar (admin only),
send `<meta name="robots" content="noindex">` and
`Vary: Cookie`; canonical URLs never include `?template=`.

## Fonts & theme

- Fonts are **self-hosted** with Fontsource packages (`@fontsource-variable/*`,
  `@fontsource/*`), imported at the top of `resources/js/styles/main.css`.
  Vite fingerprints the `.woff2` files; `@font-face` + `unicode-range` means a
  page downloads only the faces it renders. No third-party font requests.
- `preloadFonts` in each `template.json` lists the above-the-fold font files (Vite manifest
  keys); `app.blade.php` emits `<link rel="preload" as="font">` for the active
  template only, so the first paint uses the right type (no swap CLS).
- `<html data-template="terminal">` is set server-side in Blade.
- Theme (light/dark) pre-hydration script from reference `index.html` moves
  into `app.blade.php` (minus template/locale bits).
- Template CSS: `resources/js/templates/<id>/styles.css` imported by that
  template's layout so it is code-split; shared `base.css` imported by
  `app.tsx`. Tailwind 4 `@source` must include `resources/js/**`.

## Dependencies to add to root `package.json`

From the reference: `lucide-react`, `motion`, `sugar-high`. `zod` was dropped
(the contact form uses a tiny hand-written validator; the server validates anyway).
`motion` is used through `LazyMotion` + `m.*` components: `app.tsx` loads the
`domMax` feature bundle asynchronously (`lib/motionFeatures.ts`), so **always use
`m.div` etc., never `motion.div`** (`strict` mode throws).
**Not** needed: `@tanstack/react-router`, `@tanstack/router-plugin`,
`sharp` (Spatie handles images).

Match versions with the reference `package.json`; keep root tooling
(`vite-plus`, React Compiler) as is. Reference used TypeScript 7 while root
uses 5.7 — keep root's and fix any type errors.

## Adding a new template later

1. Create `resources/js/templates/<id>/` with a `template.json` manifest
   (id, name, description, author, preloadFonts, screenshot — see
   [11](11-template-kit.md#1-template-registry-replaces-the-enum)) and import
   its Fontsource packages in `styles/main.css`.
2. Add the 9 pages in `resources/js/pages/<id>/`.
3. Add `public/templates/<id>.webp` screenshot for the Appearance page.
4. The registry discovers the manifest and the Pest test matrix picks the new
   template up automatically. No PHP change is needed.
