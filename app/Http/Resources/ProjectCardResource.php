<?php

namespace App\Http\Resources;

/**
 * Frontend `Project` for list contexts (home, archive): same shape as {@see ProjectResource},
 * but the case-study-only fields are empty to keep the Inertia page payload small.
 */
class ProjectCardResource extends ProjectResource
{
    /**
     * @return array<string, mixed>
     */
    protected function caseStudyFields(): array
    {
        return [
            'gallery' => [],
            'overview' => [],
            'problem' => [],
            'approach' => [],
            'architecture' => null,
            'features' => [],
            'challenges' => [],
        ];
    }
}
