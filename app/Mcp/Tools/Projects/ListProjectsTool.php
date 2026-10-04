<?php

namespace App\Mcp\Tools\Projects;

use App\Enums\ProjectCategory;
use App\Enums\ProjectStatus;
use App\Mcp\Support\Payload;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_projects')]
#[Title('List projects')]
#[Description('Lists the portfolio\'s projects (drafts included), newest first, with filters. Use it to check whether a project already exists before creating one.')]
#[IsReadOnly]
class ListProjectsTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(ProjectStatus::class)],
            'category' => ['nullable', Rule::enum(ProjectCategory::class)],
            'published' => ['nullable', 'boolean'],
            'include_deleted' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $projects = Project::query()
            ->with(['company', 'skills', 'media'])
            ->when($data['include_deleted'] ?? false, fn (Builder $query) => $query->withTrashed())
            ->when($data['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('tagline', 'like', "%{$search}%")))
            ->when($data['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($data['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->when(isset($data['published']), fn (Builder $query) => $query->where('is_published', (bool) $data['published']))
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(perPage: (int) ($data['per_page'] ?? 25), page: (int) ($data['page'] ?? 1));

        return Response::structured([
            'projects' => $projects->getCollection()->map(fn (Project $project): array => Payload::projectSummary($project))->values()->all(),
            'page' => $projects->currentPage(),
            'last_page' => $projects->lastPage(),
            'total' => $projects->total(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Matches the title, slug or tagline.'),
            'status' => $schema->string()->enum(ProjectStatus::class),
            'category' => $schema->string()->enum(ProjectCategory::class),
            'published' => $schema->boolean()->description('true = only published, false = only drafts.'),
            'include_deleted' => $schema->boolean()->description('Also list deleted projects (they can be restored with restore_project).'),
            'page' => $schema->integer()->min(1)->default(1),
            'per_page' => $schema->integer()->min(1)->max(100)->default(25),
        ];
    }
}
