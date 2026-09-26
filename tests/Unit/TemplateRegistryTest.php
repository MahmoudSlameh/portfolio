<?php

use App\Support\Templates\TemplateRegistry;

function registryFor(string $path, string $default = 'changelog', ?string $cache = null): TemplateRegistry
{
    return new TemplateRegistry($path, $default, $cache ?? sys_get_temp_dir().'/templates-'.uniqid().'.php');
}

function fakeTemplatesDir(array $manifests): string
{
    $dir = sys_get_temp_dir().'/template-registry-'.uniqid();

    foreach ($manifests as $folder => $manifest) {
        mkdir("{$dir}/{$folder}", recursive: true);
        file_put_contents("{$dir}/{$folder}/template.json", is_string($manifest) ? $manifest : json_encode($manifest));
    }

    return $dir;
}

function manifest(string $id, array $overrides = []): array
{
    return [
        'id' => $id,
        'name' => ucfirst($id),
        'description' => "The {$id} template.",
        'preloadFonts' => [],
        'screenshot' => "templates/{$id}.webp",
        ...$overrides,
    ];
}

$root = dirname(__DIR__, 2);

test('the bundled templates are discovered in id order', function () use ($root) {
    expect(registryFor("{$root}/resources/js/templates")->ids())->toBe(['changelog', 'minimal', 'playground', 'terminal']);
});

test('every bundled template has a label, description, screenshot and all nine Inertia pages', function (string $id) use ($root) {
    $template = registryFor("{$root}/resources/js/templates")->find($id);

    expect($template->label)->not->toBeEmpty()
        ->and($template->description)->not->toBeEmpty()
        ->and(file_exists("{$root}/public/{$template->screenshot}"))->toBeTrue();

    foreach (['Home', 'ProjectArchive', 'CaseStudy', 'WritingArchive', 'Article', 'Books', 'Uses', 'Now', 'NotFound'] as $page) {
        expect(file_exists("{$root}/resources/js/pages/{$id}/{$page}.tsx"))->toBeTrue("{$id}/{$page}.tsx is missing");
    }
})->with('templates');

test('every font a bundled template preloads is a self-hosted file that exists', function (string $id) use ($root) {
    // A template may use system fonts only (minimal), so the list can be empty.
    $fonts = registryFor("{$root}/resources/js/templates")->find($id)->preloadFonts;
    expect($fonts)->toBeArray();

    foreach ($fonts as $font) {
        expect($font)->toEndWith('.woff2')
            ->and(file_exists("{$root}/{$font}"))->toBeTrue("{$font} does not exist");
    }
})->with('templates');

test('a new folder with a manifest is picked up without any PHP change', function () {
    $registry = registryFor(fakeTemplatesDir(['zine' => manifest('zine', ['author' => 'Someone']), 'alpha' => manifest('alpha')]), default: 'zine');

    expect($registry->ids())->toBe(['alpha', 'zine'])
        ->and($registry->has('zine'))->toBeTrue()
        ->and($registry->find('zine')->author)->toBe('Someone')
        ->and($registry->default()->id)->toBe('zine')
        ->and($registry->find('missing'))->toBeNull()
        ->and($registry->find(null))->toBeNull();
});

test('the default falls back to the first template when the configured one does not exist', function () {
    expect(registryFor(fakeTemplatesDir(['beta' => manifest('beta')]), default: 'nope')->default()->id)->toBe('beta');
});

test('invalid manifests are rejected with a precise message', function (array|string $manifest, string $message) {
    expect(fn () => registryFor(fakeTemplatesDir(['broken' => $manifest]))->all())
        ->toThrow(InvalidArgumentException::class, $message);
})->with([
    'not json' => ['{nope', 'not valid JSON'],
    'missing name' => [manifest('broken', ['name' => '']), '"name"'],
    'id does not match the folder' => [manifest('other'), 'folder name'],
    'id is not a slug' => [manifest('Broken Id'), 'slug'],
    'fonts are not strings' => [manifest('broken', ['preloadFonts' => [1]]), 'preloadFonts'],
]);

test('the cache file is used until it is cleared', function () {
    $dir = fakeTemplatesDir(['alpha' => manifest('alpha')]);
    $cache = sys_get_temp_dir().'/templates-cache-'.uniqid().'.php';

    registryFor($dir, cache: $cache)->cache();
    mkdir("{$dir}/beta");
    file_put_contents("{$dir}/beta/template.json", json_encode(manifest('beta')));

    $registry = registryFor($dir, cache: $cache);
    expect($registry->ids())->toBe(['alpha']);

    $registry->clearCache();
    expect(file_exists($cache))->toBeFalse()
        ->and($registry->ids())->toBe(['alpha', 'beta']);
});
