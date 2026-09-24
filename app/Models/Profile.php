<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use App\Models\Concerns\IsSingleton;
use App\Models\Concerns\RegistersImageConversions;
use App\Support\Media\MimeTypes;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * The site owner's public profile and bio (single row).
 *
 * @property int $id
 * @property string $name
 * @property string|null $initials
 * @property string $role
 * @property string|null $headline
 * @property list<string> $focus_areas
 * @property string|null $summary
 * @property list<string> $story
 * @property string|null $location
 * @property string $timezone
 * @property string|null $timezone_label
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $current_version
 * @property AvailabilityStatus $availability_status
 * @property string|null $availability_label
 * @property string|null $availability_note
 * @property array{added: list<string>, changed: list<string>, removed: list<string>} $latest_release
 * @property list<array{value: string, label: string}> $stats
 * @property list<array{label: string, value: string, tone: string}> $status
 * @property list<array{title: string, body: string}> $principles
 * @property string|null $portrait_alt
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $resolved_initials
 */
#[Fillable([
    'name', 'initials', 'role', 'headline', 'focus_areas', 'summary', 'story', 'location',
    'timezone', 'timezone_label', 'email', 'phone', 'current_version', 'availability_status',
    'availability_label', 'availability_note', 'latest_release', 'stats', 'status', 'principles',
    'portrait_alt',
])]
class Profile extends Model implements HasMedia
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory, InteractsWithMedia, IsSingleton, RegistersImageConversions;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'timezone' => 'UTC',
        'availability_status' => 'open',
        'focus_areas' => '[]',
        'story' => '[]',
        'latest_release' => '{"added":[],"changed":[],"removed":[]}',
        'stats' => '[]',
        'status' => '[]',
        'principles' => '[]',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'focus_areas' => 'array',
            'story' => 'array',
            'availability_status' => AvailabilityStatus::class,
            'latest_release' => 'array',
            'stats' => 'array',
            'status' => 'array',
            'principles' => 'array',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function singletonDefaults(): array
    {
        return [
            'name' => config('app.name'),
            'role' => 'Software Engineer',
            'timezone' => config('app.timezone'),
            'availability_status' => AvailabilityStatus::Open,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portrait')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER);
        $this->addMediaCollection('resume')->singleFile()->acceptsMimeTypes(MimeTypes::PDF);
        $this->addMediaCollection('og_image')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerImageConversions(
            thumb: ['portrait', 'og_image'],
            responsive: ['portrait'],
            og: ['portrait', 'og_image'],
        );
    }

    /**
     * Initials entered by the owner, or derived from the name ("Mahmoud Slameh" → "MS").
     *
     * @return Attribute<string, never>
     */
    protected function resolvedInitials(): Attribute
    {
        return Attribute::get(function (): string {
            if (filled($this->initials)) {
                return Str::upper($this->initials);
            }

            return Str::of($this->name)
                ->explode(' ')
                ->filter()
                ->take(2)
                ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
                ->implode('');
        });
    }
}
