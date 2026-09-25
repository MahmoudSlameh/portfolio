<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Models\Concerns\GeneratesSlug;
use App\Models\Concerns\RegistersImageConversions;
use App\Support\Media\MimeTypes;
use Carbon\CarbonImmutable;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A blog post. The body is a list of Filament Builder blocks: `[{type, data}]` where type is
 * paragraph, heading, code, quote, list, callout or image.
 *
 * @phpstan-type BodyBlock array{type: string, data: array<string, mixed>}
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $excerpt
 * @property list<BodyBlock> $body
 * @property list<string> $tags
 * @property ArticleStatus $status
 * @property CarbonImmutable|null $published_at
 * @property string|null $cover_alt
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Project> $projects
 */
#[Fillable(['slug', 'title', 'excerpt', 'body', 'tags', 'status', 'published_at', 'cover_alt', 'meta_title', 'meta_description'])]
#[RouteKey('slug')]
class Article extends Model implements HasMedia
{
    /** @use HasFactory<ArticleFactory> */
    use GeneratesSlug, HasFactory, InteractsWithMedia, RegistersImageConversions, SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'body' => '[]',
        'tags' => '[]',
        'status' => 'draft',
    ];

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            if ($article->status === ArticleStatus::Published && $article->published_at === null) {
                $article->published_at = now();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'body' => 'array',
            'tags' => 'array',
            'status' => ArticleStatus::class,
            'published_at' => 'immutable_datetime',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER);
        $this->addMediaCollection('body_images')->acceptsMimeTypes(MimeTypes::RASTER);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerImageConversions(
            thumb: ['cover', 'body_images'],
            responsive: ['cover', 'body_images'],
            og: ['cover'],
        );
    }

    /**
     * Published and not scheduled for the future.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', ArticleStatus::Published)->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === ArticleStatus::Published && $this->published_at?->isPast() === true;
    }

    /**
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    /**
     * Words in the body, counted like the reference frontend (code, list items, callout title + text).
     */
    public function wordCount(): int
    {
        return collect($this->body)->sum(function (array $block): int {
            $data = $block['data'];

            $text = match ($block['type']) {
                'list' => implode(' ', array_map(strval(...), (array) ($data['items'] ?? []))),
                'code' => (string) ($data['code'] ?? ''),
                'callout' => ($data['title'] ?? '').' '.($data['text'] ?? ''),
                'image' => '',
                default => (string) ($data['text'] ?? ''),
            };

            return count(preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        });
    }

    public function readingMinutes(): int
    {
        return max(1, (int) round($this->wordCount() / config('portfolio.reading_wpm', 220)));
    }
}
