<?php

use App\Filament\Pages\CvPage;
use App\Models\Profile;
use App\Support\Content\PortfolioContent;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Smalot\PdfParser\Parser;

test('the cv page shows the options and a live preview', function () {
    actingAsAdmin();

    Livewire::test(CvPage::class)
        ->assertOk()
        ->assertSee('Classic')
        ->assertSee('Compact')
        ->assertSeeHtml('title="CV preview"')
        ->assertSeeHtml(e(route('cv.preview', ['template' => 'classic', 'paper' => 'a4', 'include_projects' => 1, 'include_certifications' => 1])))
        ->fillForm(['template' => 'compact', 'paper' => 'letter', 'include_projects' => false])
        ->assertSeeHtml('template=compact&amp;paper=letter&amp;include_projects=0');
});

test('the preview route is for panel users only', function () {
    Profile::current()->update(['name' => 'Jane Preview']);

    $this->get('/cv/preview')->assertNotFound();

    actingAsAdmin();

    $this->get('/cv/preview?template=modern&paper=letter')
        ->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('Jane Preview')
        ->assertSee('size: letter', false)
        ->assertSee('max-width: 216mm', false);
});

test('generate pdf downloads the cv', function () {
    actingAsAdmin();
    Profile::current()->update(['name' => 'Jane Doe']);

    Livewire::test(CvPage::class)
        ->fillForm(['template' => 'modern'])
        ->callAction('download')
        ->assertFileDownloaded('jane-doe-cv.pdf');
});

test('use as my resume stores the pdf behind the site download link', function () {
    Storage::fake('public');
    actingAsAdmin();
    seedCvDemo();

    Livewire::test(CvPage::class)
        ->assertSee('Your site has no resume file yet')
        ->assertActionExists('useAsResume', fn ($action): bool => str_contains((string) $action->getModalDescription(), 'will serve this PDF'))
        ->fillForm(['template' => 'compact', 'include_projects' => false])
        ->callAction('useAsResume')
        ->assertNotified('Your resume is updated');

    $media = Profile::current()->fresh()?->getFirstMedia('resume');
    $text = (new Parser)->parseContent((string) file_get_contents((string) $media?->getPath()))->getText();

    expect($media?->mime_type)->toBe('application/pdf')
        ->and($media?->name)->toBe('CV (Compact)')
        ->and($text)->toContain(Profile::current()->name)
        ->and(mb_stripos($text, 'PROJECTS'))->toBeFalse()
        ->and(app(PortfolioContent::class)->profile()['resumeUrl'] ?? null)->toBe($media?->getUrl());

    Livewire::test(CvPage::class)
        ->assertSee('Your site currently offers')
        ->assertActionExists('useAsResume', fn ($action): bool => str_contains((string) $action->getModalDescription(), 'replaces your current resume'));
});
