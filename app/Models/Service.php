<?php

namespace App\Models;

use App\Enums\ServiceIcon;
use App\Models\Concerns\HasVisibilityAndOrder;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * What the owner offers to clients and employers (home page "Services" section).
 *
 * @property int $id
 * @property string $title
 * @property string $summary
 * @property ServiceIcon $icon
 * @property list<string>|null $highlights
 * @property bool $is_visible
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'summary', 'icon', 'highlights', 'is_visible', 'sort_order'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, HasVisibilityAndOrder;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'icon' => 'sparkles',
        'is_visible' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'icon' => ServiceIcon::class,
            'highlights' => 'array',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
