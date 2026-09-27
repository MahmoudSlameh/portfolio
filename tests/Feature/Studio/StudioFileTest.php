<?php

use App\Enums\StudioSource;
use App\Filament\Pages\Appearance;
use App\Filament\Pages\StudioTemplateVersions;
use App\Models\SiteSetting;
use App\Models\StudioTemplate;
use App\Support\Studio\InvalidStudioFile;
use App\Support\Studio\SpecCatalogue;
use App\Support\Studio\StudioFile;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

function exportAction(StudioTemplate $template): TestAction
{
    $key = 'studio-'.$template->id;

    return TestAction::make("export_{$key}")->schemaComponent("template-{$key}");
}

test('an export holds the spec, a name and a description, nothing private', function () {
    $template = StudioTemplate::factory()->ready()->create(['name' => 'Night Shift', 'description' => 'Neon and loud', 'source' => StudioSource::Ai]);
    $template->activeVersion?->update(['prompt' => 'secret prompt', 'provider' => 'anthropic', 'input_tokens' => 10]);

    $file = StudioFile::export($template, $template->activeVersion);

    expect(array_keys($file))->toBe(['format', 'formatVersion', 'name', 'description', 'exportedAt', 'spec'])
        ->and($file['format'])->toBe('portfolio-studio-template')
        ->and($file['spec'])->toBe($template->activeVersion?->spec)
        ->and(StudioFile::json($template, $template->activeVersion))->not->toContain('secret prompt')->not->toContain('anthropic')
        ->and(StudioFile::filename($template))->toBe('night-shift.studio.json');
});

test('files are parsed and validated', function () {
    $wrapped = ['format' => 'portfolio-studio-template', 'formatVersion' => 1, 'name' => 'Shared', 'description' => '<b>Nice</b>', 'spec' => SpecCatalogue::example()];

    expect(StudioFile::parse((string) json_encode($wrapped)))->toMatchArray(['name' => 'Shared', 'description' => 'Nice'])
        ->and(StudioFile::parse((string) json_encode(SpecCatalogue::example()))['name'])->toBe('Neon Brutalist');
});

test('bad files are rejected with a readable reason', function (string $contents, string $reason) {
    expect(fn () => StudioFile::parse($contents))->toThrow(InvalidStudioFile::class, $reason);
})->with([
    'not json' => ['nope', 'not a JSON object'],
    'a list' => ['[1, 2]', 'not a JSON object'],
    'another format' => ['{"format": "other"}', 'not a studio template file'],
    'newer format' => ['{"format": "portfolio-studio-template", "formatVersion": 2, "spec": {}}', 'newer version of the app'],
    'no spec' => ['{"format": "portfolio-studio-template", "formatVersion": 1}', 'no "spec" object'],
    'invalid spec' => [(string) json_encode(['$schema' => 'studio/v1', 'name' => 'X']), 'The spec is invalid: tokens is required.'],
    'too big' => [str_repeat(' ', StudioFile::MAX_BYTES + 1), 'larger than 100 KB'],
]);

test('the appearance card and the versions page download a file', function () {
    actingAsAdmin();
    $template = StudioTemplate::factory()->ready()->create(['name' => 'Night Shift']);
    $v2 = $template->addVersion(SpecCatalogue::example());

    Livewire::test(Appearance::class)
        ->callAction(exportAction($template))
        ->assertFileDownloaded('night-shift.studio.json');

    Livewire::test(StudioTemplateVersions::class, ['template' => $template->id])
        ->callAction(TestAction::make('export')->table($v2))
        ->assertFileDownloaded('night-shift-v2.studio.json');
});

test('an exported template can be imported again', function () {
    actingAsAdmin();
    $source = StudioTemplate::factory()->ready()->create(['name' => 'Original', 'description' => 'Shared design']);
    $json = StudioFile::json($source, $source->activeVersion);

    Livewire::test(Appearance::class)
        ->callAction('import', data: ['file' => UploadedFile::fake()->createWithContent('original.studio.json', $json)])
        ->assertHasNoFormErrors()
        ->assertNotified('Original imported');

    $imported = StudioTemplate::query()->where('source', StudioSource::Import)->sole();

    expect($imported->name)->toBe('Original')
        ->and($imported->description)->toBe('Shared design')
        ->and($imported->activeVersion?->spec)->toBe([...$source->activeVersion->spec, 'name' => 'Original'])
        ->and(SiteSetting::current()->active_template)->not->toBe($imported->templateId());
});

test('pasted json can be imported under another name, with unsafe css removed', function () {
    actingAsAdmin();
    $spec = [...SpecCatalogue::example(), 'css' => '.st-hero { color: red; background: url(https://evil.test/x.png); }'];

    Livewire::test(Appearance::class)
        ->callAction('import', data: ['json' => json_encode($spec), 'name' => 'Pasted'])
        ->assertHasNoFormErrors()
        ->assertNotified('Pasted imported');

    $imported = StudioTemplate::query()->where('name', 'Pasted')->sole();

    expect($imported->activeVersion?->spec['name'])->toBe('Pasted')
        ->and($imported->activeVersion?->spec['css'])->not->toContain('evil.test')
        ->and($imported->activeVersion?->notes)->not->toBeEmpty();
});

test('an invalid import creates nothing', function () {
    actingAsAdmin();

    Livewire::test(Appearance::class)
        ->callAction('import', data: ['json' => '{"$schema": "studio/v1"}'])
        ->assertNotified('Not imported');

    expect(StudioTemplate::query()->count())->toBe(0);
});

test('a file or pasted text is required', function () {
    actingAsAdmin();

    Livewire::test(Appearance::class)
        ->callAction('import', data: [])
        ->assertHasFormErrors(['file' => 'required_without', 'json' => 'required_without']);
});
