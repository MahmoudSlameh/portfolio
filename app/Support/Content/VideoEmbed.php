<?php

namespace App\Support\Content;

/**
 * Turns a YouTube or Vimeo link into the URL of its embeddable player. Other hosts are not embedded.
 */
final class VideoEmbed
{
    private const YOUTUBE_ID = '[A-Za-z0-9_-]{11}';

    /**
     * The player URL, or null when the link is not a YouTube or Vimeo video.
     */
    public static function playerUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || preg_match('#^https?://#i', $url) !== 1) {
            return null;
        }

        $host = strtolower((string) preg_replace('/^(www\.|m\.)/', '', (string) parse_url($url, PHP_URL_HOST)));
        $path = (string) parse_url($url, PHP_URL_PATH);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $youtubeId = match ($host) {
            'youtu.be' => self::match('#^/('.self::YOUTUBE_ID.')#', $path),
            'youtube.com', 'youtube-nocookie.com' => is_string($query['v'] ?? null) && preg_match('#^'.self::YOUTUBE_ID.'$#', $query['v']) === 1
                ? $query['v']
                : self::match('#^/(?:embed|shorts|live)/('.self::YOUTUBE_ID.')#', $path),
            default => null,
        };

        if ($youtubeId !== null) {
            return "https://www.youtube-nocookie.com/embed/{$youtubeId}";
        }

        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            $vimeoId = self::match('#^/(?:video/)?(\d+)#', $path);

            return $vimeoId === null ? null : "https://player.vimeo.com/video/{$vimeoId}";
        }

        return null;
    }

    private static function match(string $pattern, string $subject): ?string
    {
        return preg_match($pattern, $subject, $match) === 1 ? $match[1] : null;
    }
}
