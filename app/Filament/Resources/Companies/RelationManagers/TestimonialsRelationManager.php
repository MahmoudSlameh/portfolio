<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Testimonials\TestimonialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TestimonialsRelationManager extends RelationManager
{
    protected static string $relationship = 'testimonials';

    protected static ?string $relatedResource = TestimonialResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),

            ]);
    }
}
