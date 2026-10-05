<?php

namespace App\Mcp\Support;

use App\Enums\ArchitectureNodeKind;
use App\Enums\ProjectCategory;
use App\Enums\ProjectLinkKind;
use App\Enums\ProjectStatus;
use App\Models\Project;
use Closure;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Input schema, validation and saving shared by the create_project and update_project tools. The fields
 * mirror the panel's project form (app/Filament/Resources/Projects/Schemas/ProjectForm.php).
 */
final class ProjectFields
{
    /**
     * Columns written as given (lists replace the stored list).
     */
    private const COLUMNS = [
        'title', 'slug', 'tagline', 'summary', 'year', 'version', 'status', 'category', 'is_featured',
        'is_published', 'published_at', 'company_id', 'experience_id', 'role', 'team', 'timeline',
        'overview', 'problem', 'approach', 'features', 'challenges', 'architecture', 'metrics', 'links',
        'cover_alt', 'meta_title', 'meta_description',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function schema(JsonSchema $schema): array
    {
        $titled = fn (string $what): mixed => $schema->array()->items($schema->object([
            'title' => $schema->string()->required(),
            'description' => $schema->string()->required(),
        ]))->description($what);

        return [
            'title' => $schema->string()->description('Project name, e.g. "Ledger".'),
            'slug' => $schema->string()->description('URL slug (/projects/<slug>). Generated from the title when omitted.'),
            'tagline' => $schema->string()->description('One sentence under the title, max 255 characters: what it is and why it matters.'),
            'summary' => $schema->string()->description('2–3 sentences for cards and search results, max 1000 characters.'),
            'year' => $schema->integer()->description('Year the project shipped or started. Defaults to this year.'),
            'version' => $schema->string()->description('Current version, e.g. "v3.2.0" (from the latest git tag or release).'),
            'status' => $schema->string()->enum(ProjectStatus::class)->description('live = in production, maintained = still updated, archived = no longer maintained, in-progress = not shipped yet.'),
            'category' => $schema->string()->enum(ProjectCategory::class)->description('platform, product, open-source or design-system.'),
            'is_featured' => $schema->boolean()->description('Show on the home page.'),
            'is_published' => $schema->boolean()->description('Visible on the public site. New projects are drafts unless the owner asked to publish.'),
            'published_at' => $schema->string()->description('ISO 8601 date-time; a future date schedules the project. Empty = publish immediately when is_published is true.'),
            'company_id' => $schema->integer()->description('Company or client the project was built for (see list_companies). null clears it.'),
            'experience_id' => $schema->integer()->description('The role (work experience) it belongs to (see list_experiences). null clears it.'),
            'role' => $schema->string()->description('The owner\'s role on the project, e.g. "Tech lead & architect".'),
            'team' => $schema->string()->description('Team size/shape, e.g. "7 engineers, 1 PM".'),
            'timeline' => $schema->string()->description('e.g. "Mar 2024 — ongoing" (from the first and last commits).'),
            'stack' => $schema->array()->items($schema->string())->description('Technologies in order of importance, by name (e.g. ["Laravel", "React", "PostgreSQL"]). Existing skills are reused; missing ones are created. Replaces the whole stack.'),
            'overview' => $schema->array()->items($schema->string())->description('Case study "Overview": 1–3 paragraphs.'),
            'problem' => $schema->array()->items($schema->string())->description('Case study "Problem": 1–3 paragraphs on what needed solving.'),
            'approach' => $titled('Case study "Approach": the steps taken, each {title, description}.'),
            'features' => $titled('Key features, each {title, description}.'),
            'challenges' => $titled('Hard problems and how they were solved, each {title, description}.'),
            'architecture' => $schema->object([
                'caption' => $schema->string()->description('One line under the diagram.'),
                'columns' => $schema->integer()->min(1)->max(6)->required(),
                'rows' => $schema->integer()->min(1)->max(6)->required(),
                'nodes' => $schema->array()->items($schema->object([
                    'id' => $schema->string()->description('Short unique id, letters/numbers/dashes, e.g. "api".')->required(),
                    'label' => $schema->string()->required(),
                    'detail' => $schema->string()->description('e.g. "Laravel 11 · PHP 8.3"'),
                    'kind' => $schema->string()->enum(ArchitectureNodeKind::class)->required(),
                    'column' => $schema->integer()->min(1)->max(6)->required(),
                    'row' => $schema->integer()->min(1)->max(6)->required(),
                ]))->required(),
                'edges' => $schema->array()->items($schema->object([
                    'from' => $schema->string()->description('Node id')->required(),
                    'to' => $schema->string()->description('Node id')->required(),
                    'label' => $schema->string()->description('e.g. "REST", "events"'),
                ])),
            ])->description('Architecture diagram on a grid (column × row, max 6 × 6). null removes it.'),
            'metrics' => $schema->array()->items($schema->object([
                'value' => $schema->string()->description('e.g. "12 min"')->required(),
                'label' => $schema->string()->description('e.g. "close time"')->required(),
                'detail' => $schema->string()->description('e.g. "down from 9 hours"'),
            ]))->description('Results in numbers. Only real figures, never invented ones.'),
            'links' => $schema->array()->items($schema->object([
                'label' => $schema->string()->required(),
                'url' => $schema->string()->required(),
                'kind' => $schema->string()->enum(ProjectLinkKind::class)->required(),
            ]))->description('External links: live site, source code, write-up, talk.'),
            'cover_alt' => $schema->string()->description('Alt text of the cover image.'),
            'meta_title' => $schema->string()->description('SEO title override, ≤ 60 characters.'),
            'meta_description' => $schema->string()->description('SEO description override, ≤ 160 characters.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(?Project $project = null): array
    {
        $creating = $project === null;
        $text = fn (int $max): array => ['sometimes', 'nullable', 'string', "max:{$max}"];
        $titled = fn (string $name): array => [
            $name => ['sometimes', 'array', 'max:20'],
            "{$name}.*.title" => ['required', 'string', 'max:255'],
            "{$name}.*.description" => ['required', 'string', 'max:5000'],
        ];

        return [
            'title' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'alpha_dash', 'max:255', Rule::unique('projects', 'slug')->ignore($project?->id)],
            'tagline' => $text(255),
            'summary' => $text(1000),
            'year' => ['sometimes', 'integer', 'min:1990', 'max:'.((int) date('Y') + 1)],
            'version' => $text(50),
            'status' => ['sometimes', Rule::enum(ProjectStatus::class)],
            'category' => ['sometimes', Rule::enum(ProjectCategory::class)],
            'is_featured' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'company_id' => ['sometimes', 'nullable', 'integer', Rule::exists('companies', 'id')],
            'experience_id' => ['sometimes', 'nullable', 'integer', Rule::exists('experiences', 'id')],
            'role' => $text(255),
            'team' => $text(255),
            'timeline' => $text(255),
            'stack' => ['sometimes', 'array', 'max:40'],
            'stack.*' => ['string', 'max:255'],
            'overview' => ['sometimes', 'array', 'max:10'],
            'overview.*' => ['string', 'max:5000'],
            'problem' => ['sometimes', 'array', 'max:10'],
            'problem.*' => ['string', 'max:5000'],
            ...$titled('approach'),
            ...$titled('features'),
            ...$titled('challenges'),
            'architecture' => ['sometimes', 'nullable', 'array', self::architectureRule()],
            'architecture.caption' => ['nullable', 'string', 'max:255'],
            'architecture.columns' => ['required_with:architecture', 'integer', 'min:1', 'max:6'],
            'architecture.rows' => ['required_with:architecture', 'integer', 'min:1', 'max:6'],
            'architecture.nodes' => ['required_with:architecture', 'array', 'max:36'],
            'architecture.nodes.*.id' => ['required', 'string', 'alpha_dash', 'max:50', 'distinct'],
            'architecture.nodes.*.label' => ['required', 'string', 'max:100'],
            'architecture.nodes.*.detail' => ['nullable', 'string', 'max:150'],
            'architecture.nodes.*.kind' => ['required', Rule::enum(ArchitectureNodeKind::class)],
            'architecture.nodes.*.column' => ['required', 'integer', 'min:1', 'max:6'],
            'architecture.nodes.*.row' => ['required', 'integer', 'min:1', 'max:6'],
            'architecture.edges' => ['nullable', 'array', 'max:60'],
            'architecture.edges.*.from' => ['required', 'string'],
            'architecture.edges.*.to' => ['required', 'string'],
            'architecture.edges.*.label' => ['nullable', 'string', 'max:50'],
            'metrics' => ['sometimes', 'array', 'max:12'],
            'metrics.*.value' => ['required', 'string', 'max:50'],
            'metrics.*.label' => ['required', 'string', 'max:100'],
            'metrics.*.detail' => ['nullable', 'string', 'max:255'],
            'links' => ['sometimes', 'array', 'max:12'],
            'links.*.label' => ['required', 'string', 'max:100'],
            'links.*.url' => ['required', 'string', 'url:http,https', 'max:2048'],
            'links.*.kind' => ['required', Rule::enum(ProjectLinkKind::class)],
            'cover_alt' => $text(255),
            'meta_title' => $text(70),
            'meta_description' => $text(255),
        ];
    }

    /**
     * Saves the validated input on the project.
     *
     * @param  array<string, mixed>  $data  validated input
     * @return list<string> skills created for the stack
     */
    public static function save(Project $project, array $data): array
    {
        $attributes = Arr::only($data, self::COLUMNS);

        foreach (['tagline', 'summary', 'version', 'role', 'team', 'timeline', 'cover_alt', 'meta_title', 'meta_description', 'slug'] as $column) {
            if (array_key_exists($column, $attributes) && is_string($attributes[$column])) {
                $attributes[$column] = trim($attributes[$column]) === '' ? null : trim($attributes[$column]);
            }
        }

        if (array_key_exists('architecture', $attributes) && is_array($attributes['architecture'])) {
            $attributes['architecture'] = self::normalizeArchitecture($attributes['architecture']);
        }

        foreach (['metrics', 'links'] as $list) {
            if (isset($attributes[$list]) && is_array($attributes[$list])) {
                $attributes[$list] = array_values(array_map(
                    fn (array $item): array => $list === 'metrics'
                        ? ['value' => $item['value'], 'label' => $item['label'], 'detail' => (string) ($item['detail'] ?? '')]
                        : ['label' => $item['label'], 'url' => $item['url'], 'kind' => $item['kind']],
                    $attributes[$list],
                ));
            }
        }

        if (! $project->exists && ! array_key_exists('year', $attributes)) {
            $attributes['year'] = (int) date('Y');
        }

        return DB::transaction(function () use ($project, $attributes, $data): array {
            $project->fill($attributes)->save();

            return array_key_exists('stack', $data) ? Stack::sync($project, array_values((array) $data['stack'])) : [];
        });
    }

    /**
     * Every edge must connect two existing nodes, and every node must fit in the grid.
     */
    private static function architectureRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_array($value)) {
                return;
            }

            $ids = array_column((array) ($value['nodes'] ?? []), 'id');

            foreach ((array) ($value['nodes'] ?? []) as $node) {
                if (is_array($node) && (($node['column'] ?? 0) > ($value['columns'] ?? 6) || ($node['row'] ?? 0) > ($value['rows'] ?? 6))) {
                    $fail('Architecture node "'.($node['id'] ?? '?').'" is outside the grid ('.($value['columns'] ?? '?').' columns × '.($value['rows'] ?? '?').' rows).');
                }
            }

            foreach ((array) ($value['edges'] ?? []) as $edge) {
                foreach (['from', 'to'] as $end) {
                    if (is_array($edge) && ! in_array($edge[$end] ?? null, $ids, true)) {
                        $fail("Architecture edge {$end} \"".($edge[$end] ?? '').'" is not a node id.');
                    }
                }
            }
        };
    }

    /**
     * The shape the panel form and the templates use.
     *
     * @param  array<string, mixed>  $architecture
     * @return array<string, mixed>
     */
    private static function normalizeArchitecture(array $architecture): array
    {
        return [
            'caption' => (string) ($architecture['caption'] ?? ''),
            'columns' => (int) $architecture['columns'],
            'rows' => (int) $architecture['rows'],
            'nodes' => array_values(array_map(fn (array $node): array => [
                'id' => (string) $node['id'],
                'label' => (string) $node['label'],
                'detail' => (string) ($node['detail'] ?? ''),
                'kind' => (string) $node['kind'],
                'column' => (int) $node['column'],
                'row' => (int) $node['row'],
            ], (array) $architecture['nodes'])),
            'edges' => array_values(array_map(fn (array $edge): array => array_filter([
                'from' => (string) $edge['from'],
                'to' => (string) $edge['to'],
                'label' => $edge['label'] ?? null,
            ], fn (mixed $value): bool => $value !== null), (array) ($architecture['edges'] ?? []))),
        ];
    }
}
