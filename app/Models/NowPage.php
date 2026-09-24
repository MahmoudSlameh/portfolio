<?php

namespace App\Models;

use App\Models\Concerns\IsSingleton;
use Database\Factories\NowPageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Content of the /now page (single row).
 *
 * @property int $id
 * @property string|null $location
 * @property string|null $availability
 * @property list<array{title: string, body: string}> $focus
 * @property list<array{title: string, body: string}> $learning
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['location', 'availability', 'focus', 'learning'])]
class NowPage extends Model
{
    /** @use HasFactory<NowPageFactory> */
    use HasFactory, IsSingleton;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'focus' => '[]',
        'learning' => '[]',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'focus' => 'array',
            'learning' => 'array',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function singletonDefaults(): array
    {
        return [];
    }
}
