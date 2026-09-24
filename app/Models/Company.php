<?php

namespace App\Models;

use App\Enums\CompanyKind;
use App\Enums\WordmarkStyle;
use App\Models\Concerns\HasVisibilityAndOrder;
use App\Models\Concerns\RegistersImageConversions;
use App\Support\Content\Location;
use App\Support\Media\MimeTypes;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * An employer or client — shown on the clients wall and linked from experiences, projects and testimonials.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property CompanyKind $kind
 * @property string|null $website_url
 * @property string|null $industry
 * @property string|null $city
 * @property string|null $country_code
 * @property string|null $period_label
 * @property string|null $engagement
 * @property WordmarkStyle $wordmark_style
 * @property bool $is_featured
 * @property bool $is_visible
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Experience> $experiences
 * @property-read Collection<int, Testimonial> $testimonials
 * @property-read string|null $location_label
 * @property-read string|null $resolved_period
 */
#[Fillable([
    'name', 'slug', 'kind', 'website_url', 'industry', 'city', 'country_code', 'period_label',
    'engagement', 'wordmark_style', 'is_featured', 'is_visible', 'sort_order',
])]
#[RouteKey('slug')]
class Company extends Model implements HasMedia
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, HasVisibilityAndOrder, InteractsWithMedia, RegistersImageConversions;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'kind' => 'employer',
        'wordmark_style' => 'sans-bold',
        'is_featured' => true,
        'is_visible' => true,
        'sort_order' => 0,
    ];

    protected static function booted(): void
    {
        static::saving(function (Company $company): void {
            if (blank($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => CompanyKind::class,
            'wordmark_style' => WordmarkStyle::class,
            'is_featured' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->acceptsMimeTypes(MimeTypes::LOGO);
        $this->addMediaCollection('logo_dark')->singleFile()->acceptsMimeTypes(MimeTypes::LOGO);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerImageConversions(thumb: ['logo', 'logo_dark'], responsive: ['logo', 'logo_dark']);
    }

    /**
     * @return HasMany<Experience, $this>
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /**
     * @return HasMany<Testimonial, $this>
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * "Berlin, Germany" (either part may be missing).
     *
     * @return Attribute<string|null, never>
     */
    protected function locationLabel(): Attribute
    {
        return Attribute::get(fn (): ?string => Location::label($this->city, $this->country_code));
    }

    /**
     * The manual period label, or one derived from the linked experiences ("2021 — 2024", "2024 — now").
     *
     * @return Attribute<string|null, never>
     */
    protected function resolvedPeriod(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (filled($this->period_label)) {
                return $this->period_label;
            }

            $experiences = $this->experiences;

            if ($experiences->isEmpty()) {
                return null;
            }

            $startYear = $experiences->min(fn (Experience $experience): int => $experience->start_date->year);
            $endYear = $experiences->contains(fn (Experience $experience): bool => $experience->is_current)
                ? 'now'
                : (string) $experiences->max(fn (Experience $experience): int => $experience->end_date->year ?? 0);

            return (string) $startYear === $endYear ? $endYear : "{$startYear} — {$endYear}";
        });
    }
}
