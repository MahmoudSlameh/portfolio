<?php

namespace App\Models;

use App\Enums\UsesKind;
use App\Models\Concerns\HasSortOrder;
use Database\Factories\UsesGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A section of the /uses page (hardware, software, development).
 *
 * @property int $id
 * @property UsesKind $kind
 * @property string $title
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, UsesItem> $items
 */
#[Fillable(['kind', 'title', 'sort_order'])]
class UsesGroup extends Model
{
    /** @use HasFactory<UsesGroupFactory> */
    use HasFactory, HasSortOrder;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'kind' => 'software',
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => UsesKind::class,
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<UsesItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(UsesItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
