<?php

namespace App\Mcp\Tools\Experiences;

use App\Models\Experience;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_experience')]
#[Title('Delete work experience')]
#[Description('Deletes a role for good. Projects linked to it are kept but lose the link. Only when the owner asked for it.')]
#[IsDestructive]
class DeleteExperienceTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $id = $request->validate(['id' => ['required', 'integer']])['id'];
        $experience = Experience::query()->with('company')->find($id);

        if ($experience === null) {
            return Response::error("No experience has id {$id}. Call list_experiences to find it.");
        }

        $experience->delete();

        return Response::structured(['message' => "Deleted \"{$experience->role}\" at {$experience->organization}."]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The experience id (from list_experiences).')->required(),
        ];
    }
}
