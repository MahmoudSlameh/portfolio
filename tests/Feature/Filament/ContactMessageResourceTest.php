<?php

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Notifications\NewContactMessage;
use Filament\Actions\Testing\TestAction;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = actingAsAdmin();
});

test('a new message notifies the owner in the panel and by email', function () {
    Notification::fake();
    SiteSetting::current()->update(['contact_recipient' => 'owner@example.com']);

    $message = ContactMessage::factory()->create();

    Notification::assertSentTo(new AnonymousNotifiable, NewContactMessage::class,
        fn (NewContactMessage $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'owner@example.com'
            && $notification->contactMessage->is($message));
});

test('the panel notification is stored for admins', function () {
    ContactMessage::factory()->create(['name' => 'Lina']);

    expect($this->admin->notifications()->count())->toBe(1)
        ->and($this->admin->notifications()->first()?->data['title'] ?? null)->toBe('New message from Lina');
});

test('the navigation badge counts unread messages and the inbox opens on unread', function () {
    $unread = ContactMessage::factory()->count(2)->create();
    $read = ContactMessage::factory()->read()->create();

    expect(ContactMessageResource::getNavigationBadge())->toBe('2');

    Livewire::test(ListContactMessages::class)
        ->assertCanSeeTableRecords($unread)
        ->assertCanNotSeeTableRecords([$read]);
});

test('opening a message marks it as read and it can be marked unread again', function () {
    $message = ContactMessage::factory()->create();

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertSee($message->email)
        ->callAction('toggleRead');

    expect($message->fresh()?->read_at)->toBeNull();
});

test('messages can be marked read in bulk', function () {
    $messages = ContactMessage::factory()->count(2)->create();

    Livewire::test(ListContactMessages::class)
        ->selectTableRecords($messages)
        ->callAction(TestAction::make('markRead')->table()->bulk());

    expect(ContactMessage::query()->unread()->count())->toBe(0);
});

test('messages cannot be created or edited from the panel', function () {
    expect(ContactMessageResource::canCreate())->toBeFalse()
        ->and(ContactMessageResource::hasPage('edit'))->toBeFalse();
});
