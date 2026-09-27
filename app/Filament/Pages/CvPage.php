<?php

namespace App\Filament\Pages;

use App\Enums\CvTemplate;
use App\Models\Profile;
use App\Support\Cv\CvOptions;
use App\Support\Cv\CvPdf;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

/**
 * Profile → CV (P11-04): pick an ATS-friendly template, preview it live, download it as a PDF, or make
 * it the file behind the site's "Download CV" link. The content comes from the rest of the panel.
 *
 * @property-read Schema $form
 */
class CvPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Profile';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'CV';

    protected static ?string $title = 'CV';

    protected static ?string $slug = 'cv';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function getSubheading(): string
    {
        return 'An ATS-friendly CV built from your profile, experience, education, skills, certifications and projects. Edit those pages to change what it says.';
    }

    public function mount(): void
    {
        $this->form->fill([
            'template' => CvTemplate::Classic->value,
            'paper' => 'a4',
            'include_projects' => true,
            'include_certifications' => true,
            'max_roles' => null,
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([EmbeddedSchema::make('form')]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Grid::make(['default' => 1, 'lg' => 5])->schema([
                Section::make('Template and options')->columnSpan(['lg' => 2])->schema([
                    Radio::make('template')
                        ->hiddenLabel()
                        ->options(CvTemplate::class)
                        ->required()
                        ->live(),
                    ToggleButtons::make('paper')
                        ->options(['a4' => 'A4', 'letter' => 'US Letter'])
                        ->inline()
                        ->required()
                        ->live(),
                    Toggle::make('include_projects')->label('Include projects (featured ones)')->live(),
                    Toggle::make('include_certifications')->label('Include certifications (current ones)')->live(),
                    TextInput::make('max_roles')
                        ->label('Most recent roles to include')
                        ->placeholder('All')
                        ->integer()
                        ->minValue(1)
                        ->maxValue(30)
                        ->live(onBlur: true),
                    Text::make(fn (): string => $this->resumeStatus())->color('gray'),
                ]),
                Section::make('Preview')
                    ->description('How the PDF will read. Page breaks appear in the PDF only.')
                    ->columnSpan(['lg' => 3])
                    ->schema([
                        View::make('filament.cv-preview')->viewData(fn (Get $get): array => ['src' => $this->previewUrl($get)]),
                    ]),
            ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Generate PDF')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(function (CvPdf $pdf): StreamedResponse {
                    $contents = $pdf->render($this->options());

                    return response()->streamDownload(function () use ($contents): void {
                        echo $contents;
                    }, $pdf->filename(), ['Content-Type' => 'application/pdf']);
                }),
            Action::make('openPreview')
                ->label('Open preview')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => $this->previewUrl(), shouldOpenInNewTab: true),
            Action::make('useAsResume')
                ->label('Use as my resume')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Use this CV as your resume?')
                ->modalDescription(fn (): string => Profile::current()->hasMedia('resume')
                    ? 'The PDF replaces your current resume file; the site\'s "Download CV" link serves it right away.'
                    : 'The site\'s "Download CV" link will serve this PDF.')
                ->action(function (CvPdf $pdf): void {
                    Profile::current()
                        ->addMediaFromString($pdf->render($this->options()))
                        ->usingFileName($pdf->filename())
                        ->usingName('CV ('.$this->options()->template->getLabel().')')
                        ->toMediaCollection('resume');

                    Notification::make()->success()->title('Your resume is updated')->body('Visitors now download this CV from the site.')->send();
                }),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $state
     */
    private function options(?array $state = null): CvOptions
    {
        return CvOptions::fromArray($state ?? $this->data ?? []);
    }

    private function previewUrl(?Get $get = null): string
    {
        $state = $get !== null
            ? ['template' => $get('template'), 'paper' => $get('paper'), 'include_projects' => $get('include_projects'), 'include_certifications' => $get('include_certifications'), 'max_roles' => $get('max_roles')]
            : ($this->data ?? []);

        return route('cv.preview', [
            'template' => $this->options($state)->template->value,
            'paper' => (string) ($state['paper'] ?? 'a4'),
            'include_projects' => ($state['include_projects'] ?? true) ? 1 : 0,
            'include_certifications' => ($state['include_certifications'] ?? true) ? 1 : 0,
            'max_roles' => $state['max_roles'] ?? null,
        ]);
    }

    private function resumeStatus(): string
    {
        $media = Profile::current()->getFirstMedia('resume');

        return $media === null
            ? 'Your site has no resume file yet: "Use as my resume" adds one.'
            : "Your site currently offers {$media->file_name} (updated {$media->updated_at?->diffForHumans()}).";
    }
}
