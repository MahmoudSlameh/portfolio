<?php

namespace App\Filament\Resources\Experiences\Pages;

use App\Filament\Resources\Experiences\ExperienceResource;
use App\Filament\Support\SyncsStack;
use Filament\Resources\Pages\CreateRecord;

class CreateExperience extends CreateRecord
{
    use SyncsStack;

    protected static string $resource = ExperienceResource::class;
}
