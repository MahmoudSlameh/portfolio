<?php

namespace App\Models;

use App\Enums\BookCategory;
use App\Enums\BookCoverStyle;
use App\Enums\ReadingStatus;
use App\Models\Concerns\GeneratesSlug;
use App\Models\Concerns\RegistersImageConversions;
use App\Support\Media\MimeTypes;
use Carbon\CarbonImmutable;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A book on the owner's shelf. Without an uploaded cover the templates draw a generated one.
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $author
 * @property int|null $published_year
 * @property BookCategory $category
 * @property ReadingStatus $status
 * @property CarbonImmutable|null $finished_at
 * @property int|null $rating
 * @property int|null $pages
 * @property string|null $note
 * @property string $cover_background
 * @property string $cover_ink
 * @property string $cover_accent
 * @property BookCoverStyle $cover_style
 * @property bool $is_visible
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'slug', 'title', 'author', 'published_year', 'category', 'status', 'finished_at', 'rating', 'pages',
    'note', 'cover_background', 'cover_ink', 'cover_accent', 'cover_style', 'is_visible',
])]
#[RouteKey('slug')]
class Book extends Model implements HasMedia
{
    /** @use HasFactory<BookFactory> */
    use GeneratesSlug, HasFactory, InteractsWithMedia, RegistersImageConversions;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'category' => 'engineering',
        'status' => 'to-read',
        'cover_background' => '#1d2b3a',
        'cover_ink' => '#f1ede4',
        'cover_accent' => '#e8a33d',
        'cover_style' => 'band',
        'is_visible' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_year' => 'integer',
            'category' => BookCategory::class,
            'status' => ReadingStatus::class,
            'finished_at' => 'immutable_date',
            'rating' => 'integer',
            'pages' => 'integer',
            'cover_style' => BookCoverStyle::class,
            'is_visible' => 'boolean',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover_image')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerImageConversions(thumb: ['cover_image'], responsive: ['cover_image']);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true);
    }
}
