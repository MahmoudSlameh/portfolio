<?php

use App\Models\SiteSetting;
use App\Support\Media\Favicons;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

function uploadFavicon(UploadedFile $file): SiteSetting
{
    $settings = SiteSetting::current();
    $settings->addMedia($file)->toMediaCollection('favicon');

    return $settings->refresh();
}

function svgFavicon(): UploadedFile
{
    return UploadedFile::fake()->createWithContent('icon.svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="12" fill="#6d4aff"/></svg>');
}

it('makes favicon.ico, a 192px PNG and the apple-touch-icon from a PNG favicon', function () {
    $settings = uploadFavicon(UploadedFile::fake()->image('icon.png', 300, 200));

    $this->artisan('site:favicons')->assertSuccessful();

    $directory = 'favicons/'.$settings->getFirstMedia('favicon')->getKey();
    $disk = Storage::disk('public');

    expect(getimagesizefromstring($disk->get("{$directory}/favicon-192.png")))->toMatchArray([0 => 192, 1 => 192])
        ->and(getimagesizefromstring($disk->get("{$directory}/apple-touch-icon.png")))->toMatchArray([0 => 180, 1 => 180]);

    $ico = $disk->get("{$directory}/favicon.ico");
    $header = unpack('vreserved/vtype/vcount', $ico);
    $sizes = array_map(fn (int $i): int => ord($ico[6 + 16 * $i]), range(0, $header['count'] - 1));

    expect($header)->toMatchArray(['reserved' => 0, 'type' => 1, 'count' => 3])
        ->and($sizes)->toBe([16, 32, 48]);

    // The touch icon has no transparency; the other icons keep it.
    $apple = imagecreatefromstring($disk->get("{$directory}/apple-touch-icon.png"));
    $png = imagecreatefromstring($disk->get("{$directory}/favicon-192.png"));

    expect(imagecolorsforindex($apple, imagecolorat($apple, 0, 0))['alpha'])->toBe(0)
        ->and(imagecolorsforindex($png, imagecolorat($png, 0, 0))['alpha'])->toBe(127);
});

it('links the generated icons in the page head', function () {
    $settings = uploadFavicon(UploadedFile::fake()->image('icon.png', 256, 256));
    Favicons::sync($settings);
    $links = Favicons::links($settings->refresh());

    $this->get('/')->assertOk()
        ->assertSee('<link rel="icon" href="'.$links['ico'].'" sizes="48x48">', false)
        ->assertSee('<link rel="icon" href="'.$links['png'].'" type="image/png" sizes="192x192">', false)
        ->assertSee('<link rel="apple-touch-icon" href="'.$links['apple'].'">', false)
        ->assertDontSee('href="/apple-touch-icon.png"', false);
});

it('keeps the default icons when no favicon is uploaded', function () {
    $this->artisan('site:favicons')->assertSuccessful();

    $this->get('/')->assertOk()
        ->assertSee('<link rel="icon" href="/favicon.svg" type="image/svg+xml">', false)
        ->assertSee('<link rel="apple-touch-icon" href="/apple-touch-icon.png">', false);
});

it('only regenerates on --force, and removes the icons of a replaced favicon', function () {
    $first = uploadFavicon(UploadedFile::fake()->image('a.png', 64, 64))->getFirstMedia('favicon');
    $this->artisan('site:favicons')->expectsOutputToContain('Favicons generated.')->assertSuccessful();
    $this->artisan('site:favicons')->expectsOutputToContain('up to date')->assertSuccessful();
    $this->artisan('site:favicons --force')->expectsOutputToContain('Favicons generated.')->assertSuccessful();

    $second = uploadFavicon(UploadedFile::fake()->image('b.png', 64, 64))->getFirstMedia('favicon');
    $this->artisan('site:favicons')->assertSuccessful();

    expect(Storage::disk('public')->directories('favicons'))->toBe(['favicons/'.$second->getKey()])
        ->and($first->getKey())->not->toBe($second->getKey());
});

it('rasterises an SVG favicon with Chrome', function () {
    $chrome = collect(glob('/opt/pw-browsers/chromium-*/chrome-linux/chrome') ?: [])->first() ?? Favicons::chrome();

    if ($chrome === null) {
        $this->markTestSkipped('Chrome is not installed.');
    }

    config(['studio.screenshots.chrome' => $chrome, 'studio.screenshots.no_sandbox' => true]);
    $settings = uploadFavicon(svgFavicon());

    $this->artisan('site:favicons')->assertSuccessful();

    $png = imagecreatefromstring(Storage::disk('public')->get('favicons/'.$settings->getFirstMedia('favicon')->getKey().'/favicon-192.png'));
    $centre = imagecolorsforindex($png, imagecolorat($png, 96, 96));

    expect(imagesx($png))->toBe(192)
        ->and([$centre['red'], $centre['green'], $centre['blue'], $centre['alpha']])->toBe([0x6D, 0x4A, 0xFF, 0])
        // The rounded corner stays transparent.
        ->and(imagecolorsforindex($png, imagecolorat($png, 0, 0))['alpha'])->toBe(127);
})->skipOnWindows();

it('explains what to do when an SVG favicon cannot be rasterised', function () {
    config(['studio.screenshots.chrome' => '/nonexistent/chrome']);
    Favicons::$useImagick = false;
    uploadFavicon(svgFavicon());

    try {
        $this->artisan('site:favicons')
            ->expectsOutputToContain('Chrome could not render the SVG favicon')
            ->assertFailed();
    } finally {
        Favicons::$useImagick = true;
    }
});
