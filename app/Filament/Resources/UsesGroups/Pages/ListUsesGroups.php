<?php

namespace App\Filament\Resources\UsesGroups\Pages;

use App\Filament\Resources\UsesGroups\UsesGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsesGroups extends ListRecords
{
    protected static string $resource = UsesGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
