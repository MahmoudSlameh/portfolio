<?php

namespace App\Mcp\Tools\Projects;

use App\Mcp\Support\Payload;
use App\Mcp\Support\ProjectFields;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_project')]
#[Title('Create a project')]
#[Description(<<<'TEXT'
Creates a project / case study. Only "title" is required; fill in as much as the sources support (README, code, commits, releases, live site). The project is a draft (not on the public site) unless is_published is true; only publish when the owner asked to. Add images afterwards with set_project_cover and add_project_image.
TEXT)]
class CreateProjectTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $data = $request->validate(ProjectFields::rules());

        $project = new Project;
        $createdSkills = ProjectFields::save($project, $data);

        return Response::structured(array_filter([
            'message' => "Created project \"{$project->title}\"".($project->is_published ? ' (published).' : ' as a draft.'),
            'created_skills' => $createdSkills ?: null,
            'project' => Payload::project($project->refresh()),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        $fields = ProjectFields::schema($schema);
        $fields['title'] = $fields['title']->required();

        return $fields;
    }
}
