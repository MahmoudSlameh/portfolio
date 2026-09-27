<?php

namespace App\Models;

use App\Enums\StudioSource;
use App\Enums\StudioStatus;
use App\Support\Media\MimeTypes;
use App\Support\Studio\CssSanitizer;
use App\Support\Studio\InvalidSpecException;
use App\Support\Studio\SpecValidator;
use App\Support\Templates\TemplateRegistry;
use Database\Factories\StudioTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * A template stored in the database as versions of a Template Spec and rendered by the `studio`
 * engine (docs/12-ai-templates.md). Its public template id is `studio:<ulid>`.
 *
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property StudioSource $source
 * @property StudioStatus $status
 * @property int $progress
 * @property string|null $current_step
 * @property string|null $error
 * @property int|null $active_version_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, StudioTemplateVersion> $versions
 * @property-read Collection<int, StudioGeneration> $generations
 * @property-read StudioTemplateVersion|null $activeVersion
 */
#[Fillable(['name', 'description', 'source', 'status', 'progress', 'current_step', 'error'])]
class StudioTemplate extends Model implements HasMedia
{
    /** @use HasFactory<StudioTemplateFactory> */
    use HasFactory, HasUlids, InteractsWithMedia;

    public const ID_PREFIX = 'studio:';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'source' => 'manual',
        'status' => 'draft',
        'progress' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => StudioSource::class,
            'status' => StudioStatus::class,
            'progress' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // The template registry lists ready studio templates; keep it in sync.
        $forget = fn () => app(TemplateRegistry::class)->forgetStudioTemplates();
        static::saved($forget);
        static::deleted($forget);
    }

    public function registerMediaCollections(): void
    {
        // Reference images for AI generation stay private.
        $this->addMediaCollection('reference')->useDisk('local')->acceptsMimeTypes(MimeTypes::RASTER);
        $this->addMediaCollection('screenshot')->singleFile()->acceptsMimeTypes(MimeTypes::RASTER);
    }

    /**
     * AI generation attempts for this template, newest first.
     *
     * @return HasMany<StudioGeneration, $this>
     */
    public function generations(): HasMany
    {
        return $this->hasMany(StudioGeneration::class)->latest('id');
    }

    /**
     * @return HasMany<StudioTemplateVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(StudioTemplateVersion::class)->orderBy('number');
    }

    /**
     * @return BelongsTo<StudioTemplateVersion, $this>
     */
    public function activeVersion(): BelongsTo
    {
        return $this->belongsTo(StudioTemplateVersion::class, 'active_version_id');
    }

    /**
     * Templates the site can render: ready, with an active version.
     *
     * @param  Builder<StudioTemplate>  $query
     */
    #[Scope]
    protected function renderable(Builder $query): void
    {
        $query->where('status', StudioStatus::Ready)->whereNotNull('active_version_id');
    }

    /**
     * The public template id (`studio:<ulid>`), as stored in site_settings.active_template.
     */
    public function templateId(): string
    {
        return self::ID_PREFIX.$this->id;
    }

    /**
     * Validate, sanitise and store a new version of the spec. The first version becomes active and
     * marks the template ready; later versions (refinements) must be activated explicitly.
     *
     * @param  array<string, mixed>  $spec
     * @param  array{prompt?: string|null, parent?: StudioTemplateVersion|null, provider?: string|null, model?: string|null, input_tokens?: int|null, output_tokens?: int|null}  $meta
     *
     * @throws InvalidSpecException when the spec is invalid (nothing is stored)
     */
    public function addVersion(array $spec, array $meta = []): StudioTemplateVersion
    {
        (new SpecValidator)->validate($spec)->throw();

        $notes = [];

        if (isset($spec['css']) && is_string($spec['css'])) {
            $result = app(CssSanitizer::class)->sanitize($spec['css']);
            $notes = array_map(fn (string $removed): string => "CSS removed: {$removed}", $result->removed);
            $spec['css'] = $result->css;

            if ($result->css === '') {
                unset($spec['css']);
            }
        }

        return DB::transaction(function () use ($spec, $notes, $meta): StudioTemplateVersion {
            $version = $this->versions()->create([
                'number' => (int) $this->versions()->max('number') + 1,
                'spec' => $spec,
                'notes' => $notes === [] ? null : $notes,
                'prompt' => $meta['prompt'] ?? null,
                'parent_id' => ($meta['parent'] ?? null)?->id,
                'provider' => $meta['provider'] ?? null,
                'model' => $meta['model'] ?? null,
                'input_tokens' => $meta['input_tokens'] ?? null,
                'output_tokens' => $meta['output_tokens'] ?? null,
            ]);

            if ($this->active_version_id === null) {
                $this->activate($version);
            }

            return $version;
        });
    }

    /**
     * Render this version on the site (roll forward or back).
     *
     * @throws InvalidArgumentException when the version belongs to another template
     */
    public function activate(StudioTemplateVersion $version): void
    {
        if ($version->studio_template_id !== $this->id) {
            throw new InvalidArgumentException('That version belongs to another template.');
        }

        $this->forceFill([
            'active_version_id' => $version->id,
            'status' => StudioStatus::Ready,
            'progress' => 100,
            'current_step' => null,
            'error' => null,
        ])->save();

        $this->setRelation('activeVersion', $version);
    }
}
