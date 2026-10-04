<?php

namespace App\Mcp\Tools\Projects;

use App\Models\ProjectGalleryItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('remove_project_image')]
#[Title('Remove a gallery image')]
#[Description('Deletes an image from a project\'s gallery for good (ids come from get_project → gallery).')]
#[IsDestructive]
class RemoveProjectImageTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate(['image_id' => ['required', 'integer']]);

        $item = ProjectGalleryItem::query()->with('project')->find($data['image_id']);

        if ($item === null) {
            return Response::error("No gallery image has id {$data['image_id']}. Call get_project to see the gallery.");
        }

        $item->delete();

        return Response::structured([
            'message' => 'Removed the image from the gallery of "'.$item->project->title.'".',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'image_id' => $schema->integer()->description('The gallery image id.')->required(),
        ];
    }
}
