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
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_project')]
#[Title('Get a project')]
#[Description('Returns every field of one project: story, architecture, metrics, links, stack, cover and gallery images (with their ids), and its URLs on the site and in the admin panel.')]
#[IsReadOnly]
class GetProjectTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate(['project' => ['required']]);

        $project = Records::project($data['project'], withTrashed: true);

        if ($project === null) {
            return Records::notFound('project', $data['project'], 'list_projects');
        }

        return Response::structured(['project' => Payload::project($project)]);
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
