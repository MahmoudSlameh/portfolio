<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * A model that has exactly one row (profile, site settings, now page).
 *
 * The row is created on first access and memoized in the container for the
 * rest of the request; saving or deleting the model forgets the memoized copy.
 *
 * @mixin Model
 */
trait IsSingleton
{
    public static function bootIsSingleton(): void
    {
        static::saved(fn () => app()->forgetInstance(static::singletonKey()));
        static::deleted(fn () => app()->forgetInstance(static::singletonKey()));
    }

    public static function current(): static
    {
        $key = static::singletonKey();

        if (! app()->bound($key)) {
            app()->instance($key, static::query()->oldest('id')->first() ?? static::query()->create(static::singletonDefaults()));
        }

        return app($key);
    }

    /**
     * Attribute values used when the row does not exist yet.
     *
     * @return array<string, mixed>
     */
    abstract protected static function singletonDefaults(): array;

    protected static function singletonKey(): string
    {
        return 'singleton.'.static::class;
    }
}
