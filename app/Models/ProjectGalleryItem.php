<?php

namespace App\Models;

use App\Models\Concerns\RegistersImageConversions;
use App\Support\Media\MimeTypes;
use Database\Factories\ProjectGalleryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Touches;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * One image of a project's case-study gallery, with its own alt text and caption.
 *
 * @property int $id
 * @property int $project_id
 * @property string $alt
 * @property string|null $caption
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Project $project
 */
#[Fillable(['project_id', 'alt', 'caption', 'sort_order'])]
#[Touches(['project'])]
class ProjectGalleryItem extends Model implements HasMedia
{
    /** @use HasFactory<ProjectGalleryItemFactory> */
    use HasFactory, InteractsWithMedia, RegistersImageConversions;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerImageConversions(thumb: ['image'], responsive: ['image']);
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
