<?php

namespace App\Http\Resources;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `Skill`.
 *
 * @mixin Skill
 *
 * @property Skill $resource
 */
class SkillResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->name,
            'categoryId' => $this->category?->slug,
            'proficiency' => $this->proficiency,
            'years' => $this->years,
            'icon' => $this->icon,
        ];
    }
}
