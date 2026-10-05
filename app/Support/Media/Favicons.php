<?php

namespace App\Support\Media;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Raster icons made from the favicon uploaded on Site → SEO & settings: a multi-size favicon.ico,
 * a 192px PNG (search engines want a square icon in a multiple of 48px) and a 180px apple-touch-icon.
 * They are written to the public disk and recorded on the media item, so the page head links them
 * without touching the filesystem. PNG uploads are resized with GD; SVG uploads are rasterised with
 * Imagick when it reads SVG, otherwise with headless Chrome.
 */
final class Favicons
{
    private const SOURCE = 512;

    private const ICO_SIZES = [16, 32, 48];

    private const PNG_SIZE = 192;

    private const APPLE_SIZE = 180;

    private const PROPERTY = 'icons';

    /**
     * Whether SVGs may be rasterised with Imagick before falling back to Chrome (tests turn it off to
     * exercise the Chrome path on machines that have Imagick).
     */
    public static bool $useImagick = true;

    /**
     * Make the icons for the current favicon if they are missing, and remove icons of older favicons.
     * Returns whether icons were generated.
     */
    public static function sync(SiteSetting $settings, bool $force = false): bool
    {
        $media = $settings->getFirstMedia('favicon');
        $disk = Storage::disk('public');

        foreach ($disk->directories('favicons') as $directory) {
            if ($media === null || $directory !== self::directory($media)) {
                $disk->deleteDirectory($directory);
            }
        }

        if ($media === null || (! $force && self::links($settings) !== null)) {
            return false;
        }

        self::generate($media);

        return true;
    }

    /**
     * The generated icons of the current favicon, or null when there are none (yet).
     *
     * @return array{ico: string, png: string, apple: string}|null
     */
    public static function links(SiteSetting $settings): ?array
    {
        $icons = $settings->getFirstMedia('favicon')?->getCustomProperty(self::PROPERTY);

        if (! is_array($icons) || ! isset($icons['ico'], $icons['png'], $icons['apple'])) {
            return null;
        }

        $disk = Storage::disk('public');

        return [
            'ico' => $disk->url($icons['ico']),
            'png' => $disk->url($icons['png']),
            'apple' => $disk->url($icons['apple']),
        ];
    }

    /**
     * The Chrome binary used for SVG favicons: the screenshot setting, or one found on the PATH.
     */
    public static function chrome(): ?string
    {
        $configured = config('studio.screenshots.chrome');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $finder = new ExecutableFinder;

        foreach (['chromium', 'chromium-browser', 'google-chrome', 'google-chrome-stable'] as $name) {
            if (($path = $finder->find($name)) !== null) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @throws RuntimeException when the favicon cannot be read or rasterised
     */
    private static function generate(Media $media): void
    {
        $source = self::source($media);
        $directory = self::directory($media);
        $paths = [
            'ico' => "{$directory}/favicon.ico",
            'png' => "{$directory}/favicon-".self::PNG_SIZE.'.png',
            'apple' => "{$directory}/apple-touch-icon.png",
        ];
        $disk = Storage::disk('public');

        $disk->put($paths['ico'], self::ico($source));
        $disk->put($paths['png'], self::png($source, self::PNG_SIZE));
        // iOS fills transparency with black, so the touch icon sits on white.
        $disk->put($paths['apple'], self::png($source, self::APPLE_SIZE, background: true));

        $media->setCustomProperty(self::PROPERTY, $paths)->save();
    }

    private static function directory(Media $media): string
    {
        return "favicons/{$media->getKey()}";
    }

    /**
     * The favicon as a square, transparent, SOURCE-sized image.
     */
    private static function source(Media $media): \GdImage
    {
        $path = $media->getPath();

        if (! is_file($path)) {
            throw new RuntimeException("The favicon file is missing: {$path}");
        }

        $png = $media->mime_type === 'image/svg+xml' ? self::rasterise($path) : (string) file_get_contents($path);
        $image = @imagecreatefromstring($png);

        if ($image === false) {
            throw new RuntimeException('The favicon is not a readable image.');
        }

        return self::square($image, self::SOURCE, background: false);
    }

    /**
     * PNG bytes of an SVG file.
     */
    private static function rasterise(string $svg): string
    {
        if (self::$useImagick && extension_loaded('imagick') && in_array('SVG', \Imagick::queryFormats('SVG'), true)) {
            $imagick = new \Imagick;
            $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
            $imagick->setResolution(self::SOURCE, self::SOURCE);
            $imagick->readImage($svg);
            $imagick->setImageFormat('png32');

            return $imagick->getImageBlob();
        }

        $chrome = self::chrome();

        if ($chrome === null) {
            throw new RuntimeException('SVG favicons need Imagick with SVG support or Chrome/Chromium (set STUDIO_SCREENSHOT_CHROME). Or upload a PNG of at least 192×192 instead.');
        }

        $directory = storage_path('app/private/favicons-'.Str::ulid());
        File::ensureDirectoryExists($directory);

        try {
            // An <img> sized to the viewport renders SVGs with or without their own width and height.
            $size = self::SOURCE;
            File::put("{$directory}/icon.html", '<!doctype html><html><body style="margin:0;background:transparent">'
                .'<img src="data:image/svg+xml;base64,'.base64_encode((string) file_get_contents($svg))."\" style=\"display:block;width:{$size}px;height:{$size}px\">"
                .'</body></html>');

            $result = Process::timeout(60)->run(array_values(array_filter([
                $chrome,
                '--headless=new',
                config('studio.screenshots.no_sandbox') || (function_exists('posix_getuid') && posix_getuid() === 0) ? '--no-sandbox' : null,
                '--disable-gpu',
                '--hide-scrollbars',
                '--default-background-color=00000000',
                "--window-size={$size},{$size}",
                "--screenshot={$directory}/icon.png",
                "file://{$directory}/icon.html",
            ])));

            if (! is_file("{$directory}/icon.png")) {
                throw new RuntimeException('Chrome could not render the SVG favicon: '.Str::limit(trim($result->errorOutput() ?: $result->output()), 300));
            }

            return (string) file_get_contents("{$directory}/icon.png");
        } finally {
            File::deleteDirectory($directory);
        }
    }

    /**
     * The image centred on a transparent (or white) square canvas.
     *
     * @param  int<1, max>  $size
     */
    private static function square(\GdImage $image, int $size, bool $background): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = $size / max($width, $height);
        $w = max(1, (int) round($width * $scale));
        $h = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, (int) imagecolorallocatealpha($canvas, 255, 255, 255, $background ? 0 : 127));
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $image, intdiv($size - $w, 2), intdiv($size - $h, 2), 0, 0, $w, $h, $width, $height);

        return $canvas;
    }

    /**
     * @param  int<1, max>  $size
     */
    private static function png(\GdImage $source, int $size, bool $background = false): string
    {
        $copy = imagecreatetruecolor(self::SOURCE, self::SOURCE);
        imagealphablending($copy, false);
        imagesavealpha($copy, true);
        imagecopy($copy, $source, 0, 0, 0, 0, self::SOURCE, self::SOURCE);

        $image = self::square($copy, $size, $background);
        ob_start();
        imagepng($image, null, 9);

        return (string) ob_get_clean();
    }

    /**
     * An ICO file holding PNG images (supported by every browser since Windows Vista).
     */
    private static function ico(\GdImage $source): string
    {
        $images = array_map(fn (int $size): string => self::png($source, $size), self::ICO_SIZES);
        $header = pack('vvv', 0, 1, count($images));
        $offset = 6 + 16 * count($images);
        $entries = '';

        foreach (self::ICO_SIZES as $i => $size) {
            $entries .= pack('CCCCvvVV', $size, $size, 0, 0, 1, 32, strlen($images[$i]), $offset);
            $offset += strlen($images[$i]);
        }

        return $header.$entries.implode('', $images);
    }
}
