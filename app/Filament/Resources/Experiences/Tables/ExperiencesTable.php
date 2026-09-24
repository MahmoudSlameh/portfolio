<?php

namespace App\Filament\Resources\Experiences\Tables;

use App\Enums\EmploymentType;
use App\Enums\WorkMode;
use App\Filament\Support\Columns;
use App\Models\Experience;
use App\Support\Countries;
use App\Support\Media\InitialsAvatar;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExperiencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['company.media'])->withCount('projects'))
            ->columns([
                ImageColumn::make('logo')
                    ->label('')
                    ->state(fn (Experience $record): string => $record->company?->getFirstMediaUrl('logo', 'thumb')
                        ?: InitialsAvatar::dataUri($record->organization))
                    ->imageSize(36)
                    ->circular(),
                TextColumn::make('role')
                    ->label('Job title')
                    ->weight('medium')
                    ->description(fn (Experience $record): string => $record->organization)
                    ->searchable(['role', 'organization_name']),
                TextColumn::make('employment_type')->label('Type')->badge()->toggleable(),
                TextColumn::make('work_mode')->label('Mode')->badge(),
                TextColumn::make('country_code')
                    ->label('Country')
                    ->formatStateUsing(fn (?string $state): string => trim(Countries::flag($state).' '.Countries::name($state)))
                    ->description(fn (Experience $record): ?string => $record->city)
                    ->toggleable(),
                Columns::period(),
                TextColumn::make('highlights')
                    ->label('Achievements')
                    ->state(fn (Experience $record): int => count($record->highlights))
                    ->icon(Heroicon::OutlinedTrophy)
                    ->toggleable(isToggledHiddenByDefault: true),
                Columns::visibility(),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                SelectFilter::make('employment_type')->label('Type')->options(EmploymentType::class),
                SelectFilter::make('work_mode')->label('Mode')->options(WorkMode::class),
                SelectFilter::make('company')->relationship('company', 'name')->searchable()->preload(),
                TernaryFilter::make('current')
                    ->label('Current role')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('end_date'),
                        false: fn (Builder $query) => $query->whereNotNull('end_date'),
                    ),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                ActionGroup::make([
                    ReplicateAction::make()->excludeAttributes(['projects_count']),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No experience yet')
            ->emptyStateDescription('Add the places you worked, starting with your current role.')
            ->emptyStateIcon(Heroicon::OutlinedBriefcase);
    }
}
