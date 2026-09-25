<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

/**
 * Query-string filters for the archive pages. Invalid values are dropped silently (never a 422),
 * like the `.catch(undefined)` zod schemas of the reference app.
 */
abstract class ArchiveFilters extends FormRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    abstract protected function filterRules(): array;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * The valid filters, keyed like the frontend search types.
     *
     * @return array<string, string>
     */
    public function filters(): array
    {
        $filters = [];

        foreach ($this->filterRules() as $key => $rules) {
            $value = $this->query($key);

            if (is_string($value) && $value !== '' && Validator::make([$key => $value], [$key => $rules])->passes()) {
                $filters[$key] = $value;
            }
        }

        return $filters;
    }
}
