<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\Content\ContentCache;
use App\Support\Templates\TemplateDefinition;
use App\Support\Templates\TemplateManager;
use App\Support\Templates\TemplateRegistry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Pick the public template: one card per template with a screenshot, Activate and Preview.
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

    public function content(Schema $schema): Schema
    {
        $active = app(TemplateManager::class)->active()->id;

        return $schema->components([
            Grid::make(['default' => 1, 'md' => 2, 'xl' => 3])->schema(array_map(
                fn (TemplateDefinition $template): Section => Section::make($template->label)
                    ->key("template-{$template->id}")
                    ->description($template->description)
                    ->icon($template->id === $active ? Heroicon::OutlinedCheckBadge : null)
                    ->iconColor('success')
                    ->afterHeader($template->id === $active ? [Text::make('Active')->badge()->color('success')] : [])
                    ->schema([
                        Image::make(asset($template->screenshot), "{$template->label} template preview")
                            ->imageWidth('100%')
                            ->imageHeight('auto'),
                    ])
                    ->footer([
                        $this->activateAction($template),
                        $this->previewAction($template),
                    ]),
                array_values(app(TemplateRegistry::class)->all()),
            )),
        ]);
    }

    /**
     * One action per template (unique name) so each card's button mounts its own template.
     */
    private function activateAction(TemplateDefinition $template): Action
    {
        $isActive = fn (): bool => app(TemplateManager::class)->active()->id === $template->id;

        return Action::make("activate_{$template->id}")
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
            });
    }

    private function previewAction(TemplateDefinition $template): Action
    {
        return Action::make("preview_{$template->id}")
            ->label('Preview')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->url(url('/?template='.$template->id), shouldOpenInNewTab: true);
    }
}
