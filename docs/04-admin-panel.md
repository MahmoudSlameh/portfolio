# 04 · Admin panel (Filament 5)

Goal: a panel that feels like a polished product, not a CRUD scaffold. Use
what Filament ships: sections with descriptions & icons, 2/3 + 1/3 layouts,
tabs, repeaters, builder, toggle buttons, badges with enum colors/icons,
reorderable tables, global search, database notifications, dashboard widgets,
image editor, SPA mode.

> Filament 5 = Filament 4 APIs on Livewire 4. Schemas live in
> `Filament\Schemas\Components\*` (Section, Grid, Tabs, Fieldset, Utilities\Get/Set),
> fields in `Filament\Forms\Components\*`, columns in `Filament\Tables\Columns\*`,
> actions in `Filament\Actions\*`. Generate resources with
> `php artisan make:filament-resource Name --generate` which produces
> `Resources/Names/{NameResource.php, Pages/, Schemas/NameForm.php, Tables/NamesTable.php}`.
> Use Laravel Boost's `search-docs` tool for exact v5 syntax before writing code.

## Panel configuration (`AdminPanelProvider`)

```php
$panel
    ->default()->id('admin')->path('admin')
    ->login()->passwordReset()->profile(isSimple: false)
    ->brandName(fn () => Profile::current()->name)
    ->brandLogo(...)            // optional: initials monogram SVG
    ->favicon(asset('favicon.svg'))
    ->colors(['primary' => Color::Indigo, 'gray' => Color::Zinc])
    ->font('Inter')
    ->darkMode(true)
    ->spa()
    ->sidebarCollapsibleOnDesktop()
    ->maxContentWidth(Width::Full)
    ->globalSearch()->globalSearchKeyBindings(['command+k', 'ctrl+k'])
    ->databaseNotifications()->databaseNotificationsPolling('30s')
    ->unsavedChangesAlerts()
    ->databaseTransactions()
    ->navigationGroups([
        NavigationGroup::make('Profile')->icon(Heroicon::OutlinedUserCircle),
        NavigationGroup::make('Career')->icon(Heroicon::OutlinedBriefcase),
        NavigationGroup::make('Work')->icon(Heroicon::OutlinedRectangleStack),
        NavigationGroup::make('Content')->icon(Heroicon::OutlinedNewspaper),
        NavigationGroup::make('Inbox')->icon(Heroicon::OutlinedInbox),
        NavigationGroup::make('Site')->icon(Heroicon::OutlinedCog6Tooth),
    ])
    ->userMenuItems([Action::make('viewSite')->url('/')->openUrlInNewTab()->icon(Heroicon::OutlinedGlobeAlt)]);
```

Remove `FilamentInfoWidget`. Optionally a custom theme
(`php artisan make:filament-theme`) for small polish (brand font, softer
cards).

## Navigation map

| Group | Item | Type | Model |
|-------|------|------|-------|
| — | Dashboard | Page | widgets |
| Profile | **Profile & Bio** | Singleton page | `Profile` |
| Profile | Social links | Resource (simple/modal) | `Social` |
| Profile | Now page | Singleton page | `NowPage` |
| Career | **Work experience** | Resource | `Experience` |
| Career | **Education** | Resource | `Education` |
| Career | Certifications | Resource (simple) | `Certification` |
| Career | **Companies & clients** | Resource | `Company` |
| Career | Testimonials | Resource | `Testimonial` |
| Work | Projects | Resource | `Project` |
| Work | Skills | Resource (simple) + category relation manager | `Skill`, `SkillCategory` |
| Content | Articles | Resource | `Article` |
| Content | Books | Resource | `Book` |
| Content | Uses | Resource (groups with items repeater) | `UsesGroup` |
| Inbox | Messages (badge = unread count) | Resource (read-only + actions) | `ContactMessage` |
| Site | **Appearance (templates)** | Custom page | `SiteSetting.active_template` |
| Site | SEO & settings | Singleton page | `SiteSetting` |
| Site | Users | Resource (simple) | `User` |

## Shared building blocks (`app/Filament/Support`)

- `Fields\CountrySelect` — searchable `Select` from `Countries::getNames()`,
  flag emoji prefix.
- `Fields\MonthPicker` — `DatePicker` with `->native(false)->displayFormat('M Y')`,
  stores first of month.
- `Fields\VisibilityAside` — aside `Section` with `is_visible` toggle,
  `sort_order`, created/updated `TextEntry` placeholders.
- `Columns\PeriodColumn` — "Feb 2024 — Present · 1 yr 8 mos" with `Present`
  badge when `end_date` is null.
- `Actions\ViewOnSiteAction` — opens the public URL (projects, articles).
- `Actions\ToggleVisibilityBulkAction`.
- Enum badges: all enums implement `HasLabel`, `HasColor`, `HasIcon`, so
  `TextColumn::make('status')->badge()` and `ToggleButtons::make()->options(Enum::class)`
  render consistently.

Standard layout for resource forms:

```php
Grid::make(3)->schema([
    Group::make([ /* main sections */ ])->columnSpan(['lg' => 2]),
    Group::make([ /* aside: media, visibility, meta */ ])->columnSpan(['lg' => 1]),
])
```

Standard table behaviour: `->reorderable('sort_order')` where the model has
`sort_order`, `ToggleColumn is_visible`, `->striped()`, sensible
`->defaultSort()`, `->persistFiltersInSession()`, empty state with icon +
"Create" action, record URL → edit page.

---

## Profile & Bio (singleton page `EditProfile`)

Custom `Page` with a form bound to `Profile::current()` (so Spatie uploads
work) and a sticky "Save" action. Layout: **Tabs** (persisted in query string).

| Tab | Fields |
|-----|--------|
| Identity | name, initials (auto placeholder), role (job title), location, timezone (searchable Select of `timezone_identifiers_list()`), timezone_label, email, phone · aside: **portrait** upload (image editor 3:4) + `portrait_alt`, **resume** PDF |
| Bio | headline (Textarea, char counter 300), summary (Textarea), **story** (Repeater `simple` Textarea, reorderable — long bio paragraphs), focus_areas (TagsInput, reorderable) |
| Availability | availability_status (ToggleButtons: open/limited/closed with colors), availability_label, availability_note |
| Highlights | stats (Repeater: value + label, grid 2, max 6), status strip (Repeater: label, value, tone), principles (Repeater: title + body, collapsible, `itemLabel` = title) |
| Changelog extras | current_version, latest_release.added / .changed / .removed (TagsInput ×3). Description: "Used by the Changelog template only." |

## Work experience (`ExperienceResource`) — owner priority

**Form**

- Section *Position* (icon briefcase):
  - `company_id` — Select relationship, searchable, preload,
    `->createOptionForm(CompanyForm::quick())` (name, kind, website, logo) and
    `->editOptionForm(...)`; hint "Leave empty for open-source / personal".
  - `organization_name` — visible & required when `company_id` is empty.
  - `role` — "Job title", required.
  - `employment_type` — Select (enum).
  - `work_mode` — ToggleButtons inline: On-site (building icon) / Remote
    (globe) / Hybrid (arrows).
- Section *Location* (icon map-pin), 3 columns: `country_code`
  (CountrySelect), `city`, `address` (optional, full width).
- Section *Period* (icon calendar):
  - `start_date` MonthPicker required.
  - `is_current` Toggle (dehydrated false, `->live()`, afterStateHydrated =
    `end_date === null`) — "I currently work here".
  - `end_date` MonthPicker, hidden when current, `->afterOrEqual('start_date')`.
- Section *Description*: `summary` (Textarea, 3 rows, char counter),
  `highlights` — Repeater `->simple(Textarea)` labelled **Achievements**,
  reorderable, add action "Add achievement".
- Section *Tech stack*: `skills` Select multiple relationship, searchable,
  `->createOptionForm([name, category])`.
- Aside: visibility section; collapsed section *Changelog template* with
  `branch`, `version`, `commit_hash`, `commit_message` — each shows the
  auto-derived value as placeholder.

**Table**: company logo (SpatieMediaLibraryImageColumn, circular) + `role`
with `organization` as description; `employment_type` badge; `work_mode`
badge + icon; country (flag + name); PeriodColumn; `highlights` count;
`is_visible` toggle. Default sort `start_date desc`. Filters: employment
type, work mode, company, "Current only" ternary. Actions: edit, replicate
(opens edit), delete.

**Relation manager**: *Projects* (attach existing projects to this role).

## Education (`EducationResource`) — owner priority

**Form**

- Section *Qualification*: `degree` ("Qualification", e.g. *Diploma in
  Software Engineering*), `field_of_study` ("Specialization"), `grade`
  (with hint examples).
- Section *Institution*: `institution`, `institution_url`, `country_code`,
  `city` · aside: institution `logo` upload.
- Section *Period*: `start_date`, `is_current` toggle ("I'm currently
  studying here"), `end_date` (hidden when current).
- Section *Details*: `description`, `achievements` (Repeater simple,
  reorderable), `certificate` upload (image/PDF, optional).

**Table**: logo, degree + institution (description), field, grade badge,
period ("2020 — Present"), visibility. Default sort `start_date desc`.

## Companies & clients (`CompanyResource`) — owner priority

**Form**: Section *Brand*: `name`, `slug` (auto from name, editable),
`kind` ToggleButtons (Employer / Client), `website_url` (url, prefix icon
link, suffix action "open"), `industry` · aside: `logo` (svg/png/webp, image
editor off for SVG), `logo_dark` (optional), `wordmark_style` Select with
helper "Used when no logo is uploaded", `is_featured`, visibility.
Section *Engagement*: `engagement` Textarea, `period_label` (placeholder shows
derived period), `city`, `country_code`.

**Table** (grid layout via `->contentGrid(['md' => 2, 'xl' => 4])` card view
with logo, name, kind badge, website link), reorderable, filters: kind,
featured. Relation managers: *Experiences*, *Projects*, *Testimonials*.

## Projects (`ProjectResource`)

Form uses **Tabs**:

| Tab | Content |
|-----|---------|
| Basics | title (live → slug), slug, tagline, summary, year, status (badge select), category, is_featured, version, company (select + create), experience (select filtered by company), role, team, timeline, skills (stack, multi-select) · aside: `cover` upload (16:9 editor) + `cover_alt`, publish section (is_published, published_at), ViewOnSite |
| Story | overview (Repeater simple Textarea), problem (Repeater simple), approach (Repeater title+description, collapsible), features (same), challenges (same) |
| Architecture | caption, columns, rows; nodes Repeater (id, label, detail, kind select, column, row; grid 3); edges Repeater (from/to Selects populated from node ids via `Get`, label). Optional live preview (custom view field rendering the SVG diagram). |
| Metrics & links | metrics Repeater (value, label, detail; grid 3); links Repeater (label, url, kind) |
| Gallery | relationship Repeater `galleryItems` (image upload, alt, caption), reorderable, grid 2 |
| SEO | meta_title (char counter 60), meta_description (counter 160), SERP preview (custom view) |

**Table**: cover thumb, title + tagline, category badge, status badge,
featured icon toggle, year, stack (first 3 badges), published icon.
Filters: status, category, featured, company, trashed. Reorderable.
Actions: view on site, replicate, delete/restore.

## Articles (`ArticleResource`)

- Main: title → slug, excerpt, **body = `Builder`** with blocks (icons +
  labels): Paragraph (Textarea), Heading (text; id auto-slug, hidden), Code
  (language Select, filename, `CodeEditor` if available else monospace
  Textarea), Quote, List (Repeater simple), Callout, Image (Select from
  `body_images` with thumbnails, alt, caption). `->collapsible()->cloneable()->blockNumbers(false)`.
- Aside: status ToggleButtons (Draft / Published), published_at, tags
  (TagsInput with suggestions from existing tags), related projects
  (multi-select), cover upload + alt, `body_images` (multiple), reading time
  (computed placeholder), SEO section.
- Table: title, tags badges, status badge, published_at, reading minutes.
  Tabs on list page: All / Published / Drafts (with counts).

## Books (`BookResource`)

Form: title, author, slug, published_year, category, status (ToggleButtons),
finished_at (visible when status = read), rating (ToggleButtons 1–5 stars,
visible when read), pages, note. Cover section: `cover_image` upload OR
generated cover: `cover_background` / `cover_ink` / `cover_accent`
(ColorPicker) + `cover_style` Select, with live preview (custom view
component reusing the book-cover SVG).
Table: cover, title/author, category, status badge, rating stars, finished.
Filters: status, category. Header widget: reading stats.

## Skills (`SkillResource` + `SkillCategoryResource`)

- Categories: simple resource (modal forms), reorderable, with skills count.
- Skills: name, slug, category, proficiency (ToggleButtons 1–5 or `Slider`
  if available), years, icon key (Select with brand icons preview) or custom
  icon upload. Table grouped by category (`->groups(['category.name'])`),
  reorderable, inline editable proficiency (`SelectColumn`).
  Filter "Used in stack only" (no category).

## Certifications, Testimonials, Socials, Uses

- **Certifications** (simple, modals): name, issuer, issued_at, expires_at,
  credential_id, credential_url, badge upload. Table with "Expired" badge.
- **Testimonials**: quote (Textarea), author_name, author_role, company
  (select + create), relation, avatar. Table shows avatar + quote excerpt.
- **Socials** (simple, modals, reorderable): platform (Select with icons),
  label (auto from platform), handle, url.
- **Uses**: group resource with `kind`, `title`, and relationship Repeater
  `items` (name, description, url, image).

## Now page (singleton `EditNowPage`)

location, availability, focus Repeater (title, body), learning Repeater,
reading books (multi-select of books, default = status reading). Shows
"Last updated" and a "View /now" action.

## Inbox · Messages (`ContactMessageResource`)

- No create/edit. List: unread rows bold (`->recordClasses`), name, email,
  topic badge, message excerpt, received (since). Tabs: Unread / All.
- View page (infolist): full message, meta (IP, UA), actions: **Reply**
  (`mailto:` URL with subject), Mark as read/unread, Delete.
- Navigation badge = unread count (`getNavigationBadge`, color warning).
- On new message: database notification to all admins + mail notification
  to `contact_recipient` (queued).

## Site · Appearance (custom page `Appearance`)

- Card grid (one card per `Template` enum case): screenshot
  (`public/templates/<id>.webp`), name, short description, fonts, "Active"
  badge on the current one.
- Card actions: **Activate** (confirmation modal → updates
  `site_settings.active_template`, flushes cache, success notification) and
  **Preview** (opens `/?template=<id>` in new tab; works for logged-in admins
  even when public preview is off).
- Toggle `allow_public_preview`.

## Site · SEO & settings (singleton page `SiteSettings`)

Tabs: *General* (site_name, title_separator, enabled_pages toggles,
contact_recipient) · *SEO* (meta_description, default_og_image, twitter_handle,
indexable toggle with danger description, verification codes) ·
*Advanced* (analytics_snippet, favicon). Header action: "Rebuild caches"
(flush content caches + regenerate sitemap).

## Dashboard widgets

1. `StatsOverviewWidget`: projects (published/total), articles, unread
   messages, books read this year — each with sparkline/description.
2. `LatestMessagesWidget` (table, 5 rows).
3. `ContentHealthWidget`: checklist of missing things (no portrait, projects
   without cover/alt, missing meta descriptions, no active socials) with links.
4. `QuickLinksWidget`: View site, Edit profile, New project, New article.

## Authorization

Single owner. `User::canAccessPanel()` returns true only for emails in
`config('portfolio.admin_emails')` (env `ADMIN_EMAILS`) in production.
