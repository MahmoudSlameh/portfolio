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
    ],

];
