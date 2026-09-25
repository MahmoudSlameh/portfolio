<?php

use App\Filament\Widgets\ContentHealth;
use App\Filament\Widgets\LatestMessages;
use App\Filament\Widgets\PortfolioStats;
use App\Models\ContactMessage;
use App\Models\Profile;
use App\Models\Project;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('the dashboard renders with an empty database', function () {
    $this->get('/admin')
        ->assertOk()
        ->assertSee('New project')
        ->assertSee('View site');
});

test('the widgets render with content', function () {
    Project::factory()->count(2)->create();
    $message = ContactMessage::factory()->create();

    Livewire::test(PortfolioStats::class)->assertOk()->assertSee('Unread messages');
    Livewire::test(LatestMessages::class)->assertCanSeeTableRecords([$message]);
    Livewire::test(ContentHealth::class)->assertOk()->assertSee('Content health');
});

test('content health reports what is missing', function () {
    Project::factory()->create();
    Profile::current()->update(['email' => null]);

    $checks = collect((new ContentHealth)->getChecks())->keyBy('label');

    expect($checks['Every project has a cover']['ok'])->toBeFalse()
        ->and($checks['Every project has a cover']['hint'])->toBe('1 missing')
        ->and($checks['Public email set']['ok'])->toBeFalse()
        ->and($checks['Search engines allowed']['ok'])->toBeTrue();
});
