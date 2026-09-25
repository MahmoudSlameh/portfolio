<?php

namespace App\Http\Resources;

use App\Models\Testimonial;
use App\Support\Media\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `TestimonialEntry` (testimonial + company).
 *
 * @mixin Testimonial
 *
 * @property Testimonial $resource
 */
class TestimonialResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'quote' => $this->quote,
            'author' => $this->author_name,
            'role' => (string) $this->author_role,
            'companyId' => $this->company?->slug,
            'relation' => (string) $this->relation,
            'avatar' => ImageData::fromCollection($this->resource, 'avatar', $this->author_name),
            'company' => $this->company ? (new CompanyResource($this->company))->toArray($request) : null,
        ];
    }
}
