<?php

use App\Support\Templates\TemplateRegistry;
use Illuminate\Support\Facades\File;

/*
 * make:template runs against a temporary copy of two real templates, so the tests never write to the
 * repository and always exercise the real source files.
 */
beforeEach(function () {
    $this->root = sys_get_temp_dir().'/make-template-'.uniqid();

    foreach (['minimal', 'terminal'] as $id) {
        File::copyDirectory(resource_path("js/templates/{$id}"), "{$this->root}/templates/{$id}");
        File::copyDirectory(resource_path("js/pages/{$id}"), "{$this->root}/pages/{$id}");
        File::ensureDirectoryExists("{$this->root}/screenshots");
        File::copy(public_path("templates/{$id}.webp"), "{$this->root}/screenshots/{$id}.webp");
    }

    File::put("{$this->root}/main.css", "@import './base.css';\n@import '../templates/minimal/styles.css';\n@import '../templates/terminal/styles.css';\n");

    config([
        'portfolio.templates.path' => "{$this->root}/templates",
        'portfolio.templates.pages_path' => "{$this->root}/pages",
        'portfolio.templates.stylesheet' => "{$this->root}/main.css",
        'portfolio.templates.screenshots_path' => "{$this->root}/screenshots",
    ]);
    app()->forgetInstance(TemplateRegistry::class);
});

afterEach(fn () => File::deleteDirectory($this->root));

test('it copies the minimal starter into a new, fully renamed template', function () {
    $this->artisan('make:template', ['id' => 'magazine', '--name' => 'The Magazine', '--author' => 'Jane Doe', '--no-format' => true])
        ->assertSuccessful();

    $template = "{$this->root}/templates/magazine";
    $manifest = json_decode(File::get("{$template}/template.json"), true);

    expect($manifest)->toMatchArray([
        'id' => 'magazine',
        'name' => 'The Magazine',
        'author' => 'Jane Doe',
        'preloadFonts' => [],
        'screenshot' => 'templates/magazine.webp',
    ])
        ->and(File::exists("{$template}/layout/MagazineLayout.tsx"))->toBeTrue()
        ->and(File::exists("{$template}/layout/MinimalLayout.tsx"))->toBeFalse()
        ->and(File::get("{$template}/styles.css"))->toContain("[data-template='magazine']")->not->toContain("'minimal'")
        ->and(File::exists("{$this->root}/screenshots/magazine.webp"))->toBeTrue()
        ->and(File::get("{$this->root}/main.css"))->toContain("@import '../templates/minimal/styles.css';\n@import '../templates/magazine/styles.css';");

    expect(File::files("{$this->root}/pages/magazine"))->toHaveCount(9);

    // Nothing in the code still points at the source template.
    foreach ([...File::allFiles($template), ...File::files("{$this->root}/pages/magazine")] as $file) {
        if (in_array($file->getFilename(), ['README.md', 'template.json'], true)) {
            continue;
        }

        expect($file->getContents())->not->toContain('templates/minimal')->not->toContain('MinimalLayout');
    }

    expect(File::get("{$this->root}/pages/magazine/Home.tsx"))
        ->toContain("from '@/templates/magazine/pages/HomePage'")
        ->toContain('withTemplateLayout(MagazineLayout)');

    expect(app(TemplateRegistry::class)->find('magazine')?->label)->toBe('The Magazine');
});

test('it can copy any existing template', function () {
    $this->artisan('make:template', ['id' => 'green-screen', '--from' => 'terminal', '--no-format' => true])->assertSuccessful();

    $template = "{$this->root}/templates/green-screen";

    expect(File::exists("{$template}/layout/GreenScreenLayout.tsx"))->toBeTrue()
        ->and(File::get("{$template}/styles.css"))->not->toContain("[data-template='terminal']")
        ->and(json_decode(File::get("{$template}/template.json"), true))->toMatchArray([
            'name' => 'Green Screen',
            'preloadFonts' => app(TemplateRegistry::class)->find('terminal')?->preloadFonts,
        ])
        ->and(File::get("{$this->root}/main.css"))->toEndWith("@import '../templates/terminal/styles.css';\n@import '../templates/green-screen/styles.css';\n");
});

test('it refuses invalid, reserved or taken ids and unknown sources without writing anything', function (array $arguments, string $message) {
    $before = File::allFiles($this->root);

    $this->artisan('make:template', [...$arguments, '--no-format' => true])
        ->expectsOutputToContain($message)
        ->assertFailed();

    expect(File::allFiles($this->root))->toHaveCount(count($before));
})->with([
    'not a slug' => [['id' => 'My Template'], 'not a valid template id'],
    'reserved' => [['id' => 'studio'], 'reserved'],
    'already exists' => [['id' => 'terminal'], 'already exists'],
    'unknown source' => [['id' => 'fresh', '--from' => 'nope'], 'There is no template "nope"'],
]);
