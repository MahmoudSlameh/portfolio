<?php

namespace App\Filament\Resources\UsesGroups\Pages;

use App\Filament\Resources\UsesGroups\UsesGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUsesGroup extends EditRecord
{
    protected static string $resource = UsesGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
