# 10 · Deployment & launch

How to run the portfolio in production (MySQL 8, SSR, queue worker). The
examples assume a single Ubuntu server with Nginx + PHP-FPM, deployed to
`/var/www/portfolio`. Adjust paths for Forge, Ploi or similar tools; the steps
stay the same.

## 1. Server requirements

| Component     | Version / notes                                                                                                                                              |
| ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| PHP           | **8.4+** (Symfony 8 needs ≥ 8.4.1) with `bcmath`, `ctype`, `curl`, `exif`, `fileinfo`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip` |
| Image library | `gd` **built with WebP** (`php -r "var_dump(gd_info()['WebP Support']);"`) or `imagick` with WebP                                                            |
| OPcache       | Enabled (`opcache.enable=1`, `opcache.validate_timestamps=0` in production; reload PHP-FPM on deploy)                                                        |
| Database      | MySQL 8.0+ (or MariaDB 10.11+), `utf8mb4`                                                                                                                    |
| Node.js       | 22 LTS: needed to **build** assets and to **run the SSR server**                                                                                             |
| Web server    | Nginx (or Caddy) with gzip or brotli                                                                                                                         |
| Process mgr   | Supervisor (or systemd) for the queue worker and the SSR server                                                                                              |
| Optional      | Image optimizers used by Spatie: `jpegoptim`, `optipng`, `pngquant`, `gifsicle`, `webp` (`cwebp`), `svgo`                                                    |

## 2. Environment (`.env`)

Start from `.env.example`. Production values that matter:

```dotenv
APP_NAME="Your Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com          # canonical URLs, sitemap, OG images use this

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=portfolio
DB_USERNAME=portfolio
DB_PASSWORD=secret

SESSION_DRIVER=database
CACHE_STORE=database                 # or redis
QUEUE_CONNECTION=database            # or redis

MAIL_MAILER=smtp                     # contact-form notifications are emailed
MAIL_HOST=…
MAIL_FROM_ADDRESS="hello@example.com"

ADMIN_NAME="Your Name"
ADMIN_EMAIL=you@example.com          # first admin (AdminUserSeeder)
ADMIN_PASSWORD=change-me             # only used when the user is first created
ADMIN_EMAILS="${ADMIN_EMAIL}"        # comma-separated allow-list for /admin in production

MEDIA_DISK=public                    # or s3 (set AWS_* too)
PORTFOLIO_CACHE_TTL=86400            # seconds; content cache is flushed on every change anyway
```

Notes:

- In production only emails in `ADMIN_EMAILS` can open `/admin`
  (`User::canAccessPanel`).
- Contact messages are always stored in the panel (Inbox). They are emailed
  to **Site settings → Contact recipient**, or the profile email, or
  `ADMIN_EMAIL`. The email is queued, so the queue worker must run.
- If a proxy or CDN terminates TLS, configure trusted proxies so generated
  URLs use `https`.

## 3. First deploy

```bash
git clone <repo> /var/www/portfolio && cd /var/www/portfolio
cp .env.example .env            # then edit it (section 2)

composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force     # admin user + default settings
# Optional: php artisan db:seed --class=DemoContentSeeder --force   (demo portfolio to explore the panel)

php artisan storage:link        # serves the "public" media disk at /storage

npm ci
npm run build:ssr               # client bundle (public/build) + SSR bundle (bootstrap/ssr)

php artisan optimize            # config, routes, events, views, template manifests, Filament components/icons
php artisan filament:optimize
```

The web server's document root is `/var/www/portfolio/public`. The PHP-FPM
user needs write access to `storage/` and `bootstrap/cache/`.

## 4. Every later deploy

```bash
php artisan down --render="errors::503" || true
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build:ssr
php artisan optimize && php artisan filament:optimize
php artisan inertia:stop-ssr     # Supervisor restarts it with the new bundle
php artisan queue:restart
sudo systemctl reload php8.4-fpm # clear OPcache
php artisan up
```

## 5. Long-running processes (Supervisor)

`/etc/supervisor/conf.d/portfolio.conf`:

```ini
[program:portfolio-ssr]
command=php /var/www/portfolio/artisan inertia:start-ssr
directory=/var/www/portfolio
user=www-data
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
redirect_stderr=true
stdout_logfile=/var/www/portfolio/storage/logs/ssr.log

[program:portfolio-queue]
command=php /var/www/portfolio/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/var/www/portfolio
user=www-data
numprocs=1
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
redirect_stderr=true
stdout_logfile=/var/www/portfolio/storage/logs/queue.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl status
php artisan inertia:check-ssr    # health check for the SSR server
```

- **SSR** listens on `127.0.0.1:13714` (`config/inertia.php`). If it is down,
  pages still work but are rendered client-side, which is bad for SEO.
  Watch `storage/logs/ssr.log`.
- **Queue**: image conversions (thumb/WebP/OG, queued by default) and the
  contact-form email. Without a worker, new uploads show no responsive
  variants and no email is sent.
- **Scheduler**: the app has no scheduled tasks today. Add the standard cron
  (`* * * * * php /var/www/portfolio/artisan schedule:run`) only if you add some.

## 6. Nginx

```nginx
server {
    listen 443 ssl http2;
    server_name example.com;
    root /var/www/portfolio/public;
    index index.php;

    # ssl_certificate … (Let's Encrypt / certbot)

    gzip on;
    gzip_types text/css application/javascript application/json image/svg+xml application/xml application/rss+xml text/plain;
    # brotli on; brotli_types …   (if the module is installed)

    # Hashed Vite assets and media never change: cache them for a year.
    location ^~ /build/ {
        add_header Cache-Control "public, max-age=31536000, immutable";
        try_files $uri =404;
    }
    location ^~ /storage/ {
        add_header Cache-Control "public, max-age=31536000, immutable";   # URLs carry ?v=<updated_at>
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    client_max_body_size 20m;   # panel uploads
}
```

Laravel itself serves `robots.txt`, `sitemap.xml` and `rss.xml`, so there must
be no static `public/robots.txt`.

## 7. Backups

Back up two things:

1. **Database**: a nightly `mysqldump --single-transaction portfolio | gzip`
   to off-site storage (or `spatie/laravel-backup` if you prefer a package).
2. **Media disk**: `storage/app/public/` (or the S3 bucket, with versioning
   on). This holds every uploaded image and PDF; conversions can be rebuilt
   with `php artisan media-library:regenerate`.

`.env` is not in git, so keep a copy in your password manager.

## 8. Launch checklist

Before going live:

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, correct `APP_URL` (https).
- [ ] Log in to `/admin`, change the admin password, and check that only
      `ADMIN_EMAILS` users can open the panel.
- [ ] **Profile & bio**: name, role, headline, summary, story, portrait
      (+ alt text), email, location, availability.
- [ ] **Site settings**: site name, meta description, default OG image
      (1200×630), contact recipient, Twitter handle, verification codes,
      analytics snippet; turn **Allow search engines** on.
- [ ] **Appearance**: preview each template (`?template=…`, visible only to
      you), then activate one.
- [ ] Dashboard **Content health** shows every item as green.
- [ ] Send a test message through the contact form. It should appear in
      the Inbox and arrive by email, which confirms the queue worker and mail work.
- [ ] Upload an image and check that thumb/WebP/OG conversions appear
      (queue worker + WebP support).

After going live (SEO checklist from [07 § 6](07-seo.md#6-verification-checklist-per-release)):

- [ ] `curl -s https://example.com/ | grep -E '<title>|og:title|application/ld\+json'`
      returns content, which confirms SSR is running.
- [ ] Google Rich Results Test passes for home (Person), a case study and an
      article (BlogPosting, BreadcrumbList).
- [ ] OG previews look right (opengraph.xyz, LinkedIn Post Inspector).
- [ ] `https://example.com/sitemap.xml` and `/robots.txt` are correct; submit
      the sitemap in Google Search Console and Bing Webmaster Tools.
- [ ] `https://example.com/rss.xml` validates (validator.w3.org/feed).
- [ ] Lighthouse mobile on Home, a case study and an article for the active
      template (targets: Perf ≥ 95, SEO 100, A11y ≥ 95, Best practices 100).
      Record the scores in `docs/tasks/phase-4-seo-performance.md`.
- [ ] Preview URLs (`?template=`) are `noindex` and canonical URLs have no
      query string.

## 9. Troubleshooting

| Symptom                                                                                                                                        | Cause                                                                                                                                                | Fix                                                                                                                                                                                 |
| ---------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Uploaded images return **404** at `/storage/…` (it was a Laravel "403 Forbidden" page before the private disk's `serve` option was turned off) | The `public/storage` link is missing, so the web server passes the request on to Laravel. `MEDIA_DISK=public` is correct; the link is what's missing | `php artisan storage:link` (on zero-downtime setups, run it for every release, or link `storage/` as a shared directory). Check with `ls -l public/storage`                         |
| Images uploaded from the panel **before the upload-disk fix** return 403/404 and the file is not in `storage/app/public/<id>/`                 | Older builds saved panel uploads on `FILESYSTEM_DISK` (`local` = private) instead of `MEDIA_DISK`                                                    | Deploy the fix, then upload those images again (or move `storage/app/private/<id>` to `storage/app/public/<id>` and set `disk`/`conversions_disk` to `public` in the `media` table) |
| Images upload but the responsive/WebP/OG versions never appear                                                                                 | The queue worker isn't running                                                                                                                       | Start the `portfolio-queue` Supervisor program, then run `php artisan media-library:regenerate`                                                                                     |
| Pages render but `view-source` shows almost no content                                                                                         | The SSR server is down                                                                                                                               | `php artisan inertia:check-ssr`; restart the `portfolio-ssr` program                                                                                                                |
| A panel change doesn't show on the site                                                                                                        | Stale config or content cache                                                                                                                        | **Site → SEO & settings → Rebuild caches**; after `.env` changes run `php artisan optimize`                                                                                         |
