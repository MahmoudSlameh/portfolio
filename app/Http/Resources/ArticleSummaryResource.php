<?php

namespace App\Http\Resources;

use App\Models\Article;
use App\Models\Project;
use App\Support\Media\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `ArticleSummary` (article without body).
 *
 * @mixin Article
 *
 * @property Article $resource
 */
class ArticleSummaryResource extends JsonResource
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
            'excerpt' => (string) $this->excerpt,
            'publishedAt' => ($this->published_at ?? $this->created_at ?? now())->format('Y-m-d'),
            'tags' => $this->tags,
            'projectIds' => $this->projects->filter(fn (Project $project): bool => $project->isPublished())->pluck('slug')->values()->all(),
            'cover' => ImageData::fromCollection($this->resource, 'cover', $this->cover_alt ?? $this->title),
            'readingMinutes' => $this->readingMinutes(),
        ];
    }
}
