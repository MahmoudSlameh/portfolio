<?php

namespace App\Models;

use App\Enums\SocialPlatform;
use App\Models\Concerns\HasVisibilityAndOrder;
use Database\Factories\SocialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A social profile / contact link.
 *
 * @property int $id
 * @property SocialPlatform $platform
 * @property string $label
 * @property string|null $handle
 * @property string $url
 * @property bool $is_visible
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['platform', 'label', 'handle', 'url', 'is_visible', 'sort_order'])]
class Social extends Model
{
    /** @use HasFactory<SocialFactory> */
    use HasFactory, HasVisibilityAndOrder;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_visible' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => SocialPlatform::class,
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
