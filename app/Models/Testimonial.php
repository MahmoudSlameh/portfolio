<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\RegistersImageConversions;
use App\Support\Media\MimeTypes;
use Database\Factories\TestimonialFactory;
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
 * @property string $quote
 * @property string $author_name
 * @property string|null $author_role
 * @property int|null $company_id
 * @property string|null $relation
 * @property bool $is_visible
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company|null $company
 */
#[Fillable(['quote', 'author_name', 'author_role', 'company_id', 'relation', 'is_visible', 'sort_order'])]
class Testimonial extends Model implements HasMedia
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory, HasVisibilityAndOrder, InteractsWithMedia, RegistersImageConversions;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_visible' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerImageConversions(thumb: ['avatar'], responsive: ['avatar']);
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
