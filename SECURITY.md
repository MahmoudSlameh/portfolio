# Security policy

## Supported versions

Only the latest commit on `main` receives security fixes.

## Reporting a vulnerability

**Please do not open a public issue.** Report it privately with GitHub's
[private vulnerability reporting](https://github.com/MahmoudSlameh/portfolio/security/advisories/new).

Include:

- what is affected (route, panel page, template, dependency);
- steps to reproduce or a proof of concept;
- the impact you expect.

You will get a reply within 7 days. Once a fix is released you will be
credited in the advisory, unless you prefer not to be.

## Scope notes

- The admin panel is meant for a single trusted owner; in production only
  the emails in `ADMIN_EMAILS` can sign in.
- The site settings allow the owner to add an analytics snippet, which is
  printed as raw HTML on purpose. This is not a vulnerability.
