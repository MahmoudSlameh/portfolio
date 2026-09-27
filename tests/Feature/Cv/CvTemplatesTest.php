<?php

use App\Enums\CvTemplate;
use App\Enums\SocialPlatform;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Social;
use App\Support\Cv\CvData;
use App\Support\Cv\CvOptions;
use App\Support\Cv\CvRenderer;

function cvHtml(CvTemplate $template, array $options = []): string
{
    return app(CvRenderer::class)->html(new CvOptions($template, ...$options));
}

test('every template renders the demo content with the standard sections in order', function (CvTemplate $template) {
    seedCvDemo();
    $html = cvHtml($template);
    $profile = Profile::current();
    $firstRole = Experience::query()->visible()->orderByDesc('start_date')->firstOrFail();

    expect($html)
        ->toContain($profile->name)
        ->toContain(e($firstRole->role))
        ->toContain($firstRole->start_date->format('M Y').' – Present')
        // ATS: real text only, no layout tables, images or inline SVG.
        ->not->toContain('<table')
        ->not->toContain('<img')
        ->not->toContain('<svg');

    $positions = array_map(fn (string $heading): int => (int) strpos($html, ">{$heading}</h2>"), ['Summary', 'Experience', 'Education', 'Skills', 'Certifications', 'Projects']);

    expect($positions)->each->toBeGreaterThan(0)
        ->and($positions)->toBe(collect($positions)->sort()->values()->all());
})->with(CvTemplate::cases());

test('every template renders with an empty database', function (CvTemplate $template) {
    $html = cvHtml($template);

    expect($html)->toContain('<h1 class="cv-name">')
        ->not->toContain('>Experience</h2>')
        ->not->toContain('>Projects</h2>');
})->with(CvTemplate::cases());

test('the paper size and options are applied', function () {
    seedCvDemo();

    $letter = cvHtml(CvTemplate::Modern, ['paper' => 'letter', 'includeProjects' => false, 'includeCertifications' => false, 'maxRoles' => 2]);

    expect($letter)->toContain('size: letter')
        ->not->toContain('>Projects</h2>')
        ->not->toContain('>Certifications</h2>')
        ->and(substr_count($letter, '<h3 class="cv-entry-title">'))->toBe(2 + count(CvData::build()->education))
        ->and(cvHtml(CvTemplate::Modern))->toContain('size: A4');
});

test('the cv follows the site visibility rules', function () {
    Experience::factory()->create(['role' => 'Visible role', 'is_visible' => true]);
    Experience::factory()->create(['role' => 'Hidden role', 'is_visible' => false]);
    Certification::factory()->create(['name' => 'Current cert', 'expires_at' => now()->addYear()]);
    Certification::factory()->create(['name' => 'Expired cert', 'expires_at' => now()->subDay()]);
    Social::factory()->create(['platform' => SocialPlatform::Github, 'url' => 'https://github.com/jane/']);
    Social::factory()->create(['platform' => SocialPlatform::Rss, 'url' => 'https://example.com/rss.xml']);

    $data = CvData::build();

    expect(array_column($data->experience, 'role'))->toBe(['Visible role'])
        ->and(array_column($data->certifications, 'name'))->toBe(['Current cert'])
        ->and($data->links)->toBe([['label' => 'GitHub', 'url' => 'github.com/jane']]);
});

test('names and text are escaped', function () {
    Profile::current()->update(['name' => '<script>alert(1)</script> Jane']);

    expect(cvHtml(CvTemplate::Classic))->not->toContain('<script>alert(1)</script>')->toContain('&lt;script&gt;');
});

test('dates use the MMM YYYY format parsers expect', function () {
    expect(CvData::period(now()->setDate(2021, 4, 12), now()->setDate(2024, 1, 3)))->toBe('Apr 2021 – Jan 2024')
        ->and(CvData::period(now()->setDate(2024, 2, 1), null))->toBe('Feb 2024 – Present')
        ->and(CvData::displayUrl('https://www.linkedin.com/in/jane/'))->toBe('linkedin.com/in/jane');
});
