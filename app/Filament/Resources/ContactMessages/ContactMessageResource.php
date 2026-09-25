<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\ContactMessages\Schemas\ContactMessageInfolist;
use App\Filament\Resources\ContactMessages\Tables\ContactMessagesTable;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Inbox';

    protected static ?string $navigationLabel = 'Messages';

    protected static ?string $modelLabel = 'message';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $unread = ContactMessage::query()->unread()->count();

        return $unread > 0 ? (string) $unread : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): string
    {
        return 'Unread messages';
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContactMessageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactMessagesTable::configure($table);
    }

    public static function replyAction(): Action
    {
        return Action::make('reply')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->url(fn (ContactMessage $record): string => 'mailto:'.$record->email.'?subject='.rawurlencode('Re: your message ('.$record->topic->getLabel().')'))
            ->after(fn (ContactMessage $record) => $record->forceFill(['replied_at' => now(), 'read_at' => $record->read_at ?? now()])->save());
    }

    public static function toggleReadAction(): Action
    {
        return Action::make('toggleRead')
            ->label(fn (ContactMessage $record): string => $record->read_at ? 'Mark as unread' : 'Mark as read')
            ->icon(fn (ContactMessage $record): Heroicon => $record->read_at ? Heroicon::OutlinedEnvelope : Heroicon::OutlinedEnvelopeOpen)
            ->color('gray')
            ->action(fn (ContactMessage $record) => $record->read_at ? $record->markAsUnread() : $record->markAsRead());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => ViewContactMessage::route('/{record}'),
        ];
    }
}
