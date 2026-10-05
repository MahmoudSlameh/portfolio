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

| Group   | Item                            | Type                                          | Model                         |
| ------- | ------------------------------- | --------------------------------------------- | ----------------------------- |
| —       | Dashboard                       | Page                                          | widgets                       |
| Profile | **Profile & Bio**               | Singleton page                                | `Profile`                     |
| Profile | Social links                    | Resource (simple/modal)                       | `Social`                      |
| Profile | Now page                        | Singleton page                                | `NowPage`                     |
| Career  | **Work experience**             | Resource                                      | `Experience`                  |
| Career  | **Education**                   | Resource                                      | `Education`                   |
| Career  | Certifications                  | Resource (simple)                             | `Certification`               |
| Career  | **Companies & clients**         | Resource                                      | `Company`                     |
| Career  | Testimonials                    | Resource                                      | `Testimonial`                 |
| Work    | Services                        | Resource (simple) + "Section heading" action  | `Service`, `SiteSetting`      |
| Work    | Projects                        | Resource                                      | `Project`                     |
| Work    | Skills                          | Resource (simple) + category relation manager | `Skill`, `SkillCategory`      |
| Content | Articles                        | Resource                                      | `Article`                     |
| Content | Books                           | Resource                                      | `Book`                        |
| Content | Uses                            | Resource (groups with items repeater)         | `UsesGroup`                   |
| Inbox   | Messages (badge = unread count) | Resource (read-only + actions)                | `ContactMessage`              |
| Site    | **Appearance (templates)**      | Custom page                                   | `SiteSetting.active_template` |
| Site    | SEO & settings                  | Singleton page                                | `SiteSetting`                 |
| Site    | AI                              | Singleton page (`AiSettingsPage`)             | `SiteSetting.ai_*`            |
| Site    | Claude connector                | Custom page (`ClaudeConnector`)               | Passport tokens               |
| Site    | Users                           | Resource (simple)                             | `User`                        |

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

| Tab              | Fields                                                                                                                                                                                                                                      |
| ---------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Identity         | name, initials (auto placeholder), role (job title), location, timezone (searchable Select of `timezone_identifiers_list()`), timezone_label, email, phone · aside: **portrait** upload (image editor 3:4) + `portrait_alt`, **resume** PDF |
| Bio              | headline (Textarea, char counter 300), summary (Textarea), **story** (Repeater `simple` Textarea, reorderable — long bio paragraphs), focus_areas (TagsInput, reorderable)                                                                  |
| Availability     | availability_status (ToggleButtons: open/limited/closed with colors), availability_label, availability_note                                                                                                                                 |
| Highlights       | stats (Repeater: value + label, grid 2, max 6), status strip (Repeater: label, value, tone), principles (Repeater: title + body, collapsible, `itemLabel` = title)                                                                          |
| Changelog extras | current_version, latest_release.added / .changed / .removed (TagsInput ×3). Description: "Used by the Changelog template only."                                                                                                             |

## Work experience (`ExperienceResource`) — owner priority

**Form**

- Section _Position_ (icon briefcase):
    - `company_id` — Select relationship, searchable, preload,
      `->createOptionForm(CompanyForm::quick())` (name, kind, website, logo) and
      `->editOptionForm(...)`; hint "Leave empty for open-source / personal".
    - `organization_name` — visible & required when `company_id` is empty.
    - `role` — "Job title", required.
    - `employment_type` — Select (enum).
    - `work_mode` — ToggleButtons inline: On-site (building icon) / Remote
      (globe) / Hybrid (arrows).
- Section _Location_ (icon map-pin), 3 columns: `country_code`
  (CountrySelect), `city`, `address` (optional, full width).
- Section _Period_ (icon calendar):
    - `start_date` MonthPicker required.
    - `is_current` Toggle (dehydrated false, `->live()`, afterStateHydrated =
      `end_date === null`) — "I currently work here".
    - `end_date` MonthPicker, hidden when current, `->afterOrEqual('start_date')`.
- Section _Description_: `summary` (Textarea, 3 rows, char counter),
  `highlights` — Repeater `->simple(Textarea)` labelled **Achievements**,
  reorderable, add action "Add achievement".
- Section _Tech stack_: `skills` Select multiple relationship, searchable,
  `->createOptionForm([name, category])`.
- Aside: visibility section; collapsed section _Changelog template_ with
  `branch`, `version`, `commit_hash`, `commit_message` — each shows the
  auto-derived value as placeholder.

**Table**: company logo (SpatieMediaLibraryImageColumn, circular) + `role`
with `organization` as description; `employment_type` badge; `work_mode`
badge + icon; country (flag + name); PeriodColumn; `highlights` count;
`is_visible` toggle. Default sort `start_date desc`. Filters: employment
type, work mode, company, "Current only" ternary. Actions: edit, replicate
(opens edit), delete.

**Relation manager**: _Projects_ (attach existing projects to this role).

## Education (`EducationResource`) — owner priority

**Form**

- Section _Qualification_: `degree` ("Qualification", e.g. _Diploma in
  Software Engineering_), `field_of_study` ("Specialization"), `grade`
  (with hint examples).
- Section _Institution_: `institution`, `institution_url`, `country_code`,
  `city` · aside: institution `logo` upload.
- Section _Period_: `start_date`, `is_current` toggle ("I'm currently
  studying here"), `end_date` (hidden when current).
- Section _Details_: `description`, `achievements` (Repeater simple,
  reorderable), `certificate` upload (image/PDF, optional).

**Table**: logo, degree + institution (description), field, grade badge,
period ("2020 — Present"), visibility. Default sort `start_date desc`.

## Companies & clients (`CompanyResource`) — owner priority

**Form**: Section _Brand_: `name`, `slug` (auto from name, editable),
`kind` ToggleButtons (Employer / Client), `website_url` (url, prefix icon
link, suffix action "open"), `industry` · aside: `logo` (svg/png/webp, image
editor off for SVG), `logo_dark` (optional), `wordmark_style` Select with
helper "Used when no logo is uploaded", `is_featured`, visibility.
Section _Engagement_: `engagement` Textarea, `period_label` (placeholder shows
derived period), `city`, `country_code`.

**Table** (grid layout via `->contentGrid(['md' => 2, 'xl' => 4])` card view
with logo, name, kind badge, website link), reorderable, filters: kind,
featured. Relation managers: _Experiences_, _Projects_, _Testimonials_.

## Projects (`ProjectResource`)

Form uses **Tabs**:

| Tab             | Content                                                                                                                                                                                                                                                                                                                                        |
| --------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Basics          | title (live → slug), slug, tagline, summary, year, status (badge select), category, is_featured, version, company (select + create), experience (select filtered by company), role, team, timeline, skills (stack, multi-select) · aside: `cover` upload (16:9 editor) + `cover_alt`, publish section (is_published, published_at), ViewOnSite |
| Story           | overview (Repeater simple Textarea), problem (Repeater simple), approach (Repeater title+description, collapsible), features (same), challenges (same)                                                                                                                                                                                         |
| Architecture    | caption, columns, rows; nodes Repeater (id, label, detail, kind select, column, row; grid 3); edges Repeater (from/to Selects populated from node ids via `Get`, label). Optional live preview (custom view field rendering the SVG diagram).                                                                                                  |
| Metrics & links | metrics Repeater (value, label, detail; grid 3); links Repeater (label, url, kind)                                                                                                                                                                                                                                                             |
| Gallery         | relationship Repeater `galleryItems` (image upload, alt, caption), reorderable, grid 2                                                                                                                                                                                                                                                         |
| SEO             | meta_title (char counter 60), meta_description (counter 160), SERP preview (custom view)                                                                                                                                                                                                                                                       |

**Table**: cover thumb, title + tagline, category badge, status badge,
featured icon toggle, year, stack (first 3 badges), published icon.
Filters: status, category, featured, company, trashed. Reorderable.
Actions: view on site, replicate, delete/restore.

## Articles (`ArticleResource`)

- Main: title → slug, excerpt, **body = `RichEditor`** (TipTap, stored as
  JSON; since P11-01). Pasting from a web page, Google Docs or Word keeps
  headings, lists, quotes, code, links, bold and italic. Toolbar: bold,
  italic, inline code, link · H2, H3 · quote, code block, lists · image,
  blocks · undo/redo. Custom blocks: **Callout** and **Code with file name**
  (`App\Filament\RichContent`). Images (with alt text) can be added once the
  article is saved. **Import Markdown** (hint action): paste Markdown, add it
  to the end or replace the body; raw HTML is stripped.
- Aside: status ToggleButtons (Draft / Published), published_at, tags
  (TagsInput with suggestions from existing tags), related projects
  (multi-select), cover upload + alt, reading time (computed placeholder),
  SEO section.
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
- **Services** (simple, modals, reorderable): title, icon (`ServiceIcon`
  select), summary, highlights (TagsInput). The list page's "Section heading"
  action edits `site_settings.services_*` (blank → template default copy).

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

## Profile · CV (custom page `CvPage`)

An ATS-friendly CV generated from the rest of the panel (profile, visible
experience, education, skills, current certifications, featured projects).
Choose **Classic**, **Modern** or **Compact**, A4 or US Letter, whether to
include projects and certifications, and how many recent roles; a live
preview shows the result. **Generate PDF** downloads it (dompdf, real text);
**Use as my resume** stores it as the profile's `resume` file behind the
site's "Download CV" link. Also `php artisan cv:generate`.

## Site · Appearance (custom page `Appearance`)

- Card grid (one card per template in the `TemplateRegistry`): screenshot
  (`public/templates/<id>.webp`), name, short description, fonts, "Active"
  badge on the current one.
- Card actions: **Activate** (confirmation modal → updates
  `site_settings.active_template`, flushes cache, success notification) and
  **Preview** (opens `/?template=<id>` in a new tab; preview works only for
  the logged-in admin — visitors always get the active template).
- Two sections: **Built-in templates** (code) and **Studio templates**
  (database, rendered by the studio engine; [12](12-ai-templates.md)).
- Header actions: **Generate with AI** (name, prompt, up to 3 reference
  images, start from; queued job with live progress), **New studio
  template** (from an example spec) and **Import** (a `.studio.json` file
  or pasted spec; [sharing guide](templates/sharing-studio-templates.md)).
- Studio card actions: Activate, Preview, **Refine** (ask the AI for
  changes), **Edit** (JSON spec in a slide-over; each save is a new
  version), **Versions** (page with preview/activate per version),
  **Export**, **Refresh screenshot** (when screenshots are enabled),
  Duplicate, Delete (disabled while active), **Retry** on a failed AI
  generation. Cards without a screenshot show a colour/type swatch. The section polls every 3 s
  only while a generation is queued or running.

## Site · SEO & settings (singleton page `SiteSettings`)

Tabs: _General_ (site_name, title_separator, enabled_pages toggles,
contact_recipient) · _SEO_ (meta_description, default_og_image, twitter_handle,
indexable toggle with danger description, verification codes) ·
_Advanced_ (analytics_snippet, favicon). Header action: "Rebuild caches"
(flush content caches + regenerate sitemap). Saving generates the raster favicons
(`favicon.ico`, 192px PNG, apple-touch-icon) from an uploaded favicon; if an SVG cannot be
rasterised (no Imagick SVG support or Chrome), a warning says so. `php artisan site:favicons
[--force]` does the same from the CLI.

## Site · AI (singleton page `AiSettingsPage`)

Settings for the AI template builder ([12 §7](12-ai-templates.md#7-ai-settings-p9)),
resolved by `App\Support\Studio\AiSettings` (panel → `.env` → disabled).
A status line says whether AI is ready and why not. Fields: Enable AI
features · provider (Select from `config('studio.ai.providers')`) · model
(placeholder shows the SDK's default) · API key (password field, never
filled back; "Remove the saved key" toggle) · base URL (Ollama and
OpenAI-compatible only) · generations per day. Header action **Test
connection** prompts the provider with the form's unsaved values and
reports the model and latency; errors never show the key.

## Site · Claude connector (custom page `ClaudeConnector`)

The MCP server for Claude ([13](13-mcp-connector.md)). Warnings first
(connector off, Passport keys missing, no HTTPS in production), then the
server URL and setup steps for claude.ai / Claude Desktop and Claude Code.
Header action **Create token** makes a personal access token with the
`mcp:use` scope and shows it once, with a ready-made `claude mcp add`
command. An embedded table lists the user's working tokens (OAuth apps with
the host they return to, and personal tokens) with **Revoke**, which also
revokes the refresh token.

## Dashboard widgets

1. `StatsOverviewWidget`: projects (published/total), articles, unread
   messages, books read this year — each with sparkline/description.
2. `LatestMessagesWidget` (table, 5 rows).
3. `ContentHealthWidget`: checklist of missing things (no portrait, projects
   without cover/alt, missing meta descriptions, no active socials) with links.
4. `QuickLinksWidget`: View site, Edit profile, New project, New article.

## Authorization

Single owner. `User::canAccessPanel()` returns true only for emails in
`config('portfolio.admin_emails')` (env `ADMIN_EMAILS`) in production. The
same rule (`User::isOwner()`) guards the Claude connector's consent screen
and every MCP request.
