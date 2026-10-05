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

#[Name('add_project_image')]
#[Title('Add an image to a project\'s gallery')]
#[Description('Adds an image to the end of a project\'s case-study gallery (4:3 works best), with alt text and an optional caption. Give the image as image_url, image_base64, or screenshot_url (e.g. a page of the live site; use screenshot_viewport "tablet" for 4:3).')]
#[IsOpenWorld]
class AddProjectImageTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate([
            'project' => ['required'],
            'alt' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            ...ImageInput::rules(),
        ], ImageInput::messages());

        $project = Records::project($data['project']);

        if ($project === null) {
            return Records::notFound('project', $data['project'], 'list_projects');
        }

        try {
            $item = ImageAttacher::gallery($project, ImageInput::fetch($data, MimeTypes::RASTER), $data['alt'], $data['caption'] ?? null);
        } catch (ImageRejected $exception) {
            return Response::error($exception->getMessage());
        }

        return Response::structured([
            'message' => "Added an image to the gallery of \"{$project->title}\".",
            'image' => Payload::galleryItem($item->refresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()->description('The project\'s id or slug.')->required(),
            'alt' => $schema->string()->description('Alt text: what the image shows.')->required(),
            'caption' => $schema->string()->description('Short caption shown under the image.'),
            ...ImageInput::schema($schema),
        ];
    }
}
