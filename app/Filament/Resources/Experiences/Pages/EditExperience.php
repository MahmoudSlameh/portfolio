<?php

namespace App\Filament\Resources\Experiences\Pages;

use App\Filament\Resources\Experiences\ExperienceResource;
use App\Filament\Support\SyncsStack;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExperience extends EditRecord
{
    use SyncsStack;

    protected static string $resource = ExperienceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
