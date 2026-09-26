<?php

use App\Enums\ArticleStatus;
use App\Enums\AvailabilityStatus;
use App\Enums\CareerBranch;
use App\Enums\CompanyKind;
use App\Enums\ContactTopic;
use App\Enums\EmploymentType;
use App\Enums\ProjectStatus;
use App\Enums\ReadingStatus;
use App\Enums\SocialPlatform;
use App\Enums\WorkMode;

test('badge enums expose a label, color and icon for every case', function (string $enum) {
    foreach ($enum::cases() as $case) {
        expect($case->getLabel())->not->toBeEmpty()
            ->and($case->getColor())->not->toBeEmpty()
            ->and($case->getIcon())->not->toBeNull();
    }
})->with([
    ArticleStatus::class,
    AvailabilityStatus::class,
    CompanyKind::class,
    EmploymentType::class,
    ProjectStatus::class,
    ReadingStatus::class,
    WorkMode::class,
]);

test('enum values match the frontend string unions', function () {
    expect(array_column(WorkMode::cases(), 'value'))->toBe(['on-site', 'remote', 'hybrid'])
        ->and(array_column(ContactTopic::cases(), 'value'))->toBe(['role', 'advisory', 'speaking', 'hello'])
        ->and(SocialPlatform::ReadCv->value)->toBe('read-cv');
});

test('career branch is derived from the employment type', function (EmploymentType $type, CareerBranch $branch) {
    expect(CareerBranch::forEmploymentType($type))->toBe($branch);
})->with([
    [EmploymentType::FullTime, CareerBranch::Main],
    [EmploymentType::PartTime, CareerBranch::Main],
    [EmploymentType::Internship, CareerBranch::Main],
    [EmploymentType::Freelance, CareerBranch::Freelance],
    [EmploymentType::Contract, CareerBranch::Freelance],
    [EmploymentType::OpenSource, CareerBranch::Oss],
]);
