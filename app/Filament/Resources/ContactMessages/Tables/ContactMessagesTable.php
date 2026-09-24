<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Enums\ContactTopic;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->weight(fn (ContactMessage $record): ?string => $record->read_at ? null : 'bold')
                    ->description(fn (ContactMessage $record): string => $record->email)
                    ->searchable(['name', 'email']),
                TextColumn::make('topic')->badge(),
                TextColumn::make('message')->limit(80)->color('gray')->searchable(),
                TextColumn::make('created_at')->label('Received')->since()->sortable()->dateTimeTooltip('M j, Y · H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn (ContactMessage $record): ?string => $record->read_at ? null : 'bg-primary-50/50 dark:bg-primary-500/5')
            ->filters([
                SelectFilter::make('topic')->options(ContactTopic::class),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    ContactMessageResource::replyAction(),
                    ContactMessageResource::toggleReadAction(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markRead')
                        ->label('Mark as read')
                        ->icon(Heroicon::OutlinedEnvelopeOpen)
                        ->action(fn (Collection $records) => ContactMessage::query()->whereKey($records->modelKeys())->unread()->update(['read_at' => now()]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No messages yet')
            ->emptyStateDescription('Messages sent through the contact form appear here.')
            ->emptyStateIcon(Heroicon::OutlinedInbox);
    }
}
