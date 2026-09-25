<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->label('')->collection('cover')->conversion('thumb')->imageWidth(72)->imageHeight(48),
                TextColumn::make('title')
                    ->weight('medium')
                    ->description(fn (Article $record): ?string => $record->excerpt)
                    ->wrap()
                    ->searchable(['title', 'excerpt']),
                TextColumn::make('tags')->badge()->color('gray')->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (Article $record): string => $record->status === ArticleStatus::Published && ! $record->isPublished() ? 'Scheduled' : $record->status->getLabel())
                    ->color(fn (Article $record): string => $record->status === ArticleStatus::Published && ! $record->isPublished() ? 'warning' : $record->status->getColor()),
                TextColumn::make('published_at')->label('Published')->date('M j, Y')->sortable()->placeholder('—'),
                TextColumn::make('reading')
                    ->label('Read')
                    ->state(fn (Article $record): string => $record->readingMinutes().' min')
                    ->color('gray')
                    ->toggleable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('tag')
                    ->options(fn (): array => Article::query()->pluck('tags')->flatten()->unique()->sort()->mapWithKeys(fn (string $tag): array => [$tag => $tag])->all())
                    ->query(fn ($query, array $data) => filled($data['value'] ?? null) ? $query->whereJsonContains('tags', $data['value']) : $query),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ArticleResource::viewOnSiteAction(),
                EditAction::make(),
                ActionGroup::make([
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
            ->emptyStateHeading('No articles yet')
            ->emptyStateIcon(Heroicon::OutlinedNewspaper);
    }
}
