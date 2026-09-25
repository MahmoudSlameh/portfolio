<?php

namespace App\Http\Resources;

use App\Models\Company;
use App\Support\Media\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `Company` (clients wall, employers).
 *
 * @mixin Company
 *
 * @property Company $resource
 */
class CompanyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->name,
            'kind' => $this->kind->value,
            'industry' => (string) $this->industry,
            'location' => (string) $this->location_label,
            'url' => $this->website_url,
            'period' => (string) $this->resolved_period,
            'engagement' => (string) $this->engagement,
            'wordmark' => $this->wordmark_style->value,
            'featured' => $this->is_featured,
            'logo' => ImageData::fromCollection($this->resource, 'logo', $this->name),
            'logoDark' => ImageData::fromCollection($this->resource, 'logo_dark', $this->name),
        ];
    }
}
