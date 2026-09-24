# Phase 2 — Admin panel (Filament 5)

Docs: [04-admin-panel](../04-admin-panel.md) is the spec for every screen;
[05-media](../05-media.md) for uploads.

For every resource/page: follow the layout spec in 04, use enum badges,
add a Pest Livewire test (render list, create, edit, validation failure,
delete; media upload where relevant). Check exact Filament 5 syntax with
Boost `search-docs`.

---

## P2-01 · Panel configuration & shared building blocks — `done`

- [x] Configure `AdminPanelProvider` as in 04 (colors, font, SPA, groups,
      global search, DB notifications, unsaved-changes alerts, view-site menu
      item). Remove `FilamentInfoWidget`.
- [x] `notifications` table migration (`php artisan make:notifications-table`).
- [x] `User implements FilamentUser` with `canAccessPanel()` per 04.
- [x] Shared components in `app/Filament/Support`: `CountrySelect`,
      `MonthPicker`, `VisibilityAside`, `PeriodColumn`, `ViewOnSiteAction`,
      `ToggleVisibilityBulkAction`.
- [x] (Optional) custom theme.

**Acceptance**: panel loads with empty groups hidden; dark mode works; ⌘K
opens global search.

---

## P2-02 · Profile & Bio page — `done`

- [x] `app/Filament/Pages/EditProfile.php` singleton form with the 5 tabs.
- [x] Portrait (3:4 editor) + alt required, resume PDF.
- [x] Save notification; cache flushed.

**Acceptance**: every Profile column editable; portrait conversions created;
test covers save + upload.

---

## P2-03 · Work experience resource — `done` ⭐ owner priority

- [x] Form per 04 (company select with create/edit option, organization
      fallback, job title, employment type, **work mode toggle buttons**,
      **country / city / optional address**, **start month + "currently work
      here" toggle + end month**, summary, **achievements repeater**, stack,
      changelog extras with derived placeholders).
- [x] Table, filters, default sort, projects relation manager.
- [x] Validation: end ≥ start; organization required without company.

**Acceptance**: can create "Remote / Germany / no address / current" and
"On-site / Syria / address / ended" experiences; table shows "Present" badge.

---

## P2-04 · Education resource — `todo` ⭐ owner priority

- [ ] Form per 04: qualification name, specialization, **grade**,
      institution (+ url, logo, location), **start + "currently studying" +
      optional end**, description, **achievements**, certificate upload.
- [ ] Table + default sort.

**Acceptance**: current study shows "Present"; grade visible in table.

---

## P2-05 · Companies & clients resource — `done` ⭐ owner priority

- [x] Form: **name, logo (svg/png/webp), website link**, kind, industry,
      location, engagement, period label, wordmark fallback, featured.
- [x] Card-grid table, reorderable; relation managers.
- [x] `CompanyForm::quick()` reused by Experience/Project/Testimonial selects.

**Acceptance**: logo shows in the table and in the experience select option
labels (`->getOptionLabelFromRecordUsing` / `allowHtml`).

---

## P2-06 · Certifications & testimonials — `todo`

Simple (modal) resources per 04. **Acceptance**: CRUD + reorder + uploads.

---

## P2-07 · Skills & categories — `todo`

Per 04 (grouped table, inline proficiency, reorder within group).
**Acceptance**: reordering persists; skills without category flagged
"stack only".

---

## P2-08 · Projects resource — `todo`

- [ ] Tabs: Basics, Story, Architecture, Metrics & links, Gallery
      (relationship repeater with per-image alt/caption), SEO.
- [ ] Edge selects populated from node ids (`Get`).
- [ ] Soft deletes + restore; view-on-site action.

**Acceptance**: a project equal to the reference `ledgerline` can be fully
entered through the UI.

---

## P2-09 · Articles resource — `todo`

- [ ] Builder with all 7 block types (04), body images collection + image
      block select.
- [ ] Draft/Published tabs with counts; reading time placeholder.

**Acceptance**: an article equal to `ledgers-are-just-logs` can be entered;
drafts are not public (verified in P3).

---

## P2-10 · Books, Uses, Socials, Now page — `todo`

Per 04 (book cover generator preview, uses groups with items repeater,
socials simple resource, `EditNowPage` singleton).
**Acceptance**: all CRUD works; Now page reading books defaults to status
"reading".

---

## P2-11 · Inbox (contact messages) — `todo`

- [ ] Read-only resource, unread tab/badge, view infolist, mark read/unread,
      reply (mailto) action.
- [ ] `NewContactMessage` notification (database + mail, queued).

**Acceptance**: creating a `ContactMessage` notifies admins; badge count
updates.

---

## P2-12 · Appearance & Site settings — `todo`

- [ ] `Appearance` page: template cards (screenshots in
      `public/templates/<id>.webp` — take them from the reference app),
      Activate (confirm) + Preview actions (admin-only preview).
- [ ] `SiteSettings` page: General / SEO / Advanced tabs; "Rebuild caches"
      action.

**Acceptance**: activating a template changes what `/` renders (after P3);
test asserts `active_template` updated + cache flushed.

---

## P2-13 · Dashboard widgets — `todo`

Stats overview, latest messages, content health checklist, quick links (04).
**Acceptance**: widgets render with empty and seeded databases.

---

## Notes

- 2026-09-24 — P2-01: Panel: Indigo/Zinc, Inter, SPA, collapsible sidebar, full width, ⌘K search, DB notifications (30s poll), unsaved-changes alerts, DB transactions, 6 navigation groups, 'View site' user-menu item, password reset + full profile page. Custom theme resources/css/filament/admin/theme.css (in vite inputs) so custom Blade views can use Tailwind. Shared builders: App\Filament\Support\Fields (country, month, currentToggle, slug/slugSource, visibilitySection) and Columns (period, visibility, duration). User::canAccessPanel uses config('portfolio.admin_emails') in production.
- 2026-09-24 — P2-02: App\Filament\Support\SingletonPage (abstract: fill from record, sticky save bar ⌘S, saveRelationships for Spatie uploads, DB transaction) + Pages\EditProfile (slug /admin/bio, 5 tabs). Tests: tests/Feature/Filament/EditProfilePageTest.php — use Repeater::fake() + internal item shape for simple repeaters in fillForm; actingAsAdmin() helper in tests/Pest.php.
- 2026-09-24 — P2-05: CompanyResource: 2/3 + 1/3 form (brand, engagement, logo/logo_dark/wordmark, featured/visible), card-grid table (contentGrid, reorderable, kind/featured/visible filters, visit-website action). CompanyForm::quick() for create/edit-option forms. Local InitialsAvatar SVG for missing logos (no external avatar service). Relation managers (experiences/projects/testimonials) are added in P2-08 via relatedResource().
- 2026-09-24 — P2-03: ExperienceForm: company select with logos (allowHtml) + create/edit option (CompanyForm::quick), organization fallback, job title, type, work-mode toggle buttons, country/city/optional address, month start/end + 'I currently work here' toggle, summary, achievements repeater, ordered stack (Fields::stack + SyncsStack, HasStack interface), collapsed changelog overrides with derived placeholders. Table: logo, title/org, type & mode badges, flag+country, period with Present badge, filters (type, mode, company, current), replicate. Projects relation manager comes with P2-08.
