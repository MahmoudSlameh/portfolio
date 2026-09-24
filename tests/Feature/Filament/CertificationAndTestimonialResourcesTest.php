<?php

use App\Filament\Resources\Certifications\Pages\ManageCertifications;
use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Certification;
use App\Models\Company;
use App\Models\Testimonial;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('certifications can be listed, created and edited in modals', function () {
    $existing = Certification::factory()->create(['expires_at' => now()->subYear()]);

    Livewire::test(ManageCertifications::class)
        ->assertCanSeeTableRecords([$existing])
        ->assertSee('Expired')
        ->callAction('create', data: [
            'name' => 'AWS Certified Solutions Architect',
            'issuer' => 'Amazon Web Services',
            'issued_at' => '2024-03-01',
            'credential_url' => 'https://aws.example/verify',
        ])
        ->assertHasNoFormErrors()
        ->callAction(TestAction::make('edit')->table($existing), data: ['issuer' => 'CNCF'])
        ->assertHasNoFormErrors();

    expect(Certification::query()->where('name', 'AWS Certified Solutions Architect')->exists())->toBeTrue()
        ->and($existing->fresh()?->issuer)->toBe('CNCF');
});

test('a certification cannot expire before it was issued', function () {
    Livewire::test(ManageCertifications::class)
        ->callAction('create', data: ['name' => 'X', 'issuer' => 'Y', 'issued_at' => '2024-03-01', 'expires_at' => '2023-01-01'])
        ->assertHasFormErrors(['expires_at' => 'after_or_equal']);
});

test('testimonials can be created for a company', function () {
    $company = Company::factory()->create();

    Livewire::test(ManageTestimonials::class)
        ->callAction('create', data: [
            'quote' => 'Mahmoud made the whole team sharper.',
            'author_name' => 'Lina Haddad',
            'author_role' => 'VP Engineering',
            'company_id' => $company->id,
        ])
        ->assertHasNoFormErrors();

    $testimonial = Testimonial::query()->firstOrFail();

    expect($testimonial->company?->is($company))->toBeTrue();

    Livewire::test(ManageTestimonials::class)->assertCanSeeTableRecords([$testimonial]);
});
