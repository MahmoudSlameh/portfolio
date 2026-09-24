<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Support\SyncsStack;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    use SyncsStack;

    protected static string $resource = ProjectResource::class;
}
