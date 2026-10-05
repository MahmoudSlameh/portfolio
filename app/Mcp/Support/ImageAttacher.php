<?php

namespace App\Mcp\Support;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectGalleryItem;
use App\Support\Media\ImageRejected;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Stores a checked image where it belongs: a project cover, a new gallery item or a company logo. Shared by
 * the image tools and the direct upload endpoint, so every path stores images the same way.
 */
final class ImageAttacher
{
    /**
     * @throws ImageRejected
     */
    public static function cover(Project $project, IncomingImage $image, string $alt): Media
    {
        $media = $image->storeOn($project, 'cover', "{$project->title} cover");
        $project->forceFill(['cover_alt' => $alt])->save();

        return $media;
    }

    /**
     * Adds the image at the end of the gallery.
     *
     * @throws ImageRejected
     */
    public static function gallery(Project $project, IncomingImage $image, string $alt, ?string $caption = null): ProjectGalleryItem
    {
        return DB::transaction(function () use ($project, $image, $alt, $caption): ProjectGalleryItem {
            $item = $project->galleryItems()->create([
                'alt' => $alt,
                'caption' => filled($caption) ? $caption : null,
                'sort_order' => (int) $project->galleryItems()->max('sort_order') + 1,
            ]);

            $image->storeOn($item, 'image', $alt);

            return $item;
        });
    }

    /**
     * @param  'light'|'dark'  $variant
     *
     * @throws ImageRejected
     */
    public static function logo(Company $company, IncomingImage $image, string $variant = 'light'): Media
    {
        return $image->storeOn($company, $variant === 'dark' ? 'logo_dark' : 'logo', "{$company->name} logo");
    }
}
