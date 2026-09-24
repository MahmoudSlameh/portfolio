<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use App\Models\ContactMessage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Section::make('Message')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        TextEntry::make('message')->hiddenLabel()->prose()->columnSpanFull(),
                    ]),
                Section::make('From')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')->copyable()->url(fn (ContactMessage $record): string => "mailto:{$record->email}"),
                        TextEntry::make('topic')->badge(),
                        TextEntry::make('created_at')->label('Received')->dateTime('M j, Y · H:i')->since(),
                        TextEntry::make('replied_at')->label('Replied')->dateTime('M j, Y · H:i')->placeholder('Not yet'),
                        TextEntry::make('ip_address')->label('IP')->color('gray')->placeholder('—'),
                        TextEntry::make('user_agent')->label('Browser')->color('gray')->size('xs')->placeholder('—'),
                    ]),
            ]),
        ]);
    }
}
