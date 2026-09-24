<?php

namespace App\Models;

use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\RegistersImageConversions;
use App\Support\Media\MimeTypes;
use Carbon\CarbonImmutable;
use Database\Factories\CertificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property string $name
 * @property string $issuer
 * @property CarbonImmutable $issued_at
 * @property CarbonImmutable|null $expires_at
 * @property string|null $credential_id
 * @property string|null $credential_url
 * @property bool $is_visible
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read bool $is_expired
 */
#[Fillable(['name', 'issuer', 'issued_at', 'expires_at', 'credential_id', 'credential_url', 'is_visible', 'sort_order'])]
class Certification extends Model implements HasMedia
{
    /** @use HasFactory<CertificationFactory> */
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
            'issued_at' => 'immutable_date',
            'expires_at' => 'immutable_date',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('badge')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER_OR_SVG);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerImageConversions(thumb: ['badge'], responsive: ['badge']);
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function isExpired(): Attribute
    {
        return Attribute::get(fn (): bool => $this->expires_at !== null && $this->expires_at->isPast());
    }
}
