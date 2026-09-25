<?php

namespace App\Filament\Resources\Education\Tables;

use App\Filament\Support\Columns;
use App\Models\Education;
use App\Support\Media\InitialsAvatar;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EducationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->label('')
                    ->collection('logo')
                    ->conversion('thumb')
                    ->imageSize(36)
                    ->circular()
                    ->defaultImageUrl(fn (Education $record): string => InitialsAvatar::dataUri($record->institution)),
                TextColumn::make('degree')
                    ->label('Qualification')
                    ->weight('medium')
                    ->description(fn (Education $record): string => $record->institution)
                    ->searchable(['degree', 'institution']),
                TextColumn::make('field_of_study')->label('Specialization')->searchable()->toggleable(),
                TextColumn::make('grade')->badge()->color('info')->placeholder('—'),
                Columns::period(),
                Columns::visibility(),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                TernaryFilter::make('current')
                    ->label('Currently studying')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('end_date'),
                        false: fn (Builder $query) => $query->whereNotNull('end_date'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No education yet')
            ->emptyStateIcon(Heroicon::OutlinedAcademicCap);
    }
}
