<?php

namespace App\Mcp\Support;

use App\Models\Company;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Mcp\Response;

/**
 * Finds the record a tool call refers to by id or slug, with an error that tells Claude how to recover.
 */
final class Records
{
    public static function project(mixed $reference, bool $withTrashed = false): ?Project
    {
        return self::bySlugOrId(Project::query()->when($withTrashed, fn ($query) => $query->withTrashed()), $reference);
    }

    public static function company(mixed $reference): ?Company
    {
        return self::bySlugOrId(Company::query(), $reference);
    }

    public static function notFound(string $type, mixed $reference, string $listTool): Response
    {
        return Response::error("No {$type} matches \"".(is_scalar($reference) ? $reference : '').'". Call '.$listTool.' to find its id or slug.');
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return TModel|null
     */
    private static function bySlugOrId(Builder $query, mixed $reference): ?Model
    {
        if (! is_int($reference) && (! is_string($reference) || trim($reference) === '')) {
            return null;
        }

        // A numeric reference is an id, unless only a slug matches ("2048").
        return $query->where(fn ($query) => $query
            ->where('slug', (string) $reference)
            ->when(ctype_digit((string) $reference), fn ($query) => $query->orWhere($query->getModel()->getQualifiedKeyName(), (int) $reference)))
            ->orderByRaw('case when slug = ? then 1 else 0 end', [(string) $reference])
            ->first();
    }
}
