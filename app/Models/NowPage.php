<?php

namespace App\Models;

use App\Enums\ReadingStatus;
use App\Models\Concerns\IsSingleton;
use Database\Factories\NowPageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property-read Collection<int, Book> $readingBooks
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

    /**
     * Books picked for the "currently reading" list, in the chosen order.
     *
     * @return BelongsToMany<Book, $this>
     */
    public function readingBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    /**
     * The picked books, or — when none were picked — every visible book with status "reading".
     *
     * @return Collection<int, Book>
     */
    public function currentlyReading(): Collection
    {
        $picked = $this->readingBooks()->where('is_visible', true)->get();

        if ($picked->isNotEmpty()) {
            return $picked;
        }

        return Book::query()->visible()->where('status', ReadingStatus::Reading)->orderBy('title')->get();
    }
}
