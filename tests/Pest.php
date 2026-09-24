<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
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

function something()
{
    // ..
}
