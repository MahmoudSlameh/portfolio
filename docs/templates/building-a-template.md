# Building a template

A step-by-step guide to adding a new design for the public site. A template
only decides **how things look**: every page receives its content from
Laravel as props, and all behaviour (contact form, filters, search, theme)
comes from the template kit. You never write PHP.

- Design background: [11 · Template kit](../11-template-kit.md)
- The contract (all prop types): [`resources/js/templates/types.ts`](../../resources/js/templates/types.ts)
- The content shapes: [`resources/js/types/content.ts`](../../resources/js/types/content.ts)
- A complete, commented example: [`resources/js/templates/minimal`](../../resources/js/templates/minimal)

---

## 1. Before you start

```bash
composer setup                                  # once
php artisan db:seed                             # admin user + settings
php artisan db:seed --class=DemoContentSeeder   # a full demo portfolio to design against
echo "PHP_CLI_SERVER_WORKERS=4" >> .env         # lets the gallery load its frames in parallel
composer dev                                    # Laravel + queue + Vite
```

Keep **<http://localhost:8000/dev/templates>** open while you work: every
page of every template side by side, light or dark, desktop or mobile
(local only, see [11 §5](../11-template-kit.md#5-template-gallery-for-development--built-in-p7-05)).

## 2. Create the template

```bash
php artisan make:template magazine --name="Magazine" --description="Editorial long-form layout" --author="Your Name"
```

- `--from=<id>` copies another template instead of `minimal` (for example
  `--from=terminal` to start from a dark design).
- Ids are lowercase slugs (`magazine`, `retro-80s`); `studio` and `reset` are
  reserved.

Restart `composer dev` so Vite sees the new files, then open
`/dev/templates?template=magazine`. To see it as the real site, sign in to
`/admin` and visit `/?template=magazine` (a private preview), or activate it
in **Site → Appearance**.

## 3. Anatomy

```
resources/js/templates/magazine/
  template.json            # manifest: how the site and the panel know the template
  styles.css               # design tokens + your CSS, scoped to [data-template='magazine']
  README.md                # next steps (safe to delete)
  layout/MagazineLayout.tsx  # header, nav, footer; wraps every page
  components/…             # anything you like
  pages/                   # one component per page of the contract (§7)
    HomePage.tsx ProjectArchivePage.tsx CaseStudyPage.tsx WritingArchivePage.tsx
    ArticlePage.tsx BooksPage.tsx UsesPage.tsx NowPage.tsx NotFoundPage.tsx
resources/js/pages/magazine/*.tsx   # nine thin Inertia wrappers (rarely edited)
public/templates/magazine.webp      # screenshot for the Appearance page (960 × 600)
```

The Inertia wrapper for each page renders the SEO head, your page component
and attaches your layout:

```tsx
// resources/js/pages/magazine/Home.tsx (generated)
export default function Home({
    seo,
    ...props
}: HomePageProps & { seo: SeoData }) {
    return (
        <>
            <SeoHead seo={seo} />
            <HomePage {...props} />
        </>
    );
}
Home.layout = withTemplateLayout(MagazineLayout);
```

## 4. The manifest (`template.json`)

| Field          | Required | Meaning                                                                                             |
| -------------- | -------- | --------------------------------------------------------------------------------------------------- |
| `id`           | yes      | Must equal the folder name. Also the Inertia namespace (`magazine/Home`) and `<html data-template>` |
| `name`         | yes      | Shown on the Appearance page and in the preview bar                                                 |
| `description`  | yes      | One sentence under the name on the Appearance page                                                  |
| `screenshot`   | yes      | Public path of the card image, `templates/<id>.webp`                                                |
| `preloadFonts` | no       | Above-the-fold `.woff2` files (Vite manifest keys) preloaded in `<head>`; `[]` for system fonts     |
| `author`       | no       | Your name                                                                                           |

The registry reads the manifests on every request in development. In
production `php artisan optimize` caches them (`template:cache`), so run it
after deploying a new template.

## 5. Styling

All template stylesheets ship in one bundle (decision D18), so **every rule
must be scoped** to your template:

```css
[data-template='magazine'] {
    --tpl-font-display: 'Fraunces Variable', serif;
    --tpl-font-sans: 'Inter Variable', sans-serif;
    --tpl-font-mono: ui-monospace, monospace;

    --paper: #fffdf7; /* page background   → bg-paper   */
    --surface: #f4efe3; /* cards, code blocks → bg-surface */
    --raised: #ffffff; /* inputs, popovers  → bg-raised  */
    --ink: #1b1a17; /* text              → text-ink   */
    --ink-muted: #57534e; /*                   → text-ink-muted */
    --ink-subtle: #78716c; /*                   → text-ink-subtle */
    --line: #e7e0d0; /* borders           → border-line */
    --signal: #b91c1c; /* accent            → text-signal, bg-signal */
    /* also: --line-strong --signal-ink --signal-soft --on-signal --danger --danger-soft … */
}

[data-template='magazine'][data-theme='dark'] {
    --paper: #14120f;
    --ink: #f5f1e8;
    /* … override what changes in dark mode */
}
```

- The tokens feed Tailwind colours defined in `resources/js/styles/base.css`
  (`bg-paper`, `text-ink`, `border-line`, `text-signal` …) and the fonts
  (`font-display`, `font-sans`, `font-mono`). Use them and dark mode works
  for free. The `dark:` variant is available too.
- Your own classes: prefix them (`mg-card`) and put them under the scope,
  as `minimal/styles.css` does in `@layer components`.
- **Fonts**: only self-hosted Fontsource packages (no Google Fonts or other
  third-party requests, decision D20):
    1. `npm install @fontsource-variable/fraunces`
    2. import it at the top of `resources/js/styles/main.css`
    3. list the above-the-fold `.woff2` files in `preloadFonts`
       (e.g. `node_modules/@fontsource-variable/fraunces/files/fraunces-latin-wght-normal.woff2`).
       A test checks every listed file exists.
- **Motion**: use `m.div` etc. from `motion/react`, never `motion.div`
  (decision D22). Animations must respect reduced motion (the global CSS
  already disables CSS animations for it).

## 6. The layout

```tsx
export function MagazineLayout({
    profile,
    socials,
    searchIndex,
    children,
}: LayoutProps) {
    const { site } = usePage<SharedProps>().props; // other shared props

    return (
        <>
            <a href="#main">Skip to content</a>
            <header>{/* name, nav, theme toggle */}</header>
            <main id="main" tabIndex={-1}>
                {children}
            </main>
            <footer>{/* socials */}</footer>
            {/* Ctrl/⌘ + K search over pages, projects, articles and books */}
            <CommandPalette searchIndex={searchIndex} email={profile.email} />
        </>
    );
}
```

| Prop / shared prop      | Type                               | Notes                                                                             |
| ----------------------- | ---------------------------------- | --------------------------------------------------------------------------------- |
| `profile`               | `Profile`                          | Name, role, headline, portrait, availability, stats, story… (also passed to Home) |
| `socials`               | `Social[]`                         | Render icons with `<BrandIcon icon={social.icon} />`                              |
| `searchIndex`           | `SearchIndex`                      | Deferred: empty on the first render, filled right after                           |
| `children`              | `ReactNode`                        | The page                                                                          |
| `site.name`, `site.url` | `string`                           | From `usePage<SharedProps>().props`                                               |
| `site.enabledPages`     | `Record<'writing'…'now', boolean>` | Hide nav links of pages switched off in the panel (they return 404)               |
| `template`              | `{ id, name, isPreview }`          | The preview bar is rendered for you                                               |

The nav items and their labels are in `@/config/navigation` (`primaryNav`);
`minimal`'s layout shows how to filter them by `site.enabledPages`.

## 7. Pages and their props

Every page receives exactly these props (produced by
`app/Http/Controllers/Site/*`). Types come from `@/templates/types` and
`@/types/content`. **Any list can be empty**: render nothing rather than an
empty section.

### Home — `HomePage` (`/`)

| Prop             | Type                 | Notes                                                                 |
| ---------------- | -------------------- | --------------------------------------------------------------------- |
| `profile`        | `Profile`            | `portrait` may be `null`; `story` paragraphs; `stats`; `availability` |
| `socials`        | `Social[]`           |                                                                       |
| `skillGroups`    | `SkillGroup[]`       | `{ category, skills }`                                                |
| `career`         | `CareerEntry[]`      | Experience + `company` (with `logo`/`logoDark`) + linked `projects`   |
| `projects`       | `Project[]`          | Featured projects only (case-study fields are empty in lists)         |
| `companies`      | `Company[]`          | Featured companies; show `logo` with `<CompanyLogo>`                  |
| `services`       | `ServicesSection`    | `{ heading: { kicker, title, highlight }, items }`; hide when empty   |
| `testimonials`   | `TestimonialEntry[]` |                                                                       |
| `education`      | `Education[]`        | Show `description` and every entry of `notes` (the panel's "Details") |
| `certifications` | `Certification[]`    |                                                                       |
| `articles`       | `ArticleSummary[]`   | Newest first                                                          |
| `books`          | `Book[]`             | Filter by `status` (`reading`, `read`, `to-read`)                     |

The contact form belongs on Home, in a section with `id="contact"` (the nav
and command palette link to `/#contact`). Use `useContactForm()` (§8).

### Project archive — `ProjectArchivePage` (`/projects`)

| Prop             | Type                                      | Notes                                                                 |
| ---------------- | ----------------------------------------- | --------------------------------------------------------------------- |
| `projects`       | `Project[]`                               | Already filtered and sorted by the server                             |
| `facets`         | `ProjectFacets`                           | `technologies` and `categories` to build the filters                  |
| `total`          | `number`                                  | Count before filtering                                                |
| `search`         | `ArchiveSearch`                           | Current filters: `q`, `tech`, `category`, `sort`, `view`              |
| `onSearchChange` | `(patch: Partial<ArchiveSearch>) => void` | Change filters; the page reloads only the lists (debounce text input) |

### Case study — `CaseStudyPage` (`/projects/{slug}`)

| Prop      | Type            | Notes                                                                                                                                                                                                                                |
| --------- | --------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `project` | `ProjectDetail` | `cover`, `overview`, `problem`, `approach`, `architecture` (render with `<ArchitectureDiagram>`), `features`, `challenges`, `metrics`, `gallery`, `links`, `company`, `previous`/`next`, `relatedArticles`. Every block is optional. |

### Writing archive — `WritingArchivePage` (`/writing`)

| Prop             | Type                                      | Notes                       |
| ---------------- | ----------------------------------------- | --------------------------- |
| `articles`       | `ArticleSummary[]`                        | Filtered by `q` / `tag`     |
| `allArticles`    | `ArticleSummary[]`                        | Unfiltered, e.g. for counts |
| `tags`           | `string[]`                                | Every tag in use            |
| `search`         | `WritingSearch`                           | `q`, `tag`                  |
| `onSearchChange` | `(patch: Partial<WritingSearch>) => void` |                             |

### Article — `ArticlePage` (`/writing/{slug}`)

| Prop      | Type            | Notes                                                                                                                  |
| --------- | --------------- | ---------------------------------------------------------------------------------------------------------------------- |
| `article` | `ArticleDetail` | `body` blocks (render with the kit's `ArticleBlocks`), `readingMinutes`, `cover`, `relatedProjects`, `previous`/`next` |

### Books — `BooksPage` (`/books`)

| Prop             | Type                                      | Notes                                                        |
| ---------------- | ----------------------------------------- | ------------------------------------------------------------ |
| `books`          | `Book[]`                                  | Filtered by `status` / `category`                            |
| `stats`          | `BookStats`                               | Over the whole library: totals, pages, `perYear`, categories |
| `search`         | `LibrarySearch`                           | `status`, `category`, `view`                                 |
| `onSearchChange` | `(patch: Partial<LibrarySearch>) => void` |                                                              |

Give each book `id="book-<slug>"`: the command palette links to `/books#book-<slug>`.

### Uses — `UsesPage` (`/uses`)

| Prop     | Type          | Notes                                         |
| -------- | ------------- | --------------------------------------------- |
| `groups` | `UsesGroup[]` | Hardware / software / development, with items |

### Now — `NowPage` (`/now`)

| Prop  | Type        | Notes                                                                           |
| ----- | ----------- | ------------------------------------------------------------------------------- |
| `now` | `NowDetail` | `focus`, `learning`, `reading` (books), `availability`, `location`, `updatedAt` |

### Not found — `NotFoundPage` (any unknown URL, 404 status)

No content props. Link back to `/` and `/projects`.

## 8. The kit: behaviour you do not rewrite

Import from `@/kit`:

| Need                   | Use                                                                                                                                                                         |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Contact form           | `useContactForm()` → `values`, `errors`, `errorText(field)`, `handleChange`, `handleSubmit`, `status`, `reset`, `summaryRef`, `nameRef`; `CONTACT_FIELDS`, `CONTACT_TOPICS` |
| Archive filters        | `onSearchChange` prop (already wired by the wrapper with `useArchiveFilters`)                                                                                               |
| Debounced search input | `useDebouncedCallback(fn, 300)`                                                                                                                                             |
| Site search            | `useSiteSearch(query)`, or drop in `<CommandPalette>`                                                                                                                       |
| Light/dark toggle      | `useTheme()` → `{ theme, toggleTheme }`                                                                                                                                     |
| Article body           | `<ArticleBlocks blocks={…} renderers={…} />` with one renderer per block type                                                                                               |
| Images                 | `<ResponsiveImage image={…} sizes="…" />` (renders a placeholder for `null`)                                                                                                |
| Company logos          | `<CompanyLogo company={…} fallback={…} />` (dark-mode variant handled)                                                                                                      |
| Social icons           | `<BrandIcon icon={social.icon} />`                                                                                                                                          |
| Links                  | `<Link to="/projects/$slug" params={{ slug }}>`; `useNavigate()`, `useRouterState()`                                                                                        |
| UI copy                | `useTranslation().t('contact.submit')` (English dictionary in `@/i18n/dictionary`)                                                                                          |
| Dates and numbers      | `formatDate`, `formatMonth`, `yearRange`, `formatNumber`, `cn`                                                                                                              |

`ArticleBlockRenderers` is exhaustive: if a new block type is added to the
article body, TypeScript points at every template that does not render it
yet.

Paragraph, quote, list item and callout text can hold inline formatting
(`**bold**`, `*italic*`, `` `code` ``, `[text](url)`). Render it with
`<InlineText text={block.text} classNames={{ code: '…', link: '…' }} />`,
never as raw HTML; use `plainText(text)` where you need a string (an aria
label, a title). Headings are always plain text.

## 9. Rules

1. Render only the props you receive. Never `fetch`, never hard-code
   personal content (bio, projects, links, images). UI micro-copy is fine.
2. Every list can be empty: render nothing, not an empty section.
3. Scope all CSS under `[data-template='<id>']`.
4. Self-hosted fonts only (Fontsource).
5. `m.*` from `motion/react`, never `motion.*`; respect reduced motion.
6. Images through `ResponsiveImage` / `CompanyLogo`, with meaningful `sizes`.
7. Keep it accessible: one `<h1>` per page, labelled landmarks, visible
   focus, a "skip to content" link to `#main`.

## 10. Test and ship

1. Check every page in `/dev/templates?template=<id>`, light and dark,
   desktop and mobile, with the demo content **and** with an empty database
   (`php artisan migrate:fresh --seed`; this deletes your local data, then
   re-seed the demo with `DemoContentSeeder`).
2. Replace `public/templates/<id>.webp` with a 960 × 600 screenshot of your
   home page (take it from the gallery's "Open ↗" link).
3. Run `composer ci:check`. The test suite renders every page of every
   template (the `templates` dataset reads the manifests), checks the
   manifest, the nine page files and the preloaded fonts.
4. Open a pull request with screenshots (see
   [CONTRIBUTING](../../CONTRIBUTING.md)).

## 11. Troubleshooting

| Symptom                                                  | Fix                                                                                           |
| -------------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| The template is not on the Appearance page               | Check `template.json` (the `id` must equal the folder name); run `php artisan template:clear` |
| `Unable to locate file in Vite manifest: …/pages/<id>/…` | Restart `composer dev`, or run `npm run build` / `npm run build:ssr`                          |
| Styles leak into other templates                         | A rule is missing the `[data-template='<id>']` scope                                          |
| A font does not load                                     | Import its Fontsource CSS in `styles/main.css`; check the `preloadFonts` path exists          |
| `/dev/templates` returns 404                             | It only runs with `APP_ENV=local` (or `TEMPLATE_GALLERY=true`)                                |
| Frames in the gallery load slowly                        | Set `PHP_CLI_SERVER_WORKERS=4` in `.env` and restart `composer dev`                           |
