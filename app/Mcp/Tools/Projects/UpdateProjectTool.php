<?php

namespace App\Mcp\Tools\Projects;

use App\Mcp\Support\Payload;
use App\Mcp\Support\ProjectFields;
use App\Mcp\Support\Records;
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

#[Name('update_project')]
#[Title('Update a project')]
#[Description('Changes a project. Send only the fields to change; a list field (stack, overview, features, metrics, links…) replaces the whole stored list, so read the project with get_project first when adding to a list.')]
#[IsIdempotent]
class UpdateProjectTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $reference = $request->validate(['project' => ['required']])['project'];
        $project = Records::project($reference);

        if ($project === null) {
            return Records::notFound('project', $reference, 'list_projects');
        }

        $data = Arr::except($request->validate(ProjectFields::rules($project)), ['project']);

        if ($data === []) {
            return Response::error('Nothing to update: send at least one field to change.');
        }

        $createdSkills = ProjectFields::save($project, $data);

        return Response::structured(array_filter([
            'message' => "Updated project \"{$project->title}\": ".implode(', ', array_keys($data)).'.',
            'created_skills' => $createdSkills ?: null,
            'project' => Payload::project($project->refresh()),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('The project\'s id or slug.')->required(),
            ...ProjectFields::schema($schema),
        ];
    }
}
