<?php

namespace App\Mcp\Tools\Skills;

use App\Mcp\Support\Payload;
use App\Models\SkillCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('save_skill_category')]
#[Title('Create or update a skill category')]
#[Description('Creates a skill category (a group in the site\'s skills section), or updates it when "id" is given or one with the same name exists.')]
#[IsIdempotent]
class SaveSkillCategoryTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer'],
            'name' => [Rule::requiredIf(blank($request->get('id'))), 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        $category = filled($data['id'] ?? null)
            ? SkillCategory::query()->find((int) $data['id'])
            : SkillCategory::query()->whereRaw('lower(name) = ?', [Str::lower((string) $data['name'])])->first();

        if (filled($data['id'] ?? null) && $category === null) {
            return Response::error("No skill category has id {$data['id']}. Call list_skills to find it.");
        }

        $category ??= new SkillCategory(['sort_order' => (int) SkillCategory::query()->max('sort_order') + 1]);
        $creating = ! $category->exists;
        $columns = $creating || filled($data['id'] ?? null) ? ['name'] : [];
        $category->fill(Arr::only($data, [...$columns, 'description', 'sort_order']))->save();

        return Response::structured([
            'message' => ($creating ? 'Created' : 'Updated')." skill category \"{$category->name}\".",
            'category' => Payload::skillCategory($category),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Update this category (from list_skills).'),
            'name' => $schema->string(),
            'description' => $schema->string(),
            'sort_order' => $schema->integer()->min(0)->description('Lower comes first.'),
        ];
    }
}
