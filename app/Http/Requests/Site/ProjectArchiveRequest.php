<?php

namespace App\Http\Requests\Site;

use App\Enums\ProjectCategory;
use Illuminate\Validation\Rule;

class ProjectArchiveRequest extends ArchiveFilters
{
    protected function filterRules(): array
    {
        return [
            'q' => ['string', 'max:100'],
            'tech' => ['string', 'max:100'],
            'category' => [Rule::enum(ProjectCategory::class)],
            'sort' => ['in:newest,oldest'],
            'view' => ['in:grid,table'],
        ];
    }
}
