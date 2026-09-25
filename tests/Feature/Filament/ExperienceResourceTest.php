<?php

use App\Enums\EmploymentType;
use App\Enums\WorkMode;
use App\Filament\Resources\Experiences\Pages\CreateExperience;
use App\Filament\Resources\Experiences\Pages\EditExperience;
use App\Filament\Resources\Experiences\Pages\ListExperiences;
use App\Models\Company;
use App\Models\Experience;
use App\Models\Skill;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('the experience list renders current roles with a present badge', function () {
    $current = Experience::factory()->current()->create(['start_date' => '2024-02-01']);

    Livewire::test(ListExperiences::class)
        ->assertCanSeeTableRecords([$current])
        ->assertSee('Present');
});

test('a remote role in Germany without an address that is still current can be created', function () {
    $undo = Repeater::fake();
    $company = Company::factory()->create();

    Livewire::test(CreateExperience::class)
        ->fillForm([
            'company_id' => $company->id,
            'role' => 'Senior Laravel Developer',
            'employment_type' => EmploymentType::FullTime->value,
            'work_mode' => WorkMode::Remote->value,
            'country_code' => 'DE',
            'city' => 'Berlin',
            'start_date' => '2024-02-15',
            'is_current' => true,
            'summary' => 'Leading the payments team.',
            'highlights' => [['achievement' => 'Shipped the ledger.'], ['achievement' => 'Mentored 3 engineers.']],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $undo();
    $experience = Experience::query()->firstOrFail();

    expect($experience->work_mode)->toBe(WorkMode::Remote)
        ->and($experience->country_code)->toBe('DE')
        ->and($experience->address)->toBeNull()
        ->and($experience->start_date->format('Y-m-d'))->toBe('2024-02-01')
        ->and($experience->is_current)->toBeTrue()
        ->and($experience->highlights)->toBe(['Shipped the ledger.', 'Mentored 3 engineers.'])
        ->and($experience->location_label)->toBe('Berlin, Germany · Remote');
});

test('an on-site role in Syria with an address that has ended can be created', function () {
    Livewire::test(CreateExperience::class)
        ->fillForm([
            'organization_name' => 'Local agency',
            'role' => 'Backend Developer',
            'employment_type' => EmploymentType::FullTime->value,
            'work_mode' => WorkMode::OnSite->value,
            'country_code' => 'SY',
            'city' => 'Damascus',
            'address' => 'Mazzeh, Damascus',
            'start_date' => '2019-03-01',
            'is_current' => false,
            'end_date' => '2021-06-10',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $experience = Experience::query()->firstOrFail();

    expect($experience->organization)->toBe('Local agency')
        ->and($experience->address)->toBe('Mazzeh, Damascus')
        ->and($experience->end_date?->format('Y-m-d'))->toBe('2021-06-01')
        ->and($experience->is_current)->toBeFalse();
});

test('the tech stack keeps the selected order', function () {
    $experience = Experience::factory()->create();
    $react = Skill::factory()->create(['name' => 'React']);
    $laravel = Skill::factory()->create(['name' => 'Laravel']);

    Livewire::test(EditExperience::class, ['record' => $experience->getRouteKey()])
        ->fillForm(['stack' => [(string) $laravel->id, (string) $react->id]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($experience->fresh()?->stack)->toBe(['Laravel', 'React']);
});

test('the end date cannot be before the start and an organization is needed without a company', function () {
    Livewire::test(CreateExperience::class)
        ->fillForm([
            'company_id' => null,
            'organization_name' => '',
            'role' => 'Engineer',
            'start_date' => '2022-05-01',
            'is_current' => false,
            'end_date' => '2021-01-01',
        ])
        ->call('create')
        ->assertHasFormErrors(['organization_name' => 'required', 'end_date' => 'after_or_equal']);
});

test('an ended role needs an end date', function () {
    Livewire::test(CreateExperience::class)
        ->fillForm(['organization_name' => 'X', 'role' => 'Engineer', 'start_date' => '2022-05-01', 'is_current' => false, 'end_date' => null])
        ->call('create')
        ->assertHasFormErrors(['end_date' => 'required']);
});
