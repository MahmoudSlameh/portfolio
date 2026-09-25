<?php

use App\Enums\CareerBranch;
use App\Enums\EmploymentType;
use App\Enums\WorkMode;
use App\Models\Certification;
use App\Models\Company;
use App\Models\Education;
use App\Models\Experience;
use App\Support\Content\ChangelogMetadata;

test('an experience without an end date is the current role', function () {
    expect(Experience::factory()->current()->create()->is_current)->toBeTrue()
        ->and(Experience::factory()->create(['end_date' => '2024-01-01'])->is_current)->toBeFalse();
});

test('the organization falls back to the free-text name when there is no company', function () {
    $company = Company::factory()->create(['name' => 'Tessellate']);

    expect(Experience::factory()->for($company)->create()->organization)->toBe('Tessellate')
        ->and(Experience::factory()->openSource()->create(['organization_name' => 'Quire'])->organization)->toBe('Quire');
});

test('the experience location combines city, country and work mode', function () {
    $experience = Experience::factory()->make([
        'city' => 'Amsterdam',
        'country_code' => 'NL',
        'work_mode' => WorkMode::Remote,
        'address' => null,
    ]);

    expect($experience->location_label)->toBe('Amsterdam, Netherlands · Remote');

    $experience->city = null;
    $experience->country_code = null;

    expect($experience->location_label)->toBe('Remote');
});

test('month dates are stored as the first of the month', function () {
    $experience = Experience::factory()->create(['start_date' => '2024-02-01', 'end_date' => null]);

    expect($experience->fresh()?->start_date->format('Y-m'))->toBe('2024-02');
});

test('the branch is derived from the employment type unless overridden', function () {
    $freelance = Experience::factory()->make(['employment_type' => EmploymentType::Freelance]);
    $overridden = Experience::factory()->make(['employment_type' => EmploymentType::Freelance, 'branch' => CareerBranch::Main]);

    expect($freelance->resolved_branch)->toBe(CareerBranch::Freelance)
        ->and($overridden->resolved_branch)->toBe(CareerBranch::Main);
});

test('the commit hash is a stable 7-character hash unless overridden', function () {
    $experience = Experience::factory()->create();

    expect($experience->resolved_commit)->toHaveLength(7)
        ->and($experience->resolved_commit)->toBe($experience->fresh()?->resolved_commit);

    $experience->update(['commit_hash' => 'a41c9e2']);

    expect($experience->resolved_commit)->toBe('a41c9e2');
});

test('changelog metadata numbers versions per branch from the oldest role', function () {
    $intern = Experience::factory()->create(['start_date' => '2015-06-01', 'employment_type' => EmploymentType::Internship]);
    $oss = Experience::factory()->openSource()->create(['start_date' => '2021-01-01', 'role' => 'Maintainer', 'organization_name' => 'Quire']);
    $staff = Experience::factory()->current()->create(['start_date' => '2024-02-01', 'role' => 'Staff Engineer']);
    $custom = Experience::factory()->create(['start_date' => '2022-01-01', 'version' => 'v9.9.9', 'commit_message' => 'feat: custom']);

    $metadata = ChangelogMetadata::for(Experience::all());

    expect($metadata[$intern->id])->toMatchArray(['branch' => CareerBranch::Main, 'version' => 'v1.0.0', 'message' => 'init: first commit'])
        ->and($metadata[$oss->id])->toMatchArray(['branch' => CareerBranch::Oss, 'version' => 'oss/1.0.0', 'message' => 'chore(oss): Maintainer at Quire'])
        ->and($metadata[$custom->id])->toMatchArray(['version' => 'v9.9.9', 'message' => 'feat: custom'])
        ->and($metadata[$staff->id]['version'])->toBe('v3.0.0')
        ->and($metadata[$staff->id]['message'])->toBe("feat(career): join {$staff->organization} as Staff Engineer")
        ->and($metadata[$staff->id]['commit'])->toBe($staff->resolved_commit);
});

test('a company gets a slug from its name', function () {
    $company = Company::factory()->create(['name' => 'Lattice Freight', 'slug' => null]);

    expect($company->slug)->toBe('lattice-freight')
        ->and($company->getRouteKeyName())->toBe('slug');
});

test('the company period is derived from its experiences unless set', function () {
    $company = Company::factory()->create();
    Experience::factory()->for($company)->create(['start_date' => '2019-06-01', 'end_date' => '2020-01-01']);
    Experience::factory()->for($company)->create(['start_date' => '2020-02-01', 'end_date' => '2021-03-01']);

    expect($company->fresh()?->resolved_period)->toBe('2019 — 2021');

    Experience::factory()->for($company)->current()->create(['start_date' => '2021-04-01']);

    expect($company->fresh()?->resolved_period)->toBe('2019 — now');

    $company->update(['period_label' => '2018']);

    expect($company->resolved_period)->toBe('2018')
        ->and(Company::factory()->create()->resolved_period)->toBeNull();
});

test('deleting a company keeps its experiences', function () {
    $experience = Experience::factory()->create();

    $experience->company?->delete();

    expect($experience->fresh()?->company_id)->toBeNull();
});

test('visible and ordered scopes filter hidden rows and respect sort order', function () {
    $second = Company::factory()->create(['sort_order' => 2]);
    $first = Company::factory()->create(['sort_order' => 1]);
    Company::factory()->hidden()->create(['sort_order' => 0]);

    expect(Company::query()->visible()->ordered()->pluck('id')->all())->toBe([$first->id, $second->id]);
});

test('education without an end date is current and has a location label', function () {
    $education = Education::factory()->current()->create(['city' => 'Damascus', 'country_code' => 'SY']);

    expect($education->is_current)->toBeTrue()
        ->and($education->location_label)->toBe('Damascus, Syria');
});

test('a certification past its expiry date is expired', function () {
    expect(Certification::factory()->make(['expires_at' => now()->subDay()])->is_expired)->toBeTrue()
        ->and(Certification::factory()->make(['expires_at' => null])->is_expired)->toBeFalse();
});
