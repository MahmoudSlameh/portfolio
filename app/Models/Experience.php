<?php

namespace App\Models;

use App\Enums\CareerBranch;
use App\Enums\EmploymentType;
use App\Enums\WorkMode;
use App\Models\Concerns\HasSkills;
use App\Models\Concerns\HasVisibilityAndOrder;
use App\Support\Content\ChangelogMetadata;
use App\Support\Content\Location;
use Carbon\CarbonImmutable;
use Database\Factories\ExperienceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A role in the owner's career (job, freelance period, open-source project, internship…).
 *
 * @property int $id
 * @property int|null $company_id
 * @property string|null $organization_name
 * @property string $role
 * @property EmploymentType $employment_type
 * @property WorkMode $work_mode
 * @property string|null $country_code
 * @property string|null $city
 * @property string|null $address
 * @property CarbonImmutable $start_date
 * @property CarbonImmutable|null $end_date
 * @property string|null $summary
 * @property list<string> $highlights
 * @property CareerBranch|null $branch
 * @property string|null $version
 * @property string|null $commit_hash
 * @property string|null $commit_message
 * @property bool $is_visible
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Company|null $company
 * @property-read Collection<int, Project> $projects
 * @property-read Collection<int, Skill> $skills
 * @property-read list<string> $stack
 * @property-read bool $is_current
 * @property-read string $organization
 * @property-read string|null $location_label
 * @property-read CareerBranch $resolved_branch
 * @property-read string $resolved_commit
 */
#[Fillable([
    'company_id', 'organization_name', 'role', 'employment_type', 'work_mode', 'country_code', 'city',
    'address', 'start_date', 'end_date', 'summary', 'highlights', 'branch', 'version', 'commit_hash',
    'commit_message', 'is_visible', 'sort_order',
])]
class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory, HasSkills, HasVisibilityAndOrder;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'employment_type' => 'full-time',
        'work_mode' => 'on-site',
        'highlights' => '[]',
        'is_visible' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'work_mode' => WorkMode::class,
            'start_date' => 'immutable_date',
            'end_date' => 'immutable_date',
            'highlights' => 'array',
            'branch' => CareerBranch::class,
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * A role without an end date is the current one.
     *
     * @return Attribute<bool, never>
     */
    protected function isCurrent(): Attribute
    {
        return Attribute::get(fn (): bool => $this->end_date === null);
    }

    /**
     * Display name of the organization: the linked company, or the free-text name.
     *
     * @return Attribute<string, never>
     */
    protected function organization(): Attribute
    {
        return Attribute::get(fn (): string => $this->company->name ?? (string) $this->organization_name);
    }

    /**
     * "Amsterdam, Netherlands · Remote" (empty parts are skipped).
     *
     * @return Attribute<string|null, never>
     */
    protected function locationLabel(): Attribute
    {
        return Attribute::get(fn (): ?string => Location::label($this->city, $this->country_code, $this->work_mode->getLabel()));
    }

    /**
     * @return Attribute<CareerBranch, never>
     */
    protected function resolvedBranch(): Attribute
    {
        return Attribute::get(fn (): CareerBranch => $this->branch ?? CareerBranch::forEmploymentType($this->employment_type));
    }

    /**
     * The overridden commit hash, or a stable 7-character hash derived from the role.
     *
     * @return Attribute<string, never>
     */
    protected function resolvedCommit(): Attribute
    {
        return Attribute::get(fn (): string => $this->commit_hash
            ?? substr(sha1("{$this->id}:{$this->role}:{$this->organization}"), 0, 7));
    }

    /**
     * @var array{branch: CareerBranch, version: string, commit: string, message: string}|null
     */
    protected ?array $changelogMetadata = null;

    /**
     * Attach metadata computed for the whole career (see ChangelogMetadata::for()).
     *
     * @param  array{branch: CareerBranch, version: string, commit: string, message: string}  $metadata
     */
    public function withChangelog(array $metadata): static
    {
        $this->changelogMetadata = $metadata;

        return $this;
    }

    /**
     * Changelog metadata attached for the whole career, or computed for this role alone.
     *
     * @return array{branch: CareerBranch, version: string, commit: string, message: string}
     */
    public function changelog(): array
    {
        return $this->changelogMetadata ?? ChangelogMetadata::for([$this])[$this->id];
    }
}
