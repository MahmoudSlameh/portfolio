<?php

namespace App\Mcp\Tools\Projects;

use App\Mcp\Support\Records;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_project')]
#[Title('Delete a project')]
#[Description('Moves a project to the trash: it disappears from the site and can be brought back with restore_project (or from the panel). Only do this when the owner asked for it.')]
#[IsDestructive]
class DeleteProjectTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $reference = $request->validate(['project' => ['required']])['project'];
        $project = Records::project($reference);

        if ($project === null) {
            return Records::notFound('project', $reference, 'list_projects');
        }

        $project->delete();

        return Response::structured([
            'message' => "Moved project \"{$project->title}\" to the trash. restore_project brings it back.",
            'project' => ['id' => $project->id, 'slug' => $project->slug, 'title' => $project->title],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('The project\'s id or slug.')->required(),
        ];
    }
}
