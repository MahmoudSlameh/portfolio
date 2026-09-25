<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Enums\ProjectCategory;
use App\Enums\ProjectStatus;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['skills', 'company']))
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->label('')->collection('cover')->conversion('thumb')->imageWidth(72)->imageHeight(48),
                TextColumn::make('title')
                    ->weight('medium')
                    ->description(fn (Project $record): ?string => $record->tagline)
                    ->wrap()
                    ->searchable(['title', 'tagline']),
                TextColumn::make('category')->badge()->toggleable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('year')->sortable(),
                TextColumn::make('stack')
                    ->state(fn (Project $record): array => array_slice($record->stack, 0, 3))
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                ToggleColumn::make('is_featured')->label('Featured')->onIcon(Heroicon::OutlinedStar)->alignCenter(),
                IconColumn::make('published')
                    ->state(fn (Project $record): bool => $record->isPublished())
                    ->boolean()
                    ->tooltip(fn (Project $record): string => match (true) {
                        ! $record->is_published => 'Draft',
                        $record->isPublished() => 'Published',
                        default => 'Scheduled for '.$record->published_at?->format('M j, Y H:i'),
                    })
                    ->alignCenter(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('year', 'desc')
            ->filters([
                SelectFilter::make('status')->options(ProjectStatus::class),
                SelectFilter::make('category')->options(ProjectCategory::class),
                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('is_published')->label('Published'),
                SelectFilter::make('company')->relationship('company', 'name')->searchable()->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ProjectResource::viewOnSiteAction(),
                EditAction::make(),
                ActionGroup::make([
                    ReplicateAction::make()
                        ->mutateRecordDataUsing(fn (array $data): array => [...$data, 'title' => $data['title'].' (copy)', 'slug' => null, 'is_published' => false]),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No projects yet')
            ->emptyStateDescription('Add your first case study.')
            ->emptyStateIcon(Heroicon::OutlinedRectangleStack);
    }
}
