<?php

use App\Filament\Pages\Appearance;
use App\Jobs\CaptureStudioScreenshot;
use App\Models\SiteSetting;
use App\Models\StudioTemplate;
use App\Support\Studio\SpecCatalogue;
use App\Support\Studio\StudioSwatch;
use App\Support\Templates\TemplateManager;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('portfolio.templates.dev_gallery', false);
    config()->set('studio.screenshots.chrome', null);
});

function enableScreenshots(): void
{
    config()->set('studio.screenshots.chrome', '/usr/bin/chromium');
}

/**
 * Fake Chrome: write a PNG where --screenshot= points, or fail.
 */
function fakeChrome(bool $succeeds = true): void
{
    $png = UploadedFile::fake()->image('shot.png', 32, 20)->get();

    Process::fake(function (PendingProcess $process) use ($png, $succeeds) {
        if ($succeeds) {
            $target = Str::after(collect($process->command)->first(fn (string $arg): bool => str_starts_with($arg, '--screenshot=')), '--screenshot=');
            file_put_contents($target, $png);

            return Process::result();
        }

        return Process::result(errorOutput: 'Chrome crashed', exitCode: 1);
    });
}

test('the swatch draws both palettes and escapes the name', function () {
    $spec = [...SpecCatalogue::example(), 'name' => 'A <b>&</b> B'];
    $svg = StudioSwatch::svg($spec);

    expect($svg)->toStartWith('<svg')
        ->toContain($spec['tokens']['colors']['light']['bg'])
        ->toContain($spec['tokens']['colors']['dark']['bg'])
        ->toContain($spec['tokens']['colors']['light']['accent'])
        ->toContain('Header: bar-sticky · Hero: split-portrait · Radius: none')
        ->toContain('A &lt;b&gt;&amp;&lt;/b&gt; B')
        ->not->toContain('<b>')
        ->and(StudioSwatch::dataUri($spec))->toStartWith('data:image/svg+xml;base64,');

    expect(simplexml_load_string($svg))->not->toBeFalse();
});

test('cards without a screenshot show the swatch', function () {
    actingAsAdmin();
    StudioTemplate::factory()->ready()->create();

    Livewire::test(Appearance::class)
        ->assertSeeHtml('data:image/svg+xml;base64,')
        ->assertDontSee('No screenshot yet');
});

test('a signed url renders the template for a visitor, and only then', function () {
    $template = StudioTemplate::factory()->ready()->create();
    $url = CaptureStudioScreenshot::url($template);
    $path = Str::after($url, rtrim((string) config('app.url'), '/'));

    $this->get($path)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('studio/Home')
            ->where('template.id', $template->templateId())
            ->where('template.isPreview', false)
            ->where('seo.robots', 'noindex,nofollow'));

    // Unsigned, tampered or expired: the active template.
    $active = app(TemplateManager::class)->active()->namespace();
    $this->get('/?_template='.$template->templateId())->assertInertia(fn (Assert $page) => $page->component("{$active}/Home"));
    $this->get(str_replace('_theme=light', '_theme=dark', $path))->assertInertia(fn (Assert $page) => $page->component("{$active}/Home"));
    $this->travel(6)->minutes();
    $this->get($path)->assertInertia(fn (Assert $page) => $page->component("{$active}/Home"));
});

test('signed renders never include the analytics snippet', function () {
    SiteSetting::current()->update(['analytics_snippet' => '<script data-analytics></script>']);
    $template = StudioTemplate::factory()->ready()->create();
    $path = Str::after(CaptureStudioScreenshot::url($template), rtrim((string) config('app.url'), '/'));

    $this->get('/')->assertSee('data-analytics', false);
    $this->get($path)->assertDontSee('data-analytics', false);
});

test('the base url for chrome can differ from APP_URL', function () {
    config()->set('studio.screenshots.base_url', 'http://127.0.0.1:8080/');
    $template = StudioTemplate::factory()->ready()->create();

    expect(CaptureStudioScreenshot::url($template))->toStartWith('http://127.0.0.1:8080/?_template=');
    // The host is not signed: the same query works on the app's own URL.
    $this->get('/?'.Str::after(CaptureStudioScreenshot::url($template), '?'))
        ->assertInertia(fn (Assert $page) => $page->where('template.id', $template->templateId()));
});

test('a capture saves the screenshot on the template', function () {
    Storage::fake('public');
    $template = StudioTemplate::factory()->ready()->create();
    enableScreenshots();
    config()->set('studio.screenshots.no_sandbox', true);
    fakeChrome();

    (new CaptureStudioScreenshot($template))->handle();

    expect($template->refresh()->getFirstMedia('screenshot'))->not->toBeNull();
    Process::assertRan(fn (PendingProcess $process): bool => $process->command[0] === '/usr/bin/chromium'
        && in_array('--headless=new', $process->command, true)
        && in_array('--no-sandbox', $process->command, true)
        && in_array('--window-size=1280,800', $process->command, true)
        && str_contains((string) end($process->command), '_signature='));
    expect(glob(storage_path('app/private/studio-screenshots/*')))->toBe([]);
});

test('a failed capture keeps the swatch and cleans up', function () {
    $template = StudioTemplate::factory()->ready()->create();
    enableScreenshots();
    fakeChrome(succeeds: false);

    expect(fn () => (new CaptureStudioScreenshot($template))->handle())->toThrow(RuntimeException::class, 'Chrome crashed');
    expect($template->refresh()->getFirstMedia('screenshot'))->toBeNull();
});

test('activating a version queues a capture only when screenshots are enabled', function () {
    Queue::fake();
    $template = StudioTemplate::factory()->ready()->create();
    Queue::assertNotPushed(CaptureStudioScreenshot::class);

    enableScreenshots();
    $template->activate($template->addVersion(SpecCatalogue::example()));

    Queue::assertPushed(CaptureStudioScreenshot::class, fn (CaptureStudioScreenshot $job): bool => $job->template->is($template));
});

test('the card offers a refresh only when screenshots are enabled', function () {
    actingAsAdmin();
    Queue::fake();
    $template = StudioTemplate::factory()->ready()->create();
    $key = 'studio-'.$template->id;
    $action = TestAction::make("screenshot_{$key}")->schemaComponent("template-{$key}");

    Livewire::test(Appearance::class)->assertActionDoesNotExist($action);

    enableScreenshots();

    Livewire::test(Appearance::class)
        ->callAction($action)
        ->assertNotified('Taking a screenshot…');

    Queue::assertPushed(CaptureStudioScreenshot::class);
});

test('the command captures every template, or explains how to enable it', function () {
    $this->artisan('studio:screenshots')->expectsOutputToContain('STUDIO_SCREENSHOT_CHROME')->assertFailed();

    Storage::fake('public');
    $with = StudioTemplate::factory()->ready()->create(['name' => 'Has one']);
    StudioTemplate::factory()->ready()->create(['name' => 'Needs one']);
    enableScreenshots();
    fakeChrome();
    (new CaptureStudioScreenshot($with))->handle();

    $this->artisan('studio:screenshots --missing')->expectsOutputToContain('Needs one')->doesntExpectOutputToContain('Has one')->assertSuccessful();
});
