<?php

namespace App\Filament\Resources\Experiences\RelationManagers;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\AssociateAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DissociateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    protected static ?string $relatedResource = ProjectResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make()->preloadRecordSelect(),
            ])
            ->recordActions([
                DissociateAction::make(),
            ]);
    }
}
