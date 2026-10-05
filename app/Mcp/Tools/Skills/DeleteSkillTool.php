<?php

namespace App\Mcp\Tools\Skills;

use App\Models\Skill;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_skill')]
#[Title('Delete a skill')]
#[Description('Deletes a skill for good and removes it from every project and experience stack. Only when the owner asked for it.')]
#[IsDestructive]
class DeleteSkillTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate(['id' => ['required', 'integer']]);

        $skill = Skill::query()->withCount(['projects', 'experiences'])->find((int) $data['id']);

        if ($skill === null) {
            return Response::error("No skill has id {$data['id']}. Call list_skills to find it.");
        }

        $skill->delete();

        return Response::structured([
            'message' => "Deleted skill \"{$skill->name}\" (it was in ".(int) $skill->getAttribute('projects_count').' project and '.(int) $skill->getAttribute('experiences_count').' experience stacks).',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The skill id (from list_skills).')->required(),
        ];
    }
}
