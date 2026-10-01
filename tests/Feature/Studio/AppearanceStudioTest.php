<?php

use App\Enums\StudioStatus;
use App\Filament\Pages\Appearance;
use App\Models\SiteSetting;
use App\Models\StudioTemplate;
use App\Support\Studio\SpecCatalogue;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function () {
    actingAsAdmin();
    SiteSetting::current()->update(['active_template' => 'changelog']);
});

function studioAction(string $name, StudioTemplate $template): TestAction
{
    $key = 'studio-'.$template->id;

    return TestAction::make("{$name}_{$key}")->schemaComponent("template-{$key}");
}

function specJson(array $spec): string
{
    return json_encode($spec, JSON_PRETTY_PRINT);
}

test('the page lists built-in and studio templates with their status', function () {
    StudioTemplate::factory()->ready()->create(['name' => 'Ready One']);
    StudioTemplate::factory()->failed('The provider timed out.')->create(['name' => 'Broken One']);
    StudioTemplate::factory()->generating(45)->create(['name' => 'Busy One']);

    Livewire::test(Appearance::class)
        ->assertSee(['Built-in templates', 'Terminal', 'Studio templates'])
        ->assertSee(['Ready One', 'Version 1', 'Broken One', 'Failed', 'The provider timed out.', 'Busy One', '45% — Composing pages…']);
});

test('the empty state explains how to start', function () {
    Livewire::test(Appearance::class)->assertSee('No studio templates yet.');
});

test('a studio template can be created from an example', function () {
    Livewire::test(Appearance::class)
        ->callAction('create_studio', data: ['name' => 'My Studio', 'description' => 'Mine', 'example' => 'mono-grid'])
        ->assertHasNoFormErrors()
        ->assertNotified('My Studio is ready');

    $template = StudioTemplate::query()->sole();

    expect($template->status)->toBe(StudioStatus::Ready)
        ->and($template->activeVersion?->spec['name'])->toBe('My Studio')
        ->and($template->activeVersion?->spec['layout']['header']['variant'])->toBe('floating-pill');
});

test('editing validates the JSON and the spec and shows every error', function () {
    $template = StudioTemplate::factory()->ready()->create();

    Livewire::test(Appearance::class)
        ->callAction(studioAction('edit', $template), data: ['name' => 'Edited', 'spec' => '{not json'])
        ->assertHasFormErrors(['spec']);

    $spec = SpecCatalogue::example();
    $spec['pages']['home'][0]['variant'] = 'nope';
    $spec['tokens']['radius'] = 'huge';

    $page = Livewire::test(Appearance::class)
        ->callAction(studioAction('edit', $template), data: ['name' => 'Edited', 'spec' => specJson($spec)])
        ->assertHasFormErrors(['spec']);

    $errors = collect($page->errors()->getMessages())->first(fn (array $messages, string $key): bool => str_ends_with($key, 'spec'));

    // Every problem is listed in the field's single message.
    expect($errors)->toHaveCount(1)
        ->and($errors[0])->toContain('tokens.radius must be one of: none, sm, md, lg, full.')
        ->and($errors[0])->toContain('pages.home[0].variant must be one of');

    expect($template->versions()->count())->toBe(1)
        ->and($template->fresh()->name)->not->toBe('Edited');
});

test('a valid edit is saved and activated as a new version', function () {
    $template = StudioTemplate::factory()->ready()->create();
    $spec = SpecCatalogue::example();
    $spec['tokens']['radius'] = 'lg';

    Livewire::test(Appearance::class)
        ->callAction(studioAction('edit', $template), data: ['name' => 'Rounder', 'description' => 'Softer corners', 'spec' => specJson($spec)])
        ->assertHasNoFormErrors()
        ->assertNotified('Saved as version 2')
        // The same response already shows the new card state.
        ->assertSee('Rounder')
        ->assertSee('Version 2 ·');

    $template->refresh();

    expect($template->name)->toBe('Rounder')
        ->and($template->activeVersion?->number)->toBe(2)
        ->and($template->activeVersion?->spec['tokens']['radius'])->toBe('lg')
        ->and($template->activeVersion?->parent?->number)->toBe(1);
});

test('the owner is told when unsafe css was removed', function () {
    $template = StudioTemplate::factory()->ready()->create();
    $spec = SpecCatalogue::example();
    $spec['css'] = '@import url(https://evil.test/x.css); .card { color: red }';

    Livewire::test(Appearance::class)
        ->callAction(studioAction('edit', $template), data: ['name' => $template->name, 'spec' => specJson($spec)])
        ->assertNotified('Some CSS was removed for safety');

    expect($template->fresh()->activeVersion?->notes)->toBe(['CSS removed: @import']);
});

test('a studio template can be duplicated', function () {
    $template = StudioTemplate::factory()->ready()->create(['name' => 'Original']);

    Livewire::test(Appearance::class)
        ->callAction(studioAction('duplicate', $template))
        ->assertNotified('Copy of Original created');

    $copy = StudioTemplate::query()->where('name', 'Copy of Original')->sole();

    expect($copy->activeVersion?->spec['tokens'])->toBe($template->activeVersion?->spec['tokens']);
});

test('a studio template can be deleted, but not while it is active', function () {
    $active = StudioTemplate::factory()->ready()->create();
    $other = StudioTemplate::factory()->ready()->create();
    SiteSetting::current()->update(['active_template' => $active->templateId()]);

    Livewire::test(Appearance::class)
        ->assertActionDisabled(studioAction('delete', $active))
        ->callAction(studioAction('delete', $other))
        ->assertNotified("{$other->name} deleted");

    expect(StudioTemplate::query()->pluck('id')->all())->toBe([$active->id]);
});

test('drafts can be edited but not previewed or activated', function () {
    $draft = StudioTemplate::factory()->create();

    Livewire::test(Appearance::class)
        ->assertActionExists(studioAction('edit', $draft))
        ->assertActionDoesNotExist(studioAction('activate', $draft))
        ->callAction(studioAction('edit', $draft), data: ['name' => 'Now ready', 'spec' => specJson(SpecCatalogue::example())])
        ->assertNotified('Saved as version 1');

    expect($draft->fresh()->status)->toBe(StudioStatus::Ready);
});
