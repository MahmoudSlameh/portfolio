<?php

use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Support\TypeScriptInterfaces;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

/*
 * Asserts an array has exactly the properties of a TypeScript interface in
 * resources/js/types/content.ts (all required keys, optional keys allowed, nothing else).
 */
expect()->extend('toMatchInterface', function (string $interface) {
    $keys = TypeScriptInterfaces::keys($interface);
    $actual = array_keys($this->value);

    expect(array_values(array_diff($keys['required'], $actual)))->toBe([], "{$interface}: missing keys")
        ->and(array_values(array_diff($actual, [...$keys['required'], ...$keys['optional']])))->toBe([], "{$interface}: unexpected keys");

    return $this;
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Sign in a panel user and make the admin panel current (for Filament Livewire tests).
 */
function actingAsAdmin(): User
{
    $user = User::factory()->create();

    test()->actingAs($user);
    Filament::setCurrentPanel('admin');

    return $user;
}

/**
 * Ids of the code templates (`resources/js/templates/<id>/template.json`), read without booting the
 * app so they can feed datasets. Every template added to the folder is tested automatically.
 *
 * @return list<string>
 */
function templateIds(): array
{
    $ids = array_map(fn (string $file): string => basename(dirname($file)), glob(dirname(__DIR__).'/resources/js/templates/*/template.json') ?: []);
    sort($ids);

    return $ids;
}

dataset('templates', fn (): array => templateIds());

/**
 * The demo content without its image conversions (they would run synchronously and take seconds per
 * image; the CV has no images anyway).
 */
function seedCvDemo(): void
{
    Storage::fake('public');
    Queue::fake();
    test()->seed(DemoContentSeeder::class);
}

/**
 * An RSA key pair for Passport (OAuth tokens are signed with it), made once per run.
 */
function usePassportKeys(): void
{
    static $keys = null;

    if ($keys === null) {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $private);
        $keys = ['private' => $private, 'public' => openssl_pkey_get_details($key)['key']];
    }

    config()->set('passport.private_key', $keys['private']);
    config()->set('passport.public_key', $keys['public']);
}
