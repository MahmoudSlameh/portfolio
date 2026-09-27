<?php

namespace App\Console\Commands;

use App\Enums\CvTemplate;
use App\Support\Cv\CvOptions;
use App\Support\Cv\CvPdf;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('cv:generate
    {--template=classic : classic, modern or compact}
    {--paper=a4 : a4 or letter}
    {--max-roles= : Keep only the most recent roles}
    {--no-projects : Leave out the Projects section}
    {--no-certifications : Leave out the Certifications section}
    {--output= : Where to write the PDF (default: storage/app/private/cv/<name>-cv.pdf)}')]
#[Description('Generate an ATS-friendly CV as a PDF from the panel content')]
class CvGenerateCommand extends Command
{
    public function handle(CvPdf $pdf): int
    {
        $template = CvTemplate::tryFrom((string) $this->option('template'));

        if ($template === null) {
            $this->components->error('Unknown template "'.$this->option('template').'". Use one of: '.implode(', ', array_column(CvTemplate::cases(), 'value')).'.');

            return self::FAILURE;
        }

        if (! in_array($this->option('paper'), ['a4', 'letter'], true)) {
            $this->components->error('The paper must be a4 or letter.');

            return self::FAILURE;
        }

        $options = CvOptions::fromArray([
            'template' => $template->value,
            'paper' => $this->option('paper'),
            'max_roles' => $this->option('max-roles'),
            'include_projects' => ! $this->option('no-projects'),
            'include_certifications' => ! $this->option('no-certifications'),
        ]);

        $path = (string) ($this->option('output') ?: storage_path('app/private/cv/'.$pdf->filename()));
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $pdf->render($options));

        $this->components->info("CV written to {$path} ({$template->getLabel()}, ".strtoupper($options->paper).').');

        return self::SUCCESS;
    }
}
