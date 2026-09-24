<?php

namespace App\Filament\Resources\Books\Tables;

use App\Enums\BookCategory;
use App\Enums\ReadingStatus;
use App\Filament\Support\Columns;
use App\Models\Book;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('cover_background')->label(''),
                TextColumn::make('title')->weight('medium')->description(fn (Book $record): string => $record->author)->searchable(['title', 'author']),
                TextColumn::make('category')->badge()->color('gray'),
                TextColumn::make('status')->badge(),
                TextColumn::make('rating')->formatStateUsing(fn (?int $state): string => str_repeat('★', (int) $state))->color('warning')->placeholder('—'),
                TextColumn::make('finished_at')->label('Finished')->date('M Y')->sortable()->placeholder('—'),
                Columns::visibility(),
            ])
            ->defaultSort('finished_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(ReadingStatus::class),
                SelectFilter::make('category')->options(BookCategory::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedBookOpen);
    }
}
