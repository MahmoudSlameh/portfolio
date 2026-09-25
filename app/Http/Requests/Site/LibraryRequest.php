<?php

namespace App\Http\Requests\Site;

use App\Enums\BookCategory;
use App\Enums\ReadingStatus;
use Illuminate\Validation\Rule;

class LibraryRequest extends ArchiveFilters
{
    protected function filterRules(): array
    {
        return [
            'status' => [Rule::enum(ReadingStatus::class)],
            'category' => [Rule::enum(BookCategory::class)],
            'view' => ['in:shelf,grid'],
        ];
    }
}
