<?php

namespace App\Filament\Pages;

use App\Enums\AvailabilityStatus;
use App\Enums\StatusTone;
use App\Filament\Support\Fields;
use App\Filament\Support\SingletonPage;
use App\Models\Profile;
use App\Support\Media\MimeTypes;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * The owner's public profile: identity, portrait, bio, availability and highlights.
 */
class EditProfile extends SingletonPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Profile';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Profile & bio';

    protected static ?string $title = 'Profile & bio';

    protected static ?string $slug = 'bio';

    public function getRecord(): Profile
    {
        return Profile::current();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Profile')
                ->persistTabInQueryString()
                ->tabs([
                    Tab::make('Identity')->icon(Heroicon::OutlinedUserCircle)->schema([
                        Grid::make(['default' => 1, 'lg' => 3])->schema([
                            Group::make([
                                Section::make('Who you are')
                                    ->description('Shown in the hero, the header and search results.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')->required()->maxLength(255),
                                        TextInput::make('initials')
                                            ->maxLength(4)
                                            ->placeholder(fn (): string => $this->getRecord()->resolved_initials)
                                            ->helperText('Leave empty to derive them from your name.'),
                                        TextInput::make('role')
                                            ->label('Job title')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Senior Software Engineer'),
                                        TextInput::make('location')->placeholder('Damascus, Syria')->maxLength(255),
                                        Select::make('timezone')
                                            ->options(fn (): array => array_combine(timezone_identifiers_list(), timezone_identifiers_list()))
                                            ->searchable()
                                            ->required(),
                                        TextInput::make('timezone_label')->placeholder('EET · UTC+2/+3')->maxLength(255),
                                    ]),
                                Section::make('Contact')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('email')->label('Public email')->email()->maxLength(255),
                                        TextInput::make('phone')->tel()->maxLength(50),
                                    ]),
                            ])->columnSpan(['lg' => 2]),
                            Group::make([
                                Section::make('Portrait')
                                    ->icon(Heroicon::OutlinedPhoto)
                                    ->schema([
                                        Fields::image('portrait', ['3:4', '1:1'], MimeTypes::RASTER)->hiddenLabel()->imagePreviewHeight('320'),
                                        Fields::alt('portrait_alt', 'Portrait of me in soft window light'),
                                    ]),
                                Section::make('Résumé')
                                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                                    ->description('Optional PDF offered as a download.')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('resume')
                                            ->collection('resume')
                                            ->hiddenLabel()
                                            ->acceptedFileTypes(MimeTypes::PDF)
                                            ->maxSize(10 * 1024)
                                            ->downloadable()
                                            ->openable(),
                                    ]),
                            ])->columnSpan(['lg' => 1]),
                        ]),
                    ]),
                    Tab::make('Bio')->icon(Heroicon::OutlinedDocumentText)->schema([
                        Section::make('Short bio')->schema([
                            Textarea::make('headline')
                                ->rows(2)
                                ->maxLength(300)
                                ->helperText('One sentence under your name in the hero.'),
                            Textarea::make('summary')->rows(4)->helperText('A short paragraph used on the home page and as the default meta description.'),
                            TagsInput::make('focus_areas')
                                ->label('Focus areas')
                                ->reorderable()
                                ->placeholder('Add an area and press Enter')
                                ->helperText('Rotating keywords in the hero.'),
                        ]),
                        Section::make('Story')
                            ->description('The long bio on the About section — one paragraph per item.')
                            ->schema([
                                Repeater::make('story')
                                    ->hiddenLabel()
                                    ->simple(Textarea::make('paragraph')->rows(4)->required())
                                    ->reorderable()
                                    ->addActionLabel('Add paragraph'),
                            ]),
                    ]),
                    Tab::make('Availability')->icon(Heroicon::OutlinedSignal)->schema([
                        Section::make()->schema([
                            ToggleButtons::make('availability_status')
                                ->label('Status')
                                ->options(AvailabilityStatus::class)
                                ->inline()
                                ->required(),
                            TextInput::make('availability_label')->placeholder('Open to new roles')->maxLength(255),
                            Textarea::make('availability_note')->rows(2)->maxLength(255),
                        ]),
                    ]),
                    Tab::make('Highlights')->icon(Heroicon::OutlinedSparkles)->schema([
                        Section::make('Stats')
                            ->description('Big numbers in the hero ("8 years shipping software").')
                            ->schema([
                                Repeater::make('stats')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextInput::make('value')->required()->placeholder('8'),
                                        TextInput::make('label')->required()->placeholder('years shipping software'),
                                    ])
                                    ->columns(2)
                                    ->grid(['md' => 2])
                                    ->maxItems(6)
                                    ->reorderable()
                                    ->addActionLabel('Add stat'),
                            ]),
                        Section::make('Status strip')
                            ->description('"Building …", "Reading …", "Learning …".')
                            ->schema([
                                Repeater::make('status')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextInput::make('label')->required()->placeholder('Building'),
                                        TextInput::make('value')->required(),
                                        ToggleButtons::make('tone')->options(StatusTone::class)->inline()->default(StatusTone::Neutral->value)->required(),
                                    ])
                                    ->columns(3)
                                    ->reorderable()
                                    ->addActionLabel('Add status'),
                            ]),
                        Section::make('Principles')
                            ->schema([
                                Repeater::make('principles')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextInput::make('title')->required(),
                                        Textarea::make('body')->rows(2)->required(),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                    ->collapsible()
                                    ->reorderable()
                                    ->addActionLabel('Add principle'),
                            ]),
                    ]),
                    Tab::make('Changelog extras')->icon(Heroicon::OutlinedCodeBracketSquare)->schema([
                        Section::make()
                            ->description('Only used by the Changelog template.')
                            ->schema([
                                TextInput::make('current_version')->placeholder('v5.2.0')->maxLength(50),
                                TagsInput::make('latest_release.added')->label('Latest release — added')->reorderable(),
                                TagsInput::make('latest_release.changed')->label('Latest release — changed')->reorderable(),
                                TagsInput::make('latest_release.removed')->label('Latest release — removed')->reorderable(),
                            ]),
                    ]),
                ]),
        ]);
    }
}
