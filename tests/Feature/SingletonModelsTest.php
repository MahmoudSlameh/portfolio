<?php

use App\Enums\AvailabilityStatus;
use App\Enums\Template;
use App\Models\NowPage;
use App\Models\Profile;
use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('current() creates the row once and reuses it', function (string $model) {
    $first = $model::current();
    $second = $model::current();

    expect($first->is($second))->toBeTrue()
        ->and($model::query()->count())->toBe(1);
})->with([Profile::class, SiteSetting::class, NowPage::class]);

test('saving a singleton refreshes the memoized instance', function () {
    Profile::current();

    Profile::query()->firstOrFail()->update(['name' => 'Mahmoud Slameh']);

    expect(Profile::current()->name)->toBe('Mahmoud Slameh');
});

test('a new profile has sensible defaults and empty lists', function () {
    $profile = Profile::current();

    expect($profile->availability_status)->toBe(AvailabilityStatus::Open)
        ->and($profile->story)->toBe([])
        ->and($profile->stats)->toBe([])
        ->and($profile->latest_release)->toBe(['added' => [], 'changed' => [], 'removed' => []]);
});

test('profile initials are derived from the name unless set', function () {
    $profile = Profile::factory()->make(['name' => 'mahmoud slameh', 'initials' => null]);

    expect($profile->resolved_initials)->toBe('MS');

    $profile->initials = 'ms.';

    expect($profile->resolved_initials)->toBe('MS.');
});

test('site settings default to the changelog template with every page enabled', function () {
    $settings = SiteSetting::current();

    expect($settings->active_template)->toBe(Template::Changelog)
        ->and($settings->indexable)->toBeTrue();

    foreach (SiteSetting::TOGGLEABLE_PAGES as $page) {
        expect($settings->isPageEnabled($page))->toBeTrue();
    }
});

test('secondary pages can be switched off', function () {
    $settings = SiteSetting::current();
    $settings->update(['enabled_pages' => [...$settings->enabled_pages, 'books' => false]]);

    expect(SiteSetting::current()->isPageEnabled('books'))->toBeFalse()
        ->and(SiteSetting::current()->isPageEnabled('writing'))->toBeTrue()
        ->and(SiteSetting::current()->isPageEnabled('projects'))->toBeTrue();
});

test('a portrait upload stores its dimensions and generates conversions', function () {
    Storage::fake('public');

    $media = Profile::current()
        ->addMedia(UploadedFile::fake()->image('portrait.jpg', 600, 800))
        ->toMediaCollection('portrait');

    $media->refresh();

    expect($media->getCustomProperty('width'))->toBe(600)
        ->and($media->getCustomProperty('height'))->toBe(800)
        ->and($media->hasGeneratedConversion('thumb'))->toBeTrue()
        ->and($media->hasGeneratedConversion('webp'))->toBeTrue()
        ->and($media->hasGeneratedConversion('og'))->toBeTrue()
        ->and($media->getSrcset('webp'))->not->toBeEmpty();

    Storage::disk('public')->assertExists($media->getPathRelativeToRoot('webp'));
});

test('the portrait collection keeps a single file', function () {
    Storage::fake('public');
    $profile = Profile::current();

    $profile->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection('portrait');
    $profile->addMedia(UploadedFile::fake()->image('b.jpg'))->toMediaCollection('portrait');

    expect($profile->fresh()?->getMedia('portrait'))->toHaveCount(1);
});
