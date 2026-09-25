<?php

namespace App\Http\Requests\Site;

class WritingArchiveRequest extends ArchiveFilters
{
    protected function filterRules(): array
    {
        return [
            'q' => ['string', 'max:100'],
            'tag' => ['string', 'max:100'],
        ];
    }
}
