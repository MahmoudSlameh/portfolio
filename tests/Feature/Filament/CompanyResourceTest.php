<?php

use App\Enums\CompanyKind;
use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('the companies list renders with its records', function () {
    $companies = Company::factory()->count(3)->create();

    Livewire::test(ListCompanies::class)
        ->assertOk()
        ->assertCanSeeTableRecords($companies);
});

test('clients can be filtered', function () {
    $client = Company::factory()->client()->create();
    $employer = Company::factory()->create();

    Livewire::test(ListCompanies::class)
        ->filterTable('kind', CompanyKind::Client->value)
        ->assertCanSeeTableRecords([$client])
        ->assertCanNotSeeTableRecords([$employer]);
});

test('a client can be created with a logo and a website link', function () {
    Storage::fake('public');

    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Souk.co',
            'kind' => CompanyKind::Client->value,
            'website_url' => 'https://souk.example',
            'logo' => UploadedFile::fake()->image('logo.png', 400, 160),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $company = Company::query()->where('name', 'Souk.co')->firstOrFail();

    expect($company->slug)->toBe('soukco')
        ->and($company->kind)->toBe(CompanyKind::Client)
        ->and($company->website_url)->toBe('https://souk.example')
        ->and($company->hasMedia('logo'))->toBeTrue();
});

test('a company can be edited', function () {
    $company = Company::factory()->create();

    Livewire::test(EditCompany::class, ['record' => $company->getRouteKey()])
        ->assertSchemaStateSet(['name' => $company->name])
        ->fillForm(['name' => 'Lattice Freight', 'engagement' => 'Rebuilt tracking.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($company->fresh()?->name)->toBe('Lattice Freight');
});

test('name is required and the website must be a url', function () {
    Livewire::test(CreateCompany::class)
        ->fillForm(['name' => '', 'website_url' => 'not a url'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'website_url' => 'url']);
});
