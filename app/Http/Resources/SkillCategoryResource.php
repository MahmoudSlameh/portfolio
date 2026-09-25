<?php

namespace App\Http\Resources;

use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `SkillCategory`.
 *
 * @mixin SkillCategory
 *
 * @property SkillCategory $resource
 */
class SkillCategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'label' => $this->name,
            'description' => (string) $this->description,
        ];
    }
}
