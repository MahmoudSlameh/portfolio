<?php

namespace App\Support\Media;

use Closure;

/**
 * Checks that a URL the server is asked to fetch points to the public internet (SSRF guard for images
 * Claude sends by URL): http(s) only, no credentials, and every address the host resolves to must be
 * public unless `portfolio.mcp.images.allow_private_urls` is on (local development).
 */
final class PublicUrl
{
    /**
     * Resolver used in place of DNS (tests).
     *
     * @var (Closure(string): list<string>)|null
     */
    private static ?Closure $resolver = null;

    /**
     * @param  (Closure(string): list<string>)|null  $resolver
     */
    public static function resolveUsing(?Closure $resolver): void
    {
        self::$resolver = $resolver;
    }

    /**
     * The reason the URL may not be fetched, or null when it may.
     */
    public static function problem(string $url): ?string
    {
        $parts = parse_url($url);

        if ($parts === false || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) || blank($parts['host'] ?? null)) {
            return 'Only http and https URLs are supported.';
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return 'URLs with a user name or password are not supported.';
        }

        if (config('portfolio.mcp.images.allow_private_urls')) {
            return null;
        }

        $host = trim(strtolower($parts['host']), '[]');

        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return 'Local and internal addresses are not allowed.';
        }

        $addresses = self::resolve($host);

        if ($addresses === []) {
            return "The host {$host} could not be resolved.";
        }

        foreach ($addresses as $address) {
            if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return 'Local and internal addresses are not allowed.';
            }
        }

        return null;
    }

    /**
     * The IP addresses of a host (the host itself when it is an IP).
     *
     * @return list<string>
     */
    public static function resolve(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return [$host];
        }

        if (self::$resolver !== null) {
            return (self::$resolver)($host);
        }

        $records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];

        return array_values(array_filter(array_map(
            fn (array $record): ?string => $record['ip'] ?? $record['ipv6'] ?? null,
            $records,
        )));
    }
}
