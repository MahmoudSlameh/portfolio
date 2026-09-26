<?php

use App\Enums\ProjectCategory;
use App\Models\Article;
use App\Models\Book;
use App\Models\Project;
use App\Models\SiteSetting;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->project = Project::factory()->create(['category' => ProjectCategory::Platform]);
    $this->article = Article::factory()->create();
    Book::factory()->create();
});

test('every page renders in every template with its props', function (string $template) {
    SiteSetting::current()->update(['active_template' => $template]);

    $pages = [
        '/' => ['Home', ['profile', 'socials', 'skillGroups', 'career', 'projects', 'companies', 'testimonials', 'education', 'certifications', 'articles', 'books']],
        '/projects' => ['ProjectArchive', ['projects', 'facets', 'total', 'search']],
        "/projects/{$this->project->slug}" => ['CaseStudy', ['project']],
        '/writing' => ['WritingArchive', ['articles', 'allArticles', 'tags', 'search']],
        "/writing/{$this->article->slug}" => ['Article', ['article']],
        '/books' => ['Books', ['books', 'stats', 'search']],
        '/uses' => ['Uses', ['groups']],
        '/now' => ['Now', ['now']],
    ];

    foreach ($pages as $url => [$page, $props]) {
        $this->get($url)
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component("{$template}/{$page}")
                ->hasAll([...$props, 'seo', 'site', 'profile', 'socials', 'template'])
                ->where('template.id', $template)
                ->where('template.isPreview', false));
    }
})->with('templates');

test('a fresh install without any content renders every page with empty lists', function (string $template) {
    Project::query()->delete();
    Article::query()->delete();
    Book::query()->delete();
    SiteSetting::current()->update(['active_template' => $template]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component("{$template}/Home")
            ->where('projects', [])
            ->where('career', [])
            ->where('companies', [])
            ->where('articles', [])
            ->where('books', []));

    foreach (['/projects', '/writing', '/books', '/uses', '/now'] as $url) {
        $this->get($url)->assertOk();
    }
})->with('templates');

test('unknown pages render the template 404 page with a 404 status', function (string $template) {
    SiteSetting::current()->update(['active_template' => $template]);

    $this->get('/definitely-not-here')
        ->assertNotFound()
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component("{$template}/NotFound")
            ->where('seo.robots', 'noindex,nofollow'));
})->with('templates');

test('drafts and missing slugs are not found', function () {
    $draft = Project::factory()->draft()->create();

    $this->get("/projects/{$draft->slug}")->assertNotFound();
    $this->get('/writing/missing-article')->assertNotFound();
});

test('archive filters are applied on the server and invalid values are ignored', function () {
    Project::factory()->create(['category' => ProjectCategory::Product, 'title' => 'Other']);

    $this->get('/projects?category=platform&sort=oldest&view=table')
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->has('projects', 1)
            ->where('projects.0.slug', $this->project->slug)
            ->where('search', ['category' => 'platform', 'sort' => 'oldest', 'view' => 'table'])
            ->where('total', 2));

    $this->get('/projects?category=nope&sort=sideways')
        ->assertOk()
        ->assertInertia(fn (Assert $inertia) => $inertia->has('projects', 2)->where('search', []));
});

test('switched-off pages return 404', function () {
    SiteSetting::current()->update(['enabled_pages' => ['writing' => false, 'books' => false, 'uses' => true, 'now' => true]]);

    $this->get('/writing')->assertNotFound();
    $this->get("/writing/{$this->article->slug}")->assertNotFound();
    $this->get('/books')->assertNotFound();
    $this->get('/uses')->assertOk();
});

test('pages carry complete seo data', function () {
    $this->get("/projects/{$this->project->slug}")
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->where('seo.canonical', url("/projects/{$this->project->slug}"))
            ->where('seo.type', 'article')
            ->where('seo.robots', 'index,follow,max-image-preview:large')
            ->where('seo.jsonLd.0.@type', 'CreativeWork')
            ->where('seo.jsonLd.1.@type', 'BreadcrumbList'));

    $this->get('/')
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->where('seo.type', 'profile')
            ->where('seo.jsonLd.0.@type', 'Person')
            ->where('seo.jsonLd.1.@type', 'WebSite'));
});

test('the whole site is noindex when indexing is switched off', function () {
    SiteSetting::current()->update(['indexable' => false]);

    $this->get('/')->assertInertia(fn (Assert $inertia) => $inertia->where('seo.robots', 'noindex,nofollow'));
});

test('the search index is a deferred prop', function () {
    $this->get('/')->assertInertia(fn (Assert $inertia) => $inertia
        ->missing('searchIndex')
        ->loadDeferredProps(fn (Assert $reload) => $reload->has('searchIndex.projects', 1)));
});

test('the theme cookie is passed to the page', function () {
    $this->withUnencryptedCookie('theme', 'dark')
        ->get('/')
        ->assertSee('data-theme="dark"', false)
        ->assertInertia(fn (Assert $inertia) => $inertia->where('theme', 'dark'));
});
