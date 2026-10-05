<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Downloads an image from the public internet for the MCP connector. Every hop of a redirect is checked
 * with {@see PublicUrl}, the connection is pinned to the checked address (no DNS rebinding) and the body
 * is capped at `portfolio.mcp.images.max_kilobytes`.
 */
final class RemoteImage
{
    private const MAX_REDIRECTS = 3;

    /**
     * @return string the downloaded bytes
     *
     * @throws ImageRejected
     */
    public static function download(string $url): string
    {
        $maxBytes = (int) config('portfolio.mcp.images.max_kilobytes') * 1024;

        for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
            if (($problem = PublicUrl::problem($url)) !== null) {
                throw new ImageRejected("Cannot download {$url}: {$problem}");
            }

            try {
                $response = Http::timeout((int) config('portfolio.mcp.images.timeout'))
                    ->withUserAgent('PortfolioConnector/1.0 (+'.config('app.url').')')
                    ->accept('image/*')
                    ->withOptions([
                        'allow_redirects' => false,
                        'curl' => self::pin($url),
                        'on_headers' => function (ResponseInterface $response) use ($maxBytes): void {
                            if ((int) $response->getHeaderLine('Content-Length') > $maxBytes) {
                                throw new ImageRejected('The image is larger than '.($maxBytes / 1024 / 1024).' MB.');
                            }
                        },
                    ])
                    ->get($url);
            } catch (Throwable $exception) {
                // Guzzle wraps the size check thrown from `on_headers`.
                for ($cause = $exception; $cause !== null; $cause = $cause->getPrevious()) {
                    if ($cause instanceof ImageRejected) {
                        throw $cause;
                    }
                }

                throw new ImageRejected("Cannot download {$url}: the server did not answer in time or refused the connection.");
            }

            if ($response->redirect()) {
                $location = $response->header('Location');

                if ($location === '') {
                    break;
                }

                $url = self::absolute($location, $url);

                continue;
            }

            if (! $response->successful()) {
                throw new ImageRejected("Cannot download {$url}: the server answered with HTTP {$response->status()}.");
            }

            $body = $response->body();

            if ($body === '') {
                throw new ImageRejected("Cannot download {$url}: the response is empty.");
            }

            if (strlen($body) > $maxBytes) {
                throw new ImageRejected('The image is larger than '.($maxBytes / 1024 / 1024).' MB.');
            }

            return $body;
        }

        throw new ImageRejected("Cannot download {$url}: too many redirects.");
    }

    /**
     * cURL option that connects to the address PublicUrl checked, so DNS cannot change in between.
     *
     * @return array<int, mixed>
     */
    private static function pin(string $url): array
    {
        $host = (string) parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT) ?? (strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https' ? 443 : 80);

        if ($host === '' || filter_var(trim($host, '[]'), FILTER_VALIDATE_IP) !== false || ! defined('CURLOPT_RESOLVE')) {
            return [];
        }

        $address = PublicUrl::resolve($host)[0] ?? null;

        if ($address === null) {
            return [];
        }

        return [CURLOPT_RESOLVE => ["{$host}:{$port}:".(str_contains($address, ':') ? "[{$address}]" : $address)]];
    }

    private static function absolute(string $location, string $base): string
    {
        if (preg_match('#^https?://#i', $location) === 1) {
            return $location;
        }

        $parts = parse_url($base);
        $origin = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').(isset($parts['port']) ? ':'.$parts['port'] : '');

        if (str_starts_with($location, '//')) {
            return ($parts['scheme'] ?? 'https').':'.$location;
        }

        if (str_starts_with($location, '/')) {
            return $origin.$location;
        }

        $path = $parts['path'] ?? '/';

        return $origin.substr($path, 0, (int) strrpos($path, '/') + 1).$location;
    }
}
