<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\RegistersImageConversions;
use App\Support\Content\Location;
use App\Support\Media\MimeTypes;
use Carbon\CarbonImmutable;
use Database\Factories\EducationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A qualification: degree, diploma, course…
 *
 * @property int $id
 * @property string $degree
 * @property string $institution
 * @property string|null $institution_url
 * @property string|null $field_of_study
 * @property string|null $grade
 * @property string|null $country_code
 * @property string|null $city
 * @property CarbonImmutable $start_date
 * @property CarbonImmutable|null $end_date
 * @property string|null $description
 * @property list<string> $achievements
 * @property bool $is_visible
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read bool $is_current
 * @property-read string|null $location_label
 */
#[Fillable([
    'degree', 'institution', 'institution_url', 'field_of_study', 'grade', 'country_code', 'city',
    'start_date', 'end_date', 'description', 'achievements', 'is_visible', 'sort_order',
])]
class Education extends Model implements HasMedia
{
    /** @use HasFactory<EducationFactory> */
    use HasFactory, HasVisibilityAndOrder, InteractsWithMedia, RegistersImageConversions;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'achievements' => '[]',
        'is_visible' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'immutable_date',
            'end_date' => 'immutable_date',
            'achievements' => 'array',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER_OR_SVG);
        $this->addMediaCollection('certificate')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER_OR_PDF);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerImageConversions(thumb: ['logo', 'certificate'], responsive: ['logo']);
    }

    /**
     * Still studying when there is no end date.
     *
     * @return Attribute<bool, never>
     */
    protected function isCurrent(): Attribute
    {
        return Attribute::get(fn (): bool => $this->end_date === null);
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function locationLabel(): Attribute
    {
        return Attribute::get(fn (): ?string => Location::label($this->city, $this->country_code));
    }
}
