<?php

use App\Enums\StudioSource;
use App\Enums\StudioStatus;
use App\Filament\Pages\Appearance;
use App\Jobs\GenerateStudioTemplate;
use App\Models\SiteSetting;
use App\Models\StudioGeneration;
use App\Models\StudioTemplate;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    actingAsAdmin();
    Queue::fake([GenerateStudioTemplate::class]);
    config()->set('studio.ai.provider', null);
    SiteSetting::current()->update(['ai_provider' => 'anthropic', 'ai_api_key' => 'sk-test', 'ai_daily_limit' => 5]);
});

function cardAction(string $name, StudioTemplate $template): TestAction
{
    $key = 'studio-'.$template->id;

    return TestAction::make("{$name}_{$key}")->schemaComponent("template-{$key}");
}

test('generate with ai is hidden when ai is switched off and disabled when not ready', function () {
    SiteSetting::current()->update(['ai_enabled' => false]);
    Livewire::test(Appearance::class)->assertActionHidden('generate');

    SiteSetting::current()->update(['ai_enabled' => true, 'ai_api_key' => null]);
    Livewire::test(Appearance::class)->assertActionVisible('generate')->assertActionDisabled('generate');

    SiteSetting::current()->update(['ai_api_key' => 'sk-test']);
    Livewire::test(Appearance::class)->assertActionEnabled('generate');
});

test('a prompt starts a generation', function () {
    Livewire::test(Appearance::class)
        ->mountAction('generate')
        ->assertMountedActionModalSee('5 of 5 generations left today')
        ->fillForm(['name' => 'Night Shift', 'prompt' => 'Dark and neon', 'start_from' => 'terminal'])
        ->callMountedAction()
        ->assertHasNoFormErrors()
        ->assertNotified('Generating Night Shift…')
        ->assertSee('Waiting for a worker…');

    $template = StudioTemplate::query()->sole();
    $generation = $template->generations()->sole();

    expect($template->status)->toBe(StudioStatus::Queued)
        ->and($template->source)->toBe(StudioSource::Ai)
        ->and($generation->prompt)->toBe('Dark and neon')
        ->and($generation->start_from)->toBe('terminal');

    Queue::assertPushed(GenerateStudioTemplate::class);
});

test('reference images are stored on the template', function () {
    Storage::fake('local');

    Livewire::test(Appearance::class)
        ->callAction('generate', data: [
            'name' => 'From a screenshot',
            'prompt' => '',
            'references' => [UploadedFile::fake()->image('dribbble.png', 800, 600)],
        ])
        ->assertHasNoFormErrors();

    $media = StudioTemplate::query()->sole()->getMedia('reference');

    expect($media)->toHaveCount(1)
        ->and($media->first()?->disk)->toBe('local');
});

test('a prompt or an image is required', function () {
    Livewire::test(Appearance::class)
        ->callAction('generate', data: ['name' => 'Empty', 'prompt' => ''])
        ->assertHasFormErrors(['prompt' => 'required_without']);

    expect(StudioTemplate::query()->count())->toBe(0);
});

test('a refused generation creates nothing', function () {
    SiteSetting::current()->update(['ai_daily_limit' => 1]);
    StudioGeneration::query()->create(['status' => StudioStatus::Ready]);

    Livewire::test(Appearance::class)
        ->callAction('generate', data: ['name' => 'Too many', 'prompt' => 'Anything'])
        ->assertNotified('Not started');

    expect(StudioTemplate::query()->count())->toBe(0);
    Queue::assertNothingPushed();
});

test('the page polls only while a generation is queued or running', function () {
    $ready = StudioTemplate::factory()->ready()->create();

    Livewire::test(Appearance::class)->assertDontSeeHtml('wire:poll.3s');

    $working = StudioTemplate::factory()->generating()->create();

    Livewire::test(Appearance::class)
        ->assertSeeHtml('wire:poll.3s')
        ->assertSee("{$working->progress}% — {$working->current_step}")
        ->assertActionDoesNotExist(cardAction('edit', $working))
        ->assertActionVisible(cardAction('edit', $ready));
});

test('a failed ai generation can be retried with the same request', function () {
    $template = StudioTemplate::factory()->failed()->create(['source' => StudioSource::Ai]);
    $template->generations()->create(['status' => StudioStatus::Failed, 'prompt' => 'Warm and bold', 'start_from' => 'minimal']);

    Livewire::test(Appearance::class)
        ->assertSee($template->error)
        ->callAction(cardAction('retry', $template))
        ->assertNotified("Generating {$template->name} again…");

    $latest = $template->generations()->first();

    expect($template->refresh()->status)->toBe(StudioStatus::Queued)
        ->and($template->error)->toBeNull()
        ->and($template->generations()->count())->toBe(2)
        ->and($latest?->prompt)->toBe('Warm and bold')
        ->and($latest?->start_from)->toBe('minimal');

    Queue::assertPushed(GenerateStudioTemplate::class);
});

test('only failed ai templates offer retry', function () {
    $manual = StudioTemplate::factory()->failed()->create(['source' => StudioSource::Manual]);

    Livewire::test(Appearance::class)->assertActionDoesNotExist(cardAction('retry', $manual));
});

test('ai versions show their token usage', function () {
    $template = StudioTemplate::factory()->ready()->create(['source' => StudioSource::Ai]);
    $template->activeVersion?->update(['input_tokens' => 12000, 'output_tokens' => 3400]);

    Livewire::test(Appearance::class)->assertSee('Version 1 · Generated with AI · 15,400 tokens');
});
