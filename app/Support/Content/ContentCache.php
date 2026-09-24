<?php

namespace App\Support\Content;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Cache for public site content (shared props, sitemap, feeds). Keys are versioned, so a single
 * flush() invalidates everything without cache tags (works with the database/file stores).
 */
final class ContentCache
{
    private const VERSION_KEY = 'content-cache:version';

    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public static function remember(string $key, Closure $callback): mixed
    {
        return Cache::remember(self::key($key), (int) config('portfolio.cache_ttl'), $callback);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function key(string $key): string
    {
        return 'content-cache:'.self::version().':'.$key;
    }

    private static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }
}
