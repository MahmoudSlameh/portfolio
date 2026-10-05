# 13 · Claude connector (MCP server)

> Status: **done** (P12, 2026-10-04). Tasks: [phase-12-claude-connector.md](tasks/phase-12-claude-connector.md).

The portfolio exposes a [Model Context Protocol](https://modelcontextprotocol.io)
server at **`/mcp`**, built with the official
[`laravel/mcp`](https://laravel.com/docs/mcp) package. Added as a custom
connector in Claude (claude.ai, Claude Desktop, Claude Code), it lets the
owner write things like _"Add github.com/me/ledger to my portfolio"_: Claude
reads the repository (README, code, commits, releases), then creates the
project case study, picks the stack, takes screenshots and uploads images
through the server's tools. It works on the same data as the Filament panel.

## 1. Overview

```
Claude ──HTTPS──▶ /mcp  (POST, JSON-RPC, Streamable HTTP)
   │                ├─ auth:api            Passport: OAuth access token or personal access token
   │                ├─ CheckToken mcp:use  token must carry the MCP scope
   │                ├─ EnsureMcpAccess     MCP_ENABLED and the user is the owner (User::isOwner)
   │                └─ throttle:mcp        MCP_RATE_LIMIT requests/minute per user
   │
   ├─ /.well-known/oauth-protected-resource/mcp   RFC 9728 (which server issues tokens)
   ├─ /.well-known/oauth-authorization-server     RFC 8414 (endpoints, PKCE S256)
   ├─ /oauth/register                             RFC 7591 dynamic client registration
   ├─ /oauth/authorize                            consent screen (panel login first)
   └─ /oauth/token                                code + PKCE → access & refresh token
```

| Piece                             | Where                                                                                                                 |
| --------------------------------- | --------------------------------------------------------------------------------------------------------------------- |
| Routes                            | `routes/ai.php` (loaded by `laravel/mcp`)                                                                             |
| Server, instructions, tool list   | `app/Mcp/Servers/PortfolioServer.php`                                                                                 |
| Tools                             | `app/Mcp/Tools/{Profile,Projects,Skills,Companies,Experiences}`                                                       |
| Shared field schemas / validation | `app/Mcp/Support/{ProjectFields,CompanyFields,ExperienceFields}.php`                                                  |
| JSON returned to Claude           | `app/Mcp/Support/Payload.php`                                                                                         |
| Images (URL, base64, screenshot)  | `app/Mcp/Support/ImageInput.php`, `IncomingImage.php`, `app/Support/Media/{RemoteImage,PublicUrl,PageScreenshot}.php` |
| Prompt and resource               | `app/Mcp/Prompts/AddProjectFromGithubPrompt.php`, `app/Mcp/Resources/ContentGuideResource.php`                        |
| Consent screen                    | `resources/views/mcp/authorize.blade.php` (registered in `AppServiceProvider::configurePassport`)                     |
| Panel page                        | `app/Filament/Pages/ClaudeConnector.php` (**Site → Claude connector**)                                                |
| Tokens                            | `app/Mcp/Support/ConnectorTokens.php`                                                                                 |
| Config                            | `config/portfolio.php` → `mcp`, `config/mcp.php`, `config/auth.php` (`api` guard)                                     |

## 2. Tools

All tools validate their input with Laravel rules; a failure comes back as a
tool error Claude can read and fix. Records are referred to by **id or slug**.
Annotations (`readOnlyHint`, `destructiveHint`, `idempotentHint`,
`openWorldHint`) let clients ask for confirmation where it matters.

| Group      | Tool                                                                              | What it does                                                                                                |
| ---------- | --------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| Overview   | `get_portfolio_overview`                                                          | Owner, URLs, counts, latest projects, capabilities (screenshots, image size limit). Claude's starting point |
| Profile    | `get_profile`, `update_profile`                                                   | Bio, headline, story, focus areas, availability, stats, principles                                          |
| Projects   | `list_projects`                                                                   | Filters: search, status, category, published, include_deleted; paginated                                    |
|            | `get_project`                                                                     | Every field, gallery image ids, site and admin URLs                                                         |
|            | `create_project`                                                                  | Only `title` is required; **drafts by default**                                                             |
|            | `update_project`                                                                  | Partial update; list fields replace the stored list                                                         |
|            | `delete_project`, `restore_project`                                               | Soft delete (trash) and restore                                                                             |
|            | `set_project_cover`                                                               | 16:9 cover + alt text                                                                                       |
|            | `add_project_image`, `update_project_image`, `remove_project_image`               | Case-study gallery                                                                                          |
| Skills     | `list_skills`                                                                     | Categories + skills with usage counts                                                                       |
|            | `save_skill`, `save_skill_category`                                               | Create or update; matched by id or name (case-insensitive)                                                  |
|            | `delete_skill`, `delete_skill_category`                                           | Deleting a category keeps its skills, uncategorised                                                         |
| Companies  | `list_companies`, `create_company`, `update_company`, `delete_company`            | Employers and clients                                                                                       |
|            | `set_company_logo`                                                                | Light or dark logo, PNG/WebP only                                                                           |
| Uploads    | `request_image_upload`                                                            | Single-use URL to PUT a local image file to (cover, gallery or company logo); see §3                        |
| Experience | `list_experiences`, `create_experience`, `update_experience`, `delete_experience` | Month dates `YYYY-MM`; `end_date` null = current role                                                       |

**Stacks.** Projects and experiences take `stack` as a list of skill names in
order. Existing skills are reused (matched by name, case-insensitively, never
by slug: "C", "C#" and "C++" share one); missing ones are created without a
category, exactly like a stack tag added in the panel. The tool result lists
`created_skills`.

**Prompt `add_project_from_github`** (argument `repository`, optional
`notes`) gives Claude the whole workflow: research the repository, check for
duplicates, reuse skills/companies, create the project, add a cover and
gallery, keep it a draft, report back with the admin link.

**Resource `portfolio://guides/content`** says where each field appears on
the site, recommended lengths, an architecture example and the writing style.

The server **instructions** (sent on `initialize`) repeat the rules that
matter most: never invent facts or metrics, drafts unless asked, delete only
on request, read before replacing a list, English, owner's voice.

## 3. Images

### Local files: `request_image_upload`

Agents often have the images as local files (screenshots, mockups of 1–5 MB). Base64 makes those
millions of characters inside a tool call, and uploading them to a public file host first is
rightly blocked by agent permission systems. So the server hands out an upload URL on its own
domain:

1. `request_image_upload` with `target` (`cover` / `gallery` / `company_logo`), the project or
   company, `filename`, `mime_type`, `alt` (and `caption` for the gallery, `variant` for logos)
   returns `upload_url`, `method: PUT`, `headers`, `expires_at`, `max_bytes` and a ready
   `curl_example`.
2. The agent runs `curl -X PUT --data-binary @file.png -H "Content-Type: image/png" <upload_url>`
   (a multipart POST with a `file` field works too).
3. `PUT|POST /mcp/uploads/{token}` (`App\Http\Controllers\Mcp\ImageUploadController`) checks the
   ticket, the size (`max_kilobytes`), the real type of the bytes (must match the requested
   `mime_type`) and the dimensions (≤ 10 000 px per side), attaches the image through the same
   code as the tools (`ImageAttacher`: cover, gallery item appended at the end, or logo) and
   answers `201 {ok, target, image, project_admin_url}`. Errors are JSON
   `{ok: false, error}`: 410 expired/used/unknown, 413 too large, 422 wrong type or not an image.

The 64-character random token is the only credential (the agent may only have curl). Tickets
(`UploadTickets`) live in the cache under a SHA-256 of the token, are scoped to one record,
target and type, expire after `MCP_UPLOAD_MINUTES` (15) and are deleted once used; a cache lock
stops two requests using one token at once. The route is limited to 30 requests per minute per
IP and every upload is logged. Nginx must accept the body size (`client_max_body_size`, see
[10 § 6](10-deployment.md#6-nginx)).

### Images in tool calls

Every image tool takes exactly one of:

- `image_url`: the server downloads it (e.g. a `raw.githubusercontent.com`
  screenshot from the repository);
- `image_base64`: the bytes themselves (data URIs accepted), only sensible for tiny images
  (< 200 KB);
- `screenshot_url` + `screenshot_viewport`: the server opens the page in
  headless Chrome and screenshots it: `desktop` 1600×900 (16:9, covers),
  `laptop` 1440×900, `tablet` 1024×768 (4:3, gallery), `mobile` 390×844.
  Uses `STUDIO_SCREENSHOT_CHROME` or a Chrome/Chromium on the PATH
  (`Favicons::chrome()`); `get_portfolio_overview` says whether it is available.

The bytes are type-checked with `finfo` (not the URL or header) and must be a
readable image of an accepted type: JPEG, PNG, WebP or AVIF for projects;
PNG or WebP for logos. **SVGs are refused over MCP** (they can carry scripts
and are served from the site's origin), so SVG logos are uploaded in the panel.
Files then go through Spatie Media Library like panel uploads (conversions,
dimensions, responsive images). The download happens before the database
transaction, so a slow host never holds a transaction open, and a failed
image never leaves an empty gallery item.

**SSRF guard** (`PublicUrl`, `RemoteImage`):

- http(s) only, no credentials in the URL;
- `localhost`, `*.localhost`, `*.local`, `*.internal` and every private,
  loopback, link-local (cloud metadata) or reserved address are refused,
  checked on **every address** the host resolves to;
- redirects are followed by hand (max 3), each hop checked again;
- the connection is pinned to the checked address (`CURLOPT_RESOLVE`), so DNS
  cannot change between the check and the download;
- size limit `portfolio.mcp.images.max_kilobytes` (10 MB), from
  `Content-Length` and the body, timeout 20 s.

`MCP_ALLOW_PRIVATE_URLS=true` lifts the address check for local development
(e.g. screenshotting `http://localhost:8000`). Never set it in production.

## 4. Authentication

Two kinds of bearer token, both Passport tokens with the `mcp:use` scope:

1. **OAuth 2.1** (claude.ai, Claude Desktop, Claude Code `/mcp` →
   Authenticate). `Mcp::oauthRoutes()` publishes the discovery documents and
   dynamic client registration; Passport does the authorization-code flow
   with PKCE (S256). A 401 from `/mcp` carries
   `WWW-Authenticate: Bearer resource_metadata=".../.well-known/oauth-protected-resource/mcp"`
   so clients find everything from the URL alone.
    - **Registration is restricted** to trusted callbacks
      (`MCP_REDIRECT_DOMAINS`, default `https://claude.ai`, `https://claude.com`
      and localhost for Claude Code). Without this, anyone could register a
      client that redirects to their own site and phish the owner into
      approving it.
    - **Consent.** `/oauth/authorize` sends guests to the panel login
      (`redirectGuestsTo` in `bootstrap/app.php`); Filament returns them to
      the consent screen, which names the app, **the host it will return
      to**, what it can do, and warns to approve only connections the owner
      started. Users who are not the owner get a 403.
    - Access tokens last 30 days, refresh tokens a year (Claude refreshes on
      its own).
2. **Personal access tokens** (Claude Code with `--header`, any other MCP
   client). Created on the panel page; shown once; lifetime `MCP_TOKEN_DAYS`
   (365). The personal access client is created on first use.

Every request is checked again on the server side: the token must carry
`mcp:use`, `MCP_ENABLED` must be on, and the user must be the owner
(`User::isOwner()`, the same rule as `canAccessPanel`: anyone outside
production, only `ADMIN_EMAILS` in production). Revoking on the panel page
revokes the access token and its refresh token immediately.

**Local stdio server.** `php artisan mcp:start portfolio` runs the same tools
over stdio without a token, for a local MCP client on the same machine
(whoever can run artisan already has full access).

## 5. Panel: Site → Claude connector

- Setup problems first: connector switched off, Passport keys missing
  (`php artisan passport:keys`), site not on HTTPS in production.
- The server URL, step-by-step instructions for claude.ai / Desktop, and the
  `claude mcp add --transport http portfolio <url>` command for Claude Code.
- **Create token**: shows the token and a ready-to-paste Claude Code command
  once.
- **Connected apps and tokens**: every working token of the user, OAuth app
  (with the host it returns to) or personal token, with **Revoke**.

## 6. Configuration

| Variable                                       | Default                          | Purpose                                                          |
| ---------------------------------------------- | -------------------------------- | ---------------------------------------------------------------- |
| `MCP_ENABLED`                                  | `true`                           | Switch the connector off (404) without removing anything         |
| `MCP_RATE_LIMIT`                               | `120`                            | Requests per minute and user on `/mcp`                           |
| `MCP_TOKEN_DAYS`                               | `365`                            | Lifetime of personal access tokens                               |
| `MCP_REDIRECT_DOMAINS`                         | claude.ai, claude.com, localhost | Callback domains allowed for dynamic client registration         |
| `MCP_CUSTOM_SCHEMES`                           | —                                | Custom URI schemes allowed as callbacks (e.g. `cursor,vscode`)   |
| `MCP_ALLOW_PRIVATE_URLS`                       | `false`                          | Local development only: fetch/screenshot private addresses       |
| `PASSPORT_PRIVATE_KEY` / `PASSPORT_PUBLIC_KEY` | —                                | OAuth keys from the environment instead of `storage/oauth-*.key` |

The OAuth discovery and registration routes share a `mcp-oauth` limit of 30
requests per minute per IP.

## 7. Testing

`tests/Feature/Mcp/`: every tool through `PortfolioServer::tool()`
(validation, partial updates, stacks, soft delete, images by URL / base64 /
screenshot with faked HTTP, DNS and Chrome, the SSRF cases), the HTTP
endpoint (401 + `WWW-Authenticate`, scope, owner-only, switch), the **whole
OAuth flow** (register → login → consent → code + PKCE → token → `/mcp`),
personal tokens and revocation, and the panel page.

To try it by hand: `php artisan mcp:inspector mcp` (web) or
`php artisan mcp:inspector portfolio` (stdio).
