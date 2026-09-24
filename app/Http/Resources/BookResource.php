<?php

namespace App\Http\Resources;

use App\Models\Book;
use App\Support\Media\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `Book`.
 *
 * @mixin Book
 *
 * @property Book $resource
 */
class BookResource extends JsonResource
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
            'author' => $this->author,
            'publishedYear' => $this->published_year,
            'category' => $this->category->value,
            'status' => $this->status->value,
            'finishedAt' => $this->finished_at?->format('Y-m'),
            'rating' => $this->rating,
            'pages' => $this->pages,
            'note' => (string) $this->note,
            'cover' => [
                'background' => $this->cover_background,
                'ink' => $this->cover_ink,
                'accent' => $this->cover_accent,
                'style' => $this->cover_style->value,
            ],
            'coverImage' => ImageData::fromCollection($this->resource, 'cover_image', "Cover of {$this->title}"),
        ];
    }
}
