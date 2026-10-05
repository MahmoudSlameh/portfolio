<?php

use App\Filament\Pages\ClaudeConnector;
use App\Mcp\Support\OAuthKeys;
use Illuminate\Support\Facades\File;
use Laravel\Passport\Passport;
use Livewire\Livewire;

beforeEach(function () {
    $this->keyDirectory = storage_path('framework/testing/oauth-keys-'.uniqid());
    File::ensureDirectoryExists($this->keyDirectory);
    config()->set('passport.private_key', null);
    config()->set('passport.public_key', null);
    Passport::loadKeysFrom($this->keyDirectory);
});

afterEach(function () {
    Passport::$keyPath = null;
    File::deleteDirectory($this->keyDirectory);
});

function writeKeys(string $directory, int $mode): void
{
    foreach (['oauth-private.key', 'oauth-public.key'] as $file) {
        File::put("{$directory}/{$file}", 'key');
        chmod("{$directory}/{$file}", $mode);
    }
}

test('missing key files are reported', function () {
    expect(array_keys(OAuthKeys::problems()))->toBe(['OAuth keys are missing']);
});

test('key files readable by everyone are reported with the chmod to run', function () {
    writeKeys($this->keyDirectory, 0644);

    $problems = OAuthKeys::problems();

    expect($problems)->toHaveKey('The OAuth key permissions are too open')
        ->and($problems['The OAuth key permissions are too open'])->toContain('chmod 600')->toContain('oauth-private.key')->toContain('oauth-public.key');
})->skipOnWindows();

test('keys with owner-only permissions are fine', function () {
    writeKeys($this->keyDirectory, 0600);

    expect(OAuthKeys::problems())->toBe([]);
});

test('keys from the environment are not checked on disk', function () {
    config()->set('passport.private_key', 'from-env');
    config()->set('passport.public_key', 'from-env');

    expect(OAuthKeys::problems())->toBe([]);
});

test('the connector page shows the key problem', function () {
    actingAsAdmin();
    writeKeys($this->keyDirectory, 0644);

    Livewire::test(ClaudeConnector::class)
        ->assertSee('The OAuth key permissions are too open')
        ->assertSee('chmod 600');
})->skipOnWindows();
