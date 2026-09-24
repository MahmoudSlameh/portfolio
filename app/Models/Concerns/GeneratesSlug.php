<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Fills an empty `slug` from another attribute on save, keeping it unique ("atlas", "atlas-2"…).
 *
 * @phpstan-require-extends Model
 */
trait GeneratesSlug
{
    public static function bootGeneratesSlug(): void
    {
        static::saving(function (Model $model): void {
            /** @var static $model */
            if (blank($model->getAttribute('slug'))) {
                $model->setAttribute('slug', $model->uniqueSlug((string) $model->getAttribute($model->slugSource())));
            }
        });
    }

    /**
     * Attribute the slug is generated from.
     */
    protected function slugSource(): string
    {
        return 'name';
    }

    protected function uniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->whereKeyNot($this->getKey())->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
