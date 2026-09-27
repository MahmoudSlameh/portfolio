<?php

namespace App\Filament\Pages;

use App\Models\StudioTemplate;
use App\Models\StudioTemplateVersion;
use App\Support\Content\ContentCache;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

/**
 * Every version of a studio template (docs/12-ai-templates.md, P10-01): preview any of them and
 * activate one to roll forward or back. Reached from the template's card on Appearance.
 */
class StudioTemplateVersions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'appearance/{template}/versions';

    protected static bool $shouldRegisterNavigation = false;

    public StudioTemplate $studioTemplate;

    /**
     * The route name would otherwise be derived from the slug and contain `{template}`, which the
     * Wayfinder route generator cannot turn into an identifier.
     */
    public static function getRelativeRouteName(Panel $panel): string
    {
        return 'studio-template-versions';
    }

    public function mount(string $template): void
    {
        $this->studioTemplate = StudioTemplate::query()->findOrFail($template);
    }

    public function getTitle(): string|Htmlable
    {
        return "Versions of {$this->studioTemplate->name}";
    }

    public function getSubheading(): string
    {
        return $this->studioTemplate->isLive()
            ? 'This template is live: activating a version changes what visitors see right away.'
            : 'Preview any version, then activate it. The active version is what Preview on Appearance shows.';
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        return [Appearance::getUrl() => 'Appearance', $this->studioTemplate->name, 'Versions'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->label('Back to Appearance')->icon(Heroicon::OutlinedArrowLeft)->color('gray')->url(Appearance::getUrl()),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([EmbeddedTable::make()]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->studioTemplate->versions()->getQuery()->with('parent')->reorder('number', 'desc'))
            ->paginated(false)
            ->columns([
                TextColumn::make('number')
                    ->label('Version')
                    ->formatStateUsing(fn (int $state): string => "v{$state}")
                    ->badge()
                    ->color(fn (StudioTemplateVersion $record): string => $this->isActive($record) ? 'success' : 'gray')
                    ->description(fn (StudioTemplateVersion $record): ?string => $this->isActive($record) ? 'Active' : null),
                TextColumn::make('prompt')
                    ->label('How it was made')
                    ->state(fn (StudioTemplateVersion $record): string => $this->origin($record))
                    ->description(fn (StudioTemplateVersion $record): ?string => $record->prompt !== null ? Str::limit($record->prompt, 140) : null)
                    ->wrap(),
                TextColumn::make('model')
                    ->label('Model')
                    ->placeholder('—')
                    ->description(fn (StudioTemplateVersion $record): ?string => $record->input_tokens !== null
                        ? Number::format($record->input_tokens + (int) $record->output_tokens).' tokens'
                        : null),
                TextColumn::make('notes')
                    ->label('CSS removed')
                    ->state(fn (StudioTemplateVersion $record): string => $record->notes === null ? '—' : (string) count($record->notes))
                    ->tooltip(fn (StudioTemplateVersion $record): ?string => $record->notes === null ? null : implode("\n", $record->notes)),
                TextColumn::make('created_at')->label('Created')->since()->dateTimeTooltip(),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->url(fn (StudioTemplateVersion $record): string => url('/?template='.$this->studioTemplate->templateId().'&version='.$record->number), shouldOpenInNewTab: true),
                Action::make('activate')
                    ->label('Activate')
                    ->icon(Heroicon::OutlinedBolt)
                    ->hidden(fn (StudioTemplateVersion $record): bool => $this->isActive($record))
                    ->requiresConfirmation()
                    ->modalHeading(fn (StudioTemplateVersion $record): string => "Activate version {$record->number}?")
                    ->modalDescription(fn (): string => $this->studioTemplate->isLive()
                        ? 'This template is live: visitors will see this version right away.'
                        : 'The template will show this version when it is previewed or activated.')
                    ->action(function (StudioTemplateVersion $record): void {
                        $this->studioTemplate->activate($record);
                        ContentCache::flush();

                        Notification::make()->success()->title("Version {$record->number} is active")->send();
                    }),
            ]);
    }

    private function isActive(StudioTemplateVersion $version): bool
    {
        return $this->studioTemplate->active_version_id === $version->id;
    }

    private function origin(StudioTemplateVersion $version): string
    {
        $parent = $version->getRelationValue('parent');
        $from = $parent instanceof StudioTemplateVersion ? " from v{$parent->number}" : '';

        return match (true) {
            $version->provider !== null => "Generated with AI{$from}",
            $parent instanceof StudioTemplateVersion => "Edited{$from}",
            default => 'Created',
        };
    }
}
