<?php

namespace App\Mcp\Tools\Projects;

use App\Mcp\Support\ImageAttacher;
use App\Mcp\Support\ImageInput;
use App\Mcp\Support\Payload;
use App\Mcp\Support\Records;
use App\Support\Media\ImageRejected;
use App\Support\Media\MimeTypes;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Name('set_project_cover')]
#[Title('Set a project\'s cover image')]
#[Description('Sets (replaces) the cover image of a project: 16:9 works best, it is shown on cards, the case study and social previews. Give the image as image_url, image_base64, or screenshot_url to screenshot the live site.')]
#[IsOpenWorld]
class SetProjectCoverTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate([
            'project' => ['required'],
            'alt' => ['required', 'string', 'max:255'],
            ...ImageInput::rules(),
        ], ImageInput::messages());

        $project = Records::project($data['project']);

        if ($project === null) {
            return Records::notFound('project', $data['project'], 'list_projects');
        }

        try {
            ImageAttacher::cover($project, ImageInput::fetch($data, MimeTypes::RASTER), $data['alt']);
        } catch (ImageRejected $exception) {
            return Response::error($exception->getMessage());
        }

        return Response::structured([
            'message' => "Set the cover of \"{$project->title}\".",
            'cover' => Payload::image($project->refresh()->getFirstMedia('cover'), $project->cover_alt),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('The project\'s id or slug.')->required(),
            'alt' => $schema->string()->description('Alt text: what the image shows, for screen readers and search engines.')->required(),
            ...ImageInput::schema($schema),
        ];
    }
}
