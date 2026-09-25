<?php

use App\Models\Article;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Content\PortfolioContent;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('the database seeder creates the admin from the environment and the singletons', function () {
    config(['portfolio.admin' => ['name' => 'Mahmoud', 'email' => 'owner@example.com', 'password' => 'secret-pass']]);

    $this->seed();

    $user = User::query()->where('email', 'owner@example.com')->firstOrFail();

    expect($user->name)->toBe('Mahmoud')
        ->and(Hash::check('secret-pass', $user->password))->toBeTrue()
        ->and(SiteSetting::query()->count())->toBe(1)
        ->and(Profile::query()->count())->toBe(1);
});

test('the admin user is skipped without an email', function () {
    config(['portfolio.admin' => ['name' => 'Admin', 'email' => null, 'password' => null]]);

    $this->seed();

    expect(User::query()->count())->toBe(0);
});

test('the demo content seeder imports the reference portfolio and can run twice', function () {
    Storage::fake('public');

    $this->seed(DemoContentSeeder::class);
    $this->seed(DemoContentSeeder::class);

    $content = app(PortfolioContent::class);
    $ledgerline = $content->projectBySlug('ledgerline');

    expect(Experience::query()->count())->toBe(7)
        ->and(Project::query()->count())->toBe(8)
        ->and(Article::query()->count())->toBe(5)
        ->and(Profile::current()->name)->toBe('Adam Rahman')
        ->and(Profile::current()->hasMedia('portrait'))->toBeTrue()
        ->and($ledgerline['cover']['width'] ?? null)->toBe(1280)
        ->and($ledgerline['gallery'] ?? [])->toHaveCount(2)
        ->and($ledgerline['stack'] ?? [])->toBe(['Go', 'PostgreSQL', 'Kafka', 'Terraform', 'OpenTelemetry'])
        ->and($content->articleBySlug('ledgers-are-just-logs')['readingMinutes'] ?? null)->toBe(2)
        ->and(array_column($content->career(), 'version'))->toContain('v5.0.0', 'oss/1.0.0')
        ->and($content->now()['readingBookIds'])->not->toBeEmpty();
});
