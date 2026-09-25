<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Enums\CompanyKind;
use App\Models\Company;
use App\Support\Media\InitialsAvatar;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    SpatieMediaLibraryImageColumn::make('logo')
                        ->collection('logo')
                        ->conversion('thumb')
                        ->imageSize(48)
                        ->defaultImageUrl(fn (Company $record): string => InitialsAvatar::dataUri($record->name))
                        ->grow(false),
                    Stack::make([
                        TextColumn::make('name')->weight('bold')->searchable()->sortable(),
                        TextColumn::make('industry')->color('gray')->searchable(),
                    ]),
                ]),
                Stack::make([
                    TextColumn::make('kind')->badge(),
                    TextColumn::make('website_url')
                        ->label('Website')
                        ->icon(Heroicon::OutlinedLink)
                        ->color('primary')
                        ->limit(40)
                        ->url(fn (Company $record): ?string => $record->website_url, shouldOpenInNewTab: true),
                    TextColumn::make('experiences_count')
                        ->counts('experiences')
                        ->formatStateUsing(fn (int $state): string => $state.' '.str('role')->plural($state))
                        ->color('gray'),
                ])->space(2),
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('kind')->options(CompanyKind::class),
                TernaryFilter::make('is_featured')->label('On the clients wall'),
                TernaryFilter::make('is_visible')->label('Visible'),
            ])
            ->recordActions([
                Action::make('visit')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Company $record): ?string => $record->website_url, shouldOpenInNewTab: true)
                    ->visible(fn (Company $record): bool => filled($record->website_url)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No companies yet')
            ->emptyStateDescription('Add the companies you worked for and the clients you worked with.')
            ->emptyStateIcon(Heroicon::OutlinedBuildingOffice2);
    }
}
