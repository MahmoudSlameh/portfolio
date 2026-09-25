<?php

namespace App\Filament\Pages;

use App\Enums\Template;
use App\Models\SiteSetting;
use App\Support\Content\ContentCache;
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
        $active = SiteSetting::current()->active_template;

        return $schema->components([
            Grid::make(['default' => 1, 'md' => 2, 'xl' => 3])->schema(array_map(
                fn (Template $template): Section => Section::make($template->getLabel())
                    ->description($template->getDescription())
                    ->icon($template === $active ? Heroicon::OutlinedCheckBadge : null)
                    ->iconColor('success')
                    ->afterHeader($template === $active ? [Text::make('Active')->badge()->color('success')] : [])
                    ->schema([
                        Image::make(asset($template->screenshot()), "{$template->getLabel()} template preview")
                            ->imageWidth('100%')
                            ->imageHeight('auto'),
                    ])
                    ->footer([
                        $this->activateAction()->arguments(['template' => $template->value]),
                        $this->previewAction($template),
                    ]),
                Template::cases(),
            )),
        ]);
    }

    public function activateAction(): Action
    {
        return Action::make('activate')
            ->label(fn (array $arguments): string => self::isActive($arguments) ? 'Active' : 'Activate')
            ->icon(fn (array $arguments): Heroicon => self::isActive($arguments) ? Heroicon::OutlinedCheck : Heroicon::OutlinedBolt)
            ->disabled(fn (array $arguments): bool => self::isActive($arguments))
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments): string => 'Activate the '.self::template($arguments)->getLabel().' template?')
            ->modalDescription('Every visitor will see the site in this template right away.')
            ->action(function (array $arguments): void {
                $template = self::template($arguments);

                SiteSetting::current()->update(['active_template' => $template]);
                ContentCache::flush();

                Notification::make()->success()->title("{$template->getLabel()} is now live")->send();
            });
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private static function template(array $arguments): Template
    {
        return Template::tryFrom((string) ($arguments['template'] ?? '')) ?? Template::default();
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    private static function isActive(array $arguments): bool
    {
        return SiteSetting::current()->active_template === self::template($arguments);
    }

    private function previewAction(Template $template): Action
    {
        return Action::make("preview_{$template->value}")
            ->label('Preview')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->url(url('/?template='.$template->value), shouldOpenInNewTab: true);
    }
}
