# 05 · Media (Spatie Media Library)

**Rule:** every image/file in the project is stored with
`spatie/laravel-medialibrary` and uploaded through
`filament/spatie-laravel-media-library-plugin`
(<https://filamentphp.com/plugins/filament-spatie-media-library>).

Status: package added to `composer.json`/`composer.lock` (v5.8.4 →
spatie/laravel-medialibrary 11.x). Remaining setup is task **P0-03**.

## Setup

```bash
composer install
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-config"
php artisan migrate
php artisan storage:link
```

`config/media-library.php`:

- `disk_name` → `env('MEDIA_DISK', 'public')` (S3/R2 in prod is a one-line change).
- `queue_conversions_by_default` → `true` (conversions on the queue; `composer dev`
  already runs `queue:listen`).
- `image_driver` → `imagick` if available, otherwise `gd` (must support WebP).
- Install optimizers on the server (`jpegoptim`, `optipng`, `pngquant`,
  `svgo`, `gifsicle`, `cwebp`) — optional but recommended.

## Model pattern

```php
class Project extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
            ->withResponsiveImages();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')->quality(82)
            ->fit(Fit::Max, 1920, 1920)
            ->withResponsiveImages()
            ->performOnCollections('cover');

        $this->addMediaConversion('og')        // 1200×630 for Open Graph
            ->format('jpg')->quality(85)
            ->fit(Fit::Crop, 1200, 630)
            ->performOnCollections('cover');

        $this->addMediaConversion('thumb')     // admin tables
            ->format('webp')->fit(Fit::Crop, 160, 160)->nonQueued();
    }
}
```

## Collections per model

| Model              | Collection           | Single? | Accepts      | Conversions                      | Used for                  |
| ------------------ | -------------------- | ------- | ------------ | -------------------------------- | ------------------------- |
| Profile            | `portrait`           | ✓       | image        | webp (responsive), og, thumb     | hero portrait (3:4)       |
| Profile            | `resume`             | ✓       | pdf          | —                                | "Download CV"             |
| Profile            | `og_image`           | ✓       | image        | og                               | personal share card       |
| SiteSetting        | `default_og_image`   | ✓       | image        | og                               | fallback OG image         |
| SiteSetting        | `favicon`            | ✓       | png/svg      | —                                | favicon override          |
| Company            | `logo` / `logo_dark` | ✓       | svg/png/webp | webp, thumb (svg passes through) | clients wall              |
| Education          | `logo`               | ✓       | image/svg    | thumb                            | institution logo          |
| Education          | `certificate`        | ✓       | image/pdf    | —                                | optional proof            |
| Certification      | `badge`              | ✓       | image/svg    | thumb                            | badge                     |
| Testimonial        | `avatar`             | ✓       | image        | webp 256, thumb                  | author photo              |
| Skill              | `icon`               | ✓       | svg/png      | —                                | custom icon               |
| Project            | `cover`              | ✓       | image        | webp (responsive), og, thumb     | cards + case study (16:9) |
| ProjectGalleryItem | `image`              | ✓       | image        | webp (responsive), thumb         | case-study gallery (4:3)  |
| Article            | `cover`              | ✓       | image        | webp (responsive), og            | article hero + OG         |
| Article            | `body_images`        | ✗       | image        | webp (responsive)                | image blocks              |
| Book               | `cover_image`        | ✓       | image        | webp 480, thumb                  | real book cover           |
| UsesItem           | `image`              | ✓       | image        | webp 480                         | optional                  |

## Filament usage

```php
SpatieMediaLibraryFileUpload::make('cover')
    ->collection('cover')
    ->image()
    ->imageEditor()                       // crop / aspect ratios
    ->imageEditorAspectRatioOptions(['16:9'])
    ->maxSize(8 * 1024)
    ->responsiveImages()
    ->conversion('thumb')
    ->required();

SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('thumb');
```

- SVG uploads: `->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp'])`
  and skip raster conversions for SVG (check `$media->mime_type`).
- Galleries with per-image alt/caption: relationship `Repeater`
  (`->relationship('galleryItems')->orderColumn('sort_order')`) containing a
  `SpatieMediaLibraryFileUpload::make('image')->collection('image')` plus
  `TextInput alt` + `TextInput caption`.
- Alt text is **required** for every public image (accessibility + SEO):
  either a `*_alt` column on the owner model or the gallery item's `alt`.

## Getting images to React — `App\Support\Media\ImageData`

```php
final class ImageData
{
    public static function from(?Media $media, string $alt, string $conversion = 'webp'): ?array
    {
        if (! $media) return null;
        return [
            'src' => $media->getUrl($conversion),
            'srcSet' => $media->getSrcset($conversion),
            'width' => $media->getCustomProperty('width') ?? ...,   // store dimensions on upload
            'height' => ...,
            'alt' => $alt,
            'placeholder' => $media->responsiveImages($conversion)->getPlaceholderSvg(),
        ];
    }
}
```

- Store original `width`/`height` as custom properties when the media is added
  (listen to `MediaHasBeenAddedEvent`, read dimensions with `spatie/image`) so
  the frontend always renders `width`/`height` attributes → no layout shift
  (CLS).
- React side: `shared/ui/ResponsiveImage.tsx` is rewritten to accept
  `ImageData` (`<img src srcSet sizes width height alt loading decoding>`),
  with `priority` prop for LCP images (`fetchpriority="high"`, no lazy).
- `lib/utils.ts#imageUrl/imageSources` from the reference are removed.
