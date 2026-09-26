<?php

use App\Models\Project;
use App\Models\SiteSetting;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => SiteSetting::current()->update(['active_template' => 'changelog']));

test('the gallery and its override are off unless enabled', function () {
    config(['portfolio.templates.dev_gallery' => false]);

    $this->get('/dev/templates')->assertNotFound();
    $this->get('/?_template=terminal')->assertInertia(fn (Assert $inertia) => $inertia->component('changelog/Home'));
});

test('the gallery compares one page across every template', function () {
    config(['portfolio.templates.dev_gallery' => true]);
    $project = Project::factory()->create();

    $response = $this->get('/dev/templates?page=case-study&theme=dark')->assertOk();

    foreach (templateIds() as $id) {
        $response->assertSee(url("/projects/{$project->slug}")."?_template={$id}&amp;_theme=dark", false);
    }

    $response->assertSee('noindex,nofollow', false);
});

test('the gallery shows every page of one template', function () {
    config(['portfolio.templates.dev_gallery' => true]);

    $this->get('/dev/templates?template=terminal&mobile=1')
        ->assertOk()
        ->assertSee(url('/').'?_template=terminal&amp;_theme=light', false)
        ->assertSee(url('/books').'?_template=terminal&amp;_theme=light', false)
        ->assertSee(url('/this-page-does-not-exist').'?_template=terminal&amp;_theme=light', false)
        ->assertSee('width: 390px', false)
        // No published project yet: the case study is skipped and the seeding hint is shown.
        ->assertDontSee('Case study')
        ->assertSee('DemoContentSeeder');
});

test('the override renders a template for one request only, without a preview bar and never indexed', function () {
    config(['portfolio.templates.dev_gallery' => true]);

    $this->get('/?_template=terminal')
        ->assertSee('data-template="terminal"', false)
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('terminal/Home')
            ->where('template.id', 'terminal')
            ->where('template.isPreview', false)
            ->where('seo.robots', 'noindex,nofollow'));

    // Stateless: the next request is back to the active template, and unknown ids are ignored.
    $this->get('/')->assertInertia(fn (Assert $inertia) => $inertia->component('changelog/Home'));
    $this->get('/?_template=nope')->assertInertia(fn (Assert $inertia) => $inertia->component('changelog/Home'));
});

test('the gallery theme is rendered on the server and only with the override', function () {
    config(['portfolio.templates.dev_gallery' => true]);

    $this->get('/?_template=terminal&_theme=dark')
        ->assertSee('data-theme="dark"', false)
        ->assertInertia(fn (Assert $inertia) => $inertia->where('theme', 'dark'));

    $this->get('/?_theme=dark')->assertSee('data-theme="light"', false);
    $this->withUnencryptedCookie('theme', 'dark')->get('/')->assertSee('data-theme="dark"', false);
});
