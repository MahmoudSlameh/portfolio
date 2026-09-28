<?php

namespace App\Filament\Pages;

use App\Filament\Support\Fields;
use App\Filament\Support\SingletonPage;
use App\Models\SiteSetting;
use App\Support\Content\ContentCache;
use App\Support\Media\Favicons;
use App\Support\Media\MimeTypes;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use RuntimeException;
use UnitEnum;

/**
 * Site-wide settings: title, pages, contact recipient, SEO defaults and verification.
 */
class SiteSettings extends SingletonPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'SEO & settings';

    protected static ?string $title = 'SEO & settings';

    protected static ?string $slug = 'settings';

    public function getRecord(): SiteSetting
    {
        return SiteSetting::current();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rebuildCaches')
                ->label('Rebuild caches')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (): void {
                    ContentCache::flush();

                    Notification::make()->success()->title('Caches rebuilt')->body('The site, sitemap and feeds now reflect the latest content.')->send();
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Settings')->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('General')->icon(Heroicon::OutlinedGlobeAlt)->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('site_name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Appended to every page title.'),
                        TextInput::make('title_separator')->required()->maxLength(8),
                        TextInput::make('contact_recipient')
                            ->label('Send contact messages to')
                            ->email()
                            ->placeholder('Defaults to your public email')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                    Section::make('Pages')
                        ->description('Turn secondary pages off without deleting their content.')
                        ->columns(4)
                        ->schema(array_map(
                            fn (string $page): Toggle => Toggle::make("enabled_pages.{$page}")->label(Str::headline($page))->default(true),
                            SiteSetting::TOGGLEABLE_PAGES,
                        )),
                ]),
                Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
                    Section::make()->schema([
                        Textarea::make('meta_description')
                            ->label('Default meta description')
                            ->rows(2)
                            ->maxLength(300)
                            ->live(onBlur: true)
                            ->hint(fn (?string $state): string => mb_strlen((string) $state).' / 160'),
                        TextInput::make('twitter_handle')->label('X / Twitter handle')->prefix('@')->maxLength(50),
                        Toggle::make('indexable')
                            ->label('Allow search engines to index the site')
                            ->helperText('Turn off for staging copies — adds noindex everywhere and disallows crawling in robots.txt.'),
                    ]),
                    Section::make('Default social image')
                        ->description('1200×630 image used when a page has no image of its own.')
                        ->schema([Fields::image('default_og_image', ['1.91:1'], MimeTypes::RASTER)->hiddenLabel()]),
                    Section::make('Verification')->columns(2)->schema([
                        TextInput::make('google_site_verification')->label('Google Search Console')->maxLength(255),
                        TextInput::make('bing_site_verification')->label('Bing Webmaster Tools')->maxLength(255),
                    ]),
                ]),
                Tab::make('Advanced')->icon(Heroicon::OutlinedCommandLine)->schema([
                    Section::make()->schema([
                        Textarea::make('analytics_snippet')
                            ->label('Analytics snippet')
                            ->rows(4)
                            ->helperText('Injected before </head> on public pages (e.g. Plausible or Umami). Only paste code you trust.'),
                        SpatieMediaLibraryFileUpload::make('favicon')
                            ->collection('favicon')
                            ->acceptedFileTypes(['image/svg+xml', 'image/png'])
                            ->maxSize(512)
                            ->helperText('Square SVG, or a PNG of at least 192×192. Saving makes favicon.ico, a 192px PNG and the Apple touch icon from it (search engines use these). Falls back to /favicon.svg.'),
                    ]),
                ]),
            ]),
        ]);
    }

    protected function afterSave(): void
    {
        ContentCache::flush();

        try {
            Favicons::sync($this->getRecord()->refresh());
        } catch (RuntimeException $exception) {
            Notification::make()->warning()->title('Could not make the favicon icons')->body($exception->getMessage())->persistent()->send();
        }
    }
}
