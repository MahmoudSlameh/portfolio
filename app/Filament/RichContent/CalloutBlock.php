<?php

namespace App\Filament\RichContent;

use App\Support\Content\ArticleDocument;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\RichContentCustomBlock;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * A highlighted note inside an article (the `callout` block every template renders).
 */
class CalloutBlock extends RichContentCustomBlock
{
    public static function getId(): string
    {
        return ArticleDocument::CALLOUT_BLOCK;
    }

    public static function getLabel(): string
    {
        return 'Callout';
    }

    public static function configureEditorAction(Action $action): Action
    {
        return $action
            ->modalDescription('A highlighted note. The text supports **bold**, *italic*, `code` and [links](https://…).')
            ->schema([
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('text')->rows(3)->required(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function getPreviewLabel(array $config): string
    {
        $title = is_string($config['title'] ?? null) ? $config['title'] : '';

        return $title !== '' ? "Callout: {$title}" : 'Callout';
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function toPreviewHtml(array $config): string
    {
        $text = is_string($config['text'] ?? null) ? $config['text'] : '';

        return '<div style="border-inline-start:3px solid currentColor;padding:.25rem .75rem;opacity:.85">'.e(ArticleDocument::plainText($text)).'</div>';
    }
}
