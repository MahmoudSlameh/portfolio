<?php

use App\Mcp\Servers\PortfolioServer;
use App\Mcp\Tools\Companies\SetCompanyLogoTool;
use App\Mcp\Tools\Projects\AddProjectImageTool;
use App\Mcp\Tools\Projects\RemoveProjectImageTool;
use App\Mcp\Tools\Projects\SetProjectCoverTool;
use App\Mcp\Tools\Projects\UpdateProjectImageTool;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectGalleryItem;
use App\Support\Media\PublicUrl;
use Illuminate\Http\UploadedFile;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    Storage::fake('public');
    config()->set('studio.screenshots.chrome', null);
    PublicUrl::resolveUsing(fn (string $host): array => match ($host) {
        'internal.example.com' => ['10.0.0.5'],
        default => ['93.184.216.34'],
    });
});

afterEach(function () {
    PublicUrl::resolveUsing(null);
});

function pngBytes(int $width = 64, int $height = 36): string
{
    return UploadedFile::fake()->image('image.png', $width, $height)->get();
}

test('set_project_cover downloads an image from a public url', function () {
    Http::fake(['https://raw.githubusercontent.com/*' => Http::response(pngBytes(), 200, ['Content-Type' => 'image/png'])]);
    $project = Project::factory()->create(['title' => 'Atlas']);

    PortfolioServer::tool(SetProjectCoverTool::class, [
        'project' => 'atlas',
        'alt' => 'The Atlas dashboard',
        'image_url' => 'https://raw.githubusercontent.com/acme/atlas/main/docs/screen.png',
    ])->assertOk()->assertSee('Set the cover of \"Atlas\".');

    $media = $project->refresh()->getFirstMedia('cover');

    expect($media)->not->toBeNull()
        ->and($media->mime_type)->toBe('image/png')
        ->and($media->getCustomProperty('width'))->toBe(64)
        ->and($project->cover_alt)->toBe('The Atlas dashboard');
});

test('images can be sent as base64 and must be real images of an accepted type', function () {
    $project = Project::factory()->create();

    PortfolioServer::tool(SetProjectCoverTool::class, [
        'project' => $project->id,
        'alt' => 'Cover',
        'image_base64' => 'data:image/png;base64,'.base64_encode(pngBytes()),
    ])->assertOk();

    expect($project->refresh()->getFirstMedia('cover'))->not->toBeNull();

    PortfolioServer::tool(SetProjectCoverTool::class, ['project' => $project->id, 'alt' => 'Cover', 'image_base64' => base64_encode('<svg xmlns="http://www.w3.org/2000/svg"/>')])
        ->assertHasErrors(['The base64 image is not an accepted image (got image/svg+xml; accepted: image/jpeg, image/png, image/webp, image/avif).']);

    PortfolioServer::tool(SetProjectCoverTool::class, ['project' => $project->id, 'alt' => 'Cover', 'image_base64' => 'not base64!'])
        ->assertHasErrors(['image_base64 is not valid base64 data.']);
});

test('exactly one image source is required', function () {
    $project = Project::factory()->create();

    PortfolioServer::tool(SetProjectCoverTool::class, ['project' => $project->id, 'alt' => 'Cover'])
        ->assertHasErrors(['Send the image as image_url, image_base64 or screenshot_url.']);

    PortfolioServer::tool(SetProjectCoverTool::class, [
        'project' => $project->id,
        'alt' => 'Cover',
        'image_url' => 'https://example.com/a.png',
        'image_base64' => base64_encode(pngBytes()),
    ])->assertHasErrors(['Send only one of image_url, image_base64 and screenshot_url.']);
});

test('the server refuses to fetch private or local addresses', function (string $url) {
    Http::fake();
    $project = Project::factory()->create();

    PortfolioServer::tool(SetProjectCoverTool::class, ['project' => $project->id, 'alt' => 'Cover', 'image_url' => $url])
        ->assertHasErrors();

    Http::assertNothingSent();
})->with([
    'loopback' => 'http://127.0.0.1/admin.png',
    'localhost' => 'http://localhost/image.png',
    'cloud metadata' => 'http://169.254.169.254/latest/meta-data',
    'private dns' => 'https://internal.example.com/image.png',
    'ipv6 loopback' => 'http://[::1]/image.png',
]);

test('redirects are followed only to public addresses', function () {
    Http::fake([
        'https://example.com/moved.png' => Http::response('', 302, ['Location' => 'https://cdn.example.com/real.png']),
        'https://cdn.example.com/real.png' => Http::response(pngBytes()),
        'https://example.com/sneaky.png' => Http::response('', 302, ['Location' => 'http://10.1.2.3/secret.png']),
    ]);
    $project = Project::factory()->create();

    PortfolioServer::tool(SetProjectCoverTool::class, ['project' => $project->id, 'alt' => 'Cover', 'image_url' => 'https://example.com/moved.png'])
        ->assertOk();

    PortfolioServer::tool(SetProjectCoverTool::class, ['project' => $project->id, 'alt' => 'Cover', 'image_url' => 'https://example.com/sneaky.png'])
        ->assertHasErrors(['Cannot download http://10.1.2.3/secret.png: Local and internal addresses are not allowed.']);
});

test('downloads report http errors and oversized images', function () {
    config()->set('portfolio.mcp.images.max_kilobytes', 1);
    Http::fake([
        'https://example.com/missing.png' => Http::response('Not found', 404),
        'https://example.com/huge.png' => Http::response(str_repeat('x', 2048)),
    ]);
    $project = Project::factory()->create();

    PortfolioServer::tool(SetProjectCoverTool::class, ['project' => $project->id, 'alt' => 'Cover', 'image_url' => 'https://example.com/missing.png'])
        ->assertHasErrors(['Cannot download https://example.com/missing.png: the server answered with HTTP 404.']);

    PortfolioServer::tool(SetProjectCoverTool::class, ['project' => $project->id, 'alt' => 'Cover', 'image_url' => 'https://example.com/huge.png'])
        ->assertHasErrors();
});

test('screenshot_url captures the page with headless Chrome', function () {
    config()->set('studio.screenshots.chrome', '/usr/bin/chromium');
    $png = pngBytes(160, 90);

    Process::fake(function (PendingProcess $process) use ($png) {
        expect($process->command)->toContain('--window-size=1024,768')->toContain('https://atlas.example.com');

        $target = Str::after(collect($process->command)->first(fn (string $arg): bool => str_starts_with($arg, '--screenshot=')), '--screenshot=');
        file_put_contents($target, $png);

        return Process::result();
    });

    $project = Project::factory()->create(['title' => 'Atlas']);

    PortfolioServer::tool(AddProjectImageTool::class, [
        'project' => $project->id,
        'alt' => 'Atlas home page',
        'caption' => 'The landing page',
        'screenshot_url' => 'https://atlas.example.com',
        'screenshot_viewport' => 'tablet',
    ])->assertOk();

    $item = $project->galleryItems()->sole();

    expect($item->caption)->toBe('The landing page')
        ->and($item->getFirstMedia('image')?->mime_type)->toBe('image/png');
});

test('screenshots fail clearly when Chrome is not available', function () {
    config()->set('studio.screenshots.chrome', '/nonexistent/chrome');
    Process::fake(fn () => Process::result(errorOutput: 'not found', exitCode: 127));
    $project = Project::factory()->create();

    PortfolioServer::tool(SetProjectCoverTool::class, ['project' => $project->id, 'alt' => 'Cover', 'screenshot_url' => 'https://atlas.example.com'])
        ->assertHasErrors(['Chrome could not screenshot https://atlas.example.com: not found']);

    expect($project->refresh()->getFirstMedia('cover'))->toBeNull();
});

test('gallery images are added in order, edited and removed', function () {
    $project = Project::factory()->create(['title' => 'Atlas']);

    foreach (['First', 'Second'] as $alt) {
        PortfolioServer::tool(AddProjectImageTool::class, ['project' => 'atlas', 'alt' => $alt, 'image_base64' => base64_encode(pngBytes())])->assertOk();
    }

    [$first, $second] = $project->galleryItems()->get()->all();

    expect([$first->alt, $second->alt])->toBe(['First', 'Second'])
        ->and($second->sort_order)->toBeGreaterThan($first->sort_order);

    PortfolioServer::tool(UpdateProjectImageTool::class, ['image_id' => $second->id, 'caption' => 'Now first', 'sort_order' => 0])->assertOk();
    expect($project->galleryItems()->first()->caption)->toBe('Now first');

    PortfolioServer::tool(RemoveProjectImageTool::class, ['image_id' => $first->id])->assertOk();
    expect(ProjectGalleryItem::query()->pluck('id')->all())->toBe([$second->id]);

    PortfolioServer::tool(RemoveProjectImageTool::class, ['image_id' => 999])->assertHasErrors();
});

test('a failed download does not leave an empty gallery item', function () {
    Http::fake(['*' => Http::response('Not found', 404)]);
    $project = Project::factory()->create();

    PortfolioServer::tool(AddProjectImageTool::class, ['project' => $project->id, 'alt' => 'Shot', 'image_url' => 'https://example.com/x.png'])
        ->assertHasErrors();

    expect(ProjectGalleryItem::query()->count())->toBe(0);
});

test('set_company_logo stores light and dark logos but no svg or screenshot', function () {
    $company = Company::factory()->create(['name' => 'Acme']);

    PortfolioServer::tool(SetCompanyLogoTool::class, ['company' => $company->slug, 'image_base64' => base64_encode(pngBytes())])->assertOk();
    PortfolioServer::tool(SetCompanyLogoTool::class, ['company' => $company->id, 'variant' => 'dark', 'image_base64' => base64_encode(pngBytes())])
        ->assertSee('Set the dark logo of \"Acme\".');

    expect($company->refresh()->getFirstMedia('logo'))->not->toBeNull()
        ->and($company->getFirstMedia('logo_dark'))->not->toBeNull();

    PortfolioServer::tool(SetCompanyLogoTool::class, ['company' => $company->id, 'image_base64' => base64_encode('<svg xmlns="http://www.w3.org/2000/svg"/>')])
        ->assertHasErrors();
    PortfolioServer::tool(SetCompanyLogoTool::class, ['company' => $company->id, 'screenshot_url' => 'https://acme.example.com'])
        ->assertHasErrors(['A logo cannot be a screenshot: send image_url or image_base64.']);
});
