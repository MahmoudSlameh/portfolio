<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Panel Access
    |--------------------------------------------------------------------------
    |
    | Emails allowed into the Filament panel in production. Any authenticated
    | user may access the panel in non-production environments.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'admin_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_EMAILS', (string) env('ADMIN_EMAIL', ''))),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Content
    |--------------------------------------------------------------------------
    |
    | Reading speed used to estimate an article's reading time.
    |
    */

    'reading_wpm' => 220,

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Lifetime (seconds) of cached site-wide content such as the profile,
    | socials and the search index. Model observers flush it on change.
    |
    */

    'cache_ttl' => (int) env('PORTFOLIO_CACHE_TTL', 60 * 60 * 24),

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    |
    | Code templates are discovered from `<path>/<id>/template.json` (see
    | docs/11-template-kit.md). The default is used on a fresh install and
    | whenever the active template no longer exists.
    |
    */

    'templates' => [
        'path' => resource_path('js/templates'),
        'default' => 'changelog',

        // Used by `php artisan make:template` (see docs/11-template-kit.md).
        'pages_path' => resource_path('js/pages'),
        'stylesheet' => resource_path('js/styles/main.css'),
        'screenshots_path' => public_path('templates'),

        // Developer gallery at /dev/templates (every page of every template side by side) and the
        // stateless `?_template=<id>` override it relies on. On in the local environment only.
        'dev_gallery' => (bool) env('TEMPLATE_GALLERY', env('APP_ENV') === 'local'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Claude connector (MCP)
    |--------------------------------------------------------------------------
    |
    | The MCP server at `/mcp` lets Claude (claude.ai, Claude Desktop, Claude
    | Code) read and edit the portfolio's content (see docs/13-mcp-connector.md).
    | Images given by URL are downloaded by the server: only public http(s)
    | addresses are allowed, unless `allow_private_urls` is on (local dev).
    |
    */

    'mcp' => [
        'enabled' => (bool) env('MCP_ENABLED', true),
        'rate_limit' => (int) env('MCP_RATE_LIMIT', 120), // requests per minute and user
        'token_days' => (int) env('MCP_TOKEN_DAYS', 365), // lifetime of personal access tokens
        'uploads' => [
            'minutes' => (int) env('MCP_UPLOAD_MINUTES', 15), // lifetime of a request_image_upload URL
        ],
        'images' => [
            'max_kilobytes' => 10 * 1024,
            'timeout' => 20, // seconds to download one image
            'allow_private_urls' => (bool) env('MCP_ALLOW_PRIVATE_URLS', false),
        ],
    ],

];
