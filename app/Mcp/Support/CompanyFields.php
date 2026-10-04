<?php

namespace App\Mcp\Support;

use App\Enums\CompanyKind;
use App\Enums\WordmarkStyle;
use App\Models\Company;
use App\Support\Countries;
use Closure;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * Input schema, validation and saving shared by create_company and update_company (mirrors
 * app/Filament/Resources/Companies/Schemas/CompanyForm.php).
 */
final class CompanyFields
{
    private const COLUMNS = [
        'name', 'slug', 'kind', 'website_url', 'industry', 'city', 'country_code', 'period_label',
        'engagement', 'wordmark_style', 'is_featured', 'is_visible', 'sort_order',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string(),
            'slug' => $schema->string()->description('Generated from the name when omitted.'),
            'kind' => $schema->string()->enum(CompanyKind::class)->description('employer (the owner worked there) or client (the owner worked for them).'),
            'website_url' => $schema->string(),
            'industry' => $schema->string()->description('e.g. "Fintech"'),
            'city' => $schema->string(),
            'country_code' => $schema->string()->description('ISO 3166 alpha-2, e.g. "DE".'),
            'period_label' => $schema->string()->description('e.g. "2021 — 2024". Empty = derived from the experiences.'),
            'engagement' => $schema->string()->description('One or two sentences on the work done for them.'),
            'wordmark_style' => $schema->string()->enum(WordmarkStyle::class)->description('How the name is drawn on the clients wall when there is no logo.'),
            'is_featured' => $schema->boolean()->description('Show on the clients wall.'),
            'is_visible' => $schema->boolean()->description('Show on the site.'),
            'sort_order' => $schema->integer()->min(0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(?Company $company = null): array
    {
        $text = ['sometimes', 'nullable', 'string', 'max:255'];

        return [
            'name' => [$company === null ? 'required' : 'sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'alpha_dash', 'max:255', Rule::unique('companies', 'slug')->ignore($company?->id)],
            'kind' => ['sometimes', Rule::enum(CompanyKind::class)],
            'website_url' => ['sometimes', 'nullable', 'string', 'url:http,https', 'max:2048'],
            'industry' => $text,
            'city' => $text,
            'country_code' => ['sometimes', 'nullable', 'string', 'size:2', function (string $attribute, mixed $value, Closure $fail): void {
                if (is_string($value) && Countries::name($value) === null) {
                    $fail('country_code must be an ISO 3166 alpha-2 code, e.g. "DE".');
                }
            }],
            'period_label' => $text,
            'engagement' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'wordmark_style' => ['sometimes', Rule::enum(WordmarkStyle::class)],
            'is_featured' => ['sometimes', 'boolean'],
            'is_visible' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data  validated input
     */
    public static function save(Company $company, array $data): void
    {
        $attributes = Arr::only($data, self::COLUMNS);

        if (isset($attributes['country_code'])) {
            $attributes['country_code'] = strtoupper((string) $attributes['country_code']);
        }

        $company->fill($attributes)->save();
    }
}
