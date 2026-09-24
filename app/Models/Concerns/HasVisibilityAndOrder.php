<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Content rows with an `is_visible` toggle and a manual `sort_order` (Filament reorderable tables).
 *
 * @phpstan-require-extends Model
 */
trait HasVisibilityAndOrder
{
    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_visible'), true);
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->getQualifiedKeyName());
    }
}
