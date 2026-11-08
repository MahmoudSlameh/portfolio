<?php

namespace App\Mcp\Support;

use App\Models\Article;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectGalleryItem;
use App\Support\Media\ImageRejected;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Stores a checked image where it belongs: a project cover, a new gallery item, a company logo, an article
 * cover or an image in an article's body. Shared by
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

    /**
     * @throws ImageRejected
     */
    public static function articleCover(Article $article, IncomingImage $image, string $alt): Media
    {
        $media = $image->storeOn($article, 'cover', "{$article->title} cover");
        $article->forceFill(['cover_alt' => $alt])->save();

        return $media;
    }

    /**
     * Stores an image in the article's body, in place of the placeholder `image:<key>` or at the end.
     *
     * @return array{media: Media, filled_placeholder: bool}
     *
     * @throws ImageRejected
     */
    public static function articleImage(Article $article, IncomingImage $image, string $alt, ?string $caption = null, ?string $placeholder = null): array
    {
        $media = $image->storeOn($article, 'body_images', $alt ?: $article->title);
        $filled = ArticleImages::place($article, $media, $alt, filled($caption) ? $caption : null, $placeholder);

        return ['media' => $media, 'filled_placeholder' => $filled];
    }
}
