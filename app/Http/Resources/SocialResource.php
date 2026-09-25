<?php

namespace App\Http\Resources;

use App\Models\Social;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `Social`.
 *
 * @mixin Social
 *
 * @property Social $resource
 */
class SocialResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->platform->value.'-'.$this->id,
            'label' => $this->label,
            'handle' => (string) $this->handle,
            'url' => $this->url,
            'icon' => $this->platform->value,
        ];
    }
}
