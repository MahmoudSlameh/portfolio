<?php

namespace App\Observers;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use App\Models\Profile;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\NewContactMessage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Notification as Notifier;
use Illuminate\Support\Str;

class ContactMessageObserver
{
    /**
     * Notify the owner in the panel (database notification) and by email.
     */
    public function created(ContactMessage $contactMessage): void
    {
        Notification::make()
            ->title("New message from {$contactMessage->name}")
            ->body(Str::limit($contactMessage->message, 120))
            ->icon(Heroicon::OutlinedEnvelope)
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(ContactMessageResource::getUrl('view', ['record' => $contactMessage], panel: 'admin')),
            ])
            ->sendToDatabase(User::all());

        $recipient = SiteSetting::current()->contact_recipient
            ?: Profile::current()->email
            ?: config('portfolio.admin.email');

        if (filled($recipient)) {
            Notifier::route('mail', $recipient)->notify(new NewContactMessage($contactMessage));
        }
    }
}
