<?php

use App\Models\Article;
use App\Models\Book;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\StudioTemplate;
use App\Support\Studio\SpecCatalogue;
use App\Support\Studio\SpecValidator;
use App\Support\Studio\StudioStyles;
use Database\Seeders\StudioDemoSeeder;
use Illuminate\Support\Facades\File;
use Inertia\Testing\AssertableInertia as Assert;

test('every example spec is valid', function (string $name) {
    expect((new SpecValidator)->validate(SpecCatalogue::examples()[$name])->messages())->toBe([]);
})->with(array_map(fn (string $file): string => basename($file, '.json'), glob(dirname(__DIR__, 3).'/resources/studio/examples/*.json') ?: []));

test('together the examples use every section, variant and page variant', function () {
    $used = [];

    foreach (SpecCatalogue::examples() as $spec) {
        foreach ($spec['pages']['home'] as $entry) {
            $used["home.{$entry['section']}.{$entry['variant']}"] = true;
        }

        foreach (SpecCatalogue::PAGES as $page => $variants) {
            $used["{$page}.{$spec['pages'][$page]['variant']}"] = true;
        }

        $used["header.{$spec['layout']['header']['variant']}"] = true;
        $used["footer.{$spec['layout']['footer']['variant']}"] = true;
    }

    $expected = [];

    foreach (SpecCatalogue::HOME_SECTIONS as $section => $rule) {
        foreach ($rule['variants'] as $variant) {
            $expected[] = "home.{$section}.{$variant}";
        }
    }

    foreach ([...SpecCatalogue::PAGES, 'header' => SpecCatalogue::LAYOUT['header'], 'footer' => SpecCatalogue::LAYOUT['footer']] as $page => $variants) {
        foreach ($variants as $variant) {
            $expected[] = "{$page}.{$variant}";
        }
    }

    expect(array_values(array_diff($expected, array_keys($used))))->toBe([]);
});

test('tokens become css variables for light and dark, followed by the spec css', function () {
    $spec = SpecCatalogue::example();
    $spec['css'] = '[data-template="studio"] .st-hero h1 {\n  color: red;\n}\n';
    $css = StudioStyles::render($spec);

    expect($css)
        ->toContain("[data-template=\"studio\"] {\n  --tpl-font-display: 'Space Grotesk Variable'")
        ->toContain('--paper: #f6f5f0;')
        ->toContain('--signal: #ff3d7f;')
        ->toContain('--st-radius: 0;')
        ->toContain('--st-container: 84rem;')
        ->toContain('--st-shadow: 4px 4px 0 #111111;')
        ->toContain("[data-template=\"studio\"][data-theme=\"dark\"] {\n  --paper: #0c0c0f;")
        ->toEndWith('[data-template="studio"] .st-hero h1 {\n  color: red;\n}\n')
        ->not->toContain('<');
});

test('text on the accent colour is black or white, whichever reads better', function (string $accent, string $expected) {
    expect(StudioStyles::readableOn($accent))->toBe($expected);
})->with([
    ['#ff3d7f', '#000000'],
    ['#6d28d9', '#ffffff'],
    ['#fff', '#000000'],
    ['#111111', '#ffffff'],
]);

test('every page renders through the studio engine with the spec and its styles', function () {
    $template = StudioTemplate::factory()->ready()->create();
    SiteSetting::current()->update(['active_template' => $template->templateId()]);
    $project = Project::factory()->create();
    $article = Article::factory()->create();
    Book::factory()->create();

    $this->get('/')
        ->assertOk()
        ->assertSee('data-template="studio"', false)
        ->assertSee('<style id="studio-styles">', false)
        ->assertSee('--signal: #ff3d7f;', false)
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('studio/Home')
            ->where('template.id', $template->templateId())
            ->where('studio.spec.name', 'Neon Brutalist')
            ->where('studio.spec.pages.home.0.section', 'hero'));

    foreach (['/projects' => 'ProjectArchive', "/projects/{$project->slug}" => 'CaseStudy', '/writing' => 'WritingArchive', "/writing/{$article->slug}" => 'Article', '/books' => 'Books', '/uses' => 'Uses', '/now' => 'Now'] as $url => $page) {
        $this->get($url)->assertOk()->assertInertia(fn (Assert $inertia) => $inertia->component("studio/{$page}")->has('studio.spec'));
    }

    $this->get('/missing-page')->assertNotFound()->assertInertia(fn (Assert $inertia) => $inertia->component('studio/NotFound'));
});

test('code templates get no studio prop or styles', function () {
    SiteSetting::current()->update(['active_template' => 'terminal']);

    $this->get('/')
        ->assertDontSee('studio-styles')
        ->assertInertia(fn (Assert $inertia) => $inertia->component('terminal/Home')->where('studio', null));
});

test('the owner can preview a studio template', function () {
    actingAsAdmin();
    SiteSetting::current()->update(['active_template' => 'changelog']);
    $template = StudioTemplate::factory()->ready()->create();

    $this->get('/?template='.$template->templateId())
        ->assertInertia(fn (Assert $inertia) => $inertia->component('studio/Home')->where('template.isPreview', true));
});

test('the demo seeder creates one ready template per example and can run twice', function () {
    $this->seed(StudioDemoSeeder::class);
    $this->seed(StudioDemoSeeder::class);

    expect(StudioTemplate::query()->renderable()->count())->toBe(count(SpecCatalogue::examples()));
});

test('every documented class hook exists in the engine', function () {
    $source = '';

    foreach (File::allFiles(resource_path('js/templates/studio')) as $file) {
        // The generated catalogue lists the hooks itself; only the engine's own code counts.
        if ($file->getFilename() !== 'catalogue.ts') {
            $source .= $file->getContents();
        }
    }

    foreach (SpecCatalogue::CLASS_HOOKS as $hook) {
        expect(str_contains($source, $hook))->toBeTrue("{$hook} is not used by the studio engine");
    }

    // Home sections get `st-<name>` from the Section component.
    expect($source)->toContain('`st-${name}`');
});
