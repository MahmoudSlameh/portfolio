<?php

use Laravel\Ai\AiManager;
use Laravel\Ai\Contracts\Providers\TextProvider;

test('every provider the panel offers is a text provider in the ai sdk', function (string $provider) {
    config()->set("ai.providers.{$provider}.key", 'test-key');
    config()->set("ai.providers.{$provider}.url", config("ai.providers.{$provider}.url") ?? 'http://localhost:1234/v1');

    $instance = app(AiManager::class)->textProvider($provider);

    expect($instance)->toBeInstanceOf(TextProvider::class);

    // Providers without an SDK default must be marked so the panel asks for a model.
    if (config("studio.ai.providers.{$provider}.model") === true) {
        expect(fn () => $instance->defaultTextModel())->toThrow(InvalidArgumentException::class);
    } else {
        expect($instance->defaultTextModel())->not->toBeEmpty();
    }
})->with(array_keys((require dirname(__DIR__, 3).'/config/studio.php')['ai']['providers']));
