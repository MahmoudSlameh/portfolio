<?php

use App\Enums\Template;
use App\Models\SiteSetting;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(fn () => SiteSetting::current()->update(['active_template' => Template::Changelog]));

test('visitors always see the active template and cannot preview', function () {
    $this->get('/?template=terminal')
        ->assertInertia(fn (Assert $inertia) => $inertia->component('changelog/Home')->where('template.isPreview', false));
});

test('the signed-in owner can preview another template until they reset it', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/?template=terminal')
        ->assertSee('data-template="terminal"', false)
        ->assertInertia(fn (Assert $inertia) => $inertia
            ->component('terminal/Home')
            ->where('template.isPreview', true)
            ->where('seo.robots', 'noindex,nofollow'));

    $this->get('/projects')->assertInertia(fn (Assert $inertia) => $inertia->component('terminal/ProjectArchive'));

    $this->get('/?template=reset')->assertInertia(fn (Assert $inertia) => $inertia->component('changelog/Home')->where('template.isPreview', false));
});

test('an invalid template clears the preview', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/?template=playground');
    $this->get('/?template=nope')->assertInertia(fn (Assert $inertia) => $inertia->component('changelog/Home'));
});

test('previewing the active template is not a preview', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/?template=changelog')->assertInertia(fn (Assert $inertia) => $inertia->where('template.isPreview', false));
});
