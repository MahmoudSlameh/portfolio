<?php

namespace App\Support\Seo;

use App\Models\Profile;
use App\Models\SiteSetting;
use App\Support\Templates\TemplateManager;
use Carbon\CarbonInterface;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Builds the `seo` prop every public page receives (rendered by resources/js/shared/seo/SeoHead.tsx).
 *
 * @phpstan-type SeoImage array{url: string, width: int|null, height: int|null, alt: string}
 * @phpstan-type SeoArray array{title: string, description: string, canonical: string, robots: string, type: string, siteName: string, image: SeoImage|null, twitterSite: string|null, publishedTime: string|null, modifiedTime: string|null, tags: list<string>, jsonLd: list<array<string, mixed>>}
 */
final class Seo
{
    public function __construct(private readonly TemplateManager $templates) {}

    /**
     * @param  'website'|'profile'|'article'  $type
     * @param  list<string>  $tags
     * @param  list<array<string, mixed>>  $jsonLd
     * @return SeoArray
     */
    public function page(
        ?string $title,
        ?string $description,
        string $path,
        string $type = 'website',
        ?Media $image = null,
        ?string $imageAlt = null,
        bool $noindex = false,
        ?CarbonInterface $publishedAt = null,
        ?CarbonInterface $modifiedAt = null,
        array $tags = [],
        array $jsonLd = [],
        bool $appendSiteName = true,
    ): array {
        $settings = SiteSetting::current();
        $profile = Profile::current();
        $siteName = $settings->site_name ?: $profile->name;

        $indexable = $settings->indexable && ! $noindex && ! $this->templates->isPreview() && ! $this->templates->isGalleryRender();

        return [
            'title' => match (true) {
                blank($title) || $title === $siteName => $siteName,
                ! $appendSiteName => (string) $title,
                default => "{$title} {$settings->title_separator} {$siteName}",
            },
            'description' => (string) str($description ?: ($settings->meta_description ?: $profile->summary ?: $profile->headline))->squish()->limit(300, ''),
            'canonical' => self::url($path),
            'robots' => $indexable ? 'index,follow,max-image-preview:large' : 'noindex,nofollow',
            'type' => $type,
            'siteName' => $siteName,
            'image' => $this->image($image, $imageAlt ?? $title ?? $siteName),
            'twitterSite' => filled($settings->twitter_handle) ? '@'.ltrim((string) $settings->twitter_handle, '@') : null,
            'publishedTime' => $publishedAt?->toIso8601String(),
            'modifiedTime' => $modifiedAt?->toIso8601String(),
            'tags' => $tags,
            'jsonLd' => $jsonLd,
        ];
    }

    public static function url(string $path = '/'): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    /**
     * The page image as an Open Graph crop, falling back to the profile image and the default image.
     *
     * @return SeoImage|null
     */
    private function image(?Media $media, string $alt): ?array
    {
        $media ??= Profile::current()->getFirstMedia('og_image')
            ?? SiteSetting::current()->getFirstMedia('default_og_image')
            ?? Profile::current()->getFirstMedia('portrait');

        if ($media === null) {
            return null;
        }

        $hasOg = $media->hasGeneratedConversion('og');

        return [
            'url' => $hasOg ? $media->getFullUrl('og') : $media->getFullUrl(),
            'width' => $hasOg ? 1200 : ((int) $media->getCustomProperty('width') ?: null),
            'height' => $hasOg ? 630 : ((int) $media->getCustomProperty('height') ?: null),
            'alt' => $alt,
        ];
    }
}
