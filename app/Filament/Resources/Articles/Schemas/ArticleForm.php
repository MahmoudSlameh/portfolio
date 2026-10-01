<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use App\Filament\Support\Fields;
use App\Models\Article;
use App\Support\Media\MimeTypes;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

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
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
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
                    Section::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->collapsed()->schema([
                        TextInput::make('meta_title')->maxLength(70)->live(onBlur: true)->hint(fn (?string $state): string => mb_strlen((string) $state).' / 60'),
                        Textarea::make('meta_description')->rows(2)->maxLength(255)->live(onBlur: true)->hint(fn (?string $state): string => mb_strlen((string) $state).' / 160'),
                    ]),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    public static function body(): RichEditor
    {
        return RichEditor::make('body')
            ->hiddenLabel()
            ->toolbarButtons([
                ['bold', 'italic', 'code', 'link'],
                ['h2', 'h3'],
                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                ['attachFiles', 'customBlocks'],
                ['undo', 'redo'],
            ])
            ->fileAttachmentsAcceptedFileTypes(MimeTypes::RASTER)
            ->fileAttachmentsMaxSize(10 * 1024)
            ->placeholder('Write, or paste an article from a web page, Google Docs or Word…')
            ->helperText('Images can be added once the article is saved. Use "Import Markdown" to paste Markdown.')
            ->extraInputAttributes(['style' => 'min-height: 24rem'])
            ->hintAction(self::importMarkdownAction());
    }

    /**
     * Paste Markdown (e.g. from GitHub or dev.to) and turn it into editor content.
     */
    public static function importMarkdownAction(): Action
    {
        return Action::make('importMarkdown')
            ->label('Import Markdown')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->modalHeading('Import Markdown')
            ->modalDescription('Headings, lists, quotes, code blocks, links, bold and italic are kept. Raw HTML is removed.')
            ->modalSubmitActionLabel('Import')
            ->schema([
                Textarea::make('markdown')->hiddenLabel()->rows(16)->required()->extraInputAttributes(['class' => 'font-mono']),
                ToggleButtons::make('mode')
                    ->hiddenLabel()
                    ->options(['append' => 'Add to the end', 'replace' => 'Replace the body'])
                    ->default('append')
                    ->inline()
                    ->required(),
            ])
            ->action(function (array $data, RichEditor $component): void {
                $imported = self::markdownToDocument((string) $data['markdown'], $component);
                $current = $component->getState();
                $existing = is_array($current) && is_array($current['content'] ?? null) ? $current['content'] : [];

                $component->state([
                    'type' => 'doc',
                    'content' => $data['mode'] === 'replace' ? $imported['content'] : [...$existing, ...$imported['content']],
                ]);
            });
    }

    /**
     * @return array{type: string, content: list<array<string, mixed>>}
     */
    public static function markdownToDocument(string $markdown, RichEditor $editor): array
    {
        $html = Str::markdown($markdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
        /** @var array{type: string, content?: list<array<string, mixed>>} $document */
        $document = $editor->getTipTapEditor()->setContent($html)->getDocument();

        return ['type' => 'doc', 'content' => $document['content'] ?? []];
    }

    public static function editorLanguage(mixed $language): ?Language
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
