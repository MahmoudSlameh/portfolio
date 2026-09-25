<?php

namespace App\Http\Resources;

use App\Models\Experience;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `CareerEntry`: an experience with its company and published projects.
 *
 * @mixin Experience
 *
 * @property Experience $resource
 */
class CareerEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...(new ExperienceResource($this->resource))->toArray($request),
            'company' => $this->company ? (new CompanyResource($this->company))->toArray($request) : null,
            'projects' => $this->projects
                ->filter(fn (Project $project): bool => $project->isPublished())
                ->map(fn (Project $project): array => ProjectResource::reference($project))
                ->values()
                ->all(),
        ];
    }
}
