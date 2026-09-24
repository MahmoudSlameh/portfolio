<?php

namespace App\Http\Resources;

use App\Models\Education;
use App\Support\Media\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `Education`.
 *
 * @mixin Education
 *
 * @property Education $resource
 */
class EducationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'institution' => $this->institution,
            'institutionUrl' => $this->institution_url,
            'degree' => $this->degree,
            'field' => (string) $this->field_of_study,
            'grade' => $this->grade,
            'start' => $this->start_date->format('Y-m'),
            'end' => $this->end_date?->format('Y-m'),
            'location' => (string) $this->location_label,
            'description' => $this->description,
            'notes' => $this->achievements,
            'logo' => ImageData::fromCollection($this->resource, 'logo', $this->institution),
        ];
    }
}
