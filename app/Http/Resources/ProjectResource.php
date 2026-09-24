<?php

namespace App\Http\Resources;

use App\Models\Project;
use App\Models\ProjectGalleryItem;
use App\Support\Media\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `Project`.
 *
 * @mixin Project
 *
 * @property Project $resource
 */
class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'slug' => $this->slug,
            'title' => $this->title,
            'tagline' => (string) $this->tagline,
            'summary' => (string) $this->summary,
            'year' => $this->year,
            'status' => $this->status->value,
            'category' => $this->category->value,
            'featured' => $this->is_featured,
            'version' => (string) $this->version,
            'companyId' => $this->company?->slug,
            'experienceId' => $this->experience_id !== null ? (string) $this->experience_id : null,
            'role' => (string) $this->role,
            'team' => (string) $this->team,
            'timeline' => (string) $this->timeline,
            'stack' => $this->stack,
            'cover' => ImageData::fromCollection($this->resource, 'cover', $this->cover_alt ?? $this->title),
            ...$this->caseStudyFields(),
            'metrics' => ProfileResource::withIds($this->metrics, 'label'),
            'links' => $this->links,
        ];
    }

    /**
     * Fields only the case-study page renders.
     *
     * @return array<string, mixed>
     */
    protected function caseStudyFields(): array
    {
        return [
            'gallery' => $this->galleryItems
                ->map(fn (ProjectGalleryItem $item): ?array => ($image = ImageData::fromCollection($item, 'image', $item->alt)) === null
                    ? null
                    : [...$image, 'caption' => (string) $item->caption])
                ->filter()
                ->values()
                ->all(),
            'overview' => $this->overview,
            'problem' => $this->problem,
            'approach' => $this->approach,
            'architecture' => $this->architecture,
            'features' => $this->features,
            'challenges' => $this->challenges,
        ];
    }

    /**
     * @return array{slug: string, title: string}
     */
    public static function reference(Project $project): array
    {
        return ['slug' => $project->slug, 'title' => $project->title];
    }
}
