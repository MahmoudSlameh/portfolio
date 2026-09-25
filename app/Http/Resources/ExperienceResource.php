<?php

namespace App\Http\Resources;

use App\Models\Experience;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `Experience`. Changelog metadata comes from ChangelogMetadata (see Experience::changelog()).
 *
 * @mixin Experience
 *
 * @property Experience $resource
 */
class ExperienceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $changelog = $this->changelog();

        return [
            'id' => (string) $this->id,
            'companyId' => $this->company?->slug,
            'organization' => $this->organization,
            'role' => $this->role,
            'type' => $this->employment_type->value,
            'workMode' => $this->work_mode->value,
            'branch' => $changelog['branch']->value,
            'start' => $this->start_date->format('Y-m'),
            'end' => $this->end_date?->format('Y-m'),
            'location' => (string) $this->location_label,
            'address' => $this->address,
            'version' => $changelog['version'],
            'commit' => $changelog['commit'],
            'message' => $changelog['message'],
            'summary' => (string) $this->summary,
            'highlights' => $this->highlights,
            'stack' => $this->stack,
            'projectIds' => $this->projects->filter(fn (Project $project): bool => $project->isPublished())->pluck('slug')->values()->all(),
        ];
    }
}
