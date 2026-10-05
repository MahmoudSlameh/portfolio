<?php

namespace App\Http\Resources;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `Service`.
 *
 * @mixin Service
 *
 * @property Service $resource
 */
class ServiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'icon' => $this->icon->value,
            'highlights' => collect($this->highlights ?? [])->values()->all(),
        ];
    }
}
