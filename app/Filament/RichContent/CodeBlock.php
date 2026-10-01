<?php

namespace App\Filament\RichContent;

use App\Filament\Resources\Articles\Schemas\ArticleForm;
use App\Support\Content\ArticleDocument;
use Filament\Actions\Action;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\RichEditor\RichContentCustomBlock;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;

/**
 * A code snippet with a language and a file name. Plain code blocks (typed or pasted) work too;
 * this block is for when the snippet should show which file it comes from.
 */
class CodeBlock extends RichContentCustomBlock
{
    public static function getId(): string
    {
        return ArticleDocument::CODE_BLOCK;
    }

    public static function getLabel(): string
    {
        return 'Code with file name';
    }

    public static function configureEditorAction(Action $action): Action
    {
        return $action
            ->modalWidth(Width::ThreeExtraLarge)
            ->schema([
                Select::make('language')->options(ArticleForm::CODE_LANGUAGES)->default('ts')->required()->live(),
                TextInput::make('filename')->placeholder('journal.ts')->maxLength(100),
                CodeEditor::make('code')
                    ->language(fn (Get $get) => ArticleForm::editorLanguage($get('language')))
                    ->required(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function getPreviewLabel(array $config): string
    {
        $filename = is_string($config['filename'] ?? null) ? $config['filename'] : '';

        return $filename !== '' ? "Code: {$filename}" : 'Code';
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function toPreviewHtml(array $config): string
    {
        $code = is_string($config['code'] ?? null) ? $config['code'] : '';

        return '<pre style="white-space:pre-wrap;font-size:.8rem;margin:0">'.e($code).'</pre>';
    }
}
