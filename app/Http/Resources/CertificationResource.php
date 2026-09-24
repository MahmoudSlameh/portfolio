<?php

namespace App\Http\Resources;

use App\Models\Certification;
use App\Support\Media\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Frontend `Certification`.
 *
 * @mixin Certification
 *
 * @property Certification $resource
 */
class CertificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'issuer' => $this->issuer,
            'year' => $this->issued_at->year,
            'credentialId' => (string) $this->credential_id,
            'url' => (string) $this->credential_url,
            'expired' => $this->is_expired,
            'badge' => ImageData::fromCollection($this->resource, 'badge', $this->name),
        ];
    }
}
