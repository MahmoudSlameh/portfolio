<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use App\Filament\Support\Fields;
use App\Models\Article;
use App\Support\Media\MimeTypes;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\DateTimePicker;
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
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ArticleForm
{
    /**
     * @var array<string, string>
     */
    public const CODE_LANGUAGES = [
        'ts' => 'TypeScript', 'tsx' => 'TSX', 'js' => 'JavaScript', 'php' => 'PHP', 'sql' => 'SQL', 'bash' => 'Bash',
        'json' => 'JSON', 'go' => 'Go', 'python' => 'Python', 'css' => 'CSS', 'html' => 'HTML', 'yaml' => 'YAML',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Group::make([
                    Section::make()->columns(2)->schema([
                        Fields::slugSource('title', 'Title')->columnSpanFull(),
                        Fields::slug('title'),
                        TagsInput::make('tags')
                            ->suggestions(fn (): array => Article::query()->pluck('tags')->flatten()->unique()->sort()->values()->all())
                            ->reorderable(),
                        Textarea::make('excerpt')->rows(2)->maxLength(400)->columnSpanFull()->helperText('Shown on cards and as the default meta description.'),
                    ]),
                    Section::make('Body')->icon(Heroicon::OutlinedDocumentText)->schema([
                        self::body(),
                    ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Publishing')->icon(Heroicon::OutlinedRocketLaunch)->schema([
                        ToggleButtons::make('status')->options(ArticleStatus::class)->default(ArticleStatus::Draft->value)->inline()->required(),
                        DateTimePicker::make('published_at')->label('Publish date')->native(false)->helperText('Set automatically when published; a future date schedules it.'),
                        Text::make(fn (?Article $record): string => $record ? "≈ {$record->readingMinutes()} min read" : 'Reading time is calculated on save.')->color('gray'),
                        Select::make('projects')
                            ->label('Related projects')
                            ->relationship('projects', 'title')
                            ->multiple()
                            ->preload(),
                    ]),
                    Section::make('Cover')->icon(Heroicon::OutlinedPhoto)->schema([
                        Fields::image('cover', ['16:9'], MimeTypes::RASTER)->hiddenLabel(),
                        Fields::alt('cover_alt'),
                    ]),
                    Section::make('Images')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->description('Upload images here, save, then place them in the body with an Image block.')
                        ->collapsible()
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('body_images')
                                ->collection('body_images')
                                ->hiddenLabel()
                                ->multiple()
                                ->reorderable()
                                ->image()
                                ->maxSize(10 * 1024)
                                ->panelLayout('grid'),
                        ]),
                    Section::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->collapsed()->schema([
                        TextInput::make('meta_title')->maxLength(70)->live(onBlur: true)->hint(fn (?string $state): string => mb_strlen((string) $state).' / 60'),
                        Textarea::make('meta_description')->rows(2)->maxLength(255)->live(onBlur: true)->hint(fn (?string $state): string => mb_strlen((string) $state).' / 160'),
                    ]),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    public static function body(): Builder
    {
        return Builder::make('body')
            ->hiddenLabel()
            ->blocks([
                Block::make('paragraph')->icon(Heroicon::OutlinedBars3BottomLeft)->schema([
                    Textarea::make('text')->hiddenLabel()->rows(4)->required()->helperText('Inline **bold**, `code` and [links](https://…) are supported.'),
                ]),
                Block::make('heading')->icon(Heroicon::OutlinedHashtag)->schema([
                    TextInput::make('text')->hiddenLabel()->required()->maxLength(255),
                    TextInput::make('id')->label('Anchor')->alphaDash()->placeholder('Generated from the heading')->maxLength(100),
                ])->columns(2),
                Block::make('code')->icon(Heroicon::OutlinedCodeBracket)->schema([
                    Select::make('language')->options(self::CODE_LANGUAGES)->default('ts')->required()->live(),
                    TextInput::make('filename')->placeholder('journal.ts')->maxLength(100),
                    CodeEditor::make('code')
                        ->hiddenLabel()
                        ->language(fn (Get $get): ?Language => self::editorLanguage($get('language')))
                        ->required()
                        ->columnSpanFull(),
                ])->columns(2),
                Block::make('quote')->icon(Heroicon::OutlinedChatBubbleBottomCenterText)->schema([
                    Textarea::make('text')->hiddenLabel()->rows(2)->required(),
                    TextInput::make('cite')->placeholder('Who said it')->maxLength(255),
                ]),
                Block::make('list')->icon(Heroicon::OutlinedListBullet)->schema([
                    Repeater::make('items')->hiddenLabel()->defaultItems(1)->simple(TextInput::make('item')->required())->reorderable(),
                ]),
                Block::make('callout')->icon(Heroicon::OutlinedLightBulb)->schema([
                    TextInput::make('title')->required()->maxLength(255),
                    Textarea::make('text')->rows(2)->required(),
                ]),
                Block::make('image')->icon(Heroicon::OutlinedPhoto)->schema([
                    Select::make('media_uuid')
                        ->label('Image')
                        ->options(fn (?Article $record): array => $record?->getMedia('body_images')
                            ->mapWithKeys(fn (Media $media): array => [$media->uuid => '<span class="flex items-center gap-2"><img src="'.e($media->getUrl('thumb')).'" alt="" class="size-8 rounded object-cover">'.e($media->name).'</span>'])
                            ->all() ?? [])
                        ->allowHtml()
                        ->required()
                        ->helperText('Choose from the images uploaded in the Images section.'),
                    TextInput::make('alt')->label('Alt text')->required()->maxLength(255),
                    TextInput::make('caption')->maxLength(255),
                ]),
            ])
            ->collapsible()
            ->cloneable()
            ->blockNumbers(false)
            ->addActionLabel('Add block');
    }

    private static function editorLanguage(mixed $language): ?Language
    {
        return match ($language) {
            'ts', 'tsx', 'js' => Language::JavaScript,
            'php' => Language::Php,
            'sql' => Language::Sql,
            'json' => Language::Json,
            'go' => Language::Go,
            'python' => Language::Python,
            'css' => Language::Css,
            'html' => Language::Html,
            'yaml' => Language::Yaml,
            default => null,
        };
    }
}
