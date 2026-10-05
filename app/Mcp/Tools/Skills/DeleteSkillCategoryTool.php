<?php

namespace App\Mcp\Tools\Skills;

use App\Models\SkillCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_skill_category')]
#[Title('Delete a skill category')]
#[Description('Deletes a skill category. Its skills are kept, without a category (they leave the skills section but stay in stacks).')]
#[IsDestructive]
class DeleteSkillCategoryTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate(['id' => ['required', 'integer']]);

        $category = SkillCategory::query()->withCount('skills')->find((int) $data['id']);

        if ($category === null) {
            return Response::error("No skill category has id {$data['id']}. Call list_skills to find it.");
        }

        $category->delete();

        return Response::structured([
            'message' => "Deleted skill category \"{$category->name}\"; its ".(int) $category->getAttribute('skills_count').' skills now have no category.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The category id (from list_skills).')->required(),
        ];
    }
}
