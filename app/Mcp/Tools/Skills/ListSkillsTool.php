<?php

namespace App\Mcp\Tools\Skills;

use App\Mcp\Support\Payload;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_skills')]
#[Title('List skills')]
#[Description('Lists the skill categories and every skill. Skills with a category appear in the site\'s skills section; skills without one are only used as "stack" tags on projects and experience. Reuse these exact names in a project\'s stack.')]
#[IsReadOnly]
class ListSkillsTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:255']]);

        $skills = Skill::query()
            ->with('category')
            ->withCount(['projects', 'experiences'])
            ->when($data['search'] ?? null, fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->ordered()
            ->get();

        return Response::structured([
            'categories' => SkillCategory::query()->ordered()->get()
                ->map(fn (SkillCategory $category): array => Payload::skillCategory($category))->values()->all(),
            'skills' => $skills->map(fn (Skill $skill): array => [
                ...Payload::skill($skill),
                'used_by_projects' => (int) $skill->getAttribute('projects_count'),
                'used_by_experiences' => (int) $skill->getAttribute('experiences_count'),
            ])->values()->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Only skills whose name contains this text.'),
        ];
    }
}
