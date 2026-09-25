<?php

namespace App\Filament\Resources\Socials\Pages;

use App\Filament\Resources\Socials\SocialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSocials extends ManageRecords
{
    protected static string $resource = SocialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
