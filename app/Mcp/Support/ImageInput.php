<?php

namespace App\Mcp\Support;

use App\Support\Media\ImageRejected;
use App\Support\Media\PageScreenshot;
use App\Support\Media\RemoteImage;
use Illuminate\Contracts\JsonSchema\JsonSchema;

/**
 * The three ways an MCP tool receives an image: a URL the server downloads, base64 data, or a URL the
 * server screenshots with headless Chrome. Exactly one must be given.
 */
final class ImageInput
{
    /**
     * Longest side accepted, in pixels (bigger images would exhaust memory in the conversions).
     */
    private const MAX_EDGE = 10000;

    /**
     * @return array<string, mixed>
     */
    public static function schema(JsonSchema $schema): array
    {
        return [
            'image_url' => $schema->string()
                ->description('Public http(s) URL of a JPEG, PNG, WebP or AVIF image to download (e.g. a screenshot in the repository: use the raw.githubusercontent.com URL).'),
            'image_base64' => $schema->string()
                ->description('The image itself, base64-encoded (a data: URI is fine). Only for tiny images (under ~200 KB): for local files call request_image_upload and PUT the file with curl instead.'),
            'screenshot_url' => $schema->string()
                ->description('Public URL of a web page (e.g. the project\'s live site) to screenshot with headless Chrome on the server.'),
            'screenshot_viewport' => $schema->string()
                ->enum(array_keys(PageScreenshot::VIEWPORTS))
                ->description('Viewport for screenshot_url: desktop 1600×900 (16:9, best for covers), laptop 1440×900, tablet 1024×768 (4:3, best for the gallery), mobile 390×844.')
                ->default('desktop'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        $oneOf = 'required_without_all:image_url,image_base64,screenshot_url';

        return [
            'image_url' => ['nullable', "{$oneOf}", 'prohibits:image_base64,screenshot_url', 'string', 'url:http,https', 'max:2048'],
            'image_base64' => ['nullable', 'prohibits:image_url,screenshot_url', 'string'],
            'screenshot_url' => ['nullable', 'prohibits:image_url,image_base64', 'string', 'url:http,https', 'max:2048'],
            'screenshot_viewport' => ['nullable', 'string', 'in:'.implode(',', array_keys(PageScreenshot::VIEWPORTS))],
        ];
    }

    /**
     * Validation messages that tell Claude what to send.
     *
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'image_url.required_without_all' => 'Send the image as image_url, image_base64 or screenshot_url.',
            '*.prohibits' => 'Send only one of image_url, image_base64 and screenshot_url.',
        ];
    }

    /**
     * Downloads, screenshots or decodes the image and checks its type.
     *
     * @param  array<string, mixed>  $input  validated input containing one image field
     * @param  list<string>  $mimeTypes  accepted types
     *
     * @throws ImageRejected
     */
    public static function fetch(array $input, array $mimeTypes): IncomingImage
    {
        [$bytes, $source] = self::bytes($input);

        return self::check($bytes, $mimeTypes, $source);
    }

    /**
     * Checks that the bytes are a readable image of an accepted type (by content, not by name or header).
     *
     * @param  list<string>  $mimeTypes  accepted types
     *
     * @throws ImageRejected
     */
    public static function check(string $bytes, array $mimeTypes, string $source = 'file'): IncomingImage
    {
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);

        if (! in_array($mime, $mimeTypes, true)) {
            throw new ImageRejected("The {$source} is not an accepted image (got {$mime}; accepted: ".implode(', ', $mimeTypes).').');
        }

        $size = @getimagesizefromstring($bytes);

        if ($size === false || $size[0] < 1 || $size[1] < 1) {
            throw new ImageRejected("The {$source} is not a readable image.");
        }

        if ($size[0] > self::MAX_EDGE || $size[1] > self::MAX_EDGE) {
            throw new ImageRejected("The {$source} is {$size[0]}×{$size[1]} pixels; the limit is ".self::MAX_EDGE.' pixels per side.');
        }

        return new IncomingImage($bytes, $mime);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{string, string} the bytes and a description of where they came from
     *
     * @throws ImageRejected
     */
    private static function bytes(array $input): array
    {
        if (filled($input['image_url'] ?? null)) {
            return [RemoteImage::download((string) $input['image_url']), 'downloaded file'];
        }

        if (filled($input['screenshot_url'] ?? null)) {
            return [PageScreenshot::capture((string) $input['screenshot_url'], (string) ($input['screenshot_viewport'] ?? 'desktop')), 'screenshot'];
        }

        $data = preg_replace('/^data:[^;,]+;base64,/', '', trim((string) ($input['image_base64'] ?? '')));
        $data = preg_replace('/\s+/', '', (string) $data);
        $maxBytes = (int) config('portfolio.mcp.images.max_kilobytes') * 1024;

        if (strlen((string) $data) > (int) ceil($maxBytes / 3) * 4) {
            throw new ImageRejected('The image is larger than '.($maxBytes / 1024 / 1024).' MB.');
        }

        $bytes = base64_decode((string) $data, true);

        if ($bytes === false || $bytes === '') {
            throw new ImageRejected('image_base64 is not valid base64 data.');
        }

        return [$bytes, 'base64 image'];
    }
}
