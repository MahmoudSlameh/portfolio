<?php

use App\Filament\Pages\AiSettingsPage;
use App\Models\SiteSetting;
use App\Support\Studio\AiSettings;
use Filament\Actions\Testing\TestAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\AiManager;
use Laravel\Ai\AnonymousAgent;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('studio.ai.provider', null);
    config()->set('studio.ai.model', null);

    foreach (array_keys((array) config('ai.providers')) as $provider) {
        config()->set("ai.providers.{$provider}.key", null);
    }
});

/**
 * @param  array<string, mixed>  $attributes
 */
function aiPanel(array $attributes): SiteSetting
{
    $settings = SiteSetting::current();
    $settings->update($attributes);

    return $settings;
}

test('ai is disabled when nothing is configured', function () {
    $settings = AiSettings::current();

    expect($settings->enabled())->toBeFalse()
        ->and($settings->source)->toBe('none')
        ->and($settings->problem)->toContain('Choose a provider');
});

test('.env is used when the panel has no provider', function () {
    config()->set('studio.ai.provider', 'anthropic');
    config()->set('studio.ai.model', 'claude-sonnet-5');
    config()->set('ai.providers.anthropic.key', 'env-key');

    $settings = AiSettings::current();

    expect($settings->enabled())->toBeTrue()
        ->and($settings->source)->toBe('env')
        ->and($settings->provider)->toBe('anthropic')
        ->and($settings->model)->toBe('claude-sonnet-5')
        ->and($settings->keyFromPanel)->toBeFalse();
});

test('panel settings win over .env', function () {
    config()->set('studio.ai.provider', 'anthropic');
    config()->set('studio.ai.model', 'claude-sonnet-5');
    config()->set('ai.providers.anthropic.key', 'env-key');
    aiPanel(['ai_provider' => 'openai', 'ai_model' => null, 'ai_api_key' => 'panel-key', 'ai_daily_limit' => 5]);

    $settings = AiSettings::current();

    expect($settings->source)->toBe('panel')
        ->and($settings->provider)->toBe('openai')
        ->and($settings->model)->toBeNull() // the .env model belongs to the .env provider
        ->and($settings->keyFromPanel)->toBeTrue()
        ->and($settings->dailyLimit)->toBe(5)
        ->and($settings->enabled())->toBeTrue();
});

test('a panel provider without a panel key uses that provider key from .env', function () {
    config()->set('ai.providers.gemini.key', 'env-gemini');
    aiPanel(['ai_provider' => 'gemini']);

    $settings = AiSettings::current();

    expect($settings->enabled())->toBeTrue()
        ->and($settings->hasKey())->toBeTrue()
        ->and($settings->keyFromPanel)->toBeFalse();
});

test('turning ai off in the panel disables it even with .env set', function () {
    config()->set('studio.ai.provider', 'anthropic');
    config()->set('ai.providers.anthropic.key', 'env-key');
    aiPanel(['ai_enabled' => false]);

    expect(AiSettings::current()->enabled())->toBeFalse()
        ->and(AiSettings::current()->problem)->toContain('turned off');
});

test('missing pieces are reported', function (array $attributes, ?string $problem) {
    config()->set('ai.providers.ollama.url', null);
    aiPanel($attributes);

    expect(AiSettings::current()->problem)->toBe($problem);
})->with([
    'no key' => [['ai_provider' => 'openai'], 'Add an API key in Site → AI, or set the provider key in .env.'],
    'no url' => [['ai_provider' => 'ollama'], 'This provider needs a base URL.'],
    'ollama needs no key' => [['ai_provider' => 'ollama', 'ai_base_url' => 'http://localhost:11434'], null],
    'no model' => [['ai_provider' => 'openai-compatible', 'ai_base_url' => 'https://llm.test/v1'], 'This provider needs a model id.'],
    'compatible ready' => [['ai_provider' => 'openai-compatible', 'ai_base_url' => 'https://llm.test/v1', 'ai_model' => 'llama3.1'], null],
]);

test('register writes the settings into the sdk as the studio provider', function () {
    aiPanel(['ai_provider' => 'anthropic', 'ai_api_key' => 'panel-key']);

    $name = AiSettings::current()->register();

    expect($name)->toBe('studio')
        ->and(config('ai.providers.studio.driver'))->toBe('anthropic')
        ->and(config('ai.providers.studio.key'))->toBe('panel-key')
        ->and(app(AiManager::class)->textProvider('studio'))->toBeInstanceOf(TextProvider::class)
        ->and(AiSettings::current()->effectiveModel())->not->toBeEmpty();
});

test('the key is encrypted at rest and never serialised', function () {
    $settings = aiPanel(['ai_provider' => 'openai', 'ai_api_key' => 'sk-very-secret']);

    ob_start();
    var_dump(AiSettings::current());
    $dump = (string) ob_get_clean();

    expect((string) DB::table('site_settings')->value('ai_api_key'))->not->toContain('sk-very-secret')
        ->and($settings->fresh()?->ai_api_key)->toBe('sk-very-secret')
        ->and($settings->toJson())->not->toContain('sk-very-secret')
        ->and($settings->toArray())->not->toHaveKey('ai_api_key')
        ->and($dump)->not->toContain('sk-very-secret');
});

test('the key never reaches the panel page or the public site', function () {
    actingAsAdmin();
    aiPanel(['ai_provider' => 'openai', 'ai_api_key' => 'sk-very-secret']);

    $page = Livewire::test(AiSettingsPage::class)
        ->assertOk()
        ->assertDontSee('sk-very-secret')
        ->assertSee('A key is saved');

    expect($page->get('data.ai_api_key'))->toBeNull();

    $this->get('/')->assertOk()->assertDontSee('sk-very-secret', escape: false);
});

test('a typed key is saved, an empty field keeps it and it can be removed', function () {
    actingAsAdmin();

    Livewire::test(AiSettingsPage::class)
        ->fillForm(['ai_provider' => 'anthropic', 'ai_api_key' => 'sk-first'])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertSet('data.ai_api_key', null);

    expect(SiteSetting::current()->fresh()?->ai_api_key)->toBe('sk-first');

    Livewire::test(AiSettingsPage::class)
        ->fillForm(['ai_model' => 'claude-sonnet-5'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(SiteSetting::current()->fresh()?->ai_api_key)->toBe('sk-first');

    Livewire::test(AiSettingsPage::class)
        ->fillForm(['forget_api_key' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(SiteSetting::current()->fresh()?->ai_api_key)->toBeNull();
});

test('openai-compatible requires a base url and a model', function () {
    actingAsAdmin();

    Livewire::test(AiSettingsPage::class)
        ->fillForm(['ai_provider' => 'openai-compatible', 'ai_base_url' => null, 'ai_model' => null])
        ->call('save')
        ->assertHasFormErrors(['ai_base_url' => 'required', 'ai_model' => 'required']);
});

test('test connection prompts the provider with the unsaved form values', function () {
    actingAsAdmin();
    AnonymousAgent::fake(['OK']);

    Livewire::test(AiSettingsPage::class)
        ->fillForm(['ai_provider' => 'anthropic', 'ai_api_key' => 'sk-unsaved'])
        ->callAction(TestAction::make('testConnection'))
        ->assertNotified();

    AnonymousAgent::assertPrompted('Ping');
    expect(SiteSetting::current()->fresh()?->ai_api_key)->toBeNull() // testing does not save
        ->and(config('ai.providers.studio.key'))->toBe('sk-unsaved');
});

test('test connection explains what is missing', function () {
    actingAsAdmin();
    AnonymousAgent::fake(['OK']);

    Livewire::test(AiSettingsPage::class)
        ->fillForm(['ai_provider' => 'openai'])
        ->callAction(TestAction::make('testConnection'))
        ->assertNotified('AI is not ready');

    AnonymousAgent::assertNeverPrompted();
});

test('a failed connection is reported without the key', function () {
    actingAsAdmin();
    AnonymousAgent::fake(fn () => throw new RuntimeException('401: invalid key sk-leaky'));

    Livewire::test(AiSettingsPage::class)
        ->fillForm(['ai_provider' => 'openai', 'ai_api_key' => 'sk-leaky'])
        ->callAction(TestAction::make('testConnection'))
        ->assertNotified(Notification::make()->danger()->title('Connection failed')->body('401: invalid key [key]')->persistent());
});
