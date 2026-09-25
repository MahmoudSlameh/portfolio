<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Shared Spatie Media Library conversions (see docs/05-media.md).
 *
 * - `thumb`: small square WebP used by the admin panel.
 * - `webp`:  web-optimised WebP (max 1920px) with responsive variants for srcset.
 * - `og`:    1200×630 JPEG crop for Open Graph / social cards.
 *
 * @phpstan-require-extends Model
 *
 * @mixin InteractsWithMedia
 */
trait RegistersImageConversions
{
    /**
     * @param  list<string>  $thumb  collections that get an admin thumbnail
     * @param  list<string>  $responsive  collections that get the responsive `webp` conversion
     * @param  list<string>  $og  collections that get the Open Graph crop
     */
    protected function registerImageConversions(array $thumb = [], array $responsive = [], array $og = []): void
    {
        if ($thumb !== []) {
            $this->addMediaConversion('thumb')
                ->nonQueued()
                ->performOnCollections(...$thumb)
                ->format('webp')
                ->fit(Fit::Crop, 160, 160);
        }

        if ($responsive !== []) {
            $this->addMediaConversion('webp')
                ->withResponsiveImages()
                ->performOnCollections(...$responsive)
                ->format('webp')
                ->quality(82)
                ->fit(Fit::Max, 1920, 1920);
        }

        if ($og !== []) {
            $this->addMediaConversion('og')
                ->performOnCollections(...$og)
                ->format('jpg')
                ->quality(85)
                ->fit(Fit::Crop, 1200, 630);
        }
    }
}
