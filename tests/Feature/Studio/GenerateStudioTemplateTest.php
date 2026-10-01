<?php

use App\Ai\Agents\TemplateDesigner;
use App\Enums\StudioSource;
use App\Enums\StudioStatus;
use App\Jobs\GenerateStudioTemplate;
use App\Models\SiteSetting;
use App\Models\StudioGeneration;
use App\Models\StudioTemplate;
use App\Models\User;
use App\Support\Studio\GenerationRefused;
use App\Support\Studio\SpecCatalogue;
use App\Support\Studio\StudioGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\TextUsage;
use Laravel\Ai\Responses\StructuredTextResponse;

beforeEach(function () {
    config()->set('studio.ai.provider', null);
    SiteSetting::current()->update(['ai_provider' => 'anthropic', 'ai_api_key' => 'sk-test-key', 'ai_daily_limit' => 5]);
    $this->owner = User::factory()->create();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array{spec: string, summary: string}
 */
function designed(array $overrides = [], string $summary = 'A calm serif design.'): array
{
    return ['spec' => (string) json_encode([...SpecCatalogue::example(), ...$overrides]), 'summary' => $summary];
}

function queuedGeneration(string $prompt = 'Calm and editorial', ?string $startFrom = null): StudioGeneration
{
    Queue::fake([GenerateStudioTemplate::class]);
    $template = StudioTemplate::query()->create(['name' => 'Night Shift', 'source' => StudioSource::Manual]);

    return app(StudioGenerator::class)->start($template, $prompt, $startFrom);
}

function runGeneration(StudioGeneration $generation): StudioTemplate
{
    // The queue is faked by queuedGeneration(), so run the job here, as a worker would.
    app()->call([new GenerateStudioTemplate($generation), 'handle']);

    return $generation->template()->firstOrFail()->refresh();
}

test('starting a generation queues the job and records the attempt', function () {
    $generation = queuedGeneration();
    $template = $generation->template;

    expect($template?->status)->toBe(StudioStatus::Queued)
        ->and($template?->source)->toBe(StudioSource::Ai)
        ->and($template?->current_step)->toBe('Waiting for a worker…')
        ->and($generation->provider)->toBe('anthropic')
        ->and(app(StudioGenerator::class)->remainingToday())->toBe(4);

    Queue::assertPushed(GenerateStudioTemplate::class, fn (GenerateStudioTemplate $job): bool => $job->generation->is($generation));
});

test('a generation is refused when ai is not ready', function () {
    SiteSetting::current()->update(['ai_enabled' => false]);

    expect(fn () => queuedGeneration())->toThrow(GenerationRefused::class, 'turned off');
    Queue::assertNothingPushed();
});

test('the daily limit counts every attempt, failed ones too', function () {
    SiteSetting::current()->update(['ai_daily_limit' => 2]);
    StudioGeneration::query()->create(['status' => StudioStatus::Failed]);
    StudioGeneration::query()->create(['status' => StudioStatus::Ready]);
    StudioGeneration::query()->create(['status' => StudioStatus::Ready])->forceFill(['created_at' => now()->subDay()])->save();

    expect(app(StudioGenerator::class)->remainingToday())->toBe(0)
        ->and(fn () => queuedGeneration())->toThrow(GenerationRefused::class, "Today's limit of 2 generations");
});

test('a valid design becomes version 1', function () {
    TemplateDesigner::fake([designed(['name' => 'Whatever the model said'])]);
    $generation = queuedGeneration();

    $template = runGeneration($generation);
    $version = $template->activeVersion;

    expect($template->status)->toBe(StudioStatus::Ready)
        ->and($template->progress)->toBe(100)
        ->and($template->error)->toBeNull()
        ->and($template->description)->toBe('A calm serif design.')
        ->and($version?->number)->toBe(1)
        ->and($version?->spec['name'])->toBe('Night Shift')
        ->and($version?->prompt)->toBe('Calm and editorial')
        ->and($version?->provider)->toBe('anthropic')
        ->and($generation->refresh()->status)->toBe(StudioStatus::Ready)
        ->and($generation->turns)->toBe(1)
        ->and($this->owner->notifications()->count())->toBe(1)
        ->and($this->owner->notifications()->first()?->data['title'] ?? null)->toBe('Night Shift is ready');

    TemplateDesigner::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains('<request>') && $prompt->contains('Calm and editorial'));
    expect(config('ai.providers.studio.key'))->toBe('sk-test-key');
});

test('an invalid design is repaired with the validator errors', function () {
    $broken = designed();
    $broken['spec'] = (string) json_encode([...SpecCatalogue::example(), 'tokens' => [...SpecCatalogue::example()['tokens'], 'radius' => 'huge']]);
    TemplateDesigner::fake([$broken, designed()]);

    $template = runGeneration($generation = queuedGeneration());

    expect($template->status)->toBe(StudioStatus::Ready)
        ->and($generation->refresh()->turns)->toBe(2);

    TemplateDesigner::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains('Your spec was rejected') && $prompt->contains('tokens.radius must be one of'));
});

test('broken json is repaired like any other error', function () {
    TemplateDesigner::fake([['spec' => '{"$schema": "studio/v1",', 'summary' => ''], designed()]);

    expect(runGeneration(queuedGeneration())->status)->toBe(StudioStatus::Ready);
    TemplateDesigner::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains('is not valid JSON'));
});

test('a design that is still invalid after two repairs fails', function () {
    $broken = ['spec' => '{"nope": true}', 'summary' => ''];
    TemplateDesigner::fake([$broken, $broken, $broken]);

    $template = runGeneration($generation = queuedGeneration());

    expect($template->status)->toBe(StudioStatus::Failed)
        ->and($template->error)->toStartWith('The design still had')
        ->toContain('after 2 fixes')
        ->and($template->versions()->count())->toBe(0)
        ->and($generation->refresh()->status)->toBe(StudioStatus::Failed)
        ->and($generation->turns)->toBe(3)
        ->and($this->owner->notifications()->first()?->data['title'] ?? null)->toBe('Night Shift could not be generated');
});

test('a provider error fails the template without leaking the key', function () {
    TemplateDesigner::fake(fn () => throw new RuntimeException('401 Unauthorized for key sk-test-key'));

    $template = runGeneration(queuedGeneration());

    expect($template->status)->toBe(StudioStatus::Failed)
        ->and($template->error)->toBe('401 Unauthorized for key [key]')
        ->and(json_encode($this->owner->notifications()->first()?->data))->not->toContain('sk-test-key');
});

test('reference images are attached to the prompt', function () {
    Storage::fake('local');
    TemplateDesigner::fake([designed()]);
    $generation = queuedGeneration();
    $generation->template?->addMedia(UploadedFile::fake()->image('reference.png', 64, 64))->toMediaCollection('reference');

    runGeneration($generation);

    TemplateDesigner::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->attachments->count() === 1 && $prompt->contains('One reference image is attached'));
});

test('a generation can start from a studio template or a built-in template', function (string $startFrom, string $expected) {
    TemplateDesigner::fake([designed()]);
    $source = StudioTemplate::factory()->ready()->create();

    runGeneration(queuedGeneration('Darker', $startFrom === 'studio' ? $source->templateId() : $startFrom));

    TemplateDesigner::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains($expected));
})->with([
    'studio' => ['studio', '<start-spec>'],
    'built-in' => ['terminal', 'built-in template "Terminal"'],
]);

test('token usage is recorded on the attempt and the version', function () {
    $response = fn (array $structured, int $in, int $out) => new StructuredTextResponse($structured, (string) json_encode($structured), new TextUsage($in, $out), new Meta('anthropic', 'claude-sonnet-5'));
    TemplateDesigner::fake([$response(['spec' => '{}', 'summary' => ''], 1000, 200), $response(designed(), 1500, 300)]);

    $template = runGeneration($generation = queuedGeneration());

    expect($generation->refresh()->input_tokens)->toBe(2500)
        ->and($generation->output_tokens)->toBe(500)
        ->and($generation->model)->toBe('claude-sonnet-5')
        ->and($template->activeVersion?->input_tokens)->toBe(2500)
        ->and($template->activeVersion?->output_tokens)->toBe(500)
        ->and($template->activeVersion?->model)->toBe('claude-sonnet-5');
});

test('a timeout marks the template as failed', function () {
    $generation = queuedGeneration();
    $generation->template?->forceFill(['status' => StudioStatus::InProgress, 'progress' => 30])->save();

    (new GenerateStudioTemplate($generation))->failed(new RuntimeException('Job has timed out.'));

    expect($generation->template?->refresh()->status)->toBe(StudioStatus::Failed)
        ->and($generation->refresh()->error)->toBe('Job has timed out.');
});

test('a template deleted while queued is skipped', function () {
    $generation = queuedGeneration();
    $generation->template?->delete();

    app()->call([new GenerateStudioTemplate($generation->refresh()), 'handle']);

    expect($generation->refresh()->status)->toBe(StudioStatus::Queued);
});
