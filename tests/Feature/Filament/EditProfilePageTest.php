<?php

use App\Enums\AvailabilityStatus;
use App\Filament\Pages\EditProfile;
use App\Models\Profile;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('the profile page renders', function () {
    $this->get(EditProfile::getUrl())->assertOk()->assertSee('Profile &amp; bio', false);
});

test('the owner can edit the profile, bio and highlights', function () {
    $undoRepeaterFake = Repeater::fake();

    Livewire::test(EditProfile::class)
        ->fillForm([
            'name' => 'Mahmoud Slameh',
            'role' => 'Senior Laravel Developer',
            'headline' => 'I build calm Laravel products.',
            'story' => [['paragraph' => 'First paragraph.'], ['paragraph' => 'Second paragraph.']],
            'focus_areas' => ['Laravel', 'React'],
            'availability_status' => AvailabilityStatus::Limited->value,
            'stats' => [['value' => '8', 'label' => 'years shipping software']],
            'latest_release' => ['added' => ['Portfolio v2'], 'changed' => [], 'removed' => []],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $undoRepeaterFake();
    $profile = Profile::current();

    expect($profile->name)->toBe('Mahmoud Slameh')
        ->and($profile->role)->toBe('Senior Laravel Developer')
        ->and($profile->story)->toBe(['First paragraph.', 'Second paragraph.'])
        ->and($profile->focus_areas)->toBe(['Laravel', 'React'])
        ->and($profile->availability_status)->toBe(AvailabilityStatus::Limited)
        ->and($profile->stats)->toBe([['value' => '8', 'label' => 'years shipping software']])
        ->and($profile->latest_release['added'])->toBe(['Portfolio v2']);
});

test('the portrait is uploaded to the media library', function () {
    Storage::fake('public');

    Livewire::test(EditProfile::class)
        ->fillForm([
            'portrait' => UploadedFile::fake()->image('me.jpg', 600, 800),
            'portrait_alt' => 'Me in soft light',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $media = Profile::current()->getFirstMedia('portrait');

    expect($media)->not->toBeNull()
        ->and($media?->getCustomProperty('width'))->toBe(600)
        ->and(Profile::current()->portrait_alt)->toBe('Me in soft light');
});

test('name and job title are required', function () {
    Livewire::test(EditProfile::class)
        ->fillForm(['name' => '', 'role' => ''])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required', 'role' => 'required']);
});
