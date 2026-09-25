<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Rows ordered manually through a `sort_order` column (Filament reorderable tables).
 *
 * @phpstan-require-extends Model
 */
trait HasSortOrder
{
    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->getQualifiedKeyName());
    }
}
