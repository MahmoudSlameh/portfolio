<?php

namespace App\Mcp\Tools\Experiences;

use App\Mcp\Support\ExperienceFields;
use App\Mcp\Support\Payload;
use App\Models\Experience;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_experience')]
#[Title('Add work experience')]
#[Description('Adds a role to the career timeline. Needs role, start_date ("YYYY-MM") and a company_id or organization_name. Leave end_date empty for the current role.')]
class CreateExperienceTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $data = $request->validate(ExperienceFields::rules());

        $experience = new Experience;
        $createdSkills = ExperienceFields::save($experience, $data);

        return Response::structured(array_filter([
            'message' => "Added \"{$experience->role}\" at {$experience->organization}.",
            'created_skills' => $createdSkills ?: null,
            'experience' => Payload::experience($experience->refresh()),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        $fields = ExperienceFields::schema($schema);
        $fields['role'] = $fields['role']->required();
        $fields['start_date'] = $fields['start_date']->required();

        return $fields;
    }
}
