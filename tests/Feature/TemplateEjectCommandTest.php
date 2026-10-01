<?php

use App\Models\StudioTemplate;
use App\Support\Studio\SpecCatalogue;
use App\Support\Templates\TemplateRegistry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/*
 * template:eject runs against a temporary copy of the real studio engine (and one code template, so
 * taken ids can be tested), so the tests never write to the repository.
 */
beforeEach(function () {
    $this->root = sys_get_temp_dir().'/eject-template-'.uniqid();

    foreach (['studio', 'minimal'] as $id) {
        File::copyDirectory(resource_path("js/templates/{$id}"), "{$this->root}/templates/{$id}");
        File::copyDirectory(resource_path("js/pages/{$id}"), "{$this->root}/pages/{$id}");
    }

    File::ensureDirectoryExists("{$this->root}/screenshots");
    File::put("{$this->root}/main.css", "@import './base.css';\n@import '../templates/minimal/styles.css';\n@import '../templates/studio/styles.css';\n");

    config([
        'portfolio.templates.path' => "{$this->root}/templates",
        'portfolio.templates.pages_path' => "{$this->root}/pages",
        'portfolio.templates.stylesheet' => "{$this->root}/main.css",
        'portfolio.templates.screenshots_path' => "{$this->root}/screenshots",
    ]);
    app()->forgetInstance(TemplateRegistry::class);

    $this->template = StudioTemplate::factory()->ready()->create(['name' => 'Neon Brutalist', 'description' => 'Loud and pink.']);
});

afterEach(fn () => File::deleteDirectory($this->root));

test('a studio template becomes a code template that renders its frozen spec', function () {
    $this->artisan('template:eject', ['template' => $this->template->templateId(), 'id' => 'night-shift', '--author' => 'Jane', '--no-format' => true])
        ->expectsOutputToContain('Template [night-shift] ejected')
        ->assertSuccessful();

    $dir = "{$this->root}/templates/night-shift";
    $manifest = json_decode(File::get("{$dir}/template.json"), true);

    expect($manifest)->toMatchArray([
        'id' => 'night-shift',
        'name' => 'Neon Brutalist',
        'description' => 'Loud and pink.',
        'author' => 'Jane',
        'screenshot' => 'templates/night-shift.svg',
    ])
        ->and($manifest['preloadFonts'])->not->toBeEmpty()
        // The engine, renamed.
        ->and(File::exists("{$dir}/layout/NightShiftLayout.tsx"))->toBeTrue()
        ->and(File::exists("{$dir}/layout/StudioLayout.tsx"))->toBeFalse()
        ->and(File::get("{$this->root}/pages/night-shift/Home.tsx"))
        ->toContain("from '@/templates/night-shift/home/HomePage'")
        ->toContain('NightShiftLayout')
        ->not->toContain('@/templates/studio/')
        // The frozen spec replaces the shared prop.
        ->and(File::get("{$dir}/useSpec.ts"))->toContain("import { spec } from './frozenSpec'")->not->toContain('usePage')
        ->and(File::get("{$dir}/frozenSpec.ts"))->toContain('export const spec: TemplateSpec = {')->toContain('"name": "Neon Brutalist"')
        // Tokens and CSS, scoped to the new id.
        ->and(File::get("{$dir}/styles.css"))
        ->toContain("[data-template='night-shift']")
        ->toContain('[data-template="night-shift"] {')
        ->toContain('--signal: #ff3d7f;')
        ->not->toContain("data-template='studio'")
        ->not->toContain('data-template="studio"')
        // Screenshot (swatch), README and the stylesheet import.
        ->and(File::get("{$this->root}/screenshots/night-shift.svg"))->toStartWith('<svg')
        ->and(File::get("{$dir}/README.md"))->toContain('Ejected from version 1 of the studio template "Neon Brutalist"')
        ->and(File::get("{$this->root}/main.css"))->toContain("@import '../templates/studio/styles.css';\n@import '../templates/night-shift/styles.css';")
        ->and(app(TemplateRegistry::class)->has('night-shift'))->toBeTrue();

    // The source is untouched.
    expect(File::get("{$this->root}/templates/studio/useSpec.ts"))->toContain('usePage')
        ->and($this->template->refresh()->activeVersion?->number)->toBe(1);
});

test('the studio screenshot is reused when there is one', function () {
    Storage::fake('public');
    $this->template->addMedia(UploadedFile::fake()->image('shot.png', 64, 40))->toMediaCollection('screenshot');

    $this->artisan('template:eject', ['template' => 'Neon Brutalist', 'id' => 'with-shot', '--no-format' => true])->assertSuccessful();

    expect(File::exists("{$this->root}/screenshots/with-shot.png"))->toBeTrue()
        ->and(json_decode(File::get("{$this->root}/templates/with-shot/template.json"), true)['screenshot'])->toBe('templates/with-shot.png');
});

test('the active version is the one frozen', function () {
    $spec = [...SpecCatalogue::example(), 'tokens' => [...SpecCatalogue::example()['tokens'], 'radius' => 'full']];
    $this->template->activate($this->template->addVersion($spec));

    $this->artisan('template:eject', ['template' => $this->template->id, 'id' => 'rounded', '--no-format' => true])->assertSuccessful();

    expect(File::get("{$this->root}/templates/rounded/frozenSpec.ts"))->toContain('"radius": "full"')->toContain('version 2 of the studio template');
});

test('it refuses bad ids, taken ids and unknown templates', function (array $arguments, string $message) {
    $this->artisan('template:eject', [...$arguments, '--no-format' => true])
        ->expectsOutputToContain($message)
        ->assertFailed();

    expect(File::directories("{$this->root}/templates"))->toHaveCount(2);
})->with([
    'bad id' => [['template' => 'Neon Brutalist', 'id' => 'Night Shift'], 'is not a valid template id'],
    'reserved' => [['template' => 'Neon Brutalist', 'id' => 'studio'], 'is reserved'],
    'taken' => [['template' => 'Neon Brutalist', 'id' => 'minimal'], 'already exists'],
    'unknown template' => [['template' => 'Nope', 'id' => 'fresh'], 'No studio template matches "Nope"'],
]);

test('a template without a version cannot be ejected', function () {
    $draft = StudioTemplate::query()->create(['name' => 'Draft', 'source' => 'manual']);

    $this->artisan('template:eject', ['template' => $draft->id, 'id' => 'draft-copy', '--no-format' => true])
        ->expectsOutputToContain('has no version to eject')
        ->assertFailed();
});
