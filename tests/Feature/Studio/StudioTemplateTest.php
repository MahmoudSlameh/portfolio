<?php

use App\Enums\StudioStatus;
use App\Filament\Pages\Appearance;
use App\Models\SiteSetting;
use App\Models\StudioTemplate;
use App\Models\StudioTemplateVersion;
use App\Support\Studio\InvalidSpecException;
use App\Support\Studio\SpecCatalogue;
use App\Support\Templates\TemplateManager;
use App\Support\Templates\TemplateRegistry;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('the first valid version becomes active and makes the template ready', function () {
    $template = StudioTemplate::factory()->create();

    $version = $template->addVersion(SpecCatalogue::example(), ['prompt' => 'Loud and brutalist', 'provider' => 'anthropic', 'model' => 'test-model', 'input_tokens' => 1200, 'output_tokens' => 3400]);

    expect($version->number)->toBe(1)
        ->and($version->prompt)->toBe('Loud and brutalist')
        ->and($version->output_tokens)->toBe(3400)
        ->and($version->notes)->toBeNull()
        ->and($version->spec['css'])->toStartWith('[data-template="studio"] .st-hero h1 {')
        ->and($template->fresh()->status)->toBe(StudioStatus::Ready)
        ->and($template->fresh()->progress)->toBe(100)
        ->and($template->fresh()->activeVersion?->is($version))->toBeTrue();
});

test('css is sanitised before it is stored and what was removed is kept as notes', function () {
    $spec = SpecCatalogue::example();
    $spec['css'] = '@import url(https://evil.test/x.css); .card { border: 2px solid; background: url(https://evil.test/x.png) }';

    $version = StudioTemplate::factory()->create()->addVersion($spec);

    expect($version->spec['css'])->toBe("[data-template=\"studio\"] .card {\n  border: 2px solid;\n}\n")
        ->and($version->notes)->toBe(['CSS removed: @import', 'CSS removed: background: url() may only hold an inline data: image']);
});

test('css that is entirely unsafe is dropped from the spec', function () {
    $spec = SpecCatalogue::example();
    $spec['css'] = '@import url(https://evil.test/x.css);';

    expect(StudioTemplate::factory()->create()->addVersion($spec)->spec)->not->toHaveKey('css');
});

test('an invalid spec stores nothing', function () {
    $template = StudioTemplate::factory()->create();
    $spec = SpecCatalogue::example();
    $spec['pages']['home'][0]['variant'] = 'nope';

    expect(fn () => $template->addVersion($spec))->toThrow(InvalidSpecException::class, 'pages.home[0].variant must be one of');
    expect($template->versions()->count())->toBe(0)
        ->and($template->fresh()->status)->toBe(StudioStatus::Draft);
});

test('refinements are new versions that are activated explicitly', function () {
    $template = StudioTemplate::factory()->ready()->create();
    $first = $template->activeVersion;

    $spec = SpecCatalogue::example();
    $spec['tokens']['radius'] = 'lg';
    $second = $template->addVersion($spec, ['prompt' => 'Rounder corners', 'parent' => $first]);

    expect($second->number)->toBe(2)
        ->and($second->parent?->is($first))->toBeTrue()
        ->and($template->fresh()->activeVersion?->is($first))->toBeTrue();

    $template->activate($second);
    expect($template->fresh()->active_version_id)->toBe($second->id);

    // Roll back.
    $template->activate($first);
    expect($template->fresh()->active_version_id)->toBe($first->id);
});

test('a version of another template cannot be activated', function () {
    $template = StudioTemplate::factory()->ready()->create();
    $other = StudioTemplate::factory()->ready()->create();

    expect(fn () => $template->activate($other->activeVersion))->toThrow(InvalidArgumentException::class);
});

test('deleting a template deletes its versions', function () {
    $template = StudioTemplate::factory()->ready()->create();
    $template->delete();

    expect(StudioTemplateVersion::query()->count())->toBe(0);
});

test('the registry lists ready studio templates next to the code templates', function () {
    $ready = StudioTemplate::factory()->ready()->create(['name' => 'Neon']);
    StudioTemplate::factory()->create(); // draft
    StudioTemplate::factory()->failed()->create();
    StudioTemplate::factory()->generating()->create();

    $registry = app(TemplateRegistry::class);
    $definition = $registry->find($ready->templateId());

    expect($registry->studio())->toHaveCount(1)
        ->and(array_keys($registry->code()))->toBe(templateIds())
        ->and($definition?->label)->toBe('Neon')
        ->and($definition?->namespace())->toBe('studio')
        ->and($definition?->isStudio())->toBeTrue()
        ->and($definition?->screenshotUrl())->toBeNull()
        ->and($definition?->preloadFonts)->toBe([
            'node_modules/@fontsource-variable/space-grotesk/files/space-grotesk-latin-wght-normal.woff2',
            'node_modules/@fontsource-variable/geist/files/geist-latin-wght-normal.woff2',
            'node_modules/@fontsource-variable/jetbrains-mono/files/jetbrains-mono-latin-wght-normal.woff2',
        ]);

    // Saving or deleting refreshes the list.
    $ready->update(['name' => 'Neon 2']);
    expect($registry->find($ready->templateId())?->label)->toBe('Neon 2');

    $ready->delete();
    expect($registry->studio())->toBe([]);
});

test('the uploaded screenshot is used on the card', function () {
    Storage::fake('public');
    $template = StudioTemplate::factory()->ready()->create();
    $template->addMedia(UploadedFile::fake()->image('card.png', 960, 600))->toMediaCollection('screenshot');
    $template->touch();

    expect(app(TemplateRegistry::class)->find($template->templateId())?->screenshotUrl())->toContain('card.png');
});

test('an active studio template renders with the studio pages, and falls back when deleted', function () {
    $template = StudioTemplate::factory()->ready()->create();
    SiteSetting::current()->update(['active_template' => $template->templateId()]);

    $manager = app(TemplateManager::class);

    expect($manager->active()->id)->toBe($template->templateId())
        ->and($manager->page('Home'))->toBe('studio/Home');

    $template->delete();
    app()->forgetScopedInstances();

    expect(app(TemplateManager::class)->active()->id)->toBe('changelog');
});

test('the appearance page shows a ready studio template and can activate it', function () {
    actingAsAdmin();
    SiteSetting::current()->update(['active_template' => 'changelog']);
    $template = StudioTemplate::factory()->ready()->create(['name' => 'Neon Studio']);
    $key = 'studio-'.$template->id;

    Livewire::test(Appearance::class)
        ->assertSee('Neon Studio')
        ->assertSee('Studio template · no screenshot yet')
        ->callAction(TestAction::make("activate_{$key}")->schemaComponent("template-{$key}"))
        ->assertNotified('Neon Studio is now live');

    expect(SiteSetting::current()->active_template)->toBe($template->templateId());
});

test('the site keeps working with its code templates before the studio tables are migrated', function () {
    Schema::drop('studio_template_versions');
    Schema::drop('studio_templates');
    app()->forgetInstance(TemplateRegistry::class);

    expect(app(TemplateRegistry::class)->studio())->toBe([])
        ->and(array_keys(app(TemplateRegistry::class)->all()))->toBe(templateIds());

    $this->get('/')->assertOk();
});
