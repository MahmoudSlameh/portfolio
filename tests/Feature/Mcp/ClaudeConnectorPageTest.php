<?php

use App\Filament\Pages\ClaudeConnector;
use App\Mcp\Support\ConnectorTokens;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Livewire\Livewire;

beforeEach(function () {
    usePassportKeys();
});

test('the page explains how to connect Claude', function () {
    actingAsAdmin();

    $this->get(ClaudeConnector::getUrl())
        ->assertOk()
        ->assertSee(url('/mcp'))
        ->assertSee('Add custom connector')
        ->assertSee('claude mcp add --transport http portfolio')
        ->assertSee('Nothing is connected yet');
});

test('it warns about missing OAuth keys and a disabled connector', function () {
    actingAsAdmin();
    config()->set('passport.private_key', null);
    config()->set('portfolio.mcp.enabled', false);
    Passport::loadKeysFrom(storage_path('framework/testing/no-oauth-keys'));

    try {
        Livewire::test(ClaudeConnector::class)
            ->assertSee('OAuth keys are missing')
            ->assertSee('The connector is switched off');
    } finally {
        Passport::$keyPath = null;
    }
});

test('a personal access token is created and shown once', function () {
    $user = actingAsAdmin();

    $page = Livewire::test(ClaudeConnector::class)
        ->callAction('createToken', ['name' => 'Laptop'])
        ->assertHasNoActionErrors()
        ->assertSee('Your new token')
        ->assertSee('--header');

    $token = Token::query()->sole();

    expect($token->user_id)->toBe($user->id)
        ->and($token->name)->toBe('Laptop')
        ->and($token->scopes)->toBe(['mcp:use'])
        ->and($page->get('plainToken'))->toBeString()->not->toBeEmpty();

    Livewire::test(ClaudeConnector::class)
        ->assertDontSee('Your new token')
        ->assertCanSeeTableRecords([$token]);
});

test('connected apps and tokens can be revoked', function () {
    $user = actingAsAdmin();
    $token = ConnectorTokens::create($user, 'Laptop')->token;
    $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient('Claude', ['https://claude.ai/api/mcp/auth_callback'], confidential: false);
    $oauth = Token::query()->create([
        'id' => str_repeat('a', 80),
        'user_id' => $user->id,
        'client_id' => $client->id,
        'scopes' => ['mcp:use'],
        'revoked' => false,
        'expires_at' => now()->addDay(),
    ]);
    $someoneElses = ConnectorTokens::create(User::factory()->create(), 'Other')->token;

    Livewire::test(ClaudeConnector::class)
        ->assertCanSeeTableRecords([$token, $oauth])
        ->assertCanNotSeeTableRecords([$someoneElses])
        ->assertSee('Returns to claude.ai')
        ->callAction(TestAction::make('revoke')->table($oauth));

    expect($oauth->refresh()->revoked)->toBeTrue()
        ->and($token->refresh()->revoked)->toBeFalse();
});
