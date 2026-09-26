<?php

use App\Filament\Pages\Appearance;
use App\Filament\Pages\SiteSettings;
use App\Models\SiteSetting;
use App\Support\Content\ContentCache;
use App\Support\Templates\TemplateRegistry;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('the appearance page shows every template with the active one marked', function () {
    SiteSetting::current()->update(['active_template' => 'terminal']);

    $this->get(Appearance::getUrl())
        ->assertOk()
        ->assertSee('Changelog')
        ->assertSee('Playground')
        ->assertSee('Terminal')
        ->assertSee('templates/terminal.webp')
        ->assertSee('Active');
});

test('activating a template updates the site settings and flushes the content cache', function () {
    $calls = 0;
    ContentCache::remember('probe', function () use (&$calls) {
        return ++$calls;
    });

    Livewire::test(Appearance::class)
        ->callAction(TestAction::make('activate_playground')->schemaComponent('template-playground'))
        ->assertNotified('Playground is now live');

    ContentCache::remember('probe', function () use (&$calls) {
        return ++$calls;
    });

    expect(SiteSetting::current()->active_template)->toBe('playground')
        ->and($calls)->toBe(2);
});

test('every card activates its own template and the active one is disabled', function (string $template) {
    SiteSetting::current()->update(['active_template' => 'changelog']);

    $page = Livewire::test(Appearance::class)
        ->assertActionExists(TestAction::make("activate_{$template}")->schemaComponent("template-{$template}"));

    if ($template === 'changelog') {
        $page->assertActionDisabled(TestAction::make('activate_changelog')->schemaComponent('template-changelog'));

        return;
    }

    $label = app(TemplateRegistry::class)->find($template)?->label;

    $page->callAction(TestAction::make("activate_{$template}")->schemaComponent("template-{$template}"))
        ->assertNotified("{$label} is now live");

    expect(SiteSetting::current()->active_template)->toBe($template);
})->with('templates');

test('the rendered activate buttons mount a distinct action per template', function () {
    $html = Livewire::test(Appearance::class)->html();

    foreach (templateIds() as $template) {
        expect($html)->toContain("mountAction('activate_{$template}'");
    }
});

test('site settings can be saved, including page toggles', function () {
    Livewire::test(SiteSettings::class)
        ->fillForm([
            'site_name' => 'Mahmoud Slameh',
            'meta_description' => 'Laravel developer portfolio.',
            'enabled_pages' => ['writing' => true, 'books' => false, 'uses' => true, 'now' => true],
            'indexable' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = SiteSetting::current();

    expect($settings->site_name)->toBe('Mahmoud Slameh')
        ->and($settings->isPageEnabled('books'))->toBeFalse()
        ->and($settings->indexable)->toBeFalse();
});

test('the contact recipient must be an email', function () {
    Livewire::test(SiteSettings::class)
        ->fillForm(['contact_recipient' => 'nope'])
        ->call('save')
        ->assertHasFormErrors(['contact_recipient' => 'email']);
});
