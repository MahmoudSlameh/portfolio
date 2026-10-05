<?php

namespace App\Mcp\Support;

use App\Enums\CareerBranch;
use App\Enums\EmploymentType;
use App\Enums\WorkMode;
use App\Models\Experience;
use App\Support\Countries;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Input schema, validation and saving shared by create_experience and update_experience (mirrors
 * app/Filament/Resources/Experiences/Schemas/ExperienceForm.php). Dates have month precision.
 */
final class ExperienceFields
{
    private const COLUMNS = [
        'company_id', 'organization_name', 'role', 'employment_type', 'work_mode', 'country_code', 'city',
        'start_date', 'end_date', 'summary', 'highlights', 'branch', 'version', 'commit_hash', 'commit_message',
        'is_visible', 'sort_order',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function schema(JsonSchema $schema): array
    {
        return [
            'company_id' => $schema->integer()->description('The employer/client (see list_companies). Either company_id or organization_name is needed.'),
            'organization_name' => $schema->string()->description('Organisation name when it is not a company in the portfolio.'),
            'role' => $schema->string()->description('Job title, e.g. "Senior Backend Engineer".'),
            'employment_type' => $schema->string()->enum(EmploymentType::class),
            'work_mode' => $schema->string()->enum(WorkMode::class),
            'country_code' => $schema->string()->description('ISO 3166 alpha-2, e.g. "DE".'),
            'city' => $schema->string(),
            'start_date' => $schema->string()->description('Month it started: "YYYY-MM".'),
            'end_date' => $schema->string()->description('Month it ended: "YYYY-MM". null = current role.'),
            'summary' => $schema->string()->description('1–2 sentences on the role, max 1000 characters.'),
            'highlights' => $schema->array()->items($schema->string())->description('Achievements, one sentence each (replaces the list).'),
            'stack' => $schema->array()->items($schema->string())->description('Technologies used in this role, by name (replaces the stack).'),
            'branch' => $schema->string()->enum(CareerBranch::class)->description('Changelog template only: the git branch it is drawn on. Empty = derived from the employment type.'),
            'version' => $schema->string()->description('Changelog template only, e.g. "v4.0". Empty = derived.'),
            'is_visible' => $schema->boolean(),
            'sort_order' => $schema->integer()->min(0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(?Experience $experience = null): array
    {
        $creating = $experience === null;
        $month = ['date_format:Y-m', 'before_or_equal:'.now()->format('Y-m')];

        return [
            'company_id' => ['sometimes', 'nullable', 'integer', Rule::exists('companies', 'id')],
            'organization_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'role' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'employment_type' => ['sometimes', Rule::enum(EmploymentType::class)],
            'work_mode' => ['sometimes', Rule::enum(WorkMode::class)],
            'country_code' => ['sometimes', 'nullable', 'string', 'size:2', function (string $attribute, mixed $value, Closure $fail): void {
                if (is_string($value) && Countries::name($value) === null) {
                    $fail('country_code must be an ISO 3166 alpha-2 code, e.g. "DE".');
                }
            }],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'start_date' => [$creating ? 'required' : 'sometimes', ...$month],
            'end_date' => ['sometimes', 'nullable', 'date_format:Y-m'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'highlights' => ['sometimes', 'array', 'max:20'],
            'highlights.*' => ['string', 'max:500'],
            'stack' => ['sometimes', 'array', 'max:40'],
            'stack.*' => ['string', 'max:255'],
            'branch' => ['sometimes', 'nullable', Rule::enum(CareerBranch::class)],
            'version' => ['sometimes', 'nullable', 'string', 'max:50'],
            'is_visible' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data  validated input
     * @return list<string> skills created for the stack
     *
     * @throws ValidationException when the period or organisation is incomplete
     */
    public static function save(Experience $experience, array $data): array
    {
        $attributes = Arr::only($data, self::COLUMNS);

        foreach (['start_date', 'end_date'] as $column) {
            if (array_key_exists($column, $attributes)) {
                $attributes[$column] = $attributes[$column] === null ? null : CarbonImmutable::createFromFormat('!Y-m', (string) $attributes[$column])?->toDateString();
            }
        }

        if (isset($attributes['country_code'])) {
            $attributes['country_code'] = strtoupper((string) $attributes['country_code']);
        }

        $experience->fill($attributes);

        if ($experience->end_date !== null && $experience->end_date->lt($experience->start_date)) {
            throw ValidationException::withMessages(['end_date' => 'end_date must be on or after start_date.']);
        }

        if ($experience->company_id === null && blank($experience->organization_name)) {
            throw ValidationException::withMessages(['company_id' => 'Give a company_id or an organization_name.']);
        }

        return DB::transaction(function () use ($experience, $data): array {
            $experience->save();

            return array_key_exists('stack', $data) ? Stack::sync($experience, array_values((array) $data['stack'])) : [];
        });
    }
}
