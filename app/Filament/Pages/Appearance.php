<?php

namespace App\Filament\Pages;

use App\Enums\StudioSource;
use App\Models\SiteSetting;
use App\Models\StudioTemplate;
use App\Models\StudioTemplateVersion;
use App\Support\Content\ContentCache;
use App\Support\Studio\SpecCatalogue;
use App\Support\Studio\SpecValidator;
use App\Support\Templates\TemplateDefinition;
use App\Support\Templates\TemplateManager;
use App\Support\Templates\TemplateRegistry;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Pick the public template, and manage studio templates (docs/12-ai-templates.md): create one from an
 * example, edit its spec as JSON, duplicate, delete. Every card has Preview and Activate.
 */
class Appearance extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Appearance';

    public function getSubheading(): string
    {
        return 'Visitors always see the active template. Previews are visible only to you while you are signed in.';
    }

    protected function getHeaderActions(): array
    {
        return [$this->createStudioAction()];
    }

    public function content(Schema $schema): Schema
    {
        $registry = app(TemplateRegistry::class);
        $studio = StudioTemplate::query()->with(['activeVersion', 'media'])->latest()->get();

        return $schema->components([
            Section::make('Built-in templates')
                ->description('Designed in code; see docs/templates/building-a-template.md to add your own.')
                ->schema([
                    Grid::make(['default' => 1, 'md' => 2, 'xl' => 3])->schema(array_map(
                        fn (TemplateDefinition $template): Section => $this->card($template),
                        array_values($registry->code()),
                    )),
                ]),
            Section::make('Studio templates')
                ->description('Designs stored as a Template Spec and rendered by the studio engine. Start from an example and edit the spec; AI generation comes next.')
                ->schema([
                    $studio->isEmpty()
                        ? Text::make('No studio templates yet. Use "New studio template" to start from an example.')->color('gray')
                        : Grid::make(['default' => 1, 'md' => 2, 'xl' => 3])->schema($studio->map(
                            fn (StudioTemplate $template): Section => $this->studioCard($template, $registry->find($template->templateId())),
                        )->all()),
                ]),
        ]);
    }

    private function card(TemplateDefinition $template): Section
    {
        $isActive = $this->isActive($template->id);

        return Section::make($template->label)
            ->key('template-'.self::key($template))
            ->description($template->description)
            ->icon($isActive ? Heroicon::OutlinedCheckBadge : null)
            ->iconColor('success')
            ->afterHeader($isActive ? [Text::make('Active')->badge()->color('success')] : [])
            ->schema([$this->screenshot($template)])
            ->footer([
                $this->activateAction($template),
                $this->previewAction($template),
            ]);
    }

    /**
     * A studio template card. Only ready templates (with an active version) can be previewed or
     * activated; drafts, generating and failed ones show their status.
     */
    private function studioCard(StudioTemplate $template, ?TemplateDefinition $definition): Section
    {
        $key = 'studio-'.$template->id;
        $isActive = $this->isActive($template->templateId());
        $version = $template->getRelationValue('activeVersion');
        $details = [];

        if ($definition !== null) {
            $details[] = $this->screenshot($definition);
        }

        if ($version instanceof StudioTemplateVersion) {
            $details[] = Text::make("Version {$version->number} · {$template->source->getLabel()}")->color('gray');
        }

        if ($template->status->isWorking()) {
            $details[] = Text::make("{$template->progress}% — ".($template->current_step ?? 'Working…'))->color('info');
        }

        if ($template->error !== null) {
            $details[] = Text::make($template->error)->color('danger');
        }

        return Section::make($template->name)
            ->key("template-{$key}")
            ->description($template->description)
            ->icon($isActive ? Heroicon::OutlinedCheckBadge : $template->status->getIcon())
            ->iconColor($isActive ? 'success' : $template->status->getColor())
            ->afterHeader([
                $isActive
                    ? Text::make('Active')->badge()->color('success')
                    : Text::make($template->status->getLabel())->badge()->color($template->status->getColor()),
            ])
            ->schema($details)
            ->footer(array_values(array_filter([
                $definition !== null ? $this->activateAction($definition) : null,
                $definition !== null ? $this->previewAction($definition) : null,
                $this->editStudioAction($template, $key),
                $version instanceof StudioTemplateVersion ? $this->duplicateStudioAction($template, $version, $key) : null,
                $this->deleteStudioAction($template, $key, $isActive),
            ])));
    }

    private function screenshot(TemplateDefinition $template): Text|Image
    {
        return $template->screenshotUrl() === null
            ? Text::make($template->isStudio() ? 'No screenshot yet · use Preview to see it' : 'No screenshot')->color('gray')
            : Image::make($template->screenshotUrl(), "{$template->label} template preview")->imageWidth('100%')->imageHeight('auto');
    }

    /**
     * One action per template (unique name) so each card's button mounts its own template.
     */
    private function activateAction(TemplateDefinition $template): Action
    {
        $isActive = fn (): bool => $this->isActive($template->id);

        return Action::make('activate_'.self::key($template))
            ->label(fn (): string => $isActive() ? 'Active' : 'Activate')
            ->icon(fn (): Heroicon => $isActive() ? Heroicon::OutlinedCheck : Heroicon::OutlinedBolt)
            ->disabled($isActive)
            ->requiresConfirmation()
            ->modalHeading("Activate the {$template->label} template?")
            ->modalDescription('Every visitor will see the site in this template right away.')
            ->action(function () use ($template): void {
                SiteSetting::current()->update(['active_template' => $template->id]);
                ContentCache::flush();

                Notification::make()->success()->title("{$template->label} is now live")->send();
                $this->refreshCards();
            });
    }

    private function previewAction(TemplateDefinition $template): Action
    {
        return Action::make('preview_'.self::key($template))
            ->label('Preview')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->url(url('/?template='.$template->id), shouldOpenInNewTab: true);
    }

    private function createStudioAction(): Action
    {
        $examples = SpecCatalogue::examples();

        return Action::make('create_studio')
            ->label('New studio template')
            ->icon(Heroicon::OutlinedPlus)
            ->modalHeading('New studio template')
            ->modalDescription('Start from an example design, then edit its spec or (soon) refine it with AI.')
            ->schema([
                TextInput::make('name')->required()->maxLength(SpecCatalogue::limit('name')),
                TextInput::make('description')->maxLength(255),
                Select::make('example')
                    ->label('Start from')
                    ->required()
                    ->options(array_map(fn (array $spec): string => (string) ($spec['name'] ?? ''), $examples))
                    ->default(array_key_first($examples)),
            ])
            ->action(function (array $data) use ($examples): void {
                $spec = $examples[$data['example']] ?? SpecCatalogue::example();
                $spec['name'] = $data['name'];

                $template = StudioTemplate::query()->create([
                    'name' => $data['name'],
                    'description' => $data['description'] ?: null,
                    'source' => StudioSource::Manual,
                ]);
                $template->addVersion($spec);

                Notification::make()->success()->title("{$template->name} is ready")->body('Preview it, edit its spec or activate it.')->send();
                $this->refreshCards();
            });
    }

    private function editStudioAction(StudioTemplate $template, string $key): Action
    {
        return Action::make("edit_{$key}")
            ->label('Edit')
            ->icon(Heroicon::OutlinedCodeBracket)
            ->color('gray')
            ->slideOver()
            ->modalWidth(Width::FiveExtraLarge)
            ->modalHeading("Edit {$template->name}")
            ->modalDescription('Saving validates the spec, sanitises its CSS and activates it as a new version. Reference: docs/12-ai-templates.md.')
            ->fillForm(function () use ($template): array {
                $version = $template->activeVersion()->first();

                return [
                    'name' => $template->name,
                    'description' => $template->description,
                    'spec' => json_encode($version->spec ?? SpecCatalogue::example(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ];
            })
            ->schema([
                TextInput::make('name')->required()->maxLength(SpecCatalogue::limit('name')),
                TextInput::make('description')->maxLength(255),
                CodeEditor::make('spec')
                    ->label('Template Spec (JSON)')
                    ->language(Language::Json)
                    ->required()
                    ->rules([fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                        $messages = array_map(ltrim(...), (new SpecValidator)->validateJson((string) $value)->messages());

                        // A field shows one message, so list every problem in it (the first few, then a count).
                        if ($messages !== []) {
                            $shown = array_slice($messages, 0, 5);
                            $more = count($messages) - count($shown);
                            $fail(implode(' · ', $shown).($more > 0 ? " · and {$more} more." : ''));
                        }
                    }]),
            ])
            ->modalSubmitActionLabel('Save as new version')
            ->action(function (array $data) use ($template): void {
                /** @var array<string, mixed> $spec */
                $spec = json_decode((string) $data['spec'], true);
                $template->update(['name' => $data['name'], 'description' => $data['description'] ?: null]);
                $version = $template->addVersion($spec, ['parent' => $template->activeVersion()->first()]);
                $template->activate($version);
                ContentCache::flush();

                Notification::make()->success()->title("Saved as version {$version->number}")->send();

                if ($version->notes !== null) {
                    Notification::make()->warning()->title('Some CSS was removed for safety')->body(implode("\n", $version->notes))->persistent()->send();
                }

                $this->refreshCards();
            });
    }

    private function duplicateStudioAction(StudioTemplate $template, StudioTemplateVersion $version, string $key): Action
    {
        return Action::make("duplicate_{$key}")
            ->label('Duplicate')
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->color('gray')
            ->action(function () use ($template, $version): void {
                $name = Str::limit("Copy of {$template->name}", SpecCatalogue::limit('name'), '');
                $spec = [...$version->spec, 'name' => $name];

                StudioTemplate::query()->create([
                    'name' => $name,
                    'description' => $template->description,
                    'source' => StudioSource::Manual,
                ])->addVersion($spec);

                Notification::make()->success()->title("{$name} created")->send();
                $this->refreshCards();
            });
    }

    private function deleteStudioAction(StudioTemplate $template, string $key, bool $isActive): Action
    {
        return Action::make("delete_{$key}")
            ->label('Delete')
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->disabled($isActive)
            ->tooltip($isActive ? 'Activate another template first.' : null)
            ->requiresConfirmation()
            ->modalHeading("Delete {$template->name}?")
            ->modalDescription('The template and all its versions are deleted. This cannot be undone.')
            ->action(function () use ($template): void {
                $template->delete();

                Notification::make()->success()->title("{$template->name} deleted")->send();
                $this->refreshCards();
            });
    }

    /**
     * The cards are built before an action runs; rebuild them so this response shows its result.
     */
    private function refreshCards(): void
    {
        app(TemplateRegistry::class)->forgetStudioTemplates();
        $this->cacheSchema('content', null);
    }

    private function isActive(string $templateId): bool
    {
        return app(TemplateManager::class)->active()->id === $templateId;
    }

    /**
     * A key safe for Livewire action names and schema keys (studio ids contain a colon).
     */
    public static function key(TemplateDefinition $template): string
    {
        return str_replace(':', '-', $template->id);
    }
}
