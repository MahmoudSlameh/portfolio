# Phase 6 — Open-source readiness

Goal: the repository is ready to be public. A visitor on GitHub understands
what the project is in 30 seconds, can install it in 5 minutes and knows how
to contribute.

Docs: [README](../README.md) (hard rules), [09](../09-decisions.md) (D29).

---

## P6-01 · Root `README.md` (English) — `done`

- [x] Root `README.md` written for GitHub visitors (the internal docs index
      stays in `docs/README.md`): pitch, template screenshots
      (`public/templates/*.webp`), features, tech stack, requirements,
      quick start, demo content, admin panel, templates, **AI template
      builder** (marked as in development until P9 ships, links to
      [12](../12-ai-templates.md)), building your own template (links to
      [11](../11-template-kit.md)), deployment, testing, roadmap,
      contributing, license.
- [x] Every command in the quick start is copied from files that exist
      (`composer.json` scripts, `docs/10-deployment.md`).

**Acceptance**: a fresh clone can be installed by following the README alone.

## P6-02 · License & community files — `done`

- [x] `LICENSE` (MIT, matching `composer.json`).
- [x] `CONTRIBUTING.md`: setup, branch/commit conventions, `composer ci:check`,
      where docs and tasks live, how to propose a template.
- [x] `CODE_OF_CONDUCT.md` (Contributor Covenant 2.1).
- [x] `SECURITY.md` (report privately through GitHub Security Advisories).
- [x] `.github/ISSUE_TEMPLATE/` (bug report, feature request, template
      proposal, `config.yml`) and `.github/pull_request_template.md`.

## P6-03 · English-only docs & project metadata — `done`

- [x] Remove the Arabic summary from `docs/README.md`; link the root README.
- [x] `composer.json` `name`/`description`/`keywords` describe this project
      (not `laravel/blank-react-starter-kit`).
- [x] Docs map in `docs/README.md` lists 11 and 12.

**Acceptance**: `composer validate` passes; `grep` finds no Arabic in docs
(the demo article about RTL in the seeder data is content and stays).

---

## Notes

- 2026-09-26 — P6-01…P6-03: root README (features, quick start, templates,
  AI template builder marked "in development" with a link to 12, template
  guide, roadmap), MIT LICENSE, CONTRIBUTING, CODE_OF_CONDUCT (Contributor
  Covenant 2.1), SECURITY (GitHub private vulnerability reporting), issue
  forms (bug, feature, template proposal) and PR template. Arabic summary in
  docs/README.md replaced by an English one; composer.json name is now
  `mahmoudslameh/portfolio` (lock content-hash refreshed, no package
  changes). The template screenshots in public/templates still show the
  reference's language toggle; retake them when P7-05 lands.
