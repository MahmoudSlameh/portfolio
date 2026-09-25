<?php

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Notification;

beforeEach(fn () => Notification::fake());

$valid = ['name' => 'Lina', 'email' => 'lina@example.com', 'topic' => 'role', 'message' => 'We would love to talk about a senior role.'];

test('a valid message is stored and acknowledged as json', function () use ($valid) {
    $this->postJson('/contact', $valid)
        ->assertCreated()
        ->assertJsonStructure(['id', 'receivedAt']);

    $message = ContactMessage::query()->firstOrFail();

    expect($message->email)->toBe('lina@example.com')
        ->and($message->ip_address)->not->toBeNull();
});

test('invalid messages are rejected with field errors', function () {
    $this->postJson('/contact', ['name' => '', 'email' => 'nope', 'topic' => 'spam', 'message' => 'short'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'topic', 'message']);

    expect(ContactMessage::query()->count())->toBe(0);
});

test('the honeypot field blocks bots', function () use ($valid) {
    $this->postJson('/contact', [...$valid, 'website' => 'https://spam.example'])->assertUnprocessable();

    expect(ContactMessage::query()->count())->toBe(0);
});

test('the contact form is rate limited', function () use ($valid) {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/contact', $valid)->assertCreated();
    }

    $this->postJson('/contact', $valid)->assertTooManyRequests();
});

test('a non-json submission redirects back with a flash message', function () use ($valid) {
    $this->from('/')->post('/contact', $valid)->assertRedirect('/')->assertSessionHas('success');
});
