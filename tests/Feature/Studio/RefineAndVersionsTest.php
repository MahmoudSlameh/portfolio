<?php

use App\Ai\Agents\TemplateDesigner;
use App\Enums\StudioSource;
use App\Enums\StudioStatus;
use App\Filament\Pages\Appearance;
use App\Filament\Pages\StudioTemplateVersions;
use App\Jobs\GenerateStudioTemplate;
use App\Models\SiteSetting;
use App\Models\StudioTemplate;
use App\Models\StudioTemplateVersion;
use App\Models\User;
use App\Support\Studio\GenerationRefused;
use App\Support\Studio\SpecCatalogue;
use App\Support\Studio\StudioGenerator;
use App\Support\Templates\TemplateManager;
use App\Support\Templates\TemplateRegistry;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('studio.ai.provider', null);
    SiteSetting::current()->update(['ai_provider' => 'anthropic', 'ai_api_key' => 'sk-test', 'ai_daily_limit' => 5]);
    Queue::fake([GenerateStudioTemplate::class]);
});

/**
 * A ready template with two versions: v1 (radius none) and v2 (radius lg), v2 active.
 */
function twoVersions(): StudioTemplate
{
    $template = StudioTemplate::factory()->ready()->create();
    $spec = SpecCatalogue::example();
    $spec['tokens']['radius'] = 'lg';
    $template->activate($template->addVersion($spec, ['parent' => $template->activeVersion]));

    return $template->refresh();
}

function goLive(StudioTemplate $template): void
{
    SiteSetting::current()->update(['active_template' => $template->templateId()]);
}

function refineOutput(string $radius = 'full'): array
{
    $spec = SpecCatalogue::example();
    $spec['tokens']['radius'] = $radius;

    return ['spec' => (string) json_encode($spec), 'summary' => 'Rounder.'];
}

function versionAction(string $name, StudioTemplateVersion $version): TestAction
{
    return TestAction::make($name)->table($version);
}

test('a template with a version stays on the site while a generation runs or fails', function (StudioStatus $status) {
    $template = StudioTemplate::factory()->ready()->create();
    goLive($template);
    $template->forceFill(['status' => $status])->save();

    expect(app(TemplateRegistry::class)->has($template->templateId()))->toBeTrue()
        ->and(app(TemplateManager::class)->active()->id)->toBe($template->templateId());
})->with([StudioStatus::Queued, StudioStatus::InProgress, StudioStatus::Failed]);

test('only a template with a version can be refined', function () {
    $draft = StudioTemplate::query()->create(['name' => 'Empty', 'source' => StudioSource::Manual]);

    expect(fn () => app(StudioGenerator::class)->refine($draft, 'Darker'))->toThrow(GenerationRefused::class);
});

test('refining the live template saves a new version without changing the site', function () {
    User::factory()->create();
    $template = StudioTemplate::factory()->ready()->create(['name' => 'Night Shift']);
    goLive($template);
    TemplateDesigner::fake([refineOutput()]);

    $generation = app(StudioGenerator::class)->refine($template, 'Rounder corners');
    app()->call([new GenerateStudioTemplate($generation), 'handle']);
    $template->refresh();
    $v2 = $template->versions()->where('number', 2)->firstOrFail();

    expect($template->status)->toBe(StudioStatus::Ready)
        ->and($template->activeVersion?->number)->toBe(1)
        ->and($v2->parent?->number)->toBe(1)
        ->and($v2->spec['tokens']['radius'])->toBe('full')
        ->and(User::query()->first()?->notifications()->first()?->data['title'] ?? null)->toBe('Version 2 of Night Shift is ready');

    TemplateDesigner::assertPrompted(fn ($prompt): bool => $prompt->contains('Revise the portfolio template "Night Shift"')
        && $prompt->contains('<request>')
        && $prompt->contains('Rounder corners')
        && $prompt->contains('<start-spec>'));
});

test('refining a template that is not live activates the new version', function () {
    $template = StudioTemplate::factory()->ready()->create();
    TemplateDesigner::fake([refineOutput()]);

    app()->call([new GenerateStudioTemplate(app(StudioGenerator::class)->refine($template, 'Rounder')), 'handle']);

    expect($template->refresh()->activeVersion?->number)->toBe(2)
        ->and($template->activeVersion?->spec['tokens']['radius'])->toBe('full');
});

test('the owner can preview any version', function () {
    actingAsAdmin();
    $template = twoVersions();
    goLive($template);

    $this->get('/?template='.$template->templateId().'&version=1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('studio.spec.tokens.radius', 'none')
            ->where('template.version', 1)
            ->where('template.isPreview', true));

    // Kept while browsing, like a template preview.
    $this->get('/projects')->assertInertia(fn (Assert $page) => $page->where('studio.spec.tokens.radius', 'none'));

    $this->get('/?template=reset')->assertInertia(fn (Assert $page) => $page
        ->where('studio.spec.tokens.radius', 'lg')
        ->where('template.version', null)
        ->where('template.isPreview', false));
});

test('an unknown version or a visitor gets the active version', function () {
    $template = twoVersions();
    goLive($template);

    $this->get('/?template='.$template->templateId().'&version=1')
        ->assertInertia(fn (Assert $page) => $page->where('studio.spec.tokens.radius', 'lg')->where('template.version', null));

    actingAsAdmin();

    $this->get('/?template='.$template->templateId().'&version=99')
        ->assertInertia(fn (Assert $page) => $page->where('studio.spec.tokens.radius', 'lg')->where('template.version', null));
});

test('the versions page lists versions and rolls back', function () {
    actingAsAdmin();
    $template = twoVersions();
    goLive($template);
    $v1 = $template->versions()->where('number', 1)->firstOrFail();
    $v2 = $template->versions()->where('number', 2)->firstOrFail();

    Livewire::test(StudioTemplateVersions::class, ['template' => $template->id])
        ->assertOk()
        ->assertSee('This template is live')
        ->assertCanSeeTableRecords([$v2, $v1])
        ->assertSeeInOrder(['Edited from v1', 'Created'])
        ->assertActionHidden(versionAction('activate', $v2))
        ->assertActionHasUrl(versionAction('preview', $v1), url('/?template='.$template->templateId().'&version=1'))
        ->callAction(versionAction('activate', $v1))
        ->assertNotified('Version 1 is active');

    expect($template->refresh()->activeVersion?->number)->toBe(1);
    $this->get('/')->assertInertia(fn (Assert $page) => $page->where('studio.spec.tokens.radius', 'none'));
});

test('the versions page 404s for an unknown template', function () {
    actingAsAdmin();

    $this->get(StudioTemplateVersions::getUrl(['template' => '01jxxxxxxxxxxxxxxxxxxxxxxx']))->assertNotFound();
});

test('cards offer refine and versions', function () {
    actingAsAdmin();
    $template = twoVersions();
    $key = 'studio-'.$template->id;
    $card = fn (string $name): TestAction => TestAction::make("{$name}_{$key}")->schemaComponent("template-{$key}");

    Livewire::test(Appearance::class)
        ->assertActionHasLabel($card('versions'), 'Versions (2)')
        ->assertActionHasUrl($card('versions'), StudioTemplateVersions::getUrl(['template' => $template->id]))
        ->callAction($card('refine'), data: ['instruction' => 'Warmer colours'])
        ->assertNotified("Refining {$template->name}…");

    expect($template->refresh()->status)->toBe(StudioStatus::Queued)
        ->and($template->generations()->first()?->start_from)->toBe($template->templateId());

    Livewire::test(Appearance::class)->assertActionDoesNotExist($card('refine'));
});
