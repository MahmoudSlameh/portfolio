<?php

namespace App\Http\Resources;

use App\Models\Profile;
use App\Support\Media\ImageData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Frontend `Profile` (resources/js/types/content.ts).
 *
 * @mixin Profile
 *
 * @property Profile $resource
 */
class ProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'initials' => $this->resolved_initials,
            'role' => $this->role,
            'headline' => (string) $this->headline,
            'focusAreas' => $this->focus_areas,
            'summary' => (string) $this->summary,
            'location' => (string) $this->location,
            'timezone' => $this->timezone,
            'timezoneLabel' => (string) $this->timezone_label,
            'email' => (string) $this->email,
            'phone' => $this->phone,
            'currentVersion' => (string) $this->current_version,
            'updatedAt' => ($this->updated_at ?? now())->format('Y-m-d'),
            'availability' => [
                'status' => $this->availability_status->value,
                'label' => (string) ($this->availability_label ?? $this->availability_status->getLabel()),
                'note' => (string) $this->availability_note,
            ],
            'latestRelease' => [
                'added' => $this->latest_release['added'] ?? [],
                'changed' => $this->latest_release['changed'] ?? [],
                'removed' => $this->latest_release['removed'] ?? [],
            ],
            'stats' => self::withIds($this->stats, 'label'),
            'status' => self::withIds($this->status, 'label'),
            'story' => $this->story,
            'principles' => self::withIds($this->principles, 'title'),
            'portrait' => ImageData::fromCollection($this->resource, 'portrait', $this->portrait_alt ?? $this->name),
            'resumeUrl' => $this->getFirstMediaUrl('resume') ?: null,
        ];
    }

    /**
     * Give repeater rows a stable, unique `id` derived from one of their fields.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function withIds(array $rows, string $field): array
    {
        $seen = [];

        return array_map(function (array $row) use ($field, &$seen): array {
            $id = Str::slug((string) ($row[$field] ?? '')) ?: 'item';
            $seen[$id] = ($seen[$id] ?? 0) + 1;

            return ['id' => $seen[$id] > 1 ? "{$id}-{$seen[$id]}" : $id, ...$row];
        }, $rows);
    }
}
