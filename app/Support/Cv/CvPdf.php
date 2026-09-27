<?php

namespace App\Support\Cv;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

/**
 * Turns a CV into a PDF with dompdf (P11-03): pure PHP, so it works on any host, and it writes real text,
 * which is what ATS parsers read. Remote resources are never loaded (dompdf's default).
 */
final class CvPdf
{
    public function __construct(private readonly CvRenderer $renderer) {}

    /**
     * The PDF file contents.
     */
    public function render(CvOptions $options = new CvOptions): string
    {
        return Pdf::loadHTML($this->renderer->html($options))
            ->setPaper($options->paper === 'letter' ? 'letter' : 'a4')
            ->setOption(['isRemoteEnabled' => false, 'isPhpEnabled' => false, 'isJavascriptEnabled' => false, 'isFontSubsettingEnabled' => true])
            ->output();
    }

    /**
     * `<name>-cv.pdf`, e.g. `adam-rahman-cv.pdf`.
     */
    public function filename(): string
    {
        return (Str::slug(CvData::build(new CvOptions(includeProjects: false, includeCertifications: false))->name) ?: 'cv').'-cv.pdf';
    }
}
