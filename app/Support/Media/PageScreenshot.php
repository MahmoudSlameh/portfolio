<?php

namespace App\Support\Media;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

/**
 * Screenshots a public web page with headless Chrome (the binary of STUDIO_SCREENSHOT_CHROME, or one on
 * the PATH), so Claude can give a project a cover or gallery image of its live site.
 */
final class PageScreenshot
{
    /**
     * Viewport sizes Claude can choose from.
     *
     * @var array<string, array{int, int}>
     */
    public const VIEWPORTS = [
        'desktop' => [1600, 900], // 16:9, the cover ratio
        'laptop' => [1440, 900],
        'tablet' => [1024, 768], // 4:3, the gallery ratio
        'mobile' => [390, 844],
    ];

    public static function available(): bool
    {
        return Favicons::chrome() !== null;
    }

    /**
     * @return string PNG bytes
     *
     * @throws ImageRejected
     */
    public static function capture(string $url, string $viewport = 'desktop'): string
    {
        if (($problem = PublicUrl::problem($url)) !== null) {
            throw new ImageRejected("Cannot screenshot {$url}: {$problem}");
        }

        $chrome = Favicons::chrome();

        if ($chrome === null) {
            throw new ImageRejected('Screenshots are not available on this server (no Chrome/Chromium; set STUDIO_SCREENSHOT_CHROME). Send an image by URL or base64 instead.');
        }

        [$width, $height] = self::VIEWPORTS[$viewport] ?? self::VIEWPORTS['desktop'];
        $directory = storage_path('app/private/mcp-screenshots');
        File::ensureDirectoryExists($directory);
        $path = $directory.'/'.Str::ulid().'.png';

        try {
            $result = Process::timeout((int) config('studio.screenshots.timeout', 90))->run(array_values(array_filter([
                $chrome,
                '--headless=new',
                config('studio.screenshots.no_sandbox') || (function_exists('posix_getuid') && posix_getuid() === 0) ? '--no-sandbox' : null,
                '--disable-gpu',
                '--hide-scrollbars',
                '--mute-audio',
                '--incognito',
                '--force-device-scale-factor=1',
                "--window-size={$width},{$height}",
                // Let client-side apps render and fonts load before the capture.
                '--virtual-time-budget=10000',
                "--screenshot={$path}",
                $url,
            ])));

            if (! is_file($path) || filesize($path) === 0) {
                throw new ImageRejected("Chrome could not screenshot {$url}: ".Str::limit(trim($result->errorOutput() ?: $result->output()), 300));
            }

            return (string) file_get_contents($path);
        } finally {
            File::delete($path);
        }
    }
}
