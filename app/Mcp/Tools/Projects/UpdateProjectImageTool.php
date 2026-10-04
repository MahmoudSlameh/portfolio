<?php

namespace App\Mcp\Tools\Projects;

use App\Mcp\Support\Payload;
use App\Models\ProjectGalleryItem;
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

#[Name('update_project_image')]
#[Title('Edit a gallery image')]
#[Description('Changes the alt text, caption or position of a project gallery image (ids come from get_project → gallery). Lower sort_order comes first.')]
#[IsIdempotent]
class UpdateProjectImageTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate([
            'image_id' => ['required', 'integer'],
            'alt' => ['sometimes', 'string', 'max:255'],
            'caption' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:1000'],
        ]);

        $item = ProjectGalleryItem::query()->find($data['image_id']);

        if ($item === null) {
            return Response::error("No gallery image has id {$data['image_id']}. Call get_project to see the gallery.");
        }

        $item->fill(Arr::only($data, ['alt', 'caption', 'sort_order']))->save();

        return Response::structured([
            'message' => 'Updated the gallery image.',
            'image' => Payload::galleryItem($item),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'image_id' => $schema->integer()->description('The gallery image id.')->required(),
            'alt' => $schema->string(),
            'caption' => $schema->string()->description('null removes the caption.'),
            'sort_order' => $schema->integer()->min(0)->description('Position; lower comes first.'),
        ];
    }
}
