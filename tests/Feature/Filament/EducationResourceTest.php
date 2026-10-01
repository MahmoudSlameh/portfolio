<?php

use App\Filament\Resources\Education\Pages\CreateEducation;
use App\Filament\Resources\Education\Pages\EditEducation;
use App\Filament\Resources\Education\Pages\ListEducation;
use App\Models\Education;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('the education list shows grades and current studies', function () {
    $current = Education::factory()->current()->create(['grade' => 'Distinction']);

    Livewire::test(ListEducation::class)
        ->assertCanSeeTableRecords([$current])
        ->assertSee('Distinction')
        ->assertSee('Present');
});

test('a diploma that is still being studied can be created', function () {
    $undo = Repeater::fake();

    Livewire::test(CreateEducation::class)
        ->fillForm([
            'degree' => 'Diploma in Software Engineering',
            'institution' => 'Damascus University',
            'field_of_study' => 'Software Engineering',
            'grade' => 'Very good',
            'country_code' => 'SY',
            'start_date' => '2023-09-10',
            'is_current' => true,
            'achievements' => [['achievement' => 'Top of class in databases']],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $undo();
    $education = Education::query()->firstOrFail();

    expect($education->is_current)->toBeTrue()
        ->and($education->start_date->format('Y-m-d'))->toBe('2023-09-01')
        ->and($education->grade)->toBe('Very good')
        ->and($education->achievements)->toBe(['Top of class in databases'])
        ->and($education->location_label)->toBe('Syria');
});

test('a finished qualification can be edited', function () {
    // Pinned start: the factory's random start date can fall after the end date set below.
    $education = Education::factory()->create(['start_date' => '2018-09-01']);

    Livewire::test(EditEducation::class, ['record' => $education->getRouteKey()])
        ->fillForm(['grade' => '3.7 / 4.0', 'is_current' => false, 'end_date' => '2022-06-01'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($education->fresh()?->grade)->toBe('3.7 / 4.0');
});

test('qualification, institution and start are required', function () {
    Livewire::test(CreateEducation::class)
        ->fillForm(['degree' => '', 'institution' => '', 'start_date' => null])
        ->call('create')
        ->assertHasFormErrors(['degree' => 'required', 'institution' => 'required', 'start_date' => 'required']);
});
