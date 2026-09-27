<?php

use App\Enums\CvTemplate;
use App\Models\Experience;
use App\Models\Profile;
use App\Support\Cv\CvOptions;
use App\Support\Cv\CvPdf;
use Illuminate\Support\Facades\File;
use Smalot\PdfParser\Parser;

/**
 * The PDF's text as a parser (and an ATS) reads it, whitespace collapsed.
 */
function pdfText(string $pdf): string
{
    return (string) preg_replace('/\s+/u', ' ', (new Parser)->parseContent($pdf)->getText());
}

test('the pdf text holds the name, every heading and every role, in reading order', function (CvTemplate $template) {
    seedCvDemo();
    $text = pdfText(app(CvPdf::class)->render(new CvOptions($template)));
    $roles = Experience::query()->visible()->orderByDesc('start_date')->orderByDesc('id')->pluck('role')->all();

    $expected = [Profile::current()->name, 'SUMMARY', 'EXPERIENCE', ...$roles, 'EDUCATION', 'SKILLS', 'CERTIFICATIONS', 'PROJECTS'];
    $offset = 0;

    foreach ($expected as $needle) {
        $position = mb_stripos($text, $needle, $offset);

        expect($position)->not->toBeFalse("\"{$needle}\" is missing or out of order in the {$template->value} PDF");
        $offset = (int) $position + mb_strlen($needle);
    }
})->with(CvTemplate::cases());

test('the pdf uses the chosen paper size', function (string $paper, array $size) {
    $pdf = app(CvPdf::class)->render(new CvOptions(paper: $paper));
    $box = (new Parser)->parseContent($pdf)->getPages()[0]->getDetails()['MediaBox'];

    expect([round($box[2]), round($box[3])])->toBe($size);
})->with([
    'A4' => ['a4', [595.0, 842.0]],
    'Letter' => ['letter', [612.0, 792.0]],
]);

test('an empty database still gives a valid pdf', function () {
    $pdf = app(CvPdf::class)->render();

    expect($pdf)->toStartWith('%PDF-')
        ->and(pdfText($pdf))->toContain(Profile::current()->name);
});

test('fonts are subset so the file stays small', function () {
    seedCvDemo();

    expect(strlen(app(CvPdf::class)->render()))->toBeLessThan(150 * 1024);
});

test('cv:generate writes the pdf', function () {
    seedCvDemo();
    $path = sys_get_temp_dir().'/cv-'.uniqid().'.pdf';

    $this->artisan('cv:generate', ['--template' => 'compact', '--paper' => 'letter', '--no-projects' => true, '--output' => $path])
        ->expectsOutputToContain('(Compact, LETTER)')
        ->assertSuccessful();

    $text = pdfText(File::get($path));
    File::delete($path);

    expect($text)->toContain('EXPERIENCE')->not->toContain('PROJECTS');
});

test('cv:generate rejects unknown templates and paper', function () {
    $this->artisan('cv:generate', ['--template' => 'fancy'])->expectsOutputToContain('Unknown template "fancy"')->assertFailed();
    $this->artisan('cv:generate', ['--paper' => 'a3'])->expectsOutputToContain('a4 or letter')->assertFailed();
});

test('the default file name comes from the profile name', function () {
    Profile::current()->update(['name' => 'Jane Q. Doe']);

    expect(app(CvPdf::class)->filename())->toBe('jane-q-doe-cv.pdf');
});
