<?php

use App\Models\Article;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Support\Seo\Seo;

test('the sitemap lists public pages and only published content', function () {
    $live = Project::factory()->create();
    $draft = Project::factory()->draft()->create();
    $article = Article::factory()->create();
    $unpublished = Article::factory()->draft()->create();

    $response = $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $locations = [];
    foreach (simplexml_load_string($response->getContent())->url as $url) {
        $locations[] = (string) $url->loc;
    }

    expect($locations)->toContain(Seo::url('/'), url('/projects'), url("/projects/{$live->slug}"), url("/writing/{$article->slug}"), url('/books'), url('/uses'), url('/now'))
        ->not->toContain(url("/projects/{$draft->slug}"), url("/writing/{$unpublished->slug}"));
});

test('switched-off pages are left out of the sitemap', function () {
    SiteSetting::current()->update(['enabled_pages' => ['writing' => false, 'books' => false, 'uses' => true, 'now' => true]]);

    $this->get('/sitemap.xml')->assertDontSee(url('/writing'))->assertDontSee(url('/books'))->assertSee(url('/uses'));
});

test('robots.txt points to the sitemap and blocks the panel', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap: '.url('/sitemap.xml'));
});

test('a non-indexable site disallows crawling and publishes an empty sitemap', function () {
    SiteSetting::current()->update(['indexable' => false]);
    Project::factory()->create();

    $this->get('/robots.txt')->assertSee("User-agent: *\nDisallow: /", false)->assertDontSee('Sitemap:');
    expect(simplexml_load_string($this->get('/sitemap.xml')->getContent())->url)->toHaveCount(0);
});

test('the rss feed lists published articles newest first', function () {
    $older = Article::factory()->create(['title' => 'Older', 'published_at' => now()->subMonth()]);
    $newer = Article::factory()->create(['title' => 'Newer', 'published_at' => now()->subDay()]);
    Article::factory()->draft()->create(['title' => 'Draft']);

    $response = $this->get('/rss.xml')->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
    $titles = [];
    foreach (simplexml_load_string($response->getContent())->channel->item as $item) {
        $titles[] = (string) $item->title;
    }

    expect($titles)->toBe(['Newer', 'Older']);
});

test('the rss feed disappears with the writing page', function () {
    SiteSetting::current()->update(['enabled_pages' => ['writing' => false, 'books' => true, 'uses' => true, 'now' => true]]);

    $this->get('/rss.xml')->assertNotFound();
});

test('pages link the rss feed in the head', function () {
    $this->get('/')->assertSee('type="application/rss+xml"', false);
});
