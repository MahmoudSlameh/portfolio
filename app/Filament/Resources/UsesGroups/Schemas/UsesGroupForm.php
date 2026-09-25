<?php

namespace App\Filament\Resources\UsesGroups\Schemas;

use App\Enums\UsesKind;
use App\Support\Media\MimeTypes;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UsesGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('title')->required()->maxLength(255)->placeholder('Hardware'),
                ToggleButtons::make('kind')->options(UsesKind::class)->default(UsesKind::Software->value)->inline()->required(),
            ]),
            Section::make('Items')->schema([
                Repeater::make('items')
                    ->hiddenLabel()
                    ->relationship()
                    ->orderColumn('sort_order')
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('url')->label('Link')->url()->maxLength(255),
                        Textarea::make('description')->rows(2)->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('image')->collection('image')->image()->acceptedFileTypes(MimeTypes::RASTER)->maxSize(5 * 1024)->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->collapsible()
                    ->addActionLabel('Add item'),
            ]),
        ]);
    }
}
