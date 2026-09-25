<?php

namespace App\Models;

use App\Models\Concerns\RegistersImageConversions;
use App\Support\Media\MimeTypes;
use Database\Factories\UsesItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $uses_group_id
 * @property string $name
 * @property string|null $description
 * @property string|null $url
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read UsesGroup $group
 */
#[Fillable(['uses_group_id', 'name', 'description', 'url', 'sort_order'])]
class UsesItem extends Model implements HasMedia
{
    /** @use HasFactory<UsesItemFactory> */
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
     * @return BelongsTo<UsesGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(UsesGroup::class, 'uses_group_id');
    }
}
