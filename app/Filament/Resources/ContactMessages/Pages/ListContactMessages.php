<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'unread' => Tab::make('Unread')
                ->badge(fn (): int => ContactMessage::query()->unread()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('read_at')),
            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string
    {
        return ContactMessage::query()->unread()->exists() ? 'unread' : 'all';
    }
}
