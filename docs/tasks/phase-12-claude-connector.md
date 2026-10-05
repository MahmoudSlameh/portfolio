# Phase 12 — Claude connector (MCP)

Owner request (2026-10-04): give Claude a connector to the portfolio, so a
GitHub repository can be turned into a complete project (text, stack,
screenshots) from a chat, and Claude can read and edit everything else it
needs (skills, companies, experience, profile). Built on the official
`laravel/mcp` package. Design: [13-mcp-connector.md](../13-mcp-connector.md).

---

## P12-01 · OAuth and access control — `done`

- [x] `laravel/mcp` and `laravel/passport` in `require`; Passport migrations;
      `api` guard (`passport` driver); `User` implements `OAuthenticatable`.
- [x] `Mcp::oauthRoutes()`: protected-resource and authorization-server
      metadata, dynamic client registration limited to `MCP_REDIRECT_DOMAINS`,
      `throttle:mcp-oauth`.
- [x] Consent screen `resources/views/mcp/authorize.blade.php` (app name,
      return host, permissions, warning); guests go through the panel login
      and come back; non-owners get 403.
- [x] `/mcp` behind `auth:api` + `CheckToken:mcp:use` + `EnsureMcpAccess`
      (`MCP_ENABLED`, `User::isOwner()`) + `throttle:mcp`.
- [x] Token lifetimes: access 30 days, refresh 1 year, personal
      `MCP_TOKEN_DAYS`.

## P12-02 · Tools — `done`

- [x] `PortfolioServer` with instructions; all tools listed on one page.
- [x] Overview + profile (get/update).
- [x] Projects: list, get, create (draft by default), update (partial), delete
      (trash), restore; stack by skill names (reuse, create missing).
- [x] Skills and categories: list, save (upsert by id/name), delete.
- [x] Companies: list, create, update, delete, logo (light/dark, PNG/WebP).
- [x] Experience: list, create, update, delete (month dates, stack).
- [x] Prompt `add_project_from_github`; resource `portfolio://guides/content`.

## P12-03 · Images — `done`

- [x] One of `image_url`, `image_base64`, `screenshot_url` (+ viewport).
- [x] Type check on the bytes; SVG refused over MCP.
- [x] SSRF guard: public addresses only (every resolved IP), manual
      redirects re-checked, connection pinned to the checked IP, size and
      time limits.
- [x] Headless Chrome screenshots (`PageScreenshot`), availability reported
      by `get_portfolio_overview`.
- [x] Cover, gallery add/edit/remove, company logos.

## P12-04 · Panel page and docs — `done`

- [x] **Site → Claude connector**: setup warnings, server URL, steps for
      claude.ai / Desktop / Claude Code, create a personal token (shown
      once), list and revoke connected apps and tokens.
- [x] Docs: 13-mcp-connector.md, README feature section, deployment
      (`passport:keys`), `.env.example`, decisions D33–D35.
- [x] Tests: `tests/Feature/Mcp/` (tools, images and SSRF, endpoint, full
      OAuth flow, tokens, panel page).

## Notes

- 2026-10-04: done. `composer setup` now creates the Passport keys when they
  are missing. Larastan could not run in the build container (the
  `phpstan/phpstan` download was blocked there); CI runs it.
- 2026-10-05: first production deploy hit a 500 on `/mcp` because the key
  files were `644` (Passport only accepts `600`/`660`-style modes). The
  deployment guide now sets the owner and mode after `passport:keys`, and the
  panel page warns about missing, unreadable or too-open keys (`OAuthKeys`).
