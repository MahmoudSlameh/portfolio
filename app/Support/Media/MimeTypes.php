<?php

namespace App\Support\Media;

/**
 * Accepted upload types for media collections.
 */
final class MimeTypes
{
    /** @var list<string> */
    public const RASTER = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

    /** @var list<string> */
    public const RASTER_OR_SVG = [...self::RASTER, 'image/svg+xml'];

    /** @var list<string> */
    public const LOGO = ['image/svg+xml', 'image/png', 'image/webp'];

    /** @var list<string> */
    public const PDF = ['application/pdf'];

    /** @var list<string> */
    public const RASTER_OR_PDF = [...self::RASTER, ...self::PDF];
}
