<?php

use App\Enums\Template;
use App\Filament\Pages\Appearance;
use App\Filament\Pages\SiteSettings;
use App\Models\SiteSetting;
use App\Support\Content\ContentCache;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('the appearance page shows every template with the active one marked', function () {
    SiteSetting::current()->update(['active_template' => Template::Terminal]);

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
        ->callAction('activate', arguments: ['template' => 'playground'])
        ->assertNotified();

    ContentCache::remember('probe', function () use (&$calls) {
        return ++$calls;
    });

    expect(SiteSetting::current()->active_template)->toBe(Template::Playground)
        ->and($calls)->toBe(2);
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
