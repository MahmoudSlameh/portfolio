<?php

namespace App\Models;

use App\Enums\ProjectCategory;
use App\Enums\ProjectStatus;
use App\Models\Concerns\GeneratesSlug;
use App\Models\Concerns\HasSkills;
use App\Models\Concerns\HasSortOrder;
use App\Models\Concerns\RegistersImageConversions;
use App\Models\Contracts\HasStack;
use App\Support\Media\MimeTypes;
use Carbon\CarbonImmutable;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A portfolio project / case study.
 *
 * @phpstan-type TitledItem array{title: string, description: string}
 * @phpstan-type ArchitectureNode array{id: string, label: string, detail: string, kind: string, column: int, row: int}
 * @phpstan-type ArchitectureEdge array{from: string, to: string, label?: string|null}
 * @phpstan-type Architecture array{caption: string, columns: int, rows: int, nodes: list<ArchitectureNode>, edges: list<ArchitectureEdge>}
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $tagline
 * @property string|null $summary
 * @property int $year
 * @property ProjectStatus $status
 * @property ProjectCategory $category
 * @property bool $is_featured
 * @property string|null $version
 * @property int|null $company_id
 * @property int|null $experience_id
 * @property string|null $role
 * @property string|null $team
 * @property string|null $timeline
 * @property list<string> $overview
 * @property list<string> $problem
 * @property list<TitledItem> $approach
 * @property Architecture|null $architecture
 * @property list<TitledItem> $features
 * @property list<TitledItem> $challenges
 * @property list<array{value: string, label: string, detail: string}> $metrics
 * @property list<array{label: string, url: string, kind: string}> $links
 * @property string|null $cover_alt
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property bool $is_published
 * @property CarbonImmutable|null $published_at
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Company|null $company
 * @property-read Experience|null $experience
 * @property-read Collection<int, ProjectGalleryItem> $galleryItems
 * @property-read Collection<int, Article> $articles
 * @property-read Collection<int, Skill> $skills
 * @property-read list<string> $stack
 */
#[Fillable([
    'slug', 'title', 'tagline', 'summary', 'year', 'status', 'category', 'is_featured', 'version',
    'company_id', 'experience_id', 'role', 'team', 'timeline', 'overview', 'problem', 'approach',
    'architecture', 'features', 'challenges', 'metrics', 'links', 'cover_alt', 'meta_title',
    'meta_description', 'is_published', 'published_at', 'sort_order',
])]
#[RouteKey('slug')]
class Project extends Model implements HasMedia, HasStack
{
    /** @use HasFactory<ProjectFactory> */
    use GeneratesSlug, HasFactory, HasSkills, HasSortOrder, InteractsWithMedia, RegistersImageConversions, SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'live',
        'category' => 'product',
        'is_featured' => false,
        'overview' => '[]',
        'problem' => '[]',
        'approach' => '[]',
        'features' => '[]',
        'challenges' => '[]',
        'metrics' => '[]',
        'links' => '[]',
        'is_published' => false,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'status' => ProjectStatus::class,
            'category' => ProjectCategory::class,
            'is_featured' => 'boolean',
            'overview' => 'array',
            'problem' => 'array',
            'approach' => 'array',
            'architecture' => 'array',
            'features' => 'array',
            'challenges' => 'array',
            'metrics' => 'array',
            'links' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'immutable_datetime',
            'sort_order' => 'integer',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerImageConversions(thumb: ['cover'], responsive: ['cover'], og: ['cover']);
    }

    /**
     * Published and not scheduled for the future.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function isPublished(): bool
    {
        return $this->is_published && ($this->published_at === null || $this->published_at->isPast());
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Experience, $this>
     */
    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    /**
     * @return BelongsToMany<Article, $this>
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }

    /**
     * @return HasMany<ProjectGalleryItem, $this>
     */
    public function galleryItems(): HasMany
    {
        return $this->hasMany(ProjectGalleryItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
