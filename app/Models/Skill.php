<?php

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use App\Models\Concerns\HasVisibilityAndOrder;
use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * A technology. With a category it is listed in the skills section; without one it is only used
 * as a "stack" tag on experiences and projects.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $skill_category_id
 * @property int|null $proficiency 1–5
 * @property int|null $years
 * @property string|null $icon brand icon key (simple-icons slug)
 * @property bool $is_visible
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SkillCategory|null $category
 */
#[Fillable(['name', 'slug', 'skill_category_id', 'proficiency', 'years', 'icon', 'is_visible', 'sort_order'])]
class Skill extends Model implements HasMedia
{
    /** @use HasFactory<SkillFactory> */
    use GeneratesSlug, HasFactory, HasVisibilityAndOrder, InteractsWithMedia;

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
            'proficiency' => 'integer',
            'years' => 'integer',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('icon')->singleFile()->acceptsMimeTypes(['image/svg+xml', 'image/png', 'image/webp']);
    }

    /**
     * @return BelongsTo<SkillCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class, 'skill_category_id');
    }

    /**
     * @return MorphToMany<Experience, $this>
     */
    public function experiences(): MorphToMany
    {
        return $this->morphedByMany(Experience::class, 'skillable');
    }

    /**
     * @return MorphToMany<Project, $this>
     */
    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'skillable');
    }
}
