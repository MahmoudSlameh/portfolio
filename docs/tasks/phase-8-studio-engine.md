# Phase 8 — Studio engine (spec-driven templates)

Goal: a template can be stored in the database as a **Template Spec** (JSON +
scoped CSS) and rendered by one React engine with SSR. No AI yet — specs are
written by hand or seeded. Design: [12 §§ 1–4, 6](../12-ai-templates.md).
Decision: D25.

---

## P8-01 · Spec schema `studio/v1` + validator — `todo`

- [ ] `resources/studio/schema/v1.json` and `App\Support\Studio\SpecValidator`
      (sections, variants, props, copy keys and lengths, fonts allow-list,
      colour formats); precise error messages (path + reason).
- [ ] TS types `resources/js/templates/studio/spec.ts` kept in sync (test
      compares the schema's enums with the section catalogue).
- [ ] `config/studio.php`: fonts allow-list, limits.

**Acceptance**: unit tests with valid and invalid fixtures.

## P8-02 · CSS sanitiser — `todo`

- [ ] `App\Support\Studio\CssSanitizer` per 12 §6 (allow-list, scope
      prefixing, 40 KB cap).

**Acceptance**: unit tests with malicious fixtures (`@import`, `url(http…)`,
`</style>`, unscoped selectors, `expression()`).

## P8-03 · Models, migrations, enums — `todo`

- [ ] `StudioTemplate`, `StudioTemplateVersion` (+ factories, morph map
      aliases), enums `StudioSource`, `StudioStatus`, media collections
      `reference` and `screenshot`.
- [ ] Registry (P7-01) lists ready studio templates as `studio:<ulid>`.

## P8-04 · `studio` React engine — `todo`

- [ ] `resources/js/templates/studio/` layout + 9 pages rendering the spec;
      `resources/js/pages/studio/*.tsx` wrappers.
- [ ] Tokens → CSS custom properties emitted server-side in `app.blade.php`
      (light + dark, no flash); scoped CSS injected after base styles.
- [ ] Initial section library and variants from 12 §3 with documented class
      hooks (`.st-*`), built on the kit.
- [ ] `TemplateManager::page()` returns `studio/<Page>` for studio templates;
      shared `studio` prop carries the active version's spec.

**Acceptance**: a seeded fixture spec renders every public page with SSR;
the template test matrix includes it.

## P8-05 · Appearance: studio templates without AI — `todo`

- [ ] Studio cards on Appearance (preview, activate, duplicate, delete),
      "New blank studio template" and a JSON spec editor with validation
      errors shown inline.

---

## Notes
