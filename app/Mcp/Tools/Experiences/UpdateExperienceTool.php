<?php

namespace App\Mcp\Tools\Experiences;

use App\Mcp\Support\ExperienceFields;
use App\Mcp\Support\Payload;
use App\Models\Experience;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('update_experience')]
#[Title('Update work experience')]
#[Description('Changes a role. Send only the fields to change; highlights and stack replace the stored lists.')]
#[IsIdempotent]
class UpdateExperienceTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $id = $request->validate(['id' => ['required', 'integer']])['id'];
        $experience = Experience::query()->find($id);

        if ($experience === null) {
            return Response::error("No experience has id {$id}. Call list_experiences to find it.");
        }

        $data = Arr::except($request->validate(ExperienceFields::rules($experience)), ['id']);

        if ($data === []) {
            return Response::error('Nothing to update: send at least one field to change.');
        }

        $createdSkills = ExperienceFields::save($experience, $data);

        return Response::structured(array_filter([
            'message' => "Updated \"{$experience->role}\": ".implode(', ', array_keys($data)).'.',
            'created_skills' => $createdSkills ?: null,
            'experience' => Payload::experience($experience->refresh()),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The experience id (from list_experiences).')->required(),
            ...ExperienceFields::schema($schema),
        ];
    }
}
