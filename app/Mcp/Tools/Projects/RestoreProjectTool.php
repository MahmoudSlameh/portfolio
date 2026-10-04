<?php

namespace App\Mcp\Tools\Projects;

use App\Mcp\Support\Payload;
use App\Mcp\Support\Records;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('restore_project')]
#[Title('Restore a deleted project')]
#[Description('Brings a deleted project back from the trash (list deleted ones with list_projects include_deleted=true).')]
#[IsIdempotent]
class RestoreProjectTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $reference = $request->validate(['project' => ['required']])['project'];
        $project = Records::project($reference, withTrashed: true);

        if ($project === null) {
            return Records::notFound('project', $reference, 'list_projects with include_deleted=true');
        }

        $project->restore();

        return Response::structured([
            'message' => "Restored project \"{$project->title}\".",
            'project' => Payload::project($project->refresh()),
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
