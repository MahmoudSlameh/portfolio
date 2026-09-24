<?php

namespace App\Support\Media;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Turns a Spatie media item into the `ImageData` shape the React templates render
 * (see resources/js/types/content.ts).
 *
 * @phpstan-type ImageDataArray array{src: string, srcSet: string, width: int, height: int, alt: string, placeholder: string|null}
 */
final class ImageData
{
    /**
     * Longest edge of the `webp` conversion (see RegistersImageConversions).
     */
    private const WEBP_MAX_EDGE = 1920;

    /**
     * @return ImageDataArray|null
     */
    public static function fromCollection(HasMedia $model, string $collection, ?string $alt, string $conversion = 'webp'): ?array
    {
        return self::from($model->getFirstMedia($collection), $alt, $conversion);
    }

    /**
     * @return ImageDataArray|null
     */
    public static function from(?Media $media, ?string $alt, string $conversion = 'webp'): ?array
    {
        if ($media === null) {
            return null;
        }

        $converted = $media->hasGeneratedConversion($conversion);
        [$width, $height] = self::dimensions($media, $converted ? $conversion : null);

        return [
            'src' => $converted ? $media->getUrl($conversion) : $media->getUrl(),
            'srcSet' => $converted ? self::srcSet($media, $conversion) : '',
            'width' => $width,
            'height' => $height,
            'alt' => (string) $alt,
            'placeholder' => $converted ? $media->responsiveImages($conversion)->getPlaceholderSvg() : null,
        ];
    }

    /**
     * Intrinsic size of the rendered file. The `webp` conversion scales down (never up) to 1920px.
     *
     * @return array{int, int}
     */
    private static function dimensions(Media $media, ?string $conversion): array
    {
        $width = (int) $media->getCustomProperty('width', 0);
        $height = (int) $media->getCustomProperty('height', 0);

        if ($conversion !== 'webp' || $width === 0 || $height === 0) {
            return [$width, $height];
        }

        $scale = min(1, self::WEBP_MAX_EDGE / max($width, $height));

        return [(int) round($width * $scale), (int) round($height * $scale)];
    }

    /**
     * Responsive variants without Spatie's inline placeholder entry (the placeholder is sent separately),
     * so browsers never pick the tiny blurred image as a srcset candidate.
     */
    private static function srcSet(Media $media, string $conversion): string
    {
        return collect(explode(', ', $media->getSrcset($conversion)))
            ->reject(fn (string $candidate): bool => $candidate === '' || str_starts_with($candidate, 'data:'))
            ->implode(', ');
    }
}
