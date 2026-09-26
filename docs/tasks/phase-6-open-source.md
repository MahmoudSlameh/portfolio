# Phase 6 — Open-source readiness

Goal: the repository is ready to be public. A visitor on GitHub understands
what the project is in 30 seconds, can install it in 5 minutes and knows how
to contribute.

Docs: [README](../README.md) (hard rules), [09](../09-decisions.md) (D29).

---

## P6-01 · Root `README.md` (English) — `todo`

- [ ] Root `README.md` written for GitHub visitors (the internal docs index
      stays in `docs/README.md`): pitch, template screenshots
      (`public/templates/*.webp`), features, tech stack, requirements,
      quick start, demo content, admin panel, templates, **AI template
      builder** (marked as in development until P9 ships, links to
      [12](../12-ai-templates.md)), building your own template (links to
      [11](../11-template-kit.md)), deployment, testing, roadmap,
      contributing, license.
- [ ] Every command in the quick start is copied from files that exist
      (`composer.json` scripts, `docs/10-deployment.md`).

**Acceptance**: a fresh clone can be installed by following the README alone.

## P6-02 · License & community files — `todo`

- [ ] `LICENSE` (MIT, matching `composer.json`).
- [ ] `CONTRIBUTING.md`: setup, branch/commit conventions, `composer ci:check`,
      where docs and tasks live, how to propose a template.
- [ ] `CODE_OF_CONDUCT.md` (Contributor Covenant 2.1).
- [ ] `SECURITY.md` (report privately through GitHub Security Advisories).
- [ ] `.github/ISSUE_TEMPLATE/` (bug report, feature request, template
      proposal, `config.yml`) and `.github/pull_request_template.md`.

## P6-03 · English-only docs & project metadata — `todo`

- [ ] Remove the Arabic summary from `docs/README.md`; link the root README.
- [ ] `composer.json` `name`/`description`/`keywords` describe this project
      (not `laravel/blank-react-starter-kit`).
- [ ] Docs map in `docs/README.md` lists 11 and 12.

**Acceptance**: `composer validate` passes; `grep` finds no Arabic in docs
(the demo article about RTL in the seeder data is content and stays).

---

## Notes
