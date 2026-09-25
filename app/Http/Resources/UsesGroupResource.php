<?php

namespace App\Http\Resources;

use App\Models\UsesGroup;
use App\Models\UsesItem;
use App\Support\Media\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `UsesGroup` with its items.
 *
 * @mixin UsesGroup
 *
 * @property UsesGroup $resource
 */
class UsesGroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'kind' => $this->kind->value,
            'title' => $this->title,
            'items' => $this->items->map(fn (UsesItem $item): array => array_filter([
                'id' => (string) $item->id,
                'name' => $item->name,
                'description' => (string) $item->description,
                'url' => $item->url,
                'image' => ImageData::fromCollection($item, 'image', $item->name),
            ], fn (mixed $value, string $key): bool => $key !== 'url' || $value !== null, ARRAY_FILTER_USE_BOTH))->values()->all(),
        ];
    }
}
