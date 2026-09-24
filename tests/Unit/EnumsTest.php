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
use App\Enums\Template;
use App\Enums\WorkMode;

test('every template has a label, description, fonts stylesheet and screenshot', function (Template $template) {
    expect($template->getLabel())->not->toBeEmpty()
        ->and($template->getDescription())->not->toBeEmpty()
        ->and($template->fontsHref())->toStartWith('https://fonts.googleapis.com/')
        ->and($template->screenshot())->toBe("templates/{$template->value}.webp");
})->with(Template::cases());

test('template fonts do not load arabic families', function (Template $template) {
    expect($template->fontsHref())
        ->not->toContain('Arabic')
        ->not->toContain('Readex')
        ->not->toContain('Cairo');
})->with(Template::cases());

test('the default template is changelog', function () {
    expect(Template::default())->toBe(Template::Changelog);
});

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
