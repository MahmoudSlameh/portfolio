<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $message = $this->getRecord();

        if ($message instanceof ContactMessage && $message->read_at === null) {
            $message->markAsRead();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ContactMessageResource::replyAction(),
            ContactMessageResource::toggleReadAction(),
            DeleteAction::make(),
        ];
    }
}
